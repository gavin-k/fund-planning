<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $report array */
/* @var $year int */
/* @var $availableYears array */

$this->title = $year . ' 年度财务报表';
$this->params['breadcrumbs'][] = ['label' => '财务报表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="report-annual">

    <div class="page-header">
        <h1>
            <i class="glyphicon glyphicon-book"></i> <?= Html::encode($this->title) ?>
        </h1>
        <div class="pull-right">
            <?= Html::a('<i class="glyphicon glyphicon-print"></i> 打印报表', 'javascript:window.print()', ['class' => 'btn btn-default']) ?>
            <?= Html::a('<i class="glyphicon glyphicon-save"></i> 导出PDF', ['export', 'type' => 'annual', 'year' => $year], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="glyphicon glyphicon-arrow-left"></i> 返回', ['index'], ['class' => 'btn btn-default']) ?>
        </div>
    </div>

    <hr>

    <!-- 年份切换 -->
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="btn-group" role="group">
                        <?php
                        $prevYear = $year - 1;
                        $nextYear = $year + 1;
                        ?>
                        <?= Html::a(
                            '<i class="glyphicon glyphicon-chevron-left"></i> 上年',
                            ['annual', 'year' => $prevYear],
                            ['class' => 'btn btn-default']
                        ) ?>
                        <button type="button" class="btn btn-warning" disabled>
                            <?= Html::encode($year) ?>
                        </button>
                        <?php if ($nextYear <= date('Y')): ?>
                            <?= Html::a(
                                '下年 <i class="glyphicon glyphicon-chevron-right"></i>',
                                ['annual', 'year' => $nextYear],
                                ['class' => 'btn btn-default']
                            ) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6 text-right">
                    <form class="form-inline" method="get" action="<?= Url::to(['annual']) ?>">
                        <div class="form-group">
                            <label>快速切换：</label>
                            <select name="year" class="form-control" onchange="this.form.submit()">
                                <?php foreach ($availableYears as $y): ?>
                                    <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>>
                                        <?= $y ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 年度总结 -->
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="glyphicon glyphicon-star"></i> <?= $year ?> 年度财务总结
            </h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <h4>财务健康度评估：</h4>
                    <?php
                    $savingsRate = $report['key_metrics']['savings_rate'];
                    $returnRate = $report['key_metrics']['investment_return_rate'];
                    $growthRate = $report['assets']['growth_rate'];

                    $score = 0;
                    if ($savingsRate >= 30) $score += 33;
                    elseif ($savingsRate >= 20) $score += 20;
                    elseif ($savingsRate >= 10) $score += 10;

                    if ($returnRate >= 10) $score += 33;
                    elseif ($returnRate >= 5) $score += 20;
                    elseif ($returnRate >= 0) $score += 10;

                    if ($growthRate >= 20) $score += 34;
                    elseif ($growthRate >= 10) $score += 20;
                    elseif ($growthRate >= 0) $score += 10;

                    $healthLevel = $score >= 80 ? '优秀' : ($score >= 60 ? '良好' : ($score >= 40 ? '一般' : '需改进'));
                    $healthColor = $score >= 80 ? 'success' : ($score >= 60 ? 'info' : ($score >= 40 ? 'warning' : 'danger'));
                    ?>
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar progress-bar-<?= $healthColor ?>"
                             style="width: <?= $score ?>%;">
                            <span style="font-size: 16px; line-height: 30px;">
                                财务健康度：<?= $score ?> 分 - <?= $healthLevel ?>
                            </span>
                        </div>
                    </div>

                    <div class="row" style="margin-top: 20px;">
                        <div class="col-md-4">
                            <div class="alert alert-<?= $savingsRate >= 20 ? 'success' : 'warning' ?>">
                                <strong>储蓄习惯：</strong>
                                <?php if ($savingsRate >= 30): ?>
                                    <i class="glyphicon glyphicon-ok"></i> 优秀！储蓄率达到 <?= number_format($savingsRate, 1) ?>%
                                <?php elseif ($savingsRate >= 20): ?>
                                    <i class="glyphicon glyphicon-thumbs-up"></i> 良好！储蓄率为 <?= number_format($savingsRate, 1) ?>%
                                <?php else: ?>
                                    <i class="glyphicon glyphicon-info-sign"></i> 建议提高储蓄率（当前 <?= number_format($savingsRate, 1) ?>%）
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-<?= $returnRate >= 5 ? 'success' : 'info' ?>">
                                <strong>投资收益：</strong>
                                <?php if ($returnRate >= 10): ?>
                                    <i class="glyphicon glyphicon-star"></i> 优秀！收益率达到 <?= number_format($returnRate, 2) ?>%
                                <?php elseif ($returnRate >= 5): ?>
                                    <i class="glyphicon glyphicon-ok"></i> 良好！收益率为 <?= number_format($returnRate, 2) ?>%
                                <?php elseif ($returnRate >= 0): ?>
                                    <i class="glyphicon glyphicon-info-sign"></i> 盈利中（<?= number_format($returnRate, 2) ?>%）
                                <?php else: ?>
                                    <i class="glyphicon glyphicon-warning-sign"></i> 亏损 <?= number_format(abs($returnRate), 2) ?>%
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-<?= $growthRate >= 10 ? 'success' : 'info' ?>">
                                <strong>资产增长：</strong>
                                <?php if ($growthRate >= 20): ?>
                                    <i class="glyphicon glyphicon-fire"></i> 高速增长 <?= number_format($growthRate, 2) %>%
                                <?php elseif ($growthRate >= 10): ?>
                                    <i class="glyphicon glyphicon-arrow-up"></i> 稳定增长 <?= number_format($growthRate, 2) %>%
                                <?php elseif ($growthRate >= 0): ?>
                                    <i class="glyphicon glyphicon-info-sign"></i> 小幅增长 <?= number_format($growthRate, 2) ?>%
                                <?php else: ?>
                                    <i class="glyphicon glyphicon-arrow-down"></i> 资产减少 <?= number_format(abs($growthRate), 2) ?>%
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 同比数据 -->
    <?php if (isset($report['comparison']['yoy'])): ?>
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="glyphicon glyphicon-repeat"></i> 同比分析（与 <?= $report['comparison']['yoy']['period'] ?> 年对比）
                </h3>
            </div>
            <div class="panel-body">
                <div class="row text-center">
                    <div class="col-md-12">
                        <div style="font-size: 48px; font-weight: bold; color: <?= $report['comparison']['yoy']['income_change'] >= 0 ? '#5cb85c' : '#d9534f' ?>;">
                            <?= $report['comparison']['yoy']['income_change'] >= 0 ? '+' : '' ?><?= number_format($report['comparison']['yoy']['income_change'], 2) ?>%
                        </div>
                        <div style="font-size: 20px; color: #777; margin-top: 10px;">
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
    <?php endif; ?>

    <!-- 报表摘要（复用组件） -->
    <?= $this->render('_report_summary', ['report' => $report]) ?>

</div>

<style media="print">
    .btn, .form-inline, .page-header .pull-right, .breadcrumb {
        display: none !important;
    }
</style>
