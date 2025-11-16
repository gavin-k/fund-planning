<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '收益管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="return-record-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-info">
        <strong>提示：</strong>记录理财产品收益后，系统会自动按各基金的投资比例分配收益。
    </div>

    <p>
        <?= Html::a('记录收益', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'attribute' => 'product_id',
                'value' => function ($model) {
                    return $model->product->name;
                },
            ],
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
                    return $model->is_distributed ? '<span class="label label-success">已分配</span>' : '<span class="label label-default">未分配</span>';
                },
                'format' => 'raw',
                'filter' => [0 => '未分配', 1 => '已分配'],
            ],
            'created_at:datetime',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
