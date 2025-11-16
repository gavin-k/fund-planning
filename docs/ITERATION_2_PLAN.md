# 迭代二 (v1.0) - 可视化增强 - 详细实施计划

## 📋 迭代目标

**核心目标**: 提升用户体验，增加数据可视化和前台界面，让用户能更直观地了解资产状况

**成功标准**:
- ✅ 用户能通过图表直观查看资产分布和收益趋势
- ✅ 提供友好的前台界面供日常查看
- ✅ 支持移动端访问和响应式布局
- ✅ Dashboard 加载时间 < 1.5s

**时间**: 2 周 (80 工时)

---

## 📅 详细任务分解

### 第 1-2 天: 图表库集成和基础图表 (16h)

#### 任务 1.1: 引入 Chart.js 图表库 (2h)

**目标**: 在项目中集成 Chart.js 图表库

**步骤**:

**1. 安装 Chart.js (通过 CDN)**

在 `backend/views/layouts/main.php` 的 `<head>` 部分添加:
```php
<?php
// 在 registerCss 前添加
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', [
    'position' => \yii\web\View::POS_HEAD
]);
?>
```

**2. 创建图表工具类**

文件: `backend/components/ChartHelper.php`
```php
<?php
namespace backend\components;

use common\models\Fund;
use common\models\Income;
use common\models\ReturnRecord;
use common\models\Investment;

class ChartHelper
{
    /**
     * 获取基金余额饼图数据
     */
    public static function getFundBalanceChartData()
    {
        $funds = Fund::find()
            ->where(['status' => Fund::STATUS_ACTIVE])
            ->all();

        $labels = [];
        $data = [];
        $backgroundColors = [
            'rgba(255, 99, 132, 0.8)',   // 红
            'rgba(54, 162, 235, 0.8)',   // 蓝
            'rgba(255, 206, 86, 0.8)',   // 黄
            'rgba(75, 192, 192, 0.8)',   // 青
            'rgba(153, 102, 255, 0.8)',  // 紫
            'rgba(255, 159, 64, 0.8)',   // 橙
        ];

        foreach ($funds as $index => $fund) {
            $labels[] = $fund->name;
            $data[] = (float)$fund->balance;
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'data' => $data,
                'backgroundColor' => array_slice($backgroundColors, 0, count($data)),
            ]],
        ];
    }

    /**
     * 获取近12个月收益趋势折线图数据
     */
    public static function getMonthlyReturnTrendData()
    {
        $labels = [];
        $incomeData = [];
        $returnData = [];

        // 获取最近12个月
        for ($i = 11; $i >= 0; $i--) {
            $date = date('Y-m', strtotime("-$i month"));
            $labels[] = $date;

            // 统计该月收入
            $monthIncome = Income::find()
                ->where(['like', 'income_date', $date])
                ->sum('amount');
            $incomeData[] = (float)($monthIncome ?: 0);

            // 统计该月收益
            $monthReturn = ReturnRecord::find()
                ->where(['like', 'return_date', $date])
                ->sum('amount');
            $returnData[] = (float)($monthReturn ?: 0);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => '收入',
                    'data' => $incomeData,
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'tension' => 0.4,
                ],
                [
                    'label' => '收益',
                    'data' => $returnData,
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'tension' => 0.4,
                ],
            ],
        ];
    }

    /**
     * 获取投资产品分布柱状图数据
     */
    public static function getInvestmentDistributionData()
    {
        $sql = "
            SELECT p.name, SUM(i.amount) as total
            FROM investment i
            JOIN investment_product p ON i.product_id = p.id
            WHERE i.status = :status
            GROUP BY p.id, p.name
            ORDER BY total DESC
        ";

        $investments = \Yii::$app->db->createCommand($sql)
            ->bindValue(':status', Investment::STATUS_ACTIVE)
            ->queryAll();

        $labels = [];
        $data = [];

        foreach ($investments as $inv) {
            $labels[] = $inv['name'];
            $data[] = (float)$inv['total'];
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => '投资金额',
                'data' => $data,
                'backgroundColor' => 'rgba(54, 162, 235, 0.8)',
            ]],
        ];
    }
}
```

