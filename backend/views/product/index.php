<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '理财产品管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="investment-product-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('创建理财产品', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            [
                'attribute' => 'type',
                'value' => function ($model) {
                    return $model->getTypeText();
                },
                'filter' => \common\models\InvestmentProduct::getTypeList(),
            ],
            'platform',
            [
                'attribute' => 'current_amount',
                'value' => function ($model) {
                    return '¥' . number_format($model->current_amount, 2);
                },
            ],
            [
                'attribute' => 'status',
                'value' => function ($model) {
                    return $model->getStatusText();
                },
                'filter' => \common\models\InvestmentProduct::getStatusList(),
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
