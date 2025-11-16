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
        <?= Html::a('<i class="glyphicon glyphicon-export"></i> 导出数据', ['export'], ['class' => 'btn btn-info']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            [
                'attribute' => 'allocation_percentage',
                'label' => '分配比例',
                'value' => function ($model) {
                    return $model->allocation_percentage . '%';
                },
            ],
            [
                'attribute' => 'balance',
                'label' => '当前余额',
                'value' => function ($model) {
                    return '¥' . number_format($model->balance, 2);
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