**验收标准**:
- ✅ Chart.js 库成功加载
- ✅ ChartHelper 类创建完成
- ✅ 数据方法能正确返回图表数据

---

#### 任务 1.2: Dashboard 集成饼图和折线图 (6h)

**目标**: 在 Dashboard 中显示基金余额饼图和收益趋势图

**文件**: `backend/controllers/SiteController.php`

**更新 actionIndex**:
```php
use backend\components\ChartHelper;

public function actionIndex()
{
    // ... 之前的统计数据 ...

    // 图表数据
    $fundChartData = json_encode(ChartHelper::getFundBalanceChartData());
    $trendChartData = json_encode(ChartHelper::getMonthlyReturnTrendData());
    $investmentChartData = json_encode(ChartHelper::getInvestmentDistributionData());

    return $this->render('index', [
        // ... 之前的参数 ...
        'fundChartData' => $fundChartData,
        'trendChartData' => $trendChartData,
        'investmentChartData' => $investmentChartData,
    ]);
}
```

**文件**: `backend/views/site/index.php`

**添加图表展示区域**:
```php
<!-- 在总资产概览区后添加 -->

<!-- 数据可视化区域 -->
<div class="row">
    <!-- 基金余额饼图 -->
    <div class="col-md-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="glyphicon glyphicon-stats"></i> 基金余额分布
                </h3>
            </div>
            <div class="panel-body">
                <canvas id="fundBalanceChart" style="max-height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <!-- 投资产品分布柱状图 -->
    <div class="col-md-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="glyphicon glyphicon-briefcase"></i> 投资产品分布
                </h3>
            </div>
            <div class="panel-body">
                <canvas id="investmentDistChart" style="max-height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <!-- 收益趋势折线图（占8列宽度） -->
    <div class="col-md-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="glyphicon glyphicon-signal"></i> 月度概览
                </h3>
            </div>
            <div class="panel-body">
                <canvas id="returnTrendChart" style="max-height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- 收益趋势折线图（单独一行，更宽） -->
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="glyphicon glyphicon-signal"></i> 近12个月收入收益趋势
                </h3>
            </div>
            <div class="panel-body">
                <canvas id="fullTrendChart" style="max-height: 250px;"></canvas>
            </div>
        </div>
    </div>
</div>

<?php
// 注册图表初始化 JavaScript
$this->registerJs(<<<JS
// 基金余额饼图
const fundCtx = document.getElementById('fundBalanceChart').getContext('2d');
new Chart(fundCtx, {
    type: 'pie',
    data: $fundChartData,
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        if (label) {
                            label += ': ';
                        }
                        label += '¥' + context.parsed.toLocaleString();
                        return label;
                    }
                }
            }
        }
    }
});

// 投资产品分布柱状图
const invCtx = document.getElementById('investmentDistChart').getContext('2d');
new Chart(invCtx, {
    type: 'bar',
    data: $investmentChartData,
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false,
            },
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '¥' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// 收益趋势折线图（小版）
const trendCtx = document.getElementById('returnTrendChart').getContext('2d');
new Chart(trendCtx, {
    type: 'line',
    data: $trendChartData,
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
            },
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '¥' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// 收益趋势折线图（大版）
const fullTrendCtx = document.getElementById('fullTrendChart').getContext('2d');
new Chart(fullTrendCtx, {
    type: 'line',
    data: $trendChartData,
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top',
            },
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '¥' + value.toLocaleString();
                    }
                }
            }
        }
    }
});
JS
, \yii\web\View::POS_READY);
?>
```

**验收标准**:
- ✅ 饼图正确显示基金余额分布
- ✅ 折线图正确显示收益趋势
- ✅ 柱状图正确显示投资分布
- ✅ 图表响应式布局正常
- ✅ 数据更新后图表自动刷新

