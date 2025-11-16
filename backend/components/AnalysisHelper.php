<?php
namespace backend\components;

use common\models\Fund;
use common\models\Income;
use common\models\ReturnRecord;
use common\models\Investment;
use common\models\InvestmentProduct;
use common\models\ReturnDistribution;
use Yii;

/**
 * Analysis Helper 类
 * 用于财务分析和收益率计算
 */
class AnalysisHelper
{
    /**
     * 计算整体收益率
     * 公式：(当前总资产 - 总投入) / 总投入 × 100%
     *
     * @param string|null $startDate 开始日期
     * @param string|null $endDate 结束日期
     * @return array ['total_return' => 总收益, 'return_rate' => 收益率, 'total_investment' => 总投入]
     */
    public static function getOverallReturnRate($startDate = null, $endDate = null)
    {
        // 计算总资产（所有基金余额）
        $totalAssets = Fund::find()
            ->where(['status' => Fund::STATUS_ACTIVE])
            ->sum('current_balance') ?: 0;

        // 计算总收入
        $incomeQuery = Income::find();
        if ($startDate) {
            $incomeQuery->andWhere(['>=', 'income_date', $startDate]);
        }
        if ($endDate) {
            $incomeQuery->andWhere(['<=', 'income_date', $endDate]);
        }
        $totalIncome = $incomeQuery->sum('amount') ?: 0;

        // 计算总收益
        $returnQuery = ReturnRecord::find();
        if ($startDate) {
            $returnQuery->andWhere(['>=', 'return_date', $startDate]);
        }
        if ($endDate) {
            $returnQuery->andWhere(['<=', 'return_date', $endDate]);
        }
        $totalReturn = $returnQuery->sum('total_amount') ?: 0;

        // 计算收益率
        $returnRate = 0;
        if ($totalIncome > 0) {
            $returnRate = ($totalReturn / $totalIncome) * 100;
        }

        return [
            'total_assets' => round($totalAssets, 2),
            'total_income' => round($totalIncome, 2),
            'total_return' => round($totalReturn, 2),
            'return_rate' => round($returnRate, 2),
        ];
    }

    /**
     * 计算年化收益率
     * 公式：[(1 + 总收益率)^(365/持有天数) - 1] × 100%
     *
     * @param string $startDate 开始日期
     * @param string|null $endDate 结束日期（默认今天）
     * @return float
     */
    public static function getAnnualizedReturnRate($startDate, $endDate = null)
    {
        if (!$endDate) {
            $endDate = date('Y-m-d');
        }

        // 计算持有天数
        $start = strtotime($startDate);
        $end = strtotime($endDate);
        $days = max(1, ($end - $start) / 86400);

        // 获取期间收益率
        $data = self::getOverallReturnRate($startDate, $endDate);
        $returnRate = $data['return_rate'] / 100; // 转为小数

        // 计算年化收益率
        if ($returnRate <= -1) {
            return -100; // 全部亏损
        }

        $annualizedRate = (pow(1 + $returnRate, 365 / $days) - 1) * 100;

        return round($annualizedRate, 2);
    }

