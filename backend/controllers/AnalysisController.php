<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use backend\components\AnalysisHelper;

/**
 * Analysis Controller
 * 收益分析控制器
 */
class AnalysisController extends Controller
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
     * 收益分析首页
     */
    public function actionIndex()
    {
        // 时间范围筛选
        $startDate = Yii::$app->request->get('start_date');
        $endDate = Yii::$app->request->get('end_date');

        // 整体收益率
        $overallReturn = AnalysisHelper::getOverallReturnRate($startDate, $endDate);

        // 年化收益率（如果有开始日期）
        $annualizedRate = null;
        if ($startDate) {
            $annualizedRate = AnalysisHelper::getAnnualizedReturnRate($startDate, $endDate);
        }

        // 各基金收益率排行
        $fundRanking = AnalysisHelper::getFundReturnRanking($startDate, $endDate);

        // 各产品收益率排行
        $productRanking = AnalysisHelper::getProductReturnRanking($startDate, $endDate);

        // 财务健康评分
        $healthScore = AnalysisHelper::getFinancialHealthScore();

        // 智能建议
        $suggestions = AnalysisHelper::getFinancialSuggestions();

        // 月度趋势数据（用于图表）
        $monthlyTrend = AnalysisHelper::getMonthlyTrendData(12);

        // 准备图表数据
        $chartData = $this->prepareChartData($fundRanking, $productRanking, $monthlyTrend);

        return $this->render('index', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'overallReturn' => $overallReturn,
            'annualizedRate' => $annualizedRate,
            'fundRanking' => $fundRanking,
            'productRanking' => $productRanking,
            'healthScore' => $healthScore,
            'suggestions' => $suggestions,
            'chartData' => $chartData,
        ]);
    }

    /**
     * 准备图表数据
     */
    private function prepareChartData($fundRanking, $productRanking, $monthlyTrend)
    {
        // 基金收益率对比（柱状图）
        $fundLabels = [];
        $fundReturns = [];
        foreach ($fundRanking as $fund) {
            $fundLabels[] = $fund['fund_name'];
            $fundReturns[] = $fund['return_rate'];
        }

        // 产品收益率对比（雷达图）
        $productLabels = [];
        $productRoi = [];
        $count = min(6, count($productRanking)); // 最多显示6个产品
        for ($i = 0; $i < $count; $i++) {
            $productLabels[] = $productRanking[$i]['product_name'];
            $productRoi[] = $productRanking[$i]['roi'];
        }

        // 月度趋势（折线图）
        $monthLabels = [];
        $incomeData = [];
        $returnData = [];
        $investmentData = [];
        foreach ($monthlyTrend as $month) {
            $monthLabels[] = $month['month'];
            $incomeData[] = $month['income'];
            $returnData[] = $month['return'];
            $investmentData[] = $month['investment'];
        }

        return [
            'fund' => json_encode([
                'labels' => $fundLabels,
                'data' => $fundReturns,
            ]),
            'product' => json_encode([
                'labels' => $productLabels,
                'data' => $productRoi,
            ]),
            'trend' => json_encode([
                'labels' => $monthLabels,
                'income' => $incomeData,
                'return' => $returnData,
                'investment' => $investmentData,
            ]),
        ];
    }
}
