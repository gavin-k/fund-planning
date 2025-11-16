<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $report array */
/* @var $month string */
/* @var $availableMonths array */

$this->title = $month . ' 月度财务报表';
$this->params['breadcrumbs'][] = ['label' => '财务报表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="report-monthly">

    <div class="page-header">
        <h1>
            <i class="glyphicon glyphicon-calendar"></i> <?= Html::encode($this->title) ?>
        </h1>
        <div class="pull-right">
            <?= Html::a('<i class="glyphicon glyphicon-print"></i> 打印报表', 'javascript:window.print()', ['class' => 'btn btn-default']) ?>
            <?= Html::a('<i class="glyphicon glyphicon-save"></i> 导出PDF', ['export', 'type' => 'monthly', 'month' => $month], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="glyphicon glyphicon-arrow-left"></i> 返回', ['index'], ['class' => 'btn btn-default']) ?>
        </div>
    </div>

    <hr>

    <!-- 月份切换 -->
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="btn-group" role="group">
                        <?php
                        $prevMonth = date('Y-m', strtotime('-1 month', strtotime($month . '-01')));
                        $nextMonth = date('Y-m', strtotime('+1 month', strtotime($month . '-01')));
                        ?>
                        <?= Html::a(
                            '<i class="glyphicon glyphicon-chevron-left"></i> 上月',
                            ['monthly', 'month' => $prevMonth],
                            ['class' => 'btn btn-default']
                        ) ?>
                        <button type="button" class="btn btn-primary" disabled>
                            <?= Html::encode($month) ?>
                        </button>
                        <?php if ($nextMonth <= date('Y-m')): ?>
                            <?= Html::a(
                                '下月 <i class="glyphicon glyphicon-chevron-right"></i>',
                                ['monthly', 'month' => $nextMonth],
                                ['class' => 'btn btn-default']
                            ) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6 text-right">
                    <form class="form-inline" method="get" action="<?= Url::to(['monthly']) ?>">
                        <div class="form-group">
                            <label>快速切换：</label>
                            <select name="month" class="form-control" onchange="this.form.submit()">
                                <?php foreach ($availableMonths as $m): ?>
                                    <option value="<?= $m ?>" <?= $m === $month ? 'selected' : '' ?>>
                                        <?= $m ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 同比环比数据 -->
    <?php if (isset($report['comparison'])): ?>
        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="glyphicon glyphicon-resize-horizontal"></i> 环比数据（与上月对比）
                        </h3>
                    </div>
                    <div class="panel-body">
                        <p><strong>对比月份：</strong><?= $report['comparison']['mom']['period'] ?></p>
                        <div class="row">
                            <div class="col-sm-6 text-center">
                                <div style="font-size: 32px; font-weight: bold; color: <?= $report['comparison']['mom']['income_change'] >= 0 ? '#5cb85c' : '#d9534f' ?>;">
                                    <?= $report['comparison']['mom']['income_change'] >= 0 ? '+' : '' ?><?= number_format($report['comparison']['mom']['income_change'], 2) ?>%
                                </div>
                                <div style="color: #777;">收入环比变化</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="alert alert-<?= $report['comparison']['mom']['income_change'] >= 0 ? 'success' : 'warning' ?>" style="margin-bottom: 0;">
                                    <?php if ($report['comparison']['mom']['income_change'] >= 0): ?>
                                        <i class="glyphicon glyphicon-arrow-up"></i> 收入较上月增长
                                    <?php else: ?>
                                        <i class="glyphicon glyphicon-arrow-down"></i> 收入较上月下降
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="panel panel-warning">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="glyphicon glyphicon-repeat"></i> 同比数据（与去年同期对比）
                        </h3>
                    </div>
                    <div class="panel-body">
                        <p><strong>对比月份：</strong> <?= $report['comparison']['yoy']['period'] ?></p>
                        <div class="row">
                            <div class="col-sm-6 text-center">
                                <div style="font-size: 32px; font-weight: bold; color: <?= $report['comparison']['yoy']['income_change'] >= 0 ? '#5cb85c' : '#d9534f' ?>;">
                                    <?= $report['comparison']['yoy']['income_change'] >= 0 ? '+' : '' ?><?= number_format($report['comparison']['yoy']['income_change'], 2) ?>%
                                </div>
                                <div style="color: #777;">收入同比变化</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="alert alert-<?= $report['comparison']['yoy']['income_change'] >= 0 ? 'success' : 'warning' ?>" style="margin-bottom: 0;">
                                    <?php if ($report['comparison']['yoy']['income_change'] >= 0): ?>
                                        <i class="glyphicon glyphicon-arrow-up"></i> 收入较去年增长
                                    <?php else: ?>
                                        <i class="glyphicon glyphicon-arrow-down"></i> 收入较去年下降
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- 报表摘要（复用组件） -->
    <?= $this->render('_report_summary', ['report' => $report]) ?>

</div>

<style media="print">
    .btn, .form-inline, .page-header .pull-right, .breadcrumb {
        display: none !important;
    }
</style>