---

#### 任务 1.3: 创建统计分析页面 (8h)

**目标**: 创建独立的统计分析页面，提供更详细的数据分析

**文件**: `backend/controllers/StatisticsController.php` (新建)

```php
<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use backend\components\ChartHelper;
use common\models\Fund;
use common\models\Income;
use common\models\Investment;
use common\models\ReturnRecord;

class StatisticsController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * 总览页面
     */
    public function actionIndex()
    {
        // 时间范围筛选
        $startDate = Yii::$app->request->get('start_date', date('Y-m-01'));
        $endDate = Yii::$app->request->get('end_date', date('Y-m-d'));

        // 期间统计
        $periodIncome = Income::find()
            ->where(['>=', 'income_date', $startDate])
            ->andWhere(['<=', 'income_date', $endDate])
            ->sum('amount');

        $periodReturn = ReturnRecord::find()
            ->where(['>=', 'return_date', $startDate])
            ->andWhere(['<=', 'return_date', $endDate])
            ->sum('amount');

        // 收益率计算
        $totalInvestment = Investment::find()
            ->where(['status' => Investment::STATUS_ACTIVE])
            ->sum('amount');

        $totalReturn = ReturnRecord::find()->sum('amount');
        $returnRate = $totalInvestment > 0 ? ($totalReturn / $totalInvestment * 100) : 0;

        // 各基金收益率
        $fundReturns = $this->calculateFundReturns();

        return $this->render('index', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'periodIncome' => $periodIncome,
            'periodReturn' => $periodReturn,
            'totalInvestment' => $totalInvestment,
            'totalReturn' => $totalReturn,
            'returnRate' => $returnRate,
            'fundReturns' => $fundReturns,
        ]);
    }

    /**
     * 计算各基金收益率
     */
    private function calculateFundReturns()
    {
        $funds = Fund::find()->all();
        $result = [];

        foreach ($funds as $fund) {
            // 该基金的总投资
            $invested = Investment::find()
                ->where(['fund_id' => $fund->id])
                ->sum('amount');

            // 该基金获得的总收益
            $returns = ReturnRecord::find()
                ->joinWith('distributions')
                ->where(['return_distribution.fund_id' => $fund->id])
                ->sum('return_distribution.amount');

            $rate = $invested > 0 ? ($returns / $invested * 100) : 0;

            $result[] = [
                'name' => $fund->name,
                'invested' => $invested,
                'returns' => $returns,
                'rate' => $rate,
            ];
        }

        return $result;
    }

    /**
     * 月度报表
     */
    public function actionMonthly()
    {
        $year = Yii::$app->request->get('year', date('Y'));
        $month = Yii::$app->request->get('month', date('m'));
        $yearMonth = "$year-$month";

        // 月度收入
        $monthlyIncome = Income::find()
            ->where(['like', 'income_date', $yearMonth])
            ->all();

        // 月度投资
        $monthlyInvestments = Investment::find()
            ->where(['like', 'FROM_UNIXTIME(created_at, "%Y-%m")', $yearMonth])
            ->all();

        // 月度收益
        $monthlyReturns = ReturnRecord::find()
            ->where(['like', 'return_date', $yearMonth])
            ->all();

        return $this->render('monthly', [
            'year' => $year,
            'month' => $month,
            'monthlyIncome' => $monthlyIncome,
            'monthlyInvestments' => $monthlyInvestments,
            'monthlyReturns' => $monthlyReturns,
        ]);
    }
}
```

**视图文件**: `backend/views/statistics/index.php`

**验收标准**:
- ✅ 统计分析页面能正确展示
- ✅ 日期筛选功能正常
- ✅ 收益率计算准确
- ✅ 各基金收益对比清晰

---

### 第 3-5 天: 前台用户界面开发 (24h)

