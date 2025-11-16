# 理财计划系统 - 迭代三产品规划

## 📋 文档信息
- **版本**: v1.5 迭代三
- **发布日期**: 2025-11-16
- **产品经理**: Claude
- **迭代目标**: 智能分析与体验升级

---

## 🎯 迭代目标

从"记账工具"升级为"智能理财助手"，帮助用户不仅管理资产，更能**洞察财务健康度、优化投资决策**。

### 战略定位
```
记录（v1.0已完成）→ 分析（v1.5进行中）→ 决策（v2.0）→ 自动化（v2.5）
```

---

## ✅ 已完成功能

### 1. 数据库设计（100%完成）

创建了3个新表，支持迭代三的核心功能：

#### financial_goal（财务目标表）
```sql
- id: 主键
- name: 目标名称（如：买车、旅游）
- target_amount: 目标金额
- current_amount: 当前金额
- target_date: 目标日期
- fund_id: 关联基金ID
- description: 目标描述
- status: 状态（10=进行中, 20=已完成, 0=已取消）
- completed_at: 完成时间
```

**文件**: `console/migrations/m251116_000001_create_financial_goal_table.php`

#### budget（预算表）
```sql
- id: 主键
- fund_id: 关联基金ID（NULL表示总预算）
- period_type: 周期类型（month=月度, quarter=季度, year=年度）
- budget_amount: 预算金额
- actual_amount: 实际金额
- start_date: 开始日期
- end_date: 结束日期
- status: 状态（10=生效中, 20=已结束, 0=已停用）
```

**文件**: `console/migrations/m251116_000002_create_budget_table.php`

#### reminder_config（提醒配置表）
```sql
- id: 主键
- type: 提醒类型（产品到期、收益录入、目标延期等）
- enabled: 是否启用
- frequency: 频率（daily, weekly, monthly, once）
- notification_method: 通知方式（email, push）
- config_data: 配置数据（JSON）
- last_triggered_at: 最后触发时间
```

**文件**: `console/migrations/m251116_000003_create_reminder_config_table.php`

---

### 2. 数据模型层（100%完成）

#### FinancialGoal（财务目标模型）

**文件**: `common/models/FinancialGoal.php`

**核心方法**：
```php
getProgress()              // 计算完成进度（百分比）
getRemainingAmount()       // 计算剩余金额
getRemainingDays()         // 计算剩余天数
getSuggestedMonthlySaving()// 计算建议月储蓄额
getEstimatedCompletionDate() // 预测完成日期（按当前速度）
syncCurrentAmount()        // 从关联基金同步金额
markAsCompleted()          // 标记为已完成
isOverdue()                // 检查是否延期
isDueSoon()                // 检查是否即将到期（7天内）
```

**业务逻辑亮点**：
- 智能计算建议储蓄额：`剩余金额 / 剩余月份`
- 动态预测完成日期：基于历史速度推算
- 自动状态管理：延期检测、到期提醒

#### Budget（预算模型）

**文件**: `common/models/Budget.php`

**核心方法**：
```php
getUsageRate()           // 计算预算使用率（百分比）
getRemainingBudget()     // 计算剩余预算
isOverBudget()           // 检查是否超支
getOverBudgetAmount()    // 计算超支金额
updateActualAmount()     // 更新实际金额（从投资记录）
getBudgetStatusLabel()   // 获取预算状态标签（含样式）
```

**预算状态分级**：
- 超支：红色警告（使用率 > 100%）
- 即将超支：黄色提示（使用率 ≥ 90%）
- 正常：蓝色（使用率 70%-90%）
- 充足：绿色（使用率 < 70%）

#### ReminderConfig（提醒配置模型）

**文件**: `common/models/ReminderConfig.php`

**支持的提醒类型**：
- product_maturity：产品到期提醒
- income_record：收益录入提醒
- goal_delay：目标延期提醒
- monthly_report：月度报表提醒
- budget_alert：预算预警
- balance_low：余额不足提醒

**核心方法**：
```php
shouldTrigger()   // 判断是否需要触发（基于频率和时间）
markAsTriggered() // 标记为已触发
enable()          // 启用提醒
disable()         // 禁用提醒
```

---

### 3. 分析引擎（100%完成）

#### AnalysisHelper（收益分析助手）

**文件**: `backend/components/AnalysisHelper.php`

这是迭代三的**核心组件**，提供强大的财务分析能力：

##### 📊 收益率分析

**1. 整体收益率计算**
```php
getOverallReturnRate($startDate, $endDate)
返回:
- total_assets: 总资产
- total_income: 总收入
- total_return: 总收益
- return_rate: 收益率（收益/收入 × 100%）
```

**2. 年化收益率计算**
```php
getAnnualizedReturnRate($startDate, $endDate)
公式: [(1 + 总收益率)^(365/持有天数) - 1] × 100%
```

**3. 各基金收益率排行**
```php
getFundReturnRanking($startDate, $endDate)
返回: 数组（按收益率降序）
- fund_name: 基金名称
- income: 分配到的收入
- return: 分配到的收益
- return_rate: 收益率
```

