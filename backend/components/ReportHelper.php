<?php

namespace backend\components;

use Yii;
use yii\helpers\ArrayHelper;
use common\models\Fund;
use common\models\Income;
use common\models\Investment;
use common\models\ReturnRecord;
use common\models\Budget;

/**
 * 报表数据聚合助手类
 * 提供月度、季度、年度、自定义周期的财务报表数据聚合
 */
class ReportHelper
{
    /**
     * 获取月度报表数据
     *
     * @param string $month 月份，格式：YYYY-MM
     * @return array 报表数据
     */
    public static function getMonthlyReport($month)
    {
        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        return self::getReportData($startDate, $endDate, 'monthly', $month);
    }

    /**
     * 获取季度报表数据
     *
     * @param int $year 年份
     * @param int $quarter 季度（1-4）
     * @return array 报表数据
     */
    public static function getQuarterlyReport($year, $quarter)
    {
        $startMonth = ($quarter - 1) * 3 + 1;
        $endMonth = $quarter * 3;

        $startDate = sprintf('%d-%02d-01', $year, $startMonth);
        $endDate = date('Y-m-t', strtotime(sprintf('%d-%02d-01', $year, $endMonth)));

        $period = sprintf('%dQ%d', $year, $quarter);

        return self::getReportData($startDate, $endDate, 'quarterly', $period);
    }

    /**
     * 获取年度报表数据
     *
     * @param int $year 年份
     * @return array 报表数据
     */
    public static function getAnnualReport($year)
    {
        $startDate = $year . '-01-01';
        $endDate = $year . '-12-31';

        return self::getReportData($startDate, $endDate, 'annual', $year);
    }

    /**
     * 获取自定义周期报表数据
     *
     * @param string $startDate 开始日期 YYYY-MM-DD
     * @param string $endDate 结束日期 YYYY-MM-DD
     * @return array 报表数据
     */
    public static function getCustomReport($startDate, $endDate)
    {
        $period = $startDate . ' ~ ' . $endDate;
        return self::getReportData($startDate, $endDate, 'custom', $period);
    }

    /**
     * 核心报表数据聚合方法
     *
     * @param string $startDate 开始日期
     * @param string $endDate 结束日期
     * @param string $type 报表类型（monthly/quarterly/annual/custom）
     * @param string $period 周期描述
     * @return array 完整的报表数据
     */
    private static function getReportData($startDate, $endDate, $type, $period)
    {
        // 1. 基础信息
        $report = [
            'type' => $type,
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'generated_at' => date('Y-m-d H:i:s'),
        ];

        // 2. 收入统计
        $report['income'] = self::getIncomeStats($startDate, $endDate);

        // 3. 投资统计
        $report['investment'] = self::getInvestmentStats($startDate, $endDate);

        // 4. 收益统计
        $report['return'] = self::getReturnStats($startDate, $endDate);

        // 5. 资产概览（期初、期末）
        $report['assets'] = self::getAssetsOverview($startDate, $endDate);

        // 6. 基金明细
        $report['fund_details'] = self::getFundDetails($startDate, $endDate);

        // 7. 预算执行情况
        $report['budget_execution'] = self::getBudgetExecution($startDate, $endDate);

        // 8. 关键财务指标
        $report['key_metrics'] = self::getKeyMetrics($report);

        // 9. 同比/环比分析（如果是月度或年度报表）
        if ($type === 'monthly' || $type === 'annual') {
            $report['comparison'] = self::getComparison($startDate, $endDate, $type);
        }

        return $report;
    }

    /**
     * 获取收入统计
     */
    private static function getIncomeStats($startDate, $endDate)
    {
        $incomes = Income::find()
            ->where(['>=', 'income_date', $startDate])
            ->andWhere(['<=', 'income_date', $endDate])
            ->all();

        $total = 0;
        $count = count($incomes);
        $bySource = [];

        foreach ($incomes as $income) {
            $total += $income->amount;

            // 按来源分类（可以根据note字段简单分类）
            $source = $income->note ?: '其他收入';
            if (!isset($bySource[$source])) {
                $bySource[$source] = 0;
            }
            $bySource[$source] += $income->amount;
        }

        // 排序
        arsort($bySource);

        return [
            'total' => $total,
            'count' => $count,
            'average' => $count > 0 ? $total / $count : 0,
            'by_source' => $bySource,
        ];
    }

