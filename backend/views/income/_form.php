<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;

/* @var $this yii\web\View */
/* @var $model common\models\Income */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="income-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'amount')->textInput(['type' => 'number', 'step' => '0.01', 'min' => '0.01'])->hint('输入收入金额') ?>

    <?= $form->field($model, 'source')->textInput(['maxlength' => true])->hint('例如：工资、奖金、投资收益等') ?>

    <?= $form->field($model, 'income_date')->widget(DatePicker::class, [
        'dateFormat' => 'yyyy-MM-dd',
        'options' => ['class' => 'form-control'],
    ]) ?>

    <?= $form->field($model, 'notes')->textarea(['rows' => 3]) ?>

    <div class="alert alert-warning">
        <strong>注意：</strong>保存后，系统将自动按各基金的分配比例将收入分配到各基金账户。
    </div>

    <div class="form-group">
        <?= Html::submitButton('保存并分配', ['class' => 'btn btn-success']) ?>
        <?= Html::a('取消', ['index'], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