#### 任务 2.1: 设计前台布局和样式 (8h)

**目标**: 创建简洁美观的前台界面，适合日常查看

**文件**: `frontend/views/layouts/main.php`

**优化布局**:
- 简化导航栏
- 添加响应式设计
- 优化移动端显示

**创建前台首页**: `frontend/controllers/SiteController.php`

```php
public function actionIndex()
{
    if (Yii::$app->user->isGuest) {
        return $this->redirect(['site/login']);
    }

    // 获取当前用户的基金数据
    $funds = Fund::find()
        ->where(['status' => Fund::STATUS_ACTIVE])
        ->orderBy(['allocation_percentage' => SORT_DESC])
        ->all();

    // 总资产
    $totalAssets = Fund::find()->sum('balance');

    // 本月收益
    $monthlyReturn = ReturnRecord::find()
        ->where(['>=', 'return_date', date('Y-m-01')])
        ->sum('amount');

    return $this->render('index', [
        'funds' => $funds,
        'totalAssets' => $totalAssets,
        'monthlyReturn' => $monthlyReturn,
    ]);
}
```

**视图设计**: 卡片式布局，每个基金一个卡片

**验收标准**:
- ✅ 前台界面简洁美观
- ✅ 响应式布局适配手机
- ✅ 加载速度 < 1.5s

---

#### 任务 2.2: 创建基金详情页 (8h)

**目标**: 在前台显示单个基金的详细信息

**文件**: `frontend/controllers/FundController.php` (新建)

```php
<?php
namespace frontend\controllers;

use Yii;
use common\models\Fund;
use common\models\Investment;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class FundController extends Controller
{
    /**
     * 基金列表
     */
    public function actionIndex()
    {
        $funds = Fund::find()
            ->where(['status' => Fund::STATUS_ACTIVE])
            ->all();

        return $this->render('index', [
            'funds' => $funds,
        ]);
    }

    /**
     * 基金详情
     */
    public function actionView($id)
    {
        $fund = $this->findModel($id);

        // 该基金的投资记录
        $investments = Investment::find()
            ->where(['fund_id' => $id])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        // 该基金的收益历史
        $returns = $fund->getReturnDistributions()
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(20)
            ->all();

        return $this->render('view', [
            'fund' => $fund,
            'investments' => $investments,
            'returns' => $returns,
        ]);
    }

    protected function findModel($id)
    {
        if (($model = Fund::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('基金不存在。');
    }
}
```

**验收标准**:
- ✅ 基金详情页展示完整
- ✅ 投资记录和收益历史清晰
- ✅ 移动端友好

---

#### 任务 2.3: 移动端适配优化 (8h)

**目标**: 确保所有页面在移动端正常显示

**优化内容**:
1. **响应式表格**: 小屏幕下使用卡片式布局
2. **触摸友好**: 按钮大小适合手指点击
3. **简化导航**: 移动端使用折叠菜单
4. **图片优化**: 自适应屏幕宽度

**创建移动端专用样式**: `frontend/web/css/mobile.css`

```css
@media (max-width: 768px) {
    /* 表格在移动端显示为卡片 */
    .table-responsive {
        border: none;
    }

    .table-responsive table {
        display: block;
    }

    .table-responsive thead {
        display: none;
    }

    .table-responsive tr {
        display: block;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 10px;
    }

    .table-responsive td {
        display: block;
        text-align: right;
        padding-left: 50%;
        position: relative;
    }

    .table-responsive td:before {
        content: attr(data-label);
        position: absolute;
        left: 10px;
        font-weight: bold;
        text-align: left;
    }

    /* 按钮全宽显示 */
    .btn-block-mobile {
        display: block;
        width: 100%;
        margin-bottom: 10px;
    }

    /* 图表容器 */
    canvas {
        max-width: 100%;
        height: auto !important;
    }
}
```

