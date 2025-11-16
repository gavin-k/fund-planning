# 迭代一 (v0.5) - MVP 完整闭环 - 详细实施计划

## 📊 当前进度评估

| 模块 | 完成度 | 状态说明 |
|-----|--------|---------|
| **基础架构** | 100% | ✅ 数据库、模型、迁移全部完成 |
| **基金管理** | 100% | ✅ CRUD + 视图完整 |
| **产品管理** | 100% | ✅ CRUD + 视图完整 |
| **收入管理** | 100% | ✅ CRUD + 自动分配算法 + 视图 |
| **投资管理** | 100% | ✅ CRUD + 余额检查 + 赎回功能 + 视图 |
| **收益管理** | 100% | ✅ CRUD + 按比例分配算法 + 视图 |
| **单元测试** | 80% | ✅ 4个核心模型测试完成 |
| **Dashboard** | 10% | 🔲 只有基础框架，需要增强 |
| **导航优化** | 90% | ✅ 主菜单已更新，需微调 |
| **数据导出** | 0% | 🔲 待开发 |
| **整体进度** | **85%** | 🎯 剩余约 12 工时可完成 |

---

## 🎯 迭代一目标（重新调整）

**原目标**: 实现核心业务流程,用户可完成"收入→分配→投资→收益→再分配"的完整闭环

**当前状态**: ✅ 核心业务流程已实现，剩余工作为：
1. **Dashboard 增强** - 提供数据概览和快捷入口
2. **数据导出功能** - 防止数据丢失
3. **用户体验优化** - 完善导航、提示信息等
4. **测试和文档** - 补充测试用例和使用文档

---

## 📅 剩余工作详细计划

### 第 1 天: Dashboard 开发 (6h)

#### 任务 1.1: 设计 Dashboard 数据统计 (2h)

**目标**: 在 SiteController 中实现数据统计逻辑

**文件**: `backend/controllers/SiteController.php`

**实现内容**:
```php
public function actionIndex()
{
    // 1. 总资产统计
    $totalAssets = Fund::find()->sum('balance');

    // 2. 各基金余额
    $funds = Fund::find()
        ->select(['id', 'name', 'balance', 'allocation_percentage'])
        ->orderBy(['allocation_percentage' => SORT_DESC])
        ->all();

    // 3. 总投资金额
    $totalInvestment = Investment::find()
        ->where(['status' => Investment::STATUS_ACTIVE])
        ->sum('amount');

    // 4. 本月收入
    $monthlyIncome = Income::find()
        ->where(['>=', 'income_date', date('Y-m-01')])
        ->sum('amount');

    // 5. 本月收益
    $monthlyReturn = ReturnRecord::find()
        ->where(['>=', 'return_date', date('Y-m-01')])
        ->sum('amount');

    // 6. 最近交易记录（最近10条）
    $recentIncomes = Income::find()
        ->orderBy(['income_date' => SORT_DESC])
        ->limit(5)
        ->all();

    $recentInvestments = Investment::find()
        ->orderBy(['created_at' => SORT_DESC])
        ->limit(5)
        ->all();

    return $this->render('index', [
        'totalAssets' => $totalAssets,
        'funds' => $funds,
        'totalInvestment' => $totalInvestment,
        'monthlyIncome' => $monthlyIncome,
        'monthlyReturn' => $monthlyReturn,
        'recentIncomes' => $recentIncomes,
        'recentInvestments' => $recentInvestments,
    ]);
}
```

**验收标准**:
- ✅ 能正确计算总资产
- ✅ 能统计各基金余额
- ✅ 能计算本月收入和收益
- ✅ 能获取最近交易记录

---

#### 任务 1.2: 创建 Dashboard 视图 (4h)

**目标**: 创建美观实用的 Dashboard 界面

**文件**: `backend/views/site/index.php`

**设计布局**:

