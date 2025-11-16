<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $startDate string */
/* @var $endDate string */
/* @var $overallReturn array */
/* @var $annualizedRate float */
/* @var $fundRanking array */
/* @var $productRanking array */
/* @var $healthScore array */
/* @var $suggestions array */
/* @var $chartData array */

$this->title = '收益分析';
$this->params['breadcrumbs'][] = $this->title;

// 注册 Chart.js
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', [
    'position' => \yii\web\View::POS_HEAD
]);
?>

<div class="analysis-index">
    <div class="row">
        <div class="col-md-8">
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
        <div class="col-md-4 text-right">
            <form method="get" class="form-inline" style="margin-top: 20px;">
                <div class="form-group">
                    <input type="date" name="start_date" class="form-control" value="<?= Html::encode($startDate) ?>" placeholder="开始日期">
                </div>
                <div class="form-group" style="margin-left: 5px;">
                    <input type="date" name="end_date" class="form-control" value="<?= Html::encode($endDate) ?>" placeholder="结束日期">
                </div>
                <button type="submit" class="btn btn-primary" style="margin-left: 5px;">
                    <i class="glyphicon glyphicon-filter"></i> 筛选
                </button>
                <?php if ($startDate || $endDate): ?>
                    <a href="<?= Url::to(['index']) ?>" class="btn btn-default" style="margin-left: 5px;">
                        <i class="glyphicon glyphicon-remove"></i> 清除
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <hr>

    <!-- 财务健康评分卡片 -->
    <div class="panel panel-<?= $healthScore['total_score'] >= 75 ? 'success' : ($healthScore['total_score'] >= 60 ? 'info' : 'warning') ?>">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="glyphicon glyphicon-heart"></i> 财务健康评分
            </h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-3 text-center">
                    <div style="font-size: 72px; font-weight: bold; color: <?= $healthScore['total_score'] >= 75 ? '#5cb85c' : ($healthScore['total_score'] >= 60 ? '#5bc0de' : '#f0ad4e') ?>;">
                        <?= $healthScore['total_score'] ?>
                    </div>
                    <div style="font-size: 24px; margin-top: -10px;">分</div>
                    <div style="margin-top: 10px;">
                        <span class="label label-<?= $healthScore['total_score'] >= 75 ? 'success' : ($healthScore['total_score'] >= 60 ? 'info' : 'warning') ?>" style="font-size: 16px;">
                            <?= Html::encode($healthScore['rating']) ?>
                        </span>
                    </div>
                </div>
                <div class="col-md-9">
                    <h4>评分详情</h4>
                    <?php foreach ($healthScore['details'] as $key => $detail): ?>
                        <div style="margin-bottom: 15px;">
                            <div class="clearfix">
                                <strong class="pull-left"><?= Html::encode($detail['description']) ?></strong>
                                <span class="pull-right"><?= $detail['score'] ?> 分</span>
                            </div>
                            <div class="progress" style="margin-bottom: 5px;">
                                <?php
                                $maxScore = ($key === 'saving') ? 30 : (($key === 'diversification' || $key === 'stability') ? 25 : 20);
                                $percentage = ($detail['score'] / $maxScore) * 100;
                                $colorClass = $percentage >= 80 ? 'success' : ($percentage >= 60 ? 'info' : 'warning');
                                ?>
                                <div class="progress-bar progress-bar-<?= $colorClass ?>" style="width: <?= $percentage ?>%;">
                                    <?= round($percentage, 0) ?>%
                                </div>
                            </div>
                            <small class="text-muted">
                                <?php if ($key === 'saving'): ?>
                                    储蓄率: <?= $detail['rate'] ?>%
                                <?php elseif ($key === 'diversification'): ?>
                                    投资产品数: <?= $detail['count'] ?>
                                <?php elseif ($key === 'stability'): ?>
                                    近3月收益次数: <?= $detail['count'] ?>
                                <?php else: ?>
                                    已完成: <?= $detail['completed'] ?> / 总数: <?= $detail['total'] ?>
                                <?php endif; ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 整体收益率概览 -->
    <div class="row" style="margin-bottom: 20px;">
        <div class="col-md-3">
            <div class="panel panel-primary">
                <div class="panel-body text-center">
                    <div style="font-size: 14px; color: #777;">总资产</div>
                    <div style="font-size: 28px; font-weight: bold; color: #337ab7;">
                        ¥<?= number_format($overallReturn['total_assets'], 2) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel panel-info">
                <div class="panel-body text-center">
                    <div style="font-size: 14px; color: #777;">总收入</div>
                    <div style="font-size: 28px; font-weight: bold; color: #5bc0de;">
                        ¥<?= number_format($overallReturn['total_income'], 2) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel panel-success">
                <div class="panel-body text-center">
                    <div style="font-size: 14px; color: #777;">总收益</div>
                    <div style="font-size: 28px; font-weight: bold; color: #5cb85c;">
                        ¥<?= number_format($overallReturn['total_return'], 2) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel panel-warning">
                <div class="panel-body text-center">
                    <div style="font-size: 14px; color: #777;">收益率</div>
                    <div style="font-size: 28px; font-weight: bold; color: #f0ad4e;">
                        <?= number_format($overallReturn['return_rate'], 2) ?>%
                    </div>
                    <?php if ($annualizedRate !== null): ?>
                        <div style="font-size: 12px; color: #999; margin-top: 5px;">
                            年化: <?= number_format($annualizedRate, 2) ?>%
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 智能建议 -->
    <?php if (!empty($suggestions)): ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="glyphicon glyphicon-lightbulb"></i> 智能理财建议
                </h3>
            </div>
            <div class="panel-body">
                <?php foreach ($suggestions as $suggestion): ?>
                    <div class="alert alert-<?= $suggestion['type'] ?>" style="margin-bottom: 10px;">
                        <strong><?= Html::encode($suggestion['title']) ?></strong>
                        <p style="margin: 5px 0 0 0;"><?= Html::encode($suggestion['message']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- 图表区域 -->
    <div class="row">
        <!-- 基金收益率排行 -->
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-signal"></i> 各基金收益率排行
                    </h3>
                </div>
                <div class="panel-body">
                    <canvas id="fundReturnChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- 产品收益率对比 -->
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-stats"></i> 理财产品收益对比
                    </h3>
                </div>
                <div class="panel-body">
                    <canvas id="productReturnChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- 月度趋势 -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="glyphicon glyphicon-trending-up"></i> 近12个月收支趋势
            </h3>
        </div>
        <div class="panel-body">
            <canvas id="trendChart" height="100"></canvas>
        </div>
    </div>

    <!-- 数据表格 -->
    <div class="row">
        <!-- 基金收益详情 -->
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">基金收益详情</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>基金名称</th>
                                <th class="text-right">分配收入</th>
                                <th class="text-right">分配收益</th>
                                <th class="text-right">收益率</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fundRanking as $fund): ?>
                                <tr>
                                    <td><?= Html::encode($fund['fund_name']) ?></td>
                                    <td class="text-right">¥<?= number_format($fund['income'], 2) ?></td>
                                    <td class="text-right">¥<?= number_format($fund['return'], 2) ?></td>
                                    <td class="text-right">
                                        <span class="label label-<?= $fund['return_rate'] >= 5 ? 'success' : ($fund['return_rate'] >= 2 ? 'info' : 'default') ?>">
                                            <?= number_format($fund['return_rate'], 2) ?>%
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 产品收益详情 -->
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">理财产品详情</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>产品名称</th>
                                <th class="text-right">总投资</th>
                                <th class="text-right">总收益</th>
                                <th class="text-right">ROI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productRanking as $product): ?>
                                <tr>
                                    <td>
                                        <?= Html::encode($product['product_name']) ?>
                                        <br>
                                        <small class="text-muted"><?= Html::encode($product['platform']) ?></small>
                                    </td>
                                    <td class="text-right">¥<?= number_format($product['total_investment'], 2) ?></td>
                                    <td class="text-right">¥<?= number_format($product['total_return'], 2) ?></td>
                                    <td class="text-right">
                                        <span class="label label-<?= $product['roi'] >= 5 ? 'success' : ($product['roi'] >= 2 ? 'info' : 'default') ?>">
                                            <?= number_format($product['roi'], 2) ?>%
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Chart.js 图表代码
$fundChartData = $chartData['fund'];
$productChartData = $chartData['product'];
$trendChartData = $chartData['trend'];

