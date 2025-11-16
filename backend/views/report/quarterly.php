<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $report array */
/* @var $year int */
/* @var $quarter int */
/* @var $availableYears array */
/* @var $quarters array */

$this->title = $year . ' 年第' . $quarter . '季度财务报表';
$this->params['breadcrumbs'][] = ['label' => '财务报表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="report-quarterly">

    <div class="page-header">
        <h1>
            <i class="glyphicon glyphicon-th"></i> <?= Html::encode($this->title) ?>
        </h1>
        <div class="pull-right">
            <?= Html::a('<i class="glyphicon glyphicon-print"></i> 打印报表', 'javascript:window.print()', ['class' => 'btn btn-default']) ?>
            <?= Html::a('<i class="glyphicon glyphicon-save"></i> 导出PDF', ['export', 'type' => 'quarterly', 'year' => $year, 'quarter' => $quarter], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="glyphicon glyphicon-arrow-left"></i> 返回', ['index'], ['class' => 'btn btn-default']) ?>
        </div>
    </div>

    <hr>

    <!-- 季度切换 -->
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="btn-group" role="group">
                        <?php
                        $prevQuarter = $quarter - 1;
                        $prevYear = $year;
                        if ($prevQuarter < 1) {
                            $prevQuarter = 4;
                            $prevYear--;
                        }

                        $nextQuarter = $quarter + 1;
                        $nextYear = $year;
                        if ($nextQuarter > 4) {
                            $nextQuarter = 1;
                            $nextYear++;
                        }

                        $currentYear = date('Y');
                        $currentQuarter = ceil(date('n') / 3);
                        ?>
                        <?= Html::a(
                            '<i class="glyphicon glyphicon-chevron-left"></i> 上季度',
                            ['quarterly', 'year' => $prevYear, 'quarter' => $prevQuarter],
                            ['class' => 'btn btn-default']
                        ) ?>
                        <button type="button" class="btn btn-success" disabled>
                            <?= Html::encode($year) ?> Q<?= $quarter ?>
                        </button>
                        <?php if ($nextYear < $currentYear || ($nextYear == $currentYear && $nextQuarter <= $currentQuarter)): ?>
                            <?= Html::a(
                                '下季度 <i class="glyphicon glyphicon-chevron-right"></i>',
                                ['quarterly', 'year' => $nextYear, 'quarter' => $nextQuarter],
                                ['class' => 'btn btn-default']
                            ) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6 text-right">
                    <form class="form-inline" method="get" action="<?= Url::to(['quarterly']) ?>">
                        <div class="form-group">
                            <label>年份：</label>
                            <select name="year" class="form-control">
                                <?php foreach ($availableYears as $y): ?>
                                    <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>>
                                        <?= $y ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>季度：</label>
                            <select name="quarter" class="form-control" onchange="this.form.submit()">
                                <?php for ($q = 1; $q <= 4; $q++): ?>
                                    <option value="<?= $q ?>" <?= $q == $quarter ? 'selected' : '' ?>>
                                        Q<?= $q ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 季度概览 -->
    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="glyphicon glyphicon-dashboard"></i> <?= $year ?> 年第<?= $quarter ?>季度概览
            </h3>
        </div>
        <div class="panel-body">
            <p><strong>周期：</strong> <?= Html::encode($report['start_date']) ?> ~ <?= Html::encode($report['end_date']) ?></p>
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-success">
                        <h4>季度财务表现：</h4>
                        <ul>
                            <li>
                                <strong>收入：</strong> ¥<?= number_format($report['income']['total'], 2) ?>
                                （<?= $report['income']['count'] ?> 笔，平均每笔 ¥<?= number_format($report['income']['average'], 2) ?>）
                            </li>
                            <li>
                                <strong>投资：</strong> ¥<?= number_format($report['investment']['total'], 2) ?>
                                （<?= $report['investment']['count'] ?> 笔，储蓄率 <?= number_format($report['key_metrics']['savings_rate'], 1) ?>%）
                            </li>
                            <li>
                                <strong>收益：</strong> ¥<?= number_format($report['return']['total'], 2) ?>
                                （<?= $report['return']['count'] ?> 笔，收益率 <?= number_format($report['key_metrics']['investment_return_rate'], 2) ?>%）
                            </li>
                            <li>
                                <strong>资产增长：</strong>
                                <span class="<?= $report['assets']['net_growth'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= $report['assets']['net_growth'] >= 0 ? '+' : '' ?>¥<?= number_format($report['assets']['net_growth'], 2) ?>
                                    (<?= number_format($report['assets']['growth_rate'], 2) ?>%)
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 报表摘要（复用组件） -->
    <?= $this->render('_report_summary', ['report' => $report]) ?>

</div>

<style media="print">
    .btn, .form-inline, .page-header .pull-right, .breadcrumb {
        display: none !important;
    }
</style>
