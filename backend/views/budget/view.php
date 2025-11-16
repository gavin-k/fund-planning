<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use common\models\Budget;

/* @var $this yii\web\View */
/* @var $model common\models\Budget */
/* @var $investmentProvider yii\data\ActiveDataProvider */

$this->title = '预算详情';
$this->params['breadcrumbs'][] = ['label' => '预算管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$usageRate = $model->getUsageRate();
$statusLabel = $model->getBudgetStatusLabel();
$remaining = $model->getRemainingBudget();
$isInPeriod = $model->isInPeriod();
?>
<div class="budget-view">

    <div class="page-header">
        <h1>
            <?= Html::encode($this->title) ?>
            <span class="label label-<?= $model->status == Budget::STATUS_ACTIVE ? 'success' : 'default' ?>">
                <?= $model->getStatusText() ?>
            </span>
        </h1>
        <p>
            <?= Html::a('<i class="glyphicon glyphicon-pencil"></i> 编辑', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="glyphicon glyphicon-refresh"></i> 更新实际金额', ['update-actual', 'id' => $model->id], [
                'class' => 'btn btn-info',
                'data' => [
                    'method' => 'post',
                    'confirm' => '确定要更新实际金额吗？系统将根据投资记录重新计算。',
                ],
            ]) ?>
            <?= Html::a('<i class="glyphicon glyphicon-trash"></i> 删除', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => '确定要删除此预算吗？',
                    'method' => 'post',
                ],
            ]) ?>
            <?= Html::a('<i class="glyphicon glyphicon-arrow-left"></i> 返回列表', ['index'], ['class' => 'btn btn-default']) ?>
        </p>
    </div>

    <hr>

    <!-- 预算使用情况 -->
    <div class="row">
        <div class="col-md-8">
            <div class="panel panel-<?= $statusLabel['class'] ?>">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-stats"></i> 预算使用情况
                    </h3>
                </div>
                <div class="panel-body">
                    <!-- 使用率显示 -->
                    <div class="text-center" style="margin-bottom: 20px;">
                        <div style="font-size: 60px; font-weight: bold; color: <?= $model->isOverBudget() ? '#d9534f' : '#5cb85c' ?>;">
                            <?= number_format($usageRate, 1) ?>%
                        </div>
                        <div style="font-size: 18px; color: #777;">
                            预算使用率
                        </div>
                    </div>

                    <!-- 进度条 -->
                    <div class="progress" style="height: 30px; margin-bottom: 20px;">
                        <div class="progress-bar progress-bar-<?= $statusLabel['class'] ?>"
                             role="progressbar"
                             style="width: <?= min($usageRate, 100) ?>%;">
                            <span style="font-size: 16px; line-height: 30px;">
                                ¥ <?= number_format($model->actual_amount, 2) ?> / ¥ <?= number_format($model->budget_amount, 2) ?>
                            </span>
                        </div>
                    </div>

                    <!-- 统计信息 -->
                    <div class="row">
                        <div class="col-sm-4 text-center">
                            <div style="padding: 15px; background: #f9f9f9; border-radius: 5px;">
                                <div style="font-size: 24px; font-weight: bold; color: #337ab7;">
                                    ¥ <?= number_format($model->budget_amount, 2) ?>
                                </div>
                                <div style="color: #777;">预算总额</div>
                            </div>
                        </div>
                        <div class="col-sm-4 text-center">
                            <div style="padding: 15px; background: #f9f9f9; border-radius: 5px;">
                                <div style="font-size: 24px; font-weight: bold; color: #5cb85c;">
                                    ¥ <?= number_format($model->actual_amount, 2) ?>
                                </div>
                                <div style="color: #777;">实际支出</div>
                            </div>
                        </div>
                        <div class="col-sm-4 text-center">
                            <div style="padding: 15px; background: #f9f9f9; border-radius: 5px;">
                                <div style="font-size: 24px; font-weight: bold; color: <?= $remaining >= 0 ? '#f0ad4e' : '#d9534f' ?>;">
                                    ¥ <?= number_format(abs($remaining), 2) ?>
                                </div>
                                <div style="color: #777;"><?= $remaining >= 0 ? '剩余预算' : '超支金额' ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- 预算信息 -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-info-sign"></i> 预算信息
                    </h3>
                </div>
                <div class="panel-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'attribute' => 'fund_id',
                                'label' => '关联基金',
                                'value' => $model->fund ? $model->fund->name : '全局预算（所有基金）',
                            ],
                            [
                                'attribute' => 'period_type',
                                'value' => $model->getPeriodTypeText(),
                            ],
                            'start_date:date:开始日期',
                            'end_date:date:结束日期',
                            [
                                'label' => '剩余天数',
                                'value' => $isInPeriod ? $model->getRemainingDays() . ' 天' : '已结束',
                            ],
                            'created_at:datetime:创建时间',
                            'updated_at:datetime:更新时间',
                        ],
                    ]) ?>
                </div>
            </div>

            <!-- 状态提示 -->
            <?php if ($model->isOverBudget()): ?>
                <div class="alert alert-danger">
                    <strong><i class="glyphicon glyphicon-exclamation-sign"></i> 预算超支！</strong><br>
                    已超出预算 ¥ <?= number_format($model->getOverBudgetAmount(), 2) ?>，请控制支出。
                </div>
            <?php elseif ($usageRate >= 90): ?>
                <div class="alert alert-warning">
                    <strong><i class="glyphicon glyphicon-warning-sign"></i> 预算预警！</strong><br>
                    已使用 <?= number_format($usageRate, 1) ?>%，接近预算上限。
                </div>
            <?php elseif ($usageRate >= 70): ?>
                <div class="alert alert-info">
                    <strong><i class="glyphicon glyphicon-info-sign"></i> 使用正常</strong><br>
                    已使用 <?= number_format($usageRate, 1) ?>%，剩余 ¥ <?= number_format($remaining, 2) ?>。
                </div>
            <?php else: ?>
                <div class="alert alert-success">
                    <strong><i class="glyphicon glyphicon-ok-sign"></i> 预算充足</strong><br>
                    使用率 <?= number_format($usageRate, 1) ?>%，预算管理良好。
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 预算周期内的投资记录 -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="glyphicon glyphicon-list"></i> 预算周期内的投资记录
                <small>（<?= $model->start_date ?> ~ <?= $model->end_date ?>）</small>
            </h3>
        </div>
        <div class="panel-body">
            <?= GridView::widget([
                'dataProvider' => $investmentProvider,
                'tableOptions' => ['class' => 'table table-striped table-bordered'],
                'summary' => '共 {totalCount} 条投资记录，总金额：¥ ' . number_format($model->actual_amount, 2),
                'columns' => [
                    'id',
                    [
                        'attribute' => 'fund_id',
                        'label' => '基金',
                        'value' => function ($model) {
                            return $model->fund ? $model->fund->name : '-';
                        },
                    ],
                    [
                        'attribute' => 'product_id',
                        'label' => '产品',
                        'value' => function ($model) {
                            return $model->product ? $model->product->name : '-';
                        },
                    ],
                    [
                        'attribute' => 'amount',
                        'label' => '金额',
                        'value' => function ($model) {
                            return '¥ ' . number_format($model->amount, 2);
                        },
                        'contentOptions' => ['class' => 'text-right'],
                    ],
                    'investment_date:date:投资日期',
                    [
                        'attribute' => 'status',
                        'label' => '状态',
                        'value' => function ($model) {
                            $statusList = [
                                'active' => '<span class="label label-success">进行中</span>',
                                'completed' => '<span class="label label-default">已完成</span>',
                            ];
                            return $statusList[$model->status] ?? $model->status;
                        },
                        'format' => 'raw',
                    ],
                ],
            ]); ?>
        </div>
    </div>

</div>