    /**
     * 获取各基金收益率排行
     *
     * @param string|null $startDate 开始日期
     * @param string|null $endDate 结束日期
     * @return array
     */
    public static function getFundReturnRanking($startDate = null, $endDate = null)
    {
        $funds = Fund::find()
            ->where(['status' => Fund::STATUS_ACTIVE])
            ->all();

        $ranking = [];

        foreach ($funds as $fund) {
            // 计算该基金的总收入（通过分配记录）
            $incomeQuery = Income::find()
                ->alias('i')
                ->innerJoin('income_distribution id', 'id.income_id = i.id')
                ->where(['id.fund_id' => $fund->id]);

            if ($startDate) {
                $incomeQuery->andWhere(['>=', 'i.income_date', $startDate]);
            }
            if ($endDate) {
                $incomeQuery->andWhere(['<=', 'i.income_date', $endDate]);
            }

            $fundIncome = $incomeQuery->sum('id.amount') ?: 0;

            // 计算该基金的总收益（通过收益分配记录）
            $returnQuery = ReturnRecord::find()
                ->alias('rr')
                ->innerJoin('return_distribution rd', 'rd.return_id = rr.id')
                ->where(['rd.fund_id' => $fund->id]);

            if ($startDate) {
                $returnQuery->andWhere(['>=', 'rr.return_date', $startDate]);
            }
            if ($endDate) {
                $returnQuery->andWhere(['<=', 'rr.return_date', $endDate]);
            }

            $fundReturn = $returnQuery->sum('rd.amount') ?: 0;

            // 计算收益率
            $returnRate = 0;
            if ($fundIncome > 0) {
                $returnRate = ($fundReturn / $fundIncome) * 100;
            }

            $ranking[] = [
                'fund_id' => $fund->id,
                'fund_name' => $fund->name,
                'current_balance' => $fund->current_balance,
                'income' => round($fundIncome, 2),
                'return' => round($fundReturn, 2),
                'return_rate' => round($returnRate, 2),
            ];
        }

        // 按收益率降序排序
        usort($ranking, function($a, $b) {
            return $b['return_rate'] <=> $a['return_rate'];
        });

        return $ranking;
    }

    /**
     * 获取各理财产品收益率排行
     *
     * @param string|null $startDate 开始日期
     * @param string|null $endDate 结束日期
     * @return array
     */
    public static function getProductReturnRanking($startDate = null, $endDate = null)
    {
        $products = InvestmentProduct::find()
            ->where(['status' => InvestmentProduct::STATUS_ACTIVE])
            ->all();

        $ranking = [];

        foreach ($products as $product) {
            // 计算该产品的总投资
            $investmentQuery = Investment::find()
                ->where([
                    'product_id' => $product->id,
                    'status' => Investment::STATUS_ACTIVE
                ]);

            if ($startDate) {
                $investmentQuery->andWhere(['>=', 'investment_date', $startDate]);
            }
            if ($endDate) {
                $investmentQuery->andWhere(['<=', 'investment_date', $endDate]);
            }

            $totalInvestment = $investmentQuery->sum('amount') ?: 0;

            // 计算该产品的总收益
            $returnQuery = ReturnRecord::find()
                ->where(['product_id' => $product->id]);

            if ($startDate) {
                $returnQuery->andWhere(['>=', 'return_date', $startDate]);
            }
            if ($endDate) {
                $returnQuery->andWhere(['<=', 'return_date', $endDate]);
            }

            $totalReturn = $returnQuery->sum('total_amount') ?: 0;

            // 计算收益率（ROI）
            $roi = 0;
            if ($totalInvestment > 0) {
                $roi = ($totalReturn / $totalInvestment) * 100;
            }

            // 计算收益次数（稳定性指标）
            $returnCount = ReturnRecord::find()
                ->where(['product_id' => $product->id])
                ->count();

            $ranking[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_type' => $product->type,
                'platform' => $product->platform,
                'total_investment' => round($totalInvestment, 2),
                'total_return' => round($totalReturn, 2),
                'roi' => round($roi, 2),
                'return_count' => $returnCount,
            ];
        }

        // 按ROI降序排序
        usort($ranking, function($a, $b) {
            return $b['roi'] <=> $a['roi'];
        });

        return $ranking;
    }

