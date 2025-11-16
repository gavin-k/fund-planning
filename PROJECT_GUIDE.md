# 理财计划系统 - 项目指南

## 项目概述

这是一个基于 Yii2 框架的个人理财管理系统，实现了"以基金方式管理财务"的理念。

### 核心功能

1. **基金管理**：创建和管理多个基金账户（储蓄基金、教育基金、旅游基金等）
2. **收入自动分配**：收入按预设比例自动分配到各基金
3. **投资管理**：从基金账户投资到各类理财产品
4. **收益分配**：理财产品收益按投资比例自动分配回各基金
5. **Dashboard**：总资产、收益统计、资金流向可视化

## 数据库设计

### 核心表结构

1. **fund** - 基金账户表
   - 存储基金名称、分配比例、当前余额等信息

2. **investment_product** - 理财产品表
   - 管理各类理财产品（支付宝、银行理财、股票、基金等）

3. **investment** - 投资记录表
   - 记录哪个基金投资了哪个产品及金额

4. **income** - 收入记录表
   - 记录收入及自动分配状态

5. **income_distribution** - 收入分配记录表
   - 详细记录收入如何分配到各基金

6. **return_record** - 收益记录表
   - 记录理财产品产生的收益

7. **return_distribution** - 收益分配记录表
   - 详细记录收益如何按比例分配回各基金

## 项目结构

```
fund-planning/
├── backend/                 # 后台管理
│   ├── controllers/
│   │   ├── FundController.php       # 基金管理 ✓
│   │   ├── ProductController.php    # 产品管理
│   │   ├── IncomeController.php     # 收入管理
│   │   ├── InvestmentController.php # 投资管理
│   │   └── ReturnController.php     # 收益管理
│   └── views/
│       ├── fund/                     # 基金视图 ✓
│       ├── product/
│       ├── income/
│       ├── investment/
│       └── return/
├── frontend/                # 前台用户界面
│   └── controllers/
│       └── FundController.php
├── common/
│   └── models/              # 数据模型 ✓
│       ├── Fund.php
│       ├── InvestmentProduct.php
│       ├── Investment.php
│       ├── Income.php
│       ├── IncomeDistribution.php
│       ├── ReturnRecord.php
│       └── ReturnDistribution.php
└── console/
    └── migrations/          # 数据库迁移 ✓
        ├── m251112_000001_create_fund_table.php
        ├── m251112_000002_create_investment_product_table.php
        ├── m251112_000003_create_investment_table.php
        ├── m251112_000004_create_income_table.php
        ├── m251112_000005_create_income_distribution_table.php
        ├── m251112_000006_create_return_record_table.php
        └── m251112_000007_create_return_distribution_table.php
```

## 核心业务逻辑

### 1. 收入自动分配算法（Income模型）

```php
// 当创建收入记录时，自动调用
public function distributeToFunds()
{
    // 1. 获取所有启用的基金
    // 2. 计算总分配比例
    // 3. 按比例分配收入到各基金
    // 4. 更新各基金余额
    // 5. 创建分配记录
}
```

**示例**：
- 收入：100,000元
- 储蓄基金(25%) → 25,000元
- 教育基金(8%) → 8,000元
- 旅游基金(10%) → 10,000元
- 流动资金(57%) → 57,000元

### 2. 收益按比例分配算法（ReturnRecord模型）

```php
// 当创建收益记录时，自动调用
public function distributeToFunds()
{
    // 1. 获取该产品的所有生效投资
    // 2. 按基金分组统计投资金额
    // 3. 计算各基金的投资占比
    // 4. 按占比分配收益
    // 5. 更新各基金余额
    // 6. 创建收益分配记录
}
```

**示例**：
- 产品A总投资：100,000元
  - 储蓄基金投入：60,000元（60%）
  - 教育基金投入：40,000元（40%）
- 产品A收益：5,000元
  - 分配给储蓄基金：3,000元（60%）
  - 分配给教育基金：2,000元（40%）

### 3. 投资管理

```php
// Investment模型包含：
- beforeSave()：投资前检查基金可用余额
- withdraw()：赎回投资，资金返回基金
```

## 安装和配置

### 1. 初始化项目

```bash
# 已完成 composer install
# 已完成 ./init --env=Development --overwrite=All
```

### 2. 配置数据库

数据库配置文件：`common/config/main-local.php`

当前配置为 PostgreSQL（可根据实际环境调整为 MySQL 或 SQLite）

### 3. 运行迁移

```bash
./yii migrate
```

### 4. 启动服务器

```bash
# 后台管理
cd backend/web
php -S localhost:8080

# 前台界面
cd frontend/web
php -S localhost:8081
```

## 使用流程

### 第一步：创建基金

1. 访问 `/fund/index`
2. 创建基金：储蓄基金(25%)、教育基金(8%)、旅游基金(10%)、流动资金(57%)

### 第二步：添加理财产品

1. 访问 `/product/index`
2. 创建产品：支付宝余额宝、银行理财等

### 第三步：记录收入

1. 访问 `/income/create`
2. 输入收入金额和日期
3. **系统自动按比例分配到各基金**

### 第四步：投资理财

1. 访问 `/investment/create`
2. 选择基金和产品，输入投资金额
3. 系统检查基金可用余额

### 第五步：记录收益

1. 访问 `/return/create`
2. 选择产品，输入收益金额
3. **系统自动按投资比例分配收益到各基金**

## 下一步开发任务

### 必要文件（需继续完成）

1. **产品管理**
   - backend/controllers/ProductController.php
   - backend/views/product/*

2. **收入管理**
   - backend/controllers/IncomeController.php
   - backend/views/income/*

3. **投资管理**
   - backend/controllers/InvestmentController.php
   - backend/views/investment/*

4. **收益管理**
   - backend/controllers/ReturnController.php
   - backend/views/return/*

5. **Dashboard**
   - 修改 backend/controllers/SiteController.php
   - 创建 backend/views/site/dashboard.php

6. **前台界面**
   - frontend/controllers/FundController.php
   - frontend/views/fund/*

### 增强功能（可选）

1. 数据统计和图表（使用 Chart.js）
2. 导入导出功能
3. 权限管理（RBAC）
4. 定期收益自动录入
5. 移动端适配
6. API接口

## 技术亮点

1. **自动化分配**：收入和收益全自动分配，无需手动计算
2. **事务处理**：所有金额变动都在数据库事务中完成，保证数据一致性
3. **精度控制**：使用 decimal 类型存储金额，避免浮点数精度问题
4. **余额验证**：投资前自动检查可用余额
5. **关联关系**：完善的 ActiveRecord 关联，方便数据查询

## 注意事项

1. 金额精度使用 `decimal(15, 2)`
2. 所有金额操作都使用数据库事务
3. 分配时最后一个基金使用剩余金额，避免精度损失
4. 投资时检查基金可用余额（当前余额 - 已投资金额）

## 贡献

欢迎提交 Issue 和 Pull Request！
