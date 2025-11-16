<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Investment */

$this->title = '投资详情 #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => '投资管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="investment-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if ($model->status == \common\models\Investment::STATUS_ACTIVE): ?>
            <?= Html::a('赎回投资', ['withdraw', 'id' => $model->id], [
                'class' => 'btn btn-warning',
                'data' => [
                    'confirm' => '确定要赎回这笔投资吗？资金将返回基金账户。',
                    'method' => 'post',
                ],
            ]) ?>
        <?php else: ?>
            <span class="label label-default">该投资已赎回</span>
            <?= Html::a('删除', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => '确定要删除这条投资记录吗？',
                    'method' => 'post',
                ],
            ]) ?>
        <?php endif; ?>
        <?= Html::a('返回列表', ['index'], ['class' => 'btn btn-default']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'fund_id',
                'value' => $model->fund->name,
            ],
            [
                'attribute' => 'product_id',
                'value' => $model->product->name . ' (' . $model->product->getTypeText() . ')',
            ],
            [
                'attribute' => 'amount',
                'value' => '¥' . number_format($model->amount, 2),
            ],
            'investment_date:date',
            [
                'attribute' => 'status',
                'value' => $model->getStatusText(),
            ],
            'notes:ntext',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

    <?php if ($model->status == \common\models\Investment::STATUS_ACTIVE): ?>
        <div class="alert alert-info">
            <strong>提示：</strong>该投资正在生效中。资金已从基金账户转出，但仍属于该基金。产生的收益将按投资比例分配回基金。
        </div>
    <?php else: ?>
        <div class="alert alert-success">
            <strong>已赎回：</strong>该投资已赎回，资金已返回基金账户。
        </div>
    <?php endif; ?>

</div>