**验收标准**:
- ✅ iPhone/Android 测试通过
- ✅ 横屏竖屏都正常显示
- ✅ 触摸操作流畅
- ✅ 图表在移动端正常显示

---

### 第 6-7 天: 数据统计和报表 (16h)

#### 任务 3.1: 月度收支报表 (8h)

**目标**: 自动生成月度收支汇总报表

**文件**: `backend/controllers/ReportController.php` (新建)

```php
<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use common\models\Income;
use common\models\Investment;
use common\models\ReturnRecord;
use common\models\Fund;

class ReportController extends Controller
{
    /**
     * 月度报表
     */
    public function actionMonthly()
    {
        $year = Yii::$app->request->get('year', date('Y'));
        $month = Yii::$app->request->get('month', date('m'));

        // 生成报表数据
        $report = $this->generateMonthlyReport($year, $month);

        return $this->render('monthly', [
            'year' => $year,
            'month' => $month,
            'report' => $report,
        ]);
    }

    /**
     * 生成月度报表数据
     */
    private function generateMonthlyReport($year, $month)
    {
        $yearMonth = sprintf('%04d-%02d', $year, $month);

        // 月初余额
        $beginBalance = $this->getBalanceAtDate("$yearMonth-01");

        // 收入汇总
        $incomeTotal = Income::find()
            ->where(['like', 'income_date', $yearMonth])
            ->sum('amount') ?: 0;

        $incomeCount = Income::find()
            ->where(['like', 'income_date', $yearMonth])
            ->count();

        // 投资汇总
        $investmentTotal = Investment::find()
            ->where(['like', 'FROM_UNIXTIME(created_at, "%Y-%m")', $yearMonth])
            ->sum('amount') ?: 0;

        $investmentCount = Investment::find()
            ->where(['like', 'FROM_UNIXTIME(created_at, "%Y-%m")', $yearMonth])
            ->count();

        // 收益汇总
        $returnTotal = ReturnRecord::find()
            ->where(['like', 'return_date', $yearMonth])
            ->sum('amount') ?: 0;

        $returnCount = ReturnRecord::find()
            ->where(['like', 'return_date', $yearMonth])
            ->count();

        // 月末余额
        $lastDay = date('Y-m-t', strtotime("$yearMonth-01"));
        $endBalance = $this->getBalanceAtDate($lastDay);

        // 各基金明细
        $fundDetails = $this->getFundMonthlyDetails($yearMonth);

        return [
            'beginBalance' => $beginBalance,
            'incomeTotal' => $incomeTotal,
            'incomeCount' => $incomeCount,
            'investmentTotal' => $investmentTotal,
            'investmentCount' => $investmentCount,
            'returnTotal' => $returnTotal,
            'returnCount' => $returnCount,
            'endBalance' => $endBalance,
            'fundDetails' => $fundDetails,
            'netChange' => $endBalance - $beginBalance,
        ];
    }

    /**
     * 获取指定日期的总余额（简化版，实际应该考虑历史记录）
     */
    private function getBalanceAtDate($date)
    {
        // 简化实现：返回当前余额
        // 完整实现需要根据日期回溯计算
        return Fund::find()->sum('balance') ?: 0;
    }

    /**
     * 获取各基金的月度明细
     */
    private function getFundMonthlyDetails($yearMonth)
    {
        $funds = Fund::find()->all();
        $details = [];

        foreach ($funds as $fund) {
            // 该基金的收入分配
            $incomeAmount = $fund->getIncomeDistributions()
                ->joinWith('income')
                ->where(['like', 'income.income_date', $yearMonth])
                ->sum('income_distribution.amount') ?: 0;

            // 该基金的投资
            $investmentAmount = Investment::find()
                ->where(['fund_id' => $fund->id])
                ->andWhere(['like', 'FROM_UNIXTIME(created_at, "%Y-%m")', $yearMonth])
                ->sum('amount') ?: 0;

            // 该基金的收益
            $returnAmount = $fund->getReturnDistributions()
                ->joinWith('returnRecord')
                ->where(['like', 'return_record.return_date', $yearMonth])
                ->sum('return_distribution.amount') ?: 0;

            $details[] = [
                'name' => $fund->name,
                'income' => $incomeAmount,
                'investment' => $investmentAmount,
                'return' => $returnAmount,
                'balance' => $fund->balance,
            ];
        }

        return $details;
    }

    /**
     * 导出月度报表为 Excel
     */
    public function actionExportMonthly($year, $month)
    {
        $report = $this->generateMonthlyReport($year, $month);

        // 设置响应头
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=monthly_report_' . $year . '_' . $month . '.csv');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

        // 表头
        fputcsv($output, ['理财计划系统 - 月度报表']);
        fputcsv($output, ['报表月份', $year . '年' . $month . '月']);
        fputcsv($output, []);

        // 汇总数据
        fputcsv($output, ['指标', '金额']);
        fputcsv($output, ['月初余额', $report['beginBalance']]);
        fputcsv($output, ['收入总额', $report['incomeTotal']]);
        fputcsv($output, ['投资总额', $report['investmentTotal']]);
        fputcsv($output, ['收益总额', $report['returnTotal']]);
        fputcsv($output, ['月末余额', $report['endBalance']]);
        fputcsv($output, ['净变动', $report['netChange']]);
        fputcsv($output, []);

        // 各基金明细
        fputcsv($output, ['各基金明细']);
        fputcsv($output, ['基金名称', '收入', '投资', '收益', '当前余额']);

        foreach ($report['fundDetails'] as $detail) {
            fputcsv($output, [
                $detail['name'],
                $detail['income'],
                $detail['investment'],
                $detail['return'],
                $detail['balance'],
            ]);
        }

        fclose($output);
        exit;
    }
}
```