```
┌─────────────────────────────────────────────────────┐
│  总资产概览区                                         │
│  ┌──────────┬──────────┬──────────┬──────────┐      │
│  │ 总资产    │ 总投资   │ 本月收入  │ 本月收益  │      │
│  │ ¥100,000 │ ¥60,000  │ ¥10,000  │ ¥500     │      │
│  └──────────┴──────────┴──────────┴──────────┘      │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│  各基金余额一览                                       │
│  ┌────────────────────────────────────────────┐     │
│  │ 储蓄基金    ¥30,000  [========] 30%         │     │
│  │ 教育基金    ¥10,000  [==] 10%              │     │
│  │ 旅游基金    ¥8,000   [=] 8%                │     │
│  │ 流动资金    ¥52,000  [============] 52%     │     │
│  └────────────────────────────────────────────┘     │
└─────────────────────────────────────────────────────┘

┌──────────────────┬─────────────────────────────────┐
│  最近收入记录     │  最近投资记录                     │
│  2025-11-15      │  2025-11-14                      │
│  ¥10,000         │  储蓄基金 → 余额宝 ¥5,000        │
│                  │                                  │
│  2025-11-01      │  2025-11-10                      │
│  ¥8,000          │  教育基金 → 基金A ¥3,000         │
└──────────────────┴─────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│  快捷操作                                            │
│  [+ 记录收入] [+ 新建投资] [+ 记录收益] [📊 查看报表] │
└─────────────────────────────────────────────────────┘
```

**实现代码**:

```php
<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = '理财计划 Dashboard';
?>

<div class="site-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <!-- 总资产概览区 -->
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="glyphicon glyphicon-piggy-bank" style="font-size: 48px;"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div style="font-size: 24px;">¥<?= number_format($totalAssets, 2) ?></div>
                            <div>总资产</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="glyphicon glyphicon-briefcase" style="font-size: 48px;"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div style="font-size: 24px;">¥<?= number_format($totalInvestment, 2) ?></div>
                            <div>总投资</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="glyphicon glyphicon-arrow-down" style="font-size: 48px;"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div style="font-size: 24px;">¥<?= number_format($monthlyIncome, 2) ?></div>
                            <div>本月收入</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="glyphicon glyphicon-arrow-up" style="font-size: 48px;"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div style="font-size: 24px;">¥<?= number_format($monthlyReturn, 2) ?></div>
                            <div>本月收益</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 各基金余额一览 -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="glyphicon glyphicon-folder-open"></i> 各基金余额一览
            </h3>
        </div>
        <div class="panel-body">
            <?php foreach ($funds as $fund): ?>
                <?php
                    $percentage = $totalAssets > 0 ? ($fund->balance / $totalAssets * 100) : 0;
                ?>
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <strong><?= Html::encode($fund->name) ?></strong>
                    </div>
                    <div class="col-md-3 text-right">
                        ¥<?= number_format($fund->balance, 2) ?>
                    </div>
                    <div class="col-md-6">
                        <div class="progress">
                            <div class="progress-bar progress-bar-success"
                                 role="progressbar"
                                 style="width: <?= $percentage ?>%">
                                <?= number_format($percentage, 1) ?>%
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 最近交易记录 -->
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-list-alt"></i> 最近收入记录
                    </h3>
                </div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>日期</th>
                                <th>金额</th>
                                <th>备注</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentIncomes as $income): ?>
                                <tr>
                                    <td><?= $income->income_date ?></td>
                                    <td class="text-success">
                                        <strong>+¥<?= number_format($income->amount, 2) ?></strong>
                                    </td>
                                    <td><?= Html::encode($income->note) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?= Html::a('查看全部 »', ['income/index'], ['class' => 'btn btn-sm btn-default']) ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-transfer"></i> 最近投资记录
                    </h3>
                </div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>日期</th>
                                <th>基金</th>
                                <th>产品</th>
                                <th>金额</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentInvestments as $investment): ?>
                                <tr>
                                    <td><?= date('Y-m-d', $investment->created_at) ?></td>
                                    <td><?= Html::encode($investment->fund->name) ?></td>
                                    <td><?= Html::encode($investment->product->name) ?></td>
                                    <td>¥<?= number_format($investment->amount, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?= Html::a('查看全部 »', ['investment/index'], ['class' => 'btn btn-sm btn-default']) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 快捷操作 -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="glyphicon glyphicon-flash"></i> 快捷操作
            </h3>
        </div>
        <div class="panel-body text-center">
            <?= Html::a(
                '<i class="glyphicon glyphicon-plus"></i> 记录收入',
                ['income/create'],
                ['class' => 'btn btn-success btn-lg', 'style' => 'margin: 5px;']
            ) ?>
            <?= Html::a(
                '<i class="glyphicon glyphicon-transfer"></i> 新建投资',
                ['investment/create'],
                ['class' => 'btn btn-info btn-lg', 'style' => 'margin: 5px;']
            ) ?>
            <?= Html::a(
                '<i class="glyphicon glyphicon-arrow-up"></i> 记录收益',
                ['return/create'],
                ['class' => 'btn btn-warning btn-lg', 'style' => 'margin: 5px;']
            ) ?>
            <?= Html::a(
                '<i class="glyphicon glyphicon-stats"></i> 管理基金',
                ['fund/index'],
                ['class' => 'btn btn-primary btn-lg', 'style' => 'margin: 5px;']
            ) ?>
        </div>
    </div>
</div>
```

