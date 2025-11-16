<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $startDate string */
/* @var $endDate string */
/* @var $periodIncome float */
/* @var $periodReturn float */
/* @var $periodInvestment float */
/* @var $totalInvestment float */
/* @var $totalReturn float */
/* @var $returnRate float */
/* @var $fundReturns array */
/* @var $monthlyData string */
/* @var $fundInvestmentData string */

$this->title = '统计分析';
$this->params['breadcrumbs'][] = $this->title;

// 注册 Chart.js
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', [
    'position' => \yii\web\View::POS_HEAD
]);
?>

<div class="statistics-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <!-- 时间筛选 -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="glyphicon glyphicon-calendar"></i> 时间范围筛选
            </h3>
        </div>
        <div class="panel-body">
            <form method="get" class="form-inline">
                <div class="form-group">
                    <label>开始日期：</label>
                    <input type="date" name="start_date" class="form-control" value="<?= Html::encode($startDate) ?>">
                </div>
                <div class="form-group" style="margin-left: 10px;">
                    <label>结束日期：</label>
                    <input type="date" name="end_date" class="form-control" value="<?= Html::encode($endDate) ?>">
                </div>
                <button type="submit" class="btn btn-primary" style="margin-left: 10px;">
                    <i class="glyphicon glyphicon-search"></i> 查询
                </button>
                <?= Html::a(
                    '<i class="glyphicon glyphicon-refresh"></i> 重置',
                    ['index'],
                    ['class' => 'btn btn-default', 'style' => 'margin-left: 5px;']
                ) ?>
            </form>
        </div>
    </div>

    <!-- 期间统计卡片 -->
    <div class="row">
        <div class="col-md-3">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="glyphicon glyphicon-arrow-down" style="font-size: 40px;"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div style="font-size: 20px;">¥<?= number_format($periodIncome, 2) ?></div>
                            <div>期间收入</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="glyphicon glyphicon-arrow-up" style="font-size: 40px;"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div style="font-size: 20px;">¥<?= number_format($periodReturn, 2) ?></div>
                            <div>期间收益</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="glyphicon glyphicon-transfer" style="font-size: 40px;"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div style="font-size: 20px;">¥<?= number_format($periodInvestment, 2) ?></div>
                            <div>期间投资</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="glyphicon glyphicon-stats" style="font-size: 40px;"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div style="font-size: 20px;"><?= number_format($returnRate, 2) ?>%</div>
                            <div>总收益率</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 图表区域 -->
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-signal"></i> <?= date('Y') ?>年月度收支对比
                    </h3>
                </div>
                <div class="panel-body" style="height: 400px;">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-folder-open"></i> 各基金投资占比
                    </h3>
                </div>
                <div class="panel-body" style="height: 400px;">
                    <canvas id="fundInvestmentChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- 各基金收益率详情 -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="glyphicon glyphicon-list-alt"></i> 各基金收益详情
            </h3>
        </div>
        <div class="panel-body">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>基金名称</th>
                        <th class="text-right">当前余额</th>
                        <th class="text-right">累计投资</th>
                        <th class="text-right">累计收益</th>
                        <th class="text-right">收益率</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fundReturns as $fund): ?>
                        <tr>
                            <td><strong><?= Html::encode($fund['name']) ?></strong></td>
                            <td class="text-right">¥<?= number_format($fund['balance'], 2) ?></td>
                            <td class="text-right">¥<?= number_format($fund['invested'], 2) ?></td>
                            <td class="text-right text-success">
                                <strong>¥<?= number_format($fund['returns'], 2) ?></strong>
                            </td>
                            <td class="text-right">
                                <span class="label label-<?= $fund['rate'] > 0 ? 'success' : 'default' ?>">
                                    <?= number_format($fund['rate'], 2) ?>%
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="info">
                        <td><strong>合计</strong></td>
                        <td class="text-right">
                            <strong>¥<?= number_format(array_sum(array_column($fundReturns, 'balance')), 2) ?></strong>
                        </td>
                        <td class="text-right">
                            <strong>¥<?= number_format($totalInvestment, 2) ?></strong>
                        </td>
                        <td class="text-right">
                            <strong>¥<?= number_format($totalReturn, 2) ?></strong>
                        </td>
                        <td class="text-right">
                            <strong><?= number_format($returnRate, 2) ?>%</strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php
// 注册图表初始化 JavaScript
$this->registerJs(<<<JS
// 月度收支对比柱状图
const monthlyCtx = document.getElementById('monthlyChart');
if (monthlyCtx) {
    new Chart(monthlyCtx, {
        type: 'bar',
        data: $monthlyData,
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

// 各基金投资占比饼图
const fundInvCtx = document.getElementById('fundInvestmentChart');
if (fundInvCtx) {
    new Chart(fundInvCtx, {
        type: 'doughnut',
        data: $fundInvestmentData,
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
JS
, \yii\web\View::POS_READY);
?>
