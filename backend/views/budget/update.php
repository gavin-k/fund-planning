<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Budget */
/* @var $fundList array */

$this->title = '编辑预算';
$this->params['breadcrumbs'][] = ['label' => '预算管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => '预算 #' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '编辑';
?>
<div class="budget-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <hr>

    <div class="panel panel-default">
        <div class="panel-body">
            <?= $this->render('_form', [
                'model' => $model,
                'fundList' => $fundList,
            ]) ?>
        </div>
    </div>

    <!-- 注意事项 -->
    <div class="alert alert-warning">
        <strong><i class="glyphicon glyphicon-exclamation-sign"></i> 注意：</strong>
        <ul style="margin-bottom: 0; margin-top: 10px;">
            <li>修改预算金额不会影响已有的投资记录</li>
            <li>修改日期范围会影响实际金额的计算（只统计新日期范围内的投资）</li>
            <li>实际金额会在保存后自动重新计算</li>
        </ul>
    </div>

</div>