**验收标准**:
- ✅ Dashboard 布局美观清晰
- ✅ 数据卡片显示准确
- ✅ 基金余额进度条正常显示
- ✅ 最近交易记录正常展示
- ✅ 快捷操作按钮可点击跳转

---

### 第 2 天: 数据导出功能 (4h)

#### 任务 2.1: 实现基金数据导出 (2h)

**目标**: 支持导出基金列表和明细为 CSV 格式

**文件**: `backend/controllers/FundController.php`

**实现内容**:
```php
/**
 * 导出基金数据为 CSV
 */
public function actionExport()
{
    $query = Fund::find();

    // 设置响应头
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=funds_' . date('Y-m-d') . '.csv');

    // 打开输出流
    $output = fopen('php://output', 'w');

    // 输出 BOM 头，解决 Excel 打开中文乱码问题
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // 输出表头
    fputcsv($output, ['基金名称', '分配比例(%)', '当前余额', '已投资金额', '可用余额', '状态']);

    // 输出数据
    foreach ($query->each() as $fund) {
        fputcsv($output, [
            $fund->name,
            $fund->allocation_percentage,
            $fund->balance,
            $fund->getInvestedAmount(),
            $fund->getAvailableBalance(),
            $fund->status == Fund::STATUS_ACTIVE ? '启用' : '禁用',
        ]);
    }

    fclose($output);
    exit;
}
```

**在视图中添加导出按钮**:
```php
// backend/views/fund/index.php
<?= Html::a(
    '<i class="glyphicon glyphicon-export"></i> 导出数据',
    ['export'],
    ['class' => 'btn btn-info']
) ?>
```

**验收标准**:
- ✅ 能成功导出 CSV 文件
- ✅ Excel 打开无乱码
- ✅ 数据完整准确

---

#### 任务 2.2: 实现收入和收益数据导出 (2h)

**目标**: 支持导出收入、投资、收益记录

**实现位置**:
- `backend/controllers/IncomeController.php`
- `backend/controllers/InvestmentController.php`
- `backend/controllers/ReturnController.php`

**实现逻辑**: 与基金导出类似，根据各自的字段调整

**验收标准**:
- ✅ 所有核心数据都能导出
- ✅ 导出格式统一
- ✅ 包含必要的关联信息（如基金名称、产品名称）

---

### 第 3 天: 用户体验优化和测试 (4h)

#### 任务 3.1: 优化用户体验 (2h)

**1. 完善表单验证提示**
- 在所有表单中添加友好的错误提示
- 添加必填字段的 `*` 标记
- 统一日期选择器格式

**2. 添加操作确认**
```php
// 删除操作添加确认
<?= Html::a('删除', ['delete', 'id' => $model->id], [
    'class' => 'btn btn-danger',
    'data' => [
        'confirm' => '确定要删除这条记录吗？删除后将无法恢复！',
        'method' => 'post',
    ],
]) ?>
```

**3. 添加成功/失败消息**
```php
// 在控制器操作后添加
Yii::$app->session->setFlash('success', '操作成功！');
Yii::$app->session->setFlash('error', '操作失败，请重试。');
```

**4. 优化列表分页**
- 设置合理的每页显示数量（默认 20 条）
- 添加排序功能

**验收标准**:
- ✅ 所有表单都有友好提示
- ✅ 重要操作有二次确认
- ✅ 操作后有反馈消息
- ✅ 列表分页和排序正常

---

#### 任务 3.2: 补充测试用例 (2h)

**目标**: 补充缺失的测试用例，提高覆盖率

**1. 补充 ProductTest.php**
```php
// common/tests/unit/models/ProductTest.php
- 测试产品创建
- 测试产品验证规则
- 测试产品与投资的关联关系
```

