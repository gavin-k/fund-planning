<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $totalAssets float */
/* @var $funds common\models\Fund[] */
/* @var $totalInvestment float */
/* @var $monthlyIncome float */
/* @var $monthlyReturn float */
/* @var $recentIncomes common\models\Income[] */
/* @var $recentInvestments common\models\Investment[] */
/* @var $healthScore array */
/* @var $suggestions array */
/* @var $activeGoals common\models\FinancialGoal[] */
/* @var $fundChartData string */
/* @var $trendChartData string */
/* @var $investmentChartData string */

$this->title = '理财计划 Dashboard';

// 注册 Chart.js
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', [
    'position' => \yii\web\View::POS_HEAD
]);
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
                <a href="<?= Url::to(['fund/index']) ?>">
                    <div class="panel-footer">
                        <span class="pull-left">查看详情</span>
                        <span class="pull-right"><i class="glyphicon glyphicon-circle-arrow-right"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
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
                <a href="<?= Url::to(['investment/index']) ?>">
                    <div class="panel-footer">
                        <span class="pull-left">查看详情</span>
                        <span class="pull-right"><i class="glyphicon glyphicon-circle-arrow-right"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
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
                <a href="<?= Url::to(['income/index']) ?>">
                    <div class="panel-footer">
                        <span class="pull-left">查看详情</span>
                        <span class="pull-right"><i class="glyphicon glyphicon-circle-arrow-right"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
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
                <a href="<?= Url::to(['return/index']) ?>">
                    <div class="panel-footer">
                        <span class="pull-left">查看详情</span>
                        <span class="pull-right"><i class="glyphicon glyphicon-circle-arrow-right"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- 财务健康评分卡片 -->
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-<?= $healthScore['total_score'] >= 75 ? 'success' : ($healthScore['total_score'] >= 60 ? 'info' : 'warning') ?>">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-heart"></i> 财务健康评分
                        <a href="<?= Url::to(['analysis/index']) ?>" class="pull-right" style="color: white; text-decoration: underline;">
                            查看详细分析 <i class="glyphicon glyphicon-chevron-right"></i>
                        </a>
                    </h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div style="font-size: 60px; font-weight: bold; color: <?= $healthScore['total_score'] >= 75 ? '#5cb85c' : ($healthScore['total_score'] >= 60 ? '#5bc0de' : '#f0ad4e') ?>;">
                                <?= $healthScore['total_score'] ?>
                            </div>
                            <div style="font-size: 20px; margin-top: -10px;">分</div>
                            <div style="margin-top: 10px;">
                                <span class="label label-<?= $healthScore['total_score'] >= 75 ? 'success' : ($healthScore['total_score'] >= 60 ? 'info' : 'warning') ?>" style="font-size: 14px;">
                                    <?= Html::encode($healthScore['rating']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <?php foreach ($healthScore['details'] as $key => $detail): ?>
                                <?php
                                $maxScore = ($key === 'saving') ? 30 : (($key === 'diversification' || $key === 'stability') ? 25 : 20);
                                $percentage = ($detail['score'] / $maxScore) * 100;
                                $colorClass = $percentage >= 80 ? 'success' : ($percentage >= 60 ? 'info' : 'warning');
                                ?>
                                <div style="margin-bottom: 10px;">
                                    <div class="clearfix">
                                        <small class="pull-left"><strong><?= Html::encode($detail['description']) ?></strong></small>
                                        <small class="pull-right"><?= $detail['score'] ?>/<?= $maxScore ?></small>
                                    </div>
                                    <div class="progress" style="height: 18px; margin-bottom: 0;">
                                        <div class="progress-bar progress-bar-<?= $colorClass ?>" style="width: <?= $percentage ?>%;">
                                            <?= round($percentage, 0) ?>%
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 智能建议 -->
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-lightbulb"></i> 智能理财建议
                    </h3>
                </div>
                <div class="panel-body" style="max-height: 300px; overflow-y: auto;">
                    <?php if (!empty($suggestions)): ?>
                        <?php foreach ($suggestions as $suggestion): ?>
                            <div class="alert alert-<?= $suggestion['type'] ?>" style="margin-bottom: 10px; padding: 10px;">
                                <strong><?= Html::encode($suggestion['title']) ?></strong>
                                <p style="margin: 5px 0 0 0; font-size: 12px;"><?= Html::encode($suggestion['message']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">暂无建议。您的财务状况良好！</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 财务目标进度 -->
    <?php if (!empty($activeGoals)): ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="glyphicon glyphicon-flag"></i> 进行中的财务目标
                    <a href="<?= Url::to(['goal/index']) ?>" class="pull-right">
                        查看全部 <i class="glyphicon glyphicon-chevron-right"></i>
                    </a>
                </h3>
            </div>
            <div class="panel-body">
                <?php foreach ($activeGoals as $goal): ?>
                    <?php
                    $progress = $goal->getProgress();
                    $colorClass = $progress >= 75 ? 'success' : ($progress >= 50 ? 'info' : ($progress >= 25 ? 'warning' : 'danger'));
                    ?>
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-md-3">
                            <strong><?= Html::a(Html::encode($goal->name), ['goal/view', 'id' => $goal->id]) ?></strong>
                            <br>
                            <small class="text-muted">目标日期: <?= Html::encode($goal->target_date) ?></small>
                        </div>
                        <div class="col-md-3 text-right">
                            <span style="font-size: 14px;">
                                ¥<?= number_format($goal->current_amount, 2) ?> / ¥<?= number_format($goal->target_amount, 2) ?>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <div class="progress" style="margin-bottom: 5px;">
                                <div class="progress-bar progress-bar-<?= $colorClass ?>" style="width: <?= min(100, $progress) ?>%;">
                                    <?= round($progress, 0) ?>%
                                </div>
                            </div>
                            <small class="text-muted">
                                剩余 <?= $goal->getRemainingDays() ?> 天 • 建议月储蓄 ¥<?= number_format($goal->getSuggestedMonthlySaving(), 2) ?>
                            </small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- 各基金余额一览 -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="glyphicon glyphicon-folder-open"></i> 各基金余额一览
            </h3>
        </div>
        <div class="panel-body">
            <?php if (!empty($funds)): ?>
                <?php foreach ($funds as $fund): ?>
                    <?php
                        $percentage = $totalAssets > 0 ? ($fund->current_balance / $totalAssets * 100) : 0;
                        $progressBarClass = 'progress-bar-success';
                        if ($percentage < 5) {
                            $progressBarClass = 'progress-bar-danger';
                        } elseif ($percentage < 10) {
                            $progressBarClass = 'progress-bar-warning';
                        }
                    ?>
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-md-3">
                            <strong><?= Html::a(Html::encode($fund->name), ['fund/view', 'id' => $fund->id]) ?></strong>
                            <small class="text-muted">(分配比例: <?= $fund->allocation_percent ?>%)</small>
                        </div>
                        <div class="col-md-3 text-right">
                            <span class="text-success" style="font-size: 16px;">
                                <strong>¥<?= number_format($fund->current_balance, 2) ?></strong>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <div class="progress" style="margin-bottom: 0;">
                                <div class="progress-bar <?= $progressBarClass ?>"
                                     role="progressbar"
                                     style="width: <?= $percentage ?>%; min-width: 2em;">
                                    <?= number_format($percentage, 1) ?>%
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">暂无基金数据，请先 <?= Html::a('创建基金', ['fund/create']) ?></p>
            <?php endif; ?>
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
                    <?php if (!empty($recentIncomes)): ?>
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>日期</th>
                                    <th class="text-right">金额</th>
                                    <th>备注</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentIncomes as $income): ?>
                                    <tr>
                                        <td><?= Html::encode($income->income_date) ?></td>
                                        <td class="text-right">
                                            <strong class="text-success">+¥<?= number_format($income->amount, 2) ?></strong>
                                        </td>
                                        <td>
                                            <?= Html::encode($income->note ? mb_substr($income->note, 0, 20) . (mb_strlen($income->note) > 20 ? '...' : '') : '-') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?= Html::a('查看全部 »', ['income/index'], ['class' => 'btn btn-sm btn-success']) ?>
                    <?php else: ?>
                        <p class="text-muted">暂无收入记录</p>
                        <?= Html::a('<i class="glyphicon glyphicon-plus"></i> 记录收入', ['income/create'], ['class' => 'btn btn-sm btn-success']) ?>
                    <?php endif; ?>
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
                    <?php if (!empty($recentInvestments)): ?>
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>日期</th>
                                    <th>基金</th>
                                    <th>产品</th>
                                    <th class="text-right">金额</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentInvestments as $investment): ?>
                                    <tr>
                                        <td><?= date('Y-m-d', $investment->created_at) ?></td>
                                        <td><?= Html::encode($investment->fund->name ?? '-') ?></td>
                                        <td><?= Html::encode($investment->product->name ?? '-') ?></td>
                                        <td class="text-right">¥<?= number_format($investment->amount, 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?= Html::a('查看全部 »', ['investment/index'], ['class' => 'btn btn-sm btn-info']) ?>
                    <?php else: ?>
                        <p class="text-muted">暂无投资记录</p>
                        <?= Html::a('<i class="glyphicon glyphicon-plus"></i> 新建投资', ['investment/create'], ['class' => 'btn btn-sm btn-info']) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 数据可视化图表 -->
    <div class="row">
        <!-- 基金余额饼图 -->
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-stats"></i> 基金余额分布
                    </h3>
                </div>
                <div class="panel-body" style="height: 350px;">
                    <canvas id="fundBalanceChart"></canvas>
                </div>
            </div>
        </div>

        <!-- 投资产品分布柱状图 -->
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-briefcase"></i> 投资产品分布
                    </h3>
                </div>
                <div class="panel-body" style="height: 350px;">
                    <canvas id="investmentDistChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- 收益趋势折线图 -->
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-signal"></i> 近12个月收入收益趋势
                    </h3>
                </div>
                <div class="panel-body" style="height: 300px;">
                    <canvas id="trendChart"></canvas>
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
                '<i class="glyphicon glyphicon-folder-open"></i> 管理基金',
                ['fund/index'],
                ['class' => 'btn btn-primary btn-lg', 'style' => 'margin: 5px;']
            ) ?>
            <?= Html::a(
                '<i class="glyphicon glyphicon-shopping-cart"></i> 管理产品',
                ['product/index'],
                ['class' => 'btn btn-default btn-lg', 'style' => 'margin: 5px;']
            ) ?>
        </div>
    </div>