**视图文件**: `backend/views/report/monthly.php`

**验收标准**:
- ✅ 月度报表数据准确
- ✅ 支持导出 CSV/Excel
- ✅ 各基金明细清晰
- ✅ 可选择不同月份

---

#### 任务 3.2: 年度报表和趋势分析 (8h)

**目标**: 创建年度报表，显示全年趋势

**实现位置**: `backend/controllers/ReportController.php`

**添加方法**:
```php
/**
 * 年度报表
 */
public function actionYearly()
{
    $year = Yii::$app->request->get('year', date('Y'));
    $report = $this->generateYearlyReport($year);

    return $this->render('yearly', [
        'year' => $year,
        'report' => $report,
    ]);
}

/**
 * 生成年度报表数据
 */
private function generateYearlyReport($year)
{
    // 实现逻辑类似月度报表
    // ...
}
```

**验收标准**:
- ✅ 年度汇总数据准确
- ✅ 12个月趋势图清晰
- ✅ 同比增长率计算正确

---

### 第 8-9 天: 导入功能和批量操作 (16h)

#### 任务 4.1: 实现批量导入收入数据 (8h)

**目标**: 支持通过 CSV/Excel 批量导入收入记录

**文件**: `backend/controllers/IncomeController.php`

**添加导入方法**:
```php
/**
 * 批量导入收入
 */
public function actionImport()
{
    $model = new \yii\base\DynamicModel(['file']);
    $model->addRule('file', 'file', [
        'extensions' => 'csv',
        'maxSize' => 1024 * 1024, // 1MB
    ]);

    if (Yii::$app->request->isPost) {
        $model->file = \yii\web\UploadedFile::getInstance($model, 'file');

        if ($model->file && $model->validate()) {
            $filePath = $model->file->tempName;
            $result = $this->processImportFile($filePath);

            Yii::$app->session->setFlash('success',
                "成功导入 {$result['success']} 条记录，失败 {$result['failed']} 条。");

            return $this->redirect(['index']);
        }
    }

    return $this->render('import', ['model' => $model]);
}

/**
 * 处理导入文件
 */
private function processImportFile($filePath)
{
    $success = 0;
    $failed = 0;

    if (($handle = fopen($filePath, 'r')) !== false) {
        // 跳过表头
        fgetcsv($handle);

        while (($data = fgetcsv($handle)) !== false) {
            $income = new Income();
            $income->amount = $data[0] ?? 0;
            $income->income_date = $data[1] ?? date('Y-m-d');
            $income->note = $data[2] ?? '';

            if ($income->save()) {
                // 自动分配到基金
                $income->distributeToFunds();
                $success++;
            } else {
                $failed++;
            }
        }

        fclose($handle);
    }

    return ['success' => $success, 'failed' => $failed];
}
```