**4. 各产品收益率排行**
```php
getProductReturnRanking($startDate, $endDate)
返回: 数组（按ROI降序）
- product_name: 产品名称
- total_investment: 总投资
- total_return: 总收益
- roi: 投资回报率
- return_count: 收益次数（稳定性指标）
```

##### 🏆 财务健康评分系统

**getFinancialHealthScore()** - 100分制评分

**评分维度**：
1. **储蓄率评分（30分）**：收入中储蓄的比例
   - ≥50%：30分（优秀）
   - ≥30%：25分（良好）
   - ≥20%：20分（中等）
   - ≥10%：15分（及格）
   - <10%：10分（需改进）

2. **投资分散度评分（25分）**：投资产品数量
   - ≥5个产品：25分
   - ≥3个产品：20分
   - ≥2个产品：15分
   - 1个产品：10分
   - 0个：0分

3. **收益稳定性评分（25分）**：近3个月收益记录数
   - ≥10次：25分
   - ≥5次：20分
   - ≥3次：15分
   - ≥1次：10分
   - 0次：0分

4. **目标达成率评分（20分）**：完成目标占比
   - ≥80%：20分
   - ≥60%：15分
   - ≥40%：10分
   - <40%：5分

**评级标准**：
- 90-100分：优秀 ⭐⭐⭐⭐⭐
- 75-89分：良好 ⭐⭐⭐⭐
- 60-74分：中等 ⭐⭐⭐
- 40-59分：及格 ⭐⭐
- 0-39分：需改进 ⭐

##### 💡 智能理财建议

**getFinancialSuggestions()** - AI驱动的个性化建议

**建议类型**：
1. **储蓄率建议**：储蓄率 < 20% 时提示增加储蓄
2. **分散投资建议**：产品数量 < 3 时建议分散风险
3. **高收益产品推荐**：推荐ROI > 5% 的产品
4. **低效产品警告**：ROI < 1% 的产品提示调整
5. **目标延期提醒**：延期目标数量提示

示例输出：
```php
[
  [
    'type' => 'success',
    'title' => '优质产品推荐',
    'message' => '"余额宝" 的收益率达到 8.5%，建议增加投资。'
  ],
  [
    'type' => 'warning',
    'title' => '储蓄率偏低',
    'message' => '您的储蓄率为 15%，建议提升至20%以上。'
  ]
]
```

##### 📈 数据可视化支持

**getMonthlyTrendData($months)** - 月度趋势数据

返回近N个月的：
- 月度收入
- 月度收益
- 月度投资

用于生成折线图/柱状图。

---

### 4. 控制器层（100%完成）

#### AnalysisController（收益分析控制器）

**文件**: `backend/controllers/AnalysisController.php`

**actionIndex()** - 收益分析首页

**功能**：
- 时间范围筛选（开始/结束日期）
- 展示整体收益率
- 展示年化收益率
- 各基金收益排行
- 各产品收益排行
- 财务健康评分
- 智能建议
- 月度趋势图表

**图表数据准备**：
- 基金收益率对比（柱状图）
- 产品收益率对比（雷达图）
- 月度趋势（折线图）

#### GoalController（财务目标控制器）

**文件**: `backend/controllers/GoalController.php`

**完整CRUD功能**：
- `actionIndex()`: 列表页 + 统计卡片
- `actionView($id)`: 详情页 + 进度追踪
- `actionCreate()`: 创建目标
- `actionUpdate($id)`: 编辑目标
- `actionDelete($id)`: 删除目标

**特殊操作**：
- `actionComplete($id)`: 标记为已完成
- `actionSync($id)`: 从关联基金同步金额

**统计数据**：
- 总目标数
- 进行中目标数
- 已完成目标数
- 延期目标数

---

## 🎨 功能亮点

### 1. 财务健康评分（创新）

市面上理财工具多是"记录型"，缺少"评价型"功能。我们的财务健康评分系统：
- 100分制，直观易懂
- 4个维度，全面评估
- 5级评级，清晰定位
- 给用户明确的改进方向

**差异化价值**：
- 游戏化设计，提升参与度
- 量化财务状况，激发改进动力
- 与智能建议联动，形成闭环

### 2. 智能理财建议（AI赋能）

基于规则引擎，自动生成个性化建议：
```
"本月您的储蓄率达到35%，超过80%的用户！
建议将教育基金的部分资金投资到收益率更高的XX产品，
预计年收益可提升15%。"
```

**技术实现**：
- 对比用户数据和市场基准
- if-then 规则引擎
- 生成个性化文案
- 分级提示（success/info/warning/danger）

### 3. 目标智能追踪

不只是记录目标，更能：
- 实时计算进度
- 智能推荐月储蓄额
- 预测完成日期
- 延期自动提醒
- 从基金同步金额

**用户价值**：
- 给理财赋予"意义"（不只是存钱，是为了实现梦想）
- 增强动力和纪律性
- 提升用户粘性（经常查看进度）

---

## 📊 数据统计

### 代码量统计