    /**
     * 获取投资统计
     */
    private static function getInvestmentStats($startDate, $endDate)
    {
        $investments = Investment::find()
            ->where(['>=', 'investment_date', $startDate])
            ->andWhere(['<=', 'investment_date', $endDate])
            ->with(['fund', 'product'])
            ->all();

        $total = 0;
        $count = count($investments);
        $byFund = [];
        $byProduct = [];

        foreach ($investments as $investment) {
            $total += $investment->amount;

            // 按基金分类
            $fundName = $investment->fund ? $investment->fund->name : '未分配';
            if (!isset($byFund[$fundName])) {
                $byFund[$fundName] = ['amount' => 0, 'count' => 0];
            }
            $byFund[$fundName]['amount'] += $investment->amount;
            $byFund[$fundName]['count']++;

            // 按产品分类
            $productName = $investment->product ? $investment->product->name : '未分配';
            if (!isset($byProduct[$productName])) {
                $byProduct[$productName] = ['amount' => 0, 'count' => 0];
            }
            $byProduct[$productName]['amount'] += $investment->amount;
            $byProduct[$productName]['count']++;
        }

        // 排序
        uasort($byFund, function($a, $b) {
            return $b['amount'] <=> $a['amount'];
        });
        uasort($byProduct, function($a, $b) {
            return $b['amount'] <=> $a['amount'];
        });

        return [
            'total' => $total,
            'count' => $count,
            'average' => $count > 0 ? $total / $count : 0,
            'by_fund' => $byFund,
            'by_product' => $byProduct,
        ];
    }

    /**
     * 获取收益统计
     */
    private static function getReturnStats($startDate, $endDate)
    {
        $returns = ReturnRecord::find()
            ->where(['>=', 'return_date', $startDate])
            ->andWhere(['<=', 'return_date', $endDate])
            ->with(['fund', 'product'])
            ->all();

        $total = 0;
        $count = count($returns);
        $byFund = [];
        $byProduct = [];

        foreach ($returns as $return) {
            $total += $return->total_amount;

            // 按基金分类
            $fundName = $return->fund ? $return->fund->name : '未分配';
            if (!isset($byFund[$fundName])) {
                $byFund[$fundName] = ['amount' => 0, 'count' => 0];
            }
            $byFund[$fundName]['amount'] += $return->total_amount;
            $byFund[$fundName]['count']++;

            // 按产品分类
            $productName = $return->product ? $return->product->name : '未分配';
            if (!isset($byProduct[$productName])) {
                $byProduct[$productName] = ['amount' => 0, 'count' => 0];
            }
            $byProduct[$productName]['amount'] += $return->total_amount;
            $byProduct[$productName]['count']++;
        }

        // 排序
        uasort($byFund, function($a, $b) {
            return $b['amount'] <=> $a['amount'];
        });
        uasort($byProduct, function($a, $b) {
            return $b['amount'] <=> $a['amount'];
        });

        return [
            'total' => $total,
            'count' => $count,
            'average' => $count > 0 ? $total / $count : 0,
            'by_fund' => $byFund,
            'by_product' => $byProduct,
        ];
    }

