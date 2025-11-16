<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Fund */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="fund-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'allocation_percent')->textInput(['type' => 'number', 'step' => '0.01', 'min' => '0', 'max' => '100'])->hint('请输入0-100之间的数字') ?>

    <?= $form->field($model, 'current_balance')->textInput(['type' => 'number', 'step' => '0.01', 'min' => '0'])->hint('初始余额，可以为0') ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 3]) ?>

    <?= $form->field($model, 'status')->dropDownList(\common\models\Fund::getStatusList()) ?>

    <div class="form-group">
        <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
        <?= Html::a('取消', ['index'], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