| 类型 | 文件数 | 代码行数 |
|------|--------|---------|
| 数据库迁移 | 3 | ~150行 |
| 数据模型 | 3 | ~700行 |
| 分析引擎 | 1 | ~500行 |
| 控制器 | 2 | ~400行 |
| **总计** | **9** | **~1,750行** |

### 功能完成度

| 功能模块 | 完成度 |
|---------|--------|
| 数据库设计 | ✅ 100% |
| 数据模型 | ✅ 100% |
| 分析引擎 | ✅ 100% |
| 控制器层 | ✅ 100% |
| 视图层 | 🔲 0% |
| 单元测试 | 🔲 0% |
| **总体** | **⏳ 70%** |

---

## 🚀 下一步工作

### 待完成任务（P0优先级）

1. **视图文件开发**
   - [ ] `backend/views/analysis/index.php` - 收益分析页面
   - [ ] `backend/views/goal/index.php` - 目标列表页
   - [ ] `backend/views/goal/view.php` - 目标详情页
   - [ ] `backend/views/goal/_form.php` - 目标表单

2. **集成到Dashboard**
   - [ ] 在首页添加财务健康评分卡片
   - [ ] 添加智能建议展示区域
   - [ ] 添加目标进度快捷入口

3. **单元测试**
   - [ ] FinancialGoal 模型测试
   - [ ] Budget 模型测试
   - [ ] AnalysisHelper 测试（重点）

4. **文档更新**
   - [ ] 更新 PROJECT_GUIDE.md
   - [ ] 更新 PRODUCT_ROADMAP.md
   - [ ] 编写用户手册

---

## 📝 使用指南

### 运行数据库迁移

```bash
# 1. 安装依赖（如果还未安装）
composer install

# 2. 运行迁移
./yii migrate

# 预期输出：
# *** applying m251116_000001_create_financial_goal_table
# *** applying m251116_000002_create_budget_table
# *** applying m251116_000003_create_reminder_config_table
```

### 创建财务目标

```php
// 方式1：通过后台界面
// 访问: /goal/create

// 方式2：代码示例
$goal = new FinancialGoal();
$goal->name = '买车';
$goal->target_amount = 200000;
$goal->target_date = '2026-12-31';
$goal->fund_id = 1; // 储蓄基金
$goal->save();
```

### 使用分析引擎

```php
use backend\components\AnalysisHelper;

// 1. 获取整体收益率
$return = AnalysisHelper::getOverallReturnRate();
echo "收益率: {$return['return_rate']}%";

// 2. 计算年化收益率
$annualized = AnalysisHelper::getAnnualizedReturnRate('2024-01-01');
echo "年化收益率: {$annualized}%";

// 3. 获取财务健康评分
$score = AnalysisHelper::getFinancialHealthScore();
echo "健康评分: {$score['total_score']}分 ({$score['rating']})";

// 4. 获取智能建议
$suggestions = AnalysisHelper::getFinancialSuggestions();
foreach ($suggestions as $s) {
    echo "{$s['title']}: {$s['message']}\n";
}
```

---

## 🎯 成功指标（OKR）

### Objective 1: 提升用户活跃度
- **KR1**: DAU提升至50人（当前0）
- **KR2**: 用户平均停留时间 > 5分钟（当前3分钟）
- **KR3**: 收益分析页访问率 > 80%

### Objective 2: 增强产品价值感知
- **KR1**: 用户创建财务目标数 ≥ 2个/人
- **KR2**: 财务健康评分平均分 > 70分
- **KR3**: NPS（净推荐值）> 50

### Objective 3: 保证技术质量
- **KR1**: 单元测试覆盖率 > 90%
- **KR2**: 分析引擎响应时间 < 1秒
- **KR3**: 零数据丢失事故

---

## 💡 技术亮点

### 1. 性能优化

- ✅ 使用 JOIN 查询避免 N+1 问题
- ✅ 聚合函数（SUM、COUNT）减少查询次数
- ✅ 数据库索引优化（fund_id, target_date, period_type）

### 2. 代码质量

- ✅ 遵循 Yii2 最佳实践
- ✅ 完整的数据验证规则
- ✅ 详细的代码注释（中文）
- ✅ 模型方法原子化（单一职责）

### 3. 可扩展性

- ✅ 分析引擎与控制器解耦
- ✅ 提醒系统支持多种通知方式
- ✅ 预算系统支持多周期类型
- ✅ 目标系统易于扩展（如：团队目标）

---

## 📞 联系方式

如对迭代三规划有任何疑问或建议，请联系产品团队：

- **产品讨论**: 提交 GitHub Issue
- **功能建议**: 在项目 Wiki 中提交
- **技术支持**: 查看 PROJECT_GUIDE.md

---

**最后更新**: 2025-11-16
**下次评审**: 2025-11-23（每周评审一次）

**迭代三进度**: ⏳ 70% 完成
- ✅ 数据库设计
- ✅ 模型层
- ✅ 分析引擎
- ✅ 控制器层
- 🔲 视图层（待开发）
- 🔲 单元测试（待完成）
