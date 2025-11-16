<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $report array */
/* @var $startDate string */
/* @var $endDate string */

$this->title = '自定义周期财务报表';
$this->params['breadcrumbs'][] = ['label' => '财务报表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="report-custom">

    <div class="page-header">
        <h1>
            <i class="glyphicon glyphicon-filter"></i> <?= Html::encode($this->title) ?>
            <small><?= Html::encode($startDate) ?> ~ <?= Html::encode($endDate) ?></small>
        </h1>
        <div class="pull-right">
            <?= Html::a('<i class="glyphicon glyphicon-print"></i> 打印报表', 'javascript:window.print()', ['class' => 'btn btn-default']) ?>
            <?= Html::a('<i class="glyphicon glyphicon-save"></i> 导出PDF', ['export', 'type' => 'custom', 'start_date' => $startDate, 'end_date' => $endDate], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="glyphicon glyphicon-cog"></i> 重新选择周期', ['custom'], ['class' => 'btn btn-info']) ?>
            <?= Html::a('<i class="glyphicon glyphicon-arrow-left"></i> 返回', ['index'], ['class' => 'btn btn-default']) ?>
        </div>
    </div>

    <hr>

    <!-- 周期说明 -->
    <div class="panel panel-info">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-4 text-center">
                    <i class="glyphicon glyphicon-calendar" style="font-size: 48px; color: #5bc0de;"></i>
                    <h4 style="margin-top: 10px;">自定义周期</h4>
                </div>
                <div class="col-md-8">
                    <h4>报表周期信息：</h4>
                    <ul class="list-unstyled" style="font-size: 16px;">
                        <li><strong>开始日期：</strong> <?= Html::encode($startDate) ?></li>
                        <li><strong>结束日期：</strong> <?= Html::encode($endDate) ?></li>
                        <li><strong>天数：</strong>
                            <?php
                            $days = (strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24) + 1;
                            echo $days . ' 天';
                            ?>
                        </li>
                        <li><strong>生成时间：</strong> <?= Html::encode($report['generated_at']) ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- 报表摘要（复用组件） -->
    <?= $this->render('_report_summary', ['report' => $report]) ?>

</div>

<style media="print">
    .btn, .page-header .pull-right, .breadcrumb {
        display: none !important;
    }
</style>
