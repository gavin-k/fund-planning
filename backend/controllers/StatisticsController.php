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
     */
    private function calculateFundReturns()
    {
        $funds = Fund::find()->all();
        $result = [];

        foreach ($funds as $fund) {
            // 该基金的总投资
            $invested = Investment::find()
                ->where(['fund_id' => $fund->id])
                ->sum('amount') ?: 0;

            // 该基金获得的总收益
            $returns = ReturnDistribution::find()
                ->where(['fund_id' => $fund->id])
                ->sum('amount') ?: 0;

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