    /**
     * 获取资产概览（期初、期末余额）
     */
    private static function getAssetsOverview($startDate, $endDate)
    {
        // 当前总资产（期末）
        $currentTotal = Fund::find()->sum('current_balance') ?: 0;

        // 获取所有基金的当前余额
        $funds = Fund::find()->all();
        $fundBalances = [];

        foreach ($funds as $fund) {
            $fundBalances[$fund->name] = $fund->current_balance;
        }

        // 计算期初余额（简化方法：当前余额 - 本期收入 + 本期投资 - 本期收益）
        $periodIncome = Income::find()
            ->where(['>=', 'income_date', $startDate])
            ->andWhere(['<=', 'income_date', $endDate])
            ->sum('amount') ?: 0;

        $periodInvestment = Investment::find()
            ->where(['>=', 'investment_date', $startDate])
            ->andWhere(['<=', 'investment_date', $endDate])
            ->sum('amount') ?: 0;

        $periodReturn = ReturnRecord::find()
            ->where(['>=', 'return_date', $startDate])
            ->andWhere(['<=', 'return_date', $endDate])
            ->sum('total_amount') ?: 0;

        $beginningTotal = $currentTotal - $periodIncome + $periodInvestment - $periodReturn;

        // 净增长
        $netGrowth = $currentTotal - $beginningTotal;
        $growthRate = $beginningTotal > 0 ? ($netGrowth / $beginningTotal) * 100 : 0;

        return [
            'beginning_total' => $beginningTotal,
            'ending_total' => $currentTotal,
            'net_growth' => $netGrowth,
            'growth_rate' => $growthRate,
            'fund_balances' => $fundBalances,
        ];
    }

    /**
     * 获取基金明细
     */
    private static function getFundDetails($startDate, $endDate)
    {
        $funds = Fund::find()->all();
        $details = [];

        foreach ($funds as $fund) {
            // 本期投资
            $periodInvestment = Investment::find()
                ->where(['fund_id' => $fund->id])
                ->andWhere(['>=', 'investment_date', $startDate])
                ->andWhere(['<=', 'investment_date', $endDate])
                ->sum('amount') ?: 0;

            // 本期收益
            $periodReturn = ReturnRecord::find()
                ->where(['fund_id' => $fund->id])
                ->andWhere(['>=', 'return_date', $startDate])
                ->andWhere(['<=', 'return_date', $endDate])
                ->sum('total_amount') ?: 0;

            // 收益率
            $returnRate = $periodInvestment > 0 ? ($periodReturn / $periodInvestment) * 100 : 0;

            $details[] = [
                'name' => $fund->name,
                'current_balance' => $fund->current_balance,
                'allocation_percent' => $fund->allocation_percent,
                'period_investment' => $periodInvestment,
                'period_return' => $periodReturn,
                'return_rate' => $returnRate,
            ];
        }

        // 按当前余额排序
        usort($details, function($a, $b) {
            return $b['current_balance'] <=> $a['current_balance'];
        });

        return $details;
    }

    /**
     * 获取预算执行情况
     */
    private static function getBudgetExecution($startDate, $endDate)
    {
        $budgets = Budget::find()
            ->with(['fund'])
            ->where([
                'or',
                ['and', ['<=', 'start_date', $endDate], ['>=', 'end_date', $startDate]],
            ])
            ->all();

        $execution = [];

        foreach ($budgets as $budget) {
            $usageRate = $budget->getUsageRate();
            $statusLabel = $budget->getBudgetStatusLabel();

            $execution[] = [
                'fund_name' => $budget->fund ? $budget->fund->name : '全局预算',
                'period_type' => $budget->getPeriodTypeText(),
                'budget_amount' => $budget->budget_amount,
                'actual_amount' => $budget->actual_amount,
                'usage_rate' => $usageRate,
                'status' => $statusLabel['text'],
                'is_over_budget' => $budget->isOverBudget(),
                'remaining' => $budget->getRemainingBudget(),
            ];
        }

        return $execution;
    }