    /**
     * 计算财务健康评分（0-100分）
     *
     * @return array ['score' => 总分, 'details' => 各项得分详情]
     */
    public static function getFinancialHealthScore()
    {
        $scores = [];
        $totalScore = 0;

        // 1. 储蓄率评分（30分）：收入中储蓄的比例
        $totalIncome = Income::find()->sum('amount') ?: 0;
        $totalAssets = Fund::find()->sum('current_balance') ?: 0;

        $savingRate = 0;
        if ($totalIncome > 0) {
            $savingRate = ($totalAssets / $totalIncome) * 100;
        }

        $savingScore = 0;
        if ($savingRate >= 50) {
            $savingScore = 30;
        } elseif ($savingRate >= 30) {
            $savingScore = 25;
        } elseif ($savingRate >= 20) {
            $savingScore = 20;
        } elseif ($savingRate >= 10) {
            $savingScore = 15;
        } else {
            $savingScore = 10;
        }

        $scores['saving'] = [
            'score' => $savingScore,
            'rate' => round($savingRate, 2),
            'description' => '储蓄率',
        ];
        $totalScore += $savingScore;

        // 2. 投资分散度评分（25分）：资金分散在不同产品中
        $activeInvestments = Investment::find()
            ->where(['status' => Investment::STATUS_ACTIVE])
            ->select('product_id')
            ->distinct()
            ->count();

        $diversificationScore = 0;
        if ($activeInvestments >= 5) {
            $diversificationScore = 25;
        } elseif ($activeInvestments >= 3) {
            $diversificationScore = 20;
        } elseif ($activeInvestments >= 2) {
            $diversificationScore = 15;
        } elseif ($activeInvestments >= 1) {
            $diversificationScore = 10;
        } else {
            $diversificationScore = 0;
        }

        $scores['diversification'] = [
            'score' => $diversificationScore,
            'count' => $activeInvestments,
            'description' => '投资分散度',
        ];
        $totalScore += $diversificationScore;

        // 3. 收益稳定性评分（25分）：近期收益记录频率
        $recentReturns = ReturnRecord::find()
            ->where(['>=', 'return_date', date('Y-m-d', strtotime('-3 months'))])
            ->count();

        $stabilityScore = 0;
        if ($recentReturns >= 10) {
            $stabilityScore = 25;
        } elseif ($recentReturns >= 5) {
            $stabilityScore = 20;
        } elseif ($recentReturns >= 3) {
            $stabilityScore = 15;
        } elseif ($recentReturns >= 1) {
            $stabilityScore = 10;
        } else {
            $stabilityScore = 0;
        }

        $scores['stability'] = [
            'score' => $stabilityScore,
            'count' => $recentReturns,
            'description' => '收益稳定性',
        ];
        $totalScore += $stabilityScore;

        // 4. 目标达成率评分（20分）
        $totalGoals = FinancialGoal::find()
            ->where(['status' => [FinancialGoal::STATUS_IN_PROGRESS, FinancialGoal::STATUS_COMPLETED]])
            ->count();

        $completedGoals = FinancialGoal::find()
            ->where(['status' => FinancialGoal::STATUS_COMPLETED])
            ->count();

        $goalScore = 0;
        if ($totalGoals > 0) {
            $completionRate = ($completedGoals / $totalGoals) * 100;
            if ($completionRate >= 80) {
                $goalScore = 20;
            } elseif ($completionRate >= 60) {
                $goalScore = 15;
            } elseif ($completionRate >= 40) {
                $goalScore = 10;
            } else {
                $goalScore = 5;
            }
        } else {
            $goalScore = 10; // 未设定目标，给基础分
        }

        $scores['goal'] = [
            'score' => $goalScore,
            'total' => $totalGoals,
            'completed' => $completedGoals,
            'description' => '目标达成率',
        ];
        $totalScore += $goalScore;

        // 评级
        $rating = '';
        if ($totalScore >= 90) {
            $rating = '优秀';
        } elseif ($totalScore >= 75) {
            $rating = '良好';
        } elseif ($totalScore >= 60) {
            $rating = '中等';
        } elseif ($totalScore >= 40) {
            $rating = '及格';
        } else {
            $rating = '需改进';
        }

        return [
            'total_score' => $totalScore,
            'rating' => $rating,
            'details' => $scores,
        ];
    }

