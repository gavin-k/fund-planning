<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model common\models\ReturnRecord */
/* @var $distributionProvider yii\data\ActiveDataProvider */

$this->title = '收益详情 #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => '收益管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="return-record-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if (!$model->is_distributed): ?>
            <?= Html::a('更新', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('删除', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => '确定要删除这条收益记录吗？',
                    'method' => 'post',
                ],
            ]) ?>
        <?php else: ?>
            <span class="label label-success">该收益已分配，无法修改或删除</span>
        <?php endif; ?>
        <?= Html::a('返回列表', ['index'], ['class' => 'btn btn-default']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'product_id',
                'value' => $model->product->name,
            ],
            [
                'attribute' => 'total_amount',
                'value' => '¥' . number_format($model->total_amount, 2),
            ],
            'return_date:date',
            [
                'attribute' => 'is_distributed',
                'value' => $model->is_distributed ? '已分配' : '未分配',
            ],
            'notes:ntext',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

    <?php if ($model->is_distributed): ?>
        <h3>收益分配详情</h3>
        <div class="alert alert-success">
            收益总额 <strong>¥<?= number_format($model->total_amount, 2) ?></strong> 已按投资比例自动分配到各基金
        </div>

        <?= GridView::widget([
            'dataProvider' => $distributionProvider,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                [
                    'attribute' => 'fund_id',
                    'label' => '基金名称',
                    'value' => function ($model) {
                        return $model->fund->name;
                    },
                ],
                [
                    'attribute' => 'percent',
                    'label' => '投资占比',
                    'value' => function ($model) {
                        return number_format($model->percent, 2) . '%';
                    },
                ],
                [
                    'attribute' => 'amount',
                    'label' => '分配收益',
                    'value' => function ($model) {
                        return '¥' . number_format($model->amount, 2);
                    },
                ],
                'created_at:datetime',
            ],
        ]); ?>
    <?php else: ?>
        <div class="alert alert-warning">
            该收益尚未分配到基金。
        </div>
    <?php endif; ?>

</div>
