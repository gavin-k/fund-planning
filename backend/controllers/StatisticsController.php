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
use common\models\ReturnDistribution;

/**
 * Statistics Controller
 * 统计分析控制器
 */
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
     * 统计总览页面
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
            ->sum('amount') ?: 0;

        $periodReturn = ReturnRecord::find()
            ->where(['>=', 'return_date', $startDate])
            ->andWhere(['<=', 'return_date', $endDate])
            ->sum('amount') ?: 0;

        $periodInvestment = Investment::find()
            ->where(['>=', 'created_at', strtotime($startDate)])
            ->andWhere(['<=', 'created_at', strtotime($endDate . ' 23:59:59')])
            ->sum('amount') ?: 0;

        // 收益率计算
        $totalInvestment = Investment::find()
            ->where(['status' => Investment::STATUS_ACTIVE])
            ->sum('amount') ?: 0;

        $totalReturn = ReturnRecord::find()->sum('amount') ?: 0;
        $returnRate = $totalInvestment > 0 ? ($totalReturn / $totalInvestment * 100) : 0;

        // 各基金收益率
        $fundReturns = $this->calculateFundReturns();

        // 图表数据
        $monthlyData = json_encode(ChartHelper::getMonthlyIncomeExpenseData(date('Y')));
        $fundInvestmentData = json_encode(ChartHelper::getFundInvestmentChartData());

        return $this->render('index', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'periodIncome' => $periodIncome,
            'periodReturn' => $periodReturn,
            'periodInvestment' => $periodInvestment,
            'totalInvestment' => $totalInvestment,
            'totalReturn' => $totalReturn,
            'returnRate' => $returnRate,
            'fundReturns' => $fundReturns,
            'monthlyData' => $monthlyData,
            'fundInvestmentData' => $fundInvestmentData,
        ]);
    }

    /**
     * 计算各基金收益率
     * 优化：使用 JOIN 查询，避免 N+1 问题
     */
    private function calculateFundReturns()
    {
        // 一次性查询所有基金的投资总额
        $investmentSql = "
            SELECT fund_id, SUM(amount) as total
            FROM investment
            GROUP BY fund_id
        ";
        $investments = Yii::$app->db->createCommand($investmentSql)->queryAll();
        $investmentMap = [];
        foreach ($investments as $inv) {
            $investmentMap[$inv['fund_id']] = (float)$inv['total'];
        }

        // 一次性查询所有基金的收益总额
        $returnSql = "
            SELECT fund_id, SUM(amount) as total
            FROM return_distribution
            GROUP BY fund_id
        ";
        $returns = Yii::$app->db->createCommand($returnSql)->queryAll();
        $returnMap = [];
        foreach ($returns as $ret) {
            $returnMap[$ret['fund_id']] = (float)$ret['total'];
        }

        // 查询所有基金并构建结果
        $funds = Fund::find()->all();
        $result = [];

        foreach ($funds as $fund) {
            $invested = $investmentMap[$fund->id] ?? 0;
            $returns = $returnMap[$fund->id] ?? 0;
            $rate = $invested > 0 ? ($returns / $invested * 100) : 0;

            $result[] = [
                'name' => $fund->name,
                'invested' => $invested,
                'returns' => $returns,
                'rate' => $rate,
                'balance' => $fund->balance,
            ];
        }

        return $result;
    }
}
