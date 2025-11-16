<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use common\models\FinancialGoal;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $statistics array */

$this->title = '财务目标管理';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="financial-goal-index">
    <div class="row">
        <div class="col-md-8">
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
        <div class="col-md-4 text-right" style="margin-top: 20px;">
            <?= Html::a('<i class="glyphicon glyphicon-plus"></i> 创建新目标', ['create'], ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <hr>

    <!-- 统计卡片 -->
    <div class="row" style="margin-bottom: 20px;">
        <div class="col-md-3">
            <div class="panel panel-primary">
                <div class="panel-body text-center">
                    <div style="font-size: 14px; color: #777;">总目标数</div>
                    <div style="font-size: 36px; font-weight: bold; color: #337ab7;">
                        <?= $statistics['total'] ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel panel-info">
                <div class="panel-body text-center">
                    <div style="font-size: 14px; color: #777;">进行中</div>
                    <div style="font-size: 36px; font-weight: bold; color: #5bc0de;">
                        <?= $statistics['in_progress'] ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel panel-success">
                <div class="panel-body text-center">
                    <div style="font-size: 14px; color: #777;">已完成</div>
                    <div style="font-size: 36px; font-weight: bold; color: #5cb85c;">
                        <?= $statistics['completed'] ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel panel-danger">
                <div class="panel-body text-center">
                    <div style="font-size: 14px; color: #777;">延期目标</div>
                    <div style="font-size: 36px; font-weight: bold; color: #d9534f;">
                        <?= $statistics['overdue'] ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 目标列表 -->
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            [
                'attribute' => 'name',
                'label' => '目标名称',
                'format' => 'raw',
                'value' => function ($model) {
                    $html = Html::a(Html::encode($model->name), ['view', 'id' => $model->id], ['style' => 'font-weight: bold;']);
                    if ($model->description) {
                        $html .= '<br><small class="text-muted">' . Html::encode($model->description) . '</small>';
                    }
                    return $html;
                },
            ],
            [
                'attribute' => 'fund_id',
                'label' => '关联基金',
                'value' => function ($model) {
                    return $model->fund ? $model->fund->name : '-';
                },
            ],
            [
                'label' => '进度',
                'format' => 'raw',
                'value' => function ($model) {
                    $progress = $model->getProgress();
                    $colorClass = $progress >= 75 ? 'success' : ($progress >= 50 ? 'info' : ($progress >= 25 ? 'warning' : 'danger'));

                    return '
                        <div class="progress" style="margin-bottom: 5px;">
                            <div class="progress-bar progress-bar-' . $colorClass . '" style="width: ' . $progress . '%;">
                                ' . round($progress, 0) . '%
                            </div>
                        </div>
                        <small class="text-muted">
                            ¥' . number_format($model->current_amount, 2) . ' / ¥' . number_format($model->target_amount, 2) . '
                        </small>
                    ';
                },
            ],
            [
                'attribute' => 'target_date',
                'label' => '目标日期',
                'format' => 'raw',
                'value' => function ($model) {
                    $html = Html::encode($model->target_date);
                    $remainingDays = $model->getRemainingDays();

                    if ($model->status == FinancialGoal::STATUS_COMPLETED) {
                        $html .= '<br><span class="label label-success">已完成</span>';
                    } elseif ($model->isOverdue()) {
                        $html .= '<br><span class="label label-danger">已延期</span>';
                    } elseif ($model->isDueSoon()) {
                        $html .= '<br><span class="label label-warning">即将到期（' . $remainingDays . '天）</span>';
                    } else {
                        $html .= '<br><small class="text-muted">剩余 ' . $remainingDays . ' 天</small>';
                    }

                    return $html;
                },
            ],
            [
                'attribute' => 'status',
                'label' => '状态',
                'format' => 'raw',
                'value' => function ($model) {
                    $statusColors = [
                        FinancialGoal::STATUS_CANCELLED => 'default',
                        FinancialGoal::STATUS_IN_PROGRESS => 'info',
                        FinancialGoal::STATUS_COMPLETED => 'success',
                    ];
                    $colorClass = $statusColors[$model->status] ?? 'default';
                    return '<span class="label label-' . $colorClass . '">' . Html::encode($model->getStatusText()) . '</span>';
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::a('<i class="glyphicon glyphicon-eye-open"></i>', $url, [
                            'title' => '查看',
                            'class' => 'btn btn-xs btn-info',
                        ]);
                    },
                    'update' => function ($url, $model) {
                        return Html::a('<i class="glyphicon glyphicon-pencil"></i>', $url, [
                            'title' => '编辑',
                            'class' => 'btn btn-xs btn-primary',
                        ]);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="glyphicon glyphicon-trash"></i>', $url, [
                            'title' => '删除',
                            'class' => 'btn btn-xs btn-danger',
                            'data' => [
                                'confirm' => '确定要删除这个目标吗？',
                                'method' => 'post',
                            ],
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>
</div>
