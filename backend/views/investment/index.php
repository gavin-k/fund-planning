<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '投资管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="investment-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-info">
        <strong>提示：</strong>从基金账户投资到理财产品，系统会自动检查可用余额。
    </div>

    <p>
        <?= Html::a('新增投资', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('<i class="glyphicon glyphicon-export"></i> 导出数据', ['export'], ['class' => 'btn btn-info']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'attribute' => 'fund_id',
                'value' => function ($model) {
                    return $model->fund->name;
                },
            ],
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
                    if ($model->status == \common\models\Investment::STATUS_ACTIVE) {
                        return '<span class="label label-success">生效中</span>';
                    } else {
                        return '<span class="label label-default">已赎回</span>';
                    }
                },
                'format' => 'raw',
                'filter' => \common\models\Investment::getStatusList(),
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