**2. 补充 IncomeDistributionTest.php**
```php
- 测试分配记录创建
- 测试与收入和基金的关联
```

**3. 运行测试**
```bash
vendor/bin/codecept run unit
```

**验收标准**:
- ✅ 新增测试用例通过
- ✅ 单元测试覆盖率 > 80%
- ✅ 所有核心业务逻辑都有测试

---

### 第 4 天: 文档和发布准备 (2h)

#### 任务 4.1: 更新使用文档 (1h)

**目标**: 更新 PROJECT_GUIDE.md 和 README.md

**更新内容**:
1. **README.md**: 添加快速开始指南
2. **PROJECT_GUIDE.md**: 更新功能完成状态
3. 创建 **USER_GUIDE.md**: 用户使用手册
   - 如何创建基金
   - 如何记录收入
   - 如何进行投资
   - 如何记录收益
   - 如何查看报表和导出数据

**验收标准**:
- ✅ 文档内容完整准确
- ✅ 新用户能根据文档快速上手
- ✅ 包含常见问题解答

---

#### 任务 4.2: 发布准备和验收 (1h)

**1. 功能验收清单**:
- [ ] 基金管理 CRUD 完整
- [ ] 产品管理 CRUD 完整
- [ ] 收入自动分配正常
- [ ] 投资余额检查正常
- [ ] 收益按比例分配正常
- [ ] Dashboard 数据展示准确
- [ ] 数据导出功能正常
- [ ] 所有单元测试通过

**2. 数据库迁移验证**:
```bash
./yii migrate/fresh
# 验证所有表都正确创建
```

**3. 创建演示数据**:
```bash
# 创建种子数据脚本
./yii seed/demo
```

**4. 性能检查**:
- Dashboard 加载时间 < 2s
- 列表页加载时间 < 1s
- 无明显性能瓶颈

**验收标准**:
- ✅ 所有功能验收项通过
- ✅ 数据库迁移无问题
- ✅ 演示数据可用
- ✅ 性能达标

---

## 📋 工时汇总

| 任务 | 预计工时 | 优先级 |
|-----|---------|--------|
| Dashboard 数据统计 | 2h | P0 |
| Dashboard 视图设计 | 4h | P0 |
| 数据导出功能 | 4h | P1 |
| 用户体验优化 | 2h | P1 |
| 补充测试用例 | 2h | P1 |
| 文档更新 | 1h | P2 |
| 发布准备验收 | 1h | P0 |
| **总计** | **16h** | - |

**实际可用工时**: 约 12h（考虑 85% 已完成）

---

## ✅ 验收标准

### 功能完整性
- ✅ 用户可以完成完整的理财管理流程
- ✅ 收入自动分配准确率 100%
- ✅ 收益按比例分配准确率 100%
- ✅ 所有金额计算精度误差 < 0.01 元

### 用户体验
- ✅ Dashboard 加载时间 < 2 秒
- ✅ 操作流程清晰直观
- ✅ 所有重要操作有确认提示
- ✅ 表单验证友好完整

### 数据安全
- ✅ 支持数据导出备份
- ✅ 所有金额操作使用事务
- ✅ 余额检查机制完善

### 代码质量
- ✅ 单元测试覆盖率 > 80%
- ✅ 所有单元测试通过
- ✅ 代码符合 Yii2 规范

---

## 🚀 发布计划

**发布版本**: v0.5-beta

**发布日期**: 完成上述任务后 1 周内

**发布内容**:
- 完整的核心业务功能
- Dashboard 数据概览
- 数据导出功能
- 用户使用文档

**发布渠道**:
- GitHub Release
- 内部测试组

**下一步**:
- 收集用户反馈
- 规划迭代二功能
- 开始可视化图表开发

---

## 📊 风险和应对

| 风险 | 影响 | 应对措施 |
|-----|------|---------|
| Dashboard 开发超时 | 中 | 可简化初版设计，后续迭代增强 |
| 测试用例编写困难 | 低 | 参考已有测试用例，复用测试逻辑 |
| 导出功能兼容性问题 | 低 | 重点测试 Excel 打开，确保 UTF-8 BOM |

---

**文档版本**: v1.0
**最后更新**: 2025-11-16
**负责人**: 产品开发团队
