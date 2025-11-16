<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;

/* @var $this yii\web\View */
/* @var $model common\models\Investment */
/* @var $form yii\widgets\ActiveForm */
/* @var $fundList array */
/* @var $productList array */
?>

<div class="investment-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'fund_id')->dropDownList($fundList, ['prompt' => '请选择基金']) ?>

    <?= $form->field($model, 'product_id')->dropDownList($productList, ['prompt' => '请选择理财产品']) ?>

    <?= $form->field($model, 'amount')->textInput(['type' => 'number', 'step' => '0.01', 'min' => '0.01'])->hint('系统会自动检查基金可用余额') ?>

    <?= $form->field($model, 'investment_date')->widget(DatePicker::class, [
        'dateFormat' => 'yyyy-MM-dd',
        'options' => ['class' => 'form-control'],
    ]) ?>

    <?= $form->field($model, 'notes')->textarea(['rows' => 3]) ?>

    <div class="alert alert-warning">
        <strong>注意：</strong>投资后，投资金额将从基金的可用余额中扣除，但仍属于该基金。
    </div>

    <div class="form-group">
        <?= Html::submitButton('确认投资', ['class' => 'btn btn-success']) ?>
        <?= Html::a('取消', ['index'], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