**验收标准**:
- ✅ 能成功导入 CSV 文件
- ✅ 导入后自动分配到基金
- ✅ 错误处理完善
- ✅ 有导入结果反馈

---

#### 任务 4.2: 实现批量操作（删除、导出等） (8h)

**目标**: 支持批量选择和操作记录

**实现位置**: 各个列表页面添加复选框和批量操作按钮

**验收标准**:
- ✅ 支持批量选择
- ✅ 批量删除有确认提示
- ✅ 批量导出功能正常

---

### 第 10 天: 测试和优化 (8h)

#### 任务 5.1: 功能测试和 Bug 修复 (4h)

**测试清单**:
- [ ] Dashboard 图表显示正常
- [ ] 前台界面访问正常
- [ ] 移动端适配完整
- [ ] 报表生成准确
- [ ] 导入导出功能正常
- [ ] 所有页面响应时间 < 2s

#### 任务 5.2: 性能优化 (4h)

**优化项**:
1. 添加查询缓存
2. 优化图表数据查询（减少 SQL 次数）
3. 前端资源压缩
4. 启用 Gzip 压缩

**验收标准**:
- ✅ Dashboard 加载时间 < 1.5s
- ✅ 图表渲染流畅
- ✅ 移动端访问快速

---

## 📊 工时汇总

| 阶段 | 任务 | 工时 | 优先级 |
|-----|------|------|--------|
| **图表可视化** | Chart.js 集成 | 2h | P0 |
| | Dashboard 图表 | 6h | P0 |
| | 统计分析页面 | 8h | P1 |
| **前台界面** | 前台布局设计 | 8h | P0 |
| | 基金详情页 | 8h | P0 |
| | 移动端适配 | 8h | P1 |
| **报表功能** | 月度报表 | 8h | P1 |
| | 年度报表 | 8h | P2 |
| **导入功能** | 批量导入 | 8h | P1 |
| | 批量操作 | 8h | P2 |
| **测试优化** | 功能测试 | 4h | P0 |
| | 性能优化 | 4h | P1 |
| **总计** | | **80h** | |

---

## ✅ 迭代二验收标准

### 功能完整性
- ✅ Dashboard 集成至少 3 种图表（饼图、折线图、柱状图）
- ✅ 前台界面完整可用
- ✅ 支持移动端访问
- ✅ 月度报表生成准确
- ✅ 支持数据导入导出

### 用户体验
- ✅ Dashboard 加载时间 < 1.5s
- ✅ 移动端访问流畅
- ✅ 图表交互友好
- ✅ 响应式布局适配 iOS/Android

### 数据准确性
- ✅ 图表数据与数据库一致
- ✅ 报表计算准确率 100%
- ✅ 导入数据自动分配正确

---

## 🚀 迭代二发布计划

**发布版本**: v1.0

**发布日期**: 完成后 1 周内

**发布内容**:
- 完整的数据可视化功能
- 友好的前台用户界面
- 月度/年度报表功能
- 批量导入导出功能

**宣传重点**:
- "一目了然的资产分布图表"
- "随时随地查看，支持手机访问"
- "自动生成月度报表，理财更轻松"

---

**文档版本**: v1.0
**最后更新**: 2025-11-16
**负责人**: 产品开发团队
