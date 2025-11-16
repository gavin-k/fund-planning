<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model common\models\InvestmentProduct */
/* @var $investmentProvider yii\data\ActiveDataProvider */
/* @var $returnProvider yii\data\ActiveDataProvider */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => '理财产品管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="investment-product-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('更新', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('删除', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '确定要删除这个理财产品吗？',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('返回列表', ['index'], ['class' => 'btn btn-default']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            [
                'attribute' => 'type',
                'value' => $model->getTypeText(),
            ],
            'platform',
            [
                'attribute' => 'current_amount',
                'value' => '¥' . number_format($model->current_amount, 2),
            ],
            'description:ntext',
            [
                'attribute' => 'status',
                'value' => $model->getStatusText(),
            ],
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

    <h3>投资记录</h3>

    <?= GridView::widget([
        'dataProvider' => $investmentProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'fund_id',
                'value' => function ($model) {
                    return $model->fund->name;
                },
            ],
            [
                'attribute' => 'amount',
                'value' => function ($model) {
                    return '¥' . number_format($model->amount, 2);
                },
            ],
            'investment_date:date',
            [
                'attribute' => 'status',
                'value' => function ($model) {
                    return $model->getStatusText();
                },
            ],
        ],
    ]); ?>

    <h3>收益记录</h3>

    <?= GridView::widget([
        'dataProvider' => $returnProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'total_amount',
                'value' => function ($model) {
                    return '¥' . number_format($model->total_amount, 2);
                },
            ],
            'return_date:date',
            [
                'attribute' => 'is_distributed',
                'value' => function ($model) {
                    return $model->is_distributed ? '已分配' : '未分配';
                },
            ],
            'notes:ntext',
        ],
    ]); ?>

</div>
