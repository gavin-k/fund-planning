<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model common\models\Fund */
/* @var $investmentProvider yii\data\ActiveDataProvider */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => '基金管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="fund-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('更新', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('删除', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '确定要删除这个基金吗？',
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
                'attribute' => 'allocation_percent',
                'value' => $model->allocation_percent . '%',
            ],
            [
                'attribute' => 'current_balance',
                'value' => '¥' . number_format($model->current_balance, 2),
            ],
            [
                'label' => '已投资金额',
                'value' => '¥' . number_format($model->getInvestedAmount(), 2),
            ],
            [
                'label' => '可用余额',
                'value' => '¥' . number_format($model->getAvailableBalance(), 2),
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
                'attribute' => 'product_id',
                'value' => function ($model) {
                    return $model->product->name;
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
            'notes:ntext',
        ],
    ]); ?>

</div>
