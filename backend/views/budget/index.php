<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\Budget;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $statistics array */

$this->title = '预算管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="budget-index">

    <div class="page-header">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="pull-right">
            <?= Html::a('<i class="glyphicon glyphicon-plus"></i> 创建预算', ['create'], ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <hr>

    <!-- 统计卡片 -->
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="glyphicon glyphicon-list-alt" style="font-size: 48px;"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div style="font-size: 24px; font-weight: bold;">
                                <?= $statistics['total'] ?>
                            </div>
                            <div>总预算数</div>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <span class="pull-left">所有预算</span>
                    <span class="pull-right"><i class="glyphicon glyphicon-circle-arrow-right"></i></span>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="panel panel-success">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="glyphicon glyphicon-ok-circle" style="font-size: 48px;"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div style="font-size: 24px; font-weight: bold;">
                                <?= $statistics['active'] ?>
                            </div>
                            <div>进行中</div>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <span class="pull-left">活动预算</span>
                    <span class="pull-right"><i class="glyphicon glyphicon-circle-arrow-right"></i></span>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="panel panel-danger">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="glyphicon glyphicon-exclamation-sign" style="font-size: 48px;"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div style="font-size: 24px; font-weight: bold;">
                                <?= $statistics['over_budget'] ?>
                            </div>
                            <div>超支</div>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <span class="pull-left">需要关注</span>
                    <span class="pull-right"><i class="glyphicon glyphicon-circle-arrow-right"></i></span>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="panel panel-warning">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="glyphicon glyphicon-warning-sign" style="font-size: 48px;"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div style="font-size: 24px; font-weight: bold;">
                                <?= $statistics['warning'] ?>
                            </div>
                            <div>警告（≥90%）</div>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <span class="pull-left">接近预算上限</span>
                    <span class="pull-right"><i class="glyphicon glyphicon-circle-arrow-right"></i></span>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 预算列表 -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="glyphicon glyphicon-th-list"></i> 预算列表
            </h3>
        </div>
        <div class="panel-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
                'columns' => [
                    [
                        'attribute' => 'id',
                        'headerOptions' => ['width' => '60'],
                    ],
                    [
                        'attribute' => 'fund_id',
                        'label' => '关联基金',
                        'value' => function ($model) {
                            return $model->fund ? $model->fund->name : '<span class="label label-info">全局预算</span>';
                        },
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'period_type',
                        'label' => '周期类型',
                        'value' => function ($model) {
                            return $model->getPeriodTypeText();
                        },
                        'headerOptions' => ['width' => '100'],
                    ],
                    [
                        'label' => '预算周期',
                        'value' => function ($model) {
                            return $model->start_date . ' ~ ' . $model->end_date;
                        },
                        'headerOptions' => ['width' => '200'],
                    ],
                    [
                        'attribute' => 'budget_amount',
                        'label' => '预算金额',
                        'value' => function ($model) {
                            return '¥ ' . number_format($model->budget_amount, 2);
                        },
                        'headerOptions' => ['width' => '120'],
                        'contentOptions' => ['class' => 'text-right'],
                    ],
                    [
                        'attribute' => 'actual_amount',
                        'label' => '实际金额',
                        'value' => function ($model) {
                            return '¥ ' . number_format($model->actual_amount, 2);
                        },
                        'headerOptions' => ['width' => '120'],
                        'contentOptions' => ['class' => 'text-right'],
                    ],
                    [
                        'label' => '使用率',
                        'value' => function ($model) {
                            $rate = $model->getUsageRate();
                            $statusLabel = $model->getBudgetStatusLabel();

                            $html = '<div class="progress" style="margin-bottom: 5px;">';
                            $html .= '<div class="progress-bar progress-bar-' . $statusLabel['class'] . '" ';
                            $html .= 'role="progressbar" style="width: ' . min($rate, 100) . '%">';
                            $html .= number_format($rate, 1) . '%';
                            $html .= '</div></div>';
                            $html .= '<span class="label label-' . $statusLabel['class'] . '">' . $statusLabel['text'] . '</span>';

                            return $html;
                        },
                        'format' => 'raw',
                        'headerOptions' => ['width' => '180'],
                    ],
                    [
                        'attribute' => 'status',
                        'label' => '状态',
                        'value' => function ($model) {
                            $class = $model->status == Budget::STATUS_ACTIVE ? 'success' : 'default';
                            return '<span class="label label-' . $class . '">' . $model->getStatusText() . '</span>';
                        },
                        'format' => 'raw',
                        'headerOptions' => ['width' => '80'],
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => '操作',
                        'headerOptions' => ['width' => '120'],
                        'template' => '{view} {update} {update-actual} {delete}',
                        'buttons' => [
                            'update-actual' => function ($url, $model, $key) {
                                return Html::a(
                                    '<span class="glyphicon glyphicon-refresh"></span>',
                                    ['update-actual', 'id' => $model->id],
                                    [
                                        'title' => '更新实际金额',
                                        'data-method' => 'post',
                                        'data-confirm' => '确定要更新实际金额吗？',
                                    ]
                                );
                            },
                        ],
                    ],
                ],
            ]); ?>
        </div>
    </div>

    <!-- 使用说明 -->
    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="glyphicon glyphicon-info-sign"></i> 使用提示
            </h3>
        </div>
        <div class="panel-body">
            <ul>
                <li><strong>全局预算</strong>：不关联任何基金，统计所有投资的总和</li>
                <li><strong>基金预算</strong>：关联特定基金，只统计该基金的投资</li>
                <li><strong>自动更新</strong>：实际金额会根据投资记录自动计算（也可手动刷新）</li>
                <li><strong>状态说明</strong>：
                    <span class="label label-success">正常</span> &lt;70% |
                    <span class="label label-info">良好</span> 70%-90% |
                    <span class="label label-warning">警告</span> 90%-100% |
                    <span class="label label-danger">超支</span> &gt;100%
                </li>
            </ul>
        </div>
    </div>

</div>