</div>

<style>
.panel-heading a {
    text-decoration: none;
}
.panel-footer {
    background-color: #fff;
}
.panel-footer a {
    color: #999;
}
.panel-footer a:hover {
    color: #333;
}
</style>

<?php
// 注册图表初始化 JavaScript
$this->registerJs(<<<JS
// 基金余额饼图
const fundCtx = document.getElementById('fundBalanceChart');
if (fundCtx) {
    new Chart(fundCtx, {
        type: 'pie',
        data: $fundChartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
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
                            label += '¥' + context.parsed.toLocaleString('zh-CN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            return label;
                        }
                    }
                }
            }
        }
    });
}

// 投资产品分布柱状图
const invCtx = document.getElementById('investmentDistChart');
if (invCtx) {
    new Chart(invCtx, {
        type: 'bar',
        data: $investmentChartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
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
                            return '¥' + value.toLocaleString('zh-CN');
                        }
                    }
                }
            }
        }
    });
}

// 收益趋势折线图
const trendCtx = document.getElementById('trendChart');
if (trendCtx) {
    new Chart(trendCtx, {
        type: 'line',
        data: $trendChartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
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
                            return '¥' + value.toLocaleString('zh-CN');
                        }
                    }
                }
            }
        }
    });
}
JS
, \yii\web\View::POS_READY);
?>