    /**
     * 生成智能理财建议
     *
     * @return array
     */
    public static function getFinancialSuggestions()
    {
        $suggestions = [];

        // 获取健康评分
        $healthScore = self::getFinancialHealthScore();

        // 1. 储蓄率建议
        if ($healthScore['details']['saving']['rate'] < 20) {
            $suggestions[] = [
                'type' => 'warning',
                'title' => '储蓄率偏低',
                'message' => sprintf(
                    '您的储蓄率为 %.1f%%，建议提升至20%%以上。可以考虑增加基金分配比例。',
                    $healthScore['details']['saving']['rate']
                ),
            ];
        }

        // 2. 分散投资建议
        if ($healthScore['details']['diversification']['count'] < 3) {
            $suggestions[] = [
                'type' => 'info',
                'title' => '建议分散投资',
                'message' => sprintf(
                    '您当前投资了 %d 个产品，建议分散投资到至少3个不同的理财产品，降低风险。',
                    $healthScore['details']['diversification']['count']
                ),
            ];
        }

        // 3. 高收益产品推荐
        $productRanking = self::getProductReturnRanking();
        if (!empty($productRanking)) {
            $bestProduct = $productRanking[0];
            if ($bestProduct['roi'] > 5) {
                $suggestions[] = [
                    'type' => 'success',
                    'title' => '优质产品推荐',
                    'message' => sprintf(
                        '"%s" 的收益率达到 %.2f%%，建议增加投资。',
                        $bestProduct['product_name'],
                        $bestProduct['roi']
                    ),
                ];
            }
        }

        // 4. 低效产品警告
        if (count($productRanking) > 1) {
            $worstProduct = end($productRanking);
            if ($worstProduct['roi'] < 1 && $worstProduct['total_investment'] > 0) {
                $suggestions[] = [
                    'type' => 'warning',
                    'title' => '低效产品提示',
                    'message' => sprintf(
                        '"%s" 的收益率仅为 %.2f%%，建议考虑调整投资组合。',
                        $worstProduct['product_name'],
                        $worstProduct['roi']
                    ),
                ];
            }
        }

        // 5. 目标进度提醒
        $overdueGoals = FinancialGoal::find()
            ->where(['status' => FinancialGoal::STATUS_IN_PROGRESS])
            ->andWhere(['<', 'target_date', date('Y-m-d')])
            ->count();

        if ($overdueGoals > 0) {
            $suggestions[] = [
                'type' => 'danger',
                'title' => '目标延期提醒',
                'message' => sprintf(
                    '您有 %d 个目标已经延期，请及时调整储蓄计划。',
                    $overdueGoals
                ),
            ];
        }

        return $suggestions;
    }

    /**
     * 获取月度收支趋势数据（用于图表）
     *
     * @param int $months 月份数量
     * @return array
     */
    public static function getMonthlyTrendData($months = 12)
    {
        $data = [];
        $startDate = date('Y-m-01', strtotime("-{$months} months"));

        for ($i = $months - 1; $i >= 0; $i--) {
            $monthStart = date('Y-m-01', strtotime("-{$i} months"));
            $monthEnd = date('Y-m-t', strtotime("-{$i} months"));
            $monthLabel = date('Y-m', strtotime("-{$i} months"));

            // 月度收入
            $monthlyIncome = Income::find()
                ->where(['>=', 'income_date', $monthStart])
                ->andWhere(['<=', 'income_date', $monthEnd])
                ->sum('amount') ?: 0;

            // 月度收益
            $monthlyReturn = ReturnRecord::find()
                ->where(['>=', 'return_date', $monthStart])
                ->andWhere(['<=', 'return_date', $monthEnd])
                ->sum('total_amount') ?: 0;

            // 月度投资
            $monthlyInvestment = Investment::find()
                ->where(['>=', 'investment_date', $monthStart])
                ->andWhere(['<=', 'investment_date', $monthEnd])
                ->sum('amount') ?: 0;

            $data[] = [
                'month' => $monthLabel,
                'income' => round($monthlyIncome, 2),
                'return' => round($monthlyReturn, 2),
                'investment' => round($monthlyInvestment, 2),
            ];
        }

        return $data;
    }
}
