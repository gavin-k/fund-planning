<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\FinancialGoal;

/* @var $this yii\web\View */
/* @var $model common\models\FinancialGoal */
/* @var $stats array */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => '财务目标', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="financial-goal-view">
    <div class="row">
        <div class="col-md-8">
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
        <div class="col-md-4 text-right" style="margin-top: 20px;">
            <?= Html::a('<i class="glyphicon glyphicon-pencil"></i> 编辑', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?php if ($model->status == FinancialGoal::STATUS_IN_PROGRESS && $stats['progress'] >= 100): ?>
                <?= Html::a('<i class="glyphicon glyphicon-ok"></i> 标记为完成', ['complete', 'id' => $model->id], [
                    'class' => 'btn btn-success',
                    'data' => [
                        'confirm' => '确认标记此目标为已完成？',
                        'method' => 'post',
                    ],
                ]) ?>
            <?php endif; ?>
            <?php if ($model->fund_id): ?>
                <?= Html::a('<i class="glyphicon glyphicon-refresh"></i> 同步金额', ['sync', 'id' => $model->id], [
                    'class' => 'btn btn-info',
                    'data' => ['method' => 'post'],
                ]) ?>
            <?php endif; ?>
            <?= Html::a('<i class="glyphicon glyphicon-trash"></i> 删除', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => '确定要删除这个目标吗？',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <hr>

    <!-- 进度卡片 -->
    <div class="panel panel-<?= $stats['progress'] >= 100 ? 'success' : ($stats['progress'] >= 75 ? 'info' : 'warning') ?>">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="glyphicon glyphicon-dashboard"></i> 目标进度
            </h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-4 text-center">
                    <div style="font-size: 64px; font-weight: bold; color: <?= $stats['progress'] >= 100 ? '#5cb85c' : ($stats['progress'] >= 75 ? '#5bc0de' : '#f0ad4e') ?>;">
                        <?= round($stats['progress'], 0) ?>%
                    </div>
                    <div style="font-size: 18px; margin-top: -10px;">完成度</div>

                    <?php if ($model->status == FinancialGoal::STATUS_COMPLETED): ?>
                        <div style="margin-top: 15px;">
                            <span class="label label-success" style="font-size: 14px;">
                                <i class="glyphicon glyphicon-ok"></i> 已完成
                            </span>
                        </div>
                    <?php elseif ($stats['is_overdue']): ?>
                        <div style="margin-top: 15px;">
                            <span class="label label-danger" style="font-size: 14px;">
                                <i class="glyphicon glyphicon-warning-sign"></i> 已延期
                            </span>
                        </div>
                    <?php elseif ($stats['is_due_soon']): ?>
                        <div style="margin-top: 15px;">
                            <span class="label label-warning" style="font-size: 14px;">
                                <i class="glyphicon glyphicon-time"></i> 即将到期
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-8">
                    <div class="progress" style="height: 30px; margin-bottom: 20px;">
                        <?php
                        $colorClass = $stats['progress'] >= 100 ? 'success' : ($stats['progress'] >= 75 ? 'info' : ($stats['progress'] >= 50 ? 'warning' : 'danger'));
                        ?>
                        <div class="progress-bar progress-bar-<?= $colorClass ?> progress-bar-striped" style="width: <?= min(100, $stats['progress']) ?>%; line-height: 30px; font-size: 16px;">
                            <?= round($stats['progress'], 1) ?>%
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="well well-sm">
                                <strong>当前金额：</strong>¥<?= number_format($model->current_amount, 2) ?><br>
                                <strong>目标金额：</strong>¥<?= number_format($model->target_amount, 2) ?><br>
                                <strong>剩余金额：</strong>¥<?= number_format($stats['remaining_amount'], 2) ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="well well-sm">
                                <strong>目标日期：</strong><?= Html::encode($model->target_date) ?><br>
                                <strong>剩余天数：</strong><?= $stats['remaining_days'] ?> 天<br>
                                <strong>建议月储蓄：</strong>¥<?= number_format($stats['suggested_monthly'], 2) ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($stats['estimated_completion']): ?>
                        <div class="alert alert-info">
                            <i class="glyphicon glyphicon-info-sign"></i>
                            按照当前速度，预计在 <strong><?= Html::encode($stats['estimated_completion']) ?></strong> 完成目标。
                        </div>
                    <?php endif; ?>

                    <?php if ($stats['is_overdue']): ?>
                        <div class="alert alert-danger">
                            <i class="glyphicon glyphicon-exclamation-sign"></i>
                            <strong>提示：</strong>目标已延期！建议增加月储蓄额或调整目标日期。
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 基本信息 -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="glyphicon glyphicon-info-sign"></i> 基本信息
            </h3>
        </div>
        <div class="panel-body">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'name',
                    [
                        'attribute' => 'fund_id',
                        'value' => $model->fund ? $model->fund->name : '-',
                        'label' => '关联基金',
                    ],
                    [
                        'attribute' => 'target_amount',
                        'value' => '¥' . number_format($model->target_amount, 2),
                    ],
                    [
                        'attribute' => 'current_amount',
                        'value' => '¥' . number_format($model->current_amount, 2),
                    ],
                    'target_date',
                    'description:ntext',
                    [
                        'attribute' => 'status',
                        'value' => $model->getStatusText(),
                    ],
                    [
                        'attribute' => 'completed_at',
                        'value' => $model->completed_at ? date('Y-m-d H:i:s', $model->completed_at) : '-',
                    ],
                    [
                        'attribute' => 'created_at',
                        'value' => date('Y-m-d H:i:s', $model->created_at),
                    ],
                    [
                        'attribute' => 'updated_at',
                        'value' => date('Y-m-d H:i:s', $model->updated_at),
                    ],
                ],
            ]) ?>
        </div>
    </div>
</div>