    /**
     * 计算关键财务指标
     */
    private static function getKeyMetrics($report)
    {
        $income = $report['income']['total'];
        $investment = $report['investment']['total'];
        $return = $report['return']['total'];
        $beginningAssets = $report['assets']['beginning_total'];
        $endingAssets = $report['assets']['ending_total'];

        // 1. 储蓄率 = 投资 / 收入
        $savingsRate = $income > 0 ? ($investment / $income) * 100 : 0;

        // 2. 投资收益率 = 收益 / 投资
        $investmentReturn = $investment > 0 ? ($return / $investment) * 100 : 0;

        // 3. 资产增长率 = (期末 - 期初) / 期初
        $assetGrowthRate = $report['assets']['growth_rate'];

        // 4. 净现金流 = 收入 - 投资
        $netCashFlow = $income - $investment;

        // 5. 平均单笔投资
        $avgInvestment = $report['investment']['average'];

        // 6. 平均单笔收益
        $avgReturn = $report['return']['average'];

        return [
            'savings_rate' => $savingsRate,
            'investment_return_rate' => $investmentReturn,
            'asset_growth_rate' => $assetGrowthRate,
            'net_cash_flow' => $netCashFlow,
            'avg_investment' => $avgInvestment,
            'avg_return' => $avgReturn,
        ];
    }

    /**
     * 获取同比/环比数据
     */
    private static function getComparison($startDate, $endDate, $type)
    {
        if ($type === 'monthly') {
            // 上月数据
            $prevMonth = date('Y-m', strtotime('-1 month', strtotime($startDate)));
            $prevReport = self::getMonthlyReport($prevMonth);

            // 去年同期数据
            $lastYearMonth = date('Y-m', strtotime('-1 year', strtotime($startDate)));
            $lastYearReport = self::getMonthlyReport($lastYearMonth);

            return [
                'mom' => [ // Month-over-Month 环比
                    'period' => $prevMonth,
                    'income_change' => self::calculateChangeRate(
                        $prevReport['income']['total'],
                        Income::find()
                            ->where(['>=', 'income_date', $startDate])
                            ->andWhere(['<=', 'income_date', $endDate])
                            ->sum('amount') ?: 0
                    ),
                ],
                'yoy' => [ // Year-over-Year 同比
                    'period' => $lastYearMonth,
                    'income_change' => self::calculateChangeRate(
                        $lastYearReport['income']['total'],
                        Income::find()
                            ->where(['>=', 'income_date', $startDate])
                            ->andWhere(['<=', 'income_date', $endDate])
                            ->sum('amount') ?: 0
                    ),
                ],
            ];
        } elseif ($type === 'annual') {
            // 去年数据
            $prevYear = date('Y', strtotime('-1 year', strtotime($startDate)));
            $prevReport = self::getAnnualReport($prevYear);

            return [
                'yoy' => [
                    'period' => $prevYear,
                    'income_change' => self::calculateChangeRate(
                        $prevReport['income']['total'],
                        Income::find()
                            ->where(['>=', 'income_date', $startDate])
                            ->andWhere(['<=', 'income_date', $endDate])
                            ->sum('amount') ?: 0
                    ),
                ],
            ];
        }

        return null;
    }

    /**
     * 计算变化率
     */
    private static function calculateChangeRate($oldValue, $newValue)
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0;
        }
        return (($newValue - $oldValue) / $oldValue) * 100;
    }

    /**
     * 获取可用的报表月份列表（最近12个月）
     */
    public static function getAvailableMonths($limit = 12)
    {
        $months = [];
        for ($i = 0; $i < $limit; $i++) {
            $month = date('Y-m', strtotime("-$i month"));
            $months[$month] = $month;
        }
        return $months;
    }

    /**
     * 获取可用的报表年份列表
     */
    public static function getAvailableYears()
    {
        // 从第一笔收入记录的年份到当前年份
        $firstIncome = Income::find()->orderBy(['income_date' => SORT_ASC])->one();
        $startYear = $firstIncome ? date('Y', strtotime($firstIncome->income_date)) : date('Y');
        $currentYear = date('Y');

        $years = [];
        for ($year = $currentYear; $year >= $startYear; $year--) {
            $years[$year] = $year;
        }

        return $years;
    }

    /**
     * 获取季度列表
     */
    public static function getQuarters()
    {
        return [
            1 => '第一季度 (1-3月)',
            2 => '第二季度 (4-6月)',
            3 => '第三季度 (7-9月)',
            4 => '第四季度 (10-12月)',
        ];
    }
}
