<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '基金管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="fund-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('创建基金', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            [
                'attribute' => 'allocation_percent',
                'value' => function ($model) {
                    return $model->allocation_percent . '%';
                },
            ],
            [
                'attribute' => 'current_balance',
                'value' => function ($model) {
                    return '¥' . number_format($model->current_balance, 2);
                },
            ],
            [
                'label' => '可用余额',
                'value' => function ($model) {
                    return '¥' . number_format($model->getAvailableBalance(), 2);
                },
            ],
            [
                'attribute' => 'status',
                'value' => function ($model) {
                    return $model->getStatusText();
                },
                'filter' => \common\models\Fund::getStatusList(),
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
