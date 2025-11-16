<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '收入管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="income-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-info">
        <strong>提示：</strong>创建收入记录后，系统会自动按各基金的分配比例分配金额到各基金账户。
    </div>

    <p>
        <?= Html::a('记录收入', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'attribute' => 'amount',
                'value' => function ($model) {
                    return '¥' . number_format($model->amount, 2);
                },
            ],
            'source',
            'income_date:date',
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
