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
     * 图表颜色常量
     */
    const CHART_COLORS = [
        'rgba(255, 99, 132, 0.8)',   // 红
        'rgba(54, 162, 235, 0.8)',   // 蓝
        'rgba(255, 206, 86, 0.8)',   // 黄
        'rgba(75, 192, 192, 0.8)',   // 青
        'rgba(153, 102, 255, 0.8)',  // 紫
        'rgba(255, 159, 64, 0.8)',   // 橙
        'rgba(201, 203, 207, 0.8)',  // 灰
        'rgba(255, 99, 71, 0.8)',    // 番茄红
        'rgba(0, 191, 255, 0.8)',    // 深天蓝
    ];

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

        foreach ($funds as $fund) {
            if ($fund->balance > 0) {
                $labels[] = $fund->name;
                $data[] = (float)$fund->balance;
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'data' => $data,
                'backgroundColor' => self::getColors(count($data)),
            ]],
        ];
    }

    /**
     * 获取近12个月收益趋势折线图数据
     * 优化：一次性查询所有数据，避免 N+1 问题
     * @return array
     */
    public static function getMonthlyReturnTrendData()
    {
        $labels = [];
        $incomeData = [];
        $returnData = [];

        // 计算12个月前的日期
        $startDate = date('Y-m-01', strtotime('-11 months'));

        // 一次性查询所有收入数据并按月分组
        $incomes = Income::find()
            ->select(['DATE_FORMAT(income_date, "%Y-%m") as month', 'SUM(amount) as total'])
            ->where(['>=', 'income_date', $startDate])
            ->groupBy('DATE_FORMAT(income_date, "%Y-%m")')
            ->asArray()
            ->all();

        // 一次性查询所有收益数据并按月分组
        $returns = ReturnRecord::find()
            ->select(['DATE_FORMAT(return_date, "%Y-%m") as month', 'SUM(amount) as total'])
            ->where(['>=', 'return_date', $startDate])
            ->groupBy('DATE_FORMAT(return_date, "%Y-%m")')
            ->asArray()
            ->all();

        // 转换为以月份为键的数组
        $incomeMap = [];
        foreach ($incomes as $income) {
            $incomeMap[$income['month']] = (float)$income['total'];
        }

        $returnMap = [];
        foreach ($returns as $return) {
            $returnMap[$return['month']] = (float)$return['total'];
        }

        // 生成最近12个月的数据
        for ($i = 11; $i >= 0; $i--) {
            $date = date('Y-m', strtotime("-$i month"));
            $labels[] = $date;
            $incomeData[] = $incomeMap[$date] ?? 0.0;
            $returnData[] = $returnMap[$date] ?? 0.0;
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

        foreach ($investments as $inv) {
            $labels[] = $inv['name'];
            $data[] = (float)$inv['total'];
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'data' => $data,
                'backgroundColor' => self::getColors(count($data)),
            ]],
        ];
    }

    /**
     * 获取月度收支对比柱状图
     * 优化：一次性查询所有数据，避免 N+1 问题
     * @param int $year 年份
     * @return array
     */
    public static function getMonthlyIncomeExpenseData($year = null)
    {
        if ($year === null) {
            $year = (int)date('Y');
        }

        $labels = [];
        $incomeData = [];
        $investmentData = [];

        // 一次性查询该年度所有收入数据
        $incomes = Income::find()
            ->select(['MONTH(income_date) as month', 'SUM(amount) as total'])
            ->where(['like', 'income_date', $year])
            ->groupBy('MONTH(income_date)')
            ->asArray()
            ->all();

        // 转换为以月份为键的数组
        $incomeMap = [];
        foreach ($incomes as $income) {
            $incomeMap[(int)$income['month']] = (float)$income['total'];
        }

        // 一次性查询该年度所有投资数据
        $yearStart = strtotime($year . '-01-01');
        $yearEnd = strtotime(($year + 1) . '-01-01');

        $investments = Investment::find()
            ->select(['MONTH(FROM_UNIXTIME(created_at)) as month', 'SUM(amount) as total'])
            ->where(['>=', 'created_at', $yearStart])
            ->andWhere(['<', 'created_at', $yearEnd])
            ->groupBy('MONTH(FROM_UNIXTIME(created_at))')
            ->asArray()
            ->all();

        // 转换为以月份为键的数组
        $investmentMap = [];
        foreach ($investments as $investment) {
            $investmentMap[(int)$investment['month']] = (float)$investment['total'];
        }

        // 生成12个月的数据
        for ($month = 1; $month <= 12; $month++) {
            $labels[] = $month . '月';
            $incomeData[] = $incomeMap[$month] ?? 0.0;
            $investmentData[] = $investmentMap[$month] ?? 0.0;
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

    /**
     * 获取颜色数组（循环使用，支持任意数量）
     * @param int $count 需要的颜色数量
     * @return array
     */
    private static function getColors($count)
    {
        $colors = [];
        for ($i = 0; $i < $count; $i++) {
            $colors[] = self::CHART_COLORS[$i % count(self::CHART_COLORS)];
        }
        return $colors;
    }
}
