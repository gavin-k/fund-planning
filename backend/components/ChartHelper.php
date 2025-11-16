<?php
namespace backend\components;

use common\models\Fund;
use common\models\Income;
use common\models\ReturnRecord;
use common\models\Investment;
use Yii;

/**
 * Chart Helper 类
 * 用于生成各类图表数据
 */
class ChartHelper
{
    /**
     * 获取基金余额饼图数据
     * @return array
     */
    public static function getFundBalanceChartData()
    {
        $funds = Fund::find()
            ->where(['status' => Fund::STATUS_ACTIVE])
            ->orderBy(['balance' => SORT_DESC])
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
            'rgba(201, 203, 207, 0.8)',  // 灰
        ];

        foreach ($funds as $index => $fund) {
            if ($fund->balance > 0) {
                $labels[] = $fund->name;
                $data[] = (float)$fund->balance;
            }
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
     * @return array
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
     * @return array
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

        $investments = Yii::$app->db->createCommand($sql)
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
                'borderColor' => 'rgba(54, 162, 235, 1)',
                'borderWidth' => 1,
            ]],
        ];
    }

    /**
     * 获取基金投资占比甜甜圈图数据
     * @return array
     */
    public static function getFundInvestmentChartData()
    {
        $sql = "
            SELECT f.name, SUM(i.amount) as total
            FROM investment i
            JOIN fund f ON i.fund_id = f.id
            WHERE i.status = :status
            GROUP BY f.id, f.name
            ORDER BY total DESC
        ";

        $investments = Yii::$app->db->createCommand($sql)
            ->bindValue(':status', Investment::STATUS_ACTIVE)
            ->queryAll();

        $labels = [];
        $data = [];
        $backgroundColors = [
            'rgba(255, 99, 132, 0.8)',
            'rgba(54, 162, 235, 0.8)',
            'rgba(255, 206, 86, 0.8)',
            'rgba(75, 192, 192, 0.8)',
            'rgba(153, 102, 255, 0.8)',
            'rgba(255, 159, 64, 0.8)',
        ];

        foreach ($investments as $index => $inv) {
            $labels[] = $inv['name'];
            $data[] = (float)$inv['total'];
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
     * 获取月度收支对比柱状图
     * @param int $year 年份
     * @return array
     */
    public static function getMonthlyIncomeExpenseData($year = null)
    {
        if ($year === null) {
            $year = date('Y');
        }

        $labels = [];
        $incomeData = [];
        $investmentData = [];

        for ($month = 1; $month <= 12; $month++) {
            $yearMonth = sprintf('%04d-%02d', $year, $month);
            $labels[] = $month . '月';

            // 收入
            $income = Income::find()
                ->where(['like', 'income_date', $yearMonth])
                ->sum('amount');
            $incomeData[] = (float)($income ?: 0);

            // 投资（支出）
            $investment = Investment::find()
                ->where(['>=', 'created_at', strtotime($yearMonth . '-01')])
                ->andWhere(['<', 'created_at', strtotime($yearMonth . '-01 +1 month')])
                ->sum('amount');
            $investmentData[] = (float)($investment ?: 0);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => '收入',
                    'data' => $incomeData,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.8)',
                ],
                [
                    'label' => '投资',
                    'data' => $investmentData,
                    'backgroundColor' => 'rgba(255, 99, 132, 0.8)',
                ],
            ],
        ];
    }
}