$this->registerJs(<<<JS
// 基金收益率柱状图
const fundData = $fundChartData;
const fundCtx = document.getElementById('fundReturnChart').getContext('2d');
new Chart(fundCtx, {
    type: 'bar',
    data: {
        labels: fundData.labels,
        datasets: [{
            label: '收益率 (%)',
            data: fundData.data,
            backgroundColor: 'rgba(54, 162, 235, 0.8)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value + '%';
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return '收益率: ' + context.parsed.y.toFixed(2) + '%';
                    }
                }
            }
        }
    }
});

// 产品收益率雷达图
const productData = $productChartData;
const productCtx = document.getElementById('productReturnChart').getContext('2d');
new Chart(productCtx, {
    type: 'radar',
    data: {
        labels: productData.labels,
        datasets: [{
            label: 'ROI (%)',
            data: productData.data,
            fill: true,
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            borderColor: 'rgba(255, 99, 132, 1)',
            pointBackgroundColor: 'rgba(255, 99, 132, 1)',
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: 'rgba(255, 99, 132, 1)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            r: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value + '%';
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'ROI: ' + context.parsed.r.toFixed(2) + '%';
                    }
                }
            }
        }
    }
});

// 月度趋势折线图
const trendData = $trendChartData;
const trendCtx = document.getElementById('trendChart').getContext('2d');
new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: trendData.labels,
        datasets: [
            {
                label: '收入',
                data: trendData.income,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.4
            },
            {
                label: '收益',
                data: trendData.return,
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                tension: 0.4
            },
            {
                label: '投资',
                data: trendData.investment,
                borderColor: 'rgba(255, 206, 86, 1)',
                backgroundColor: 'rgba(255, 206, 86, 0.2)',
                tension: 0.4
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ¥' + context.parsed.y.toFixed(2);
                    }
                }
            }
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
);
?>
