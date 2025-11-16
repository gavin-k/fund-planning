<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;

/* @var $this yii\web\View */
/* @var $model common\models\ReturnRecord */
/* @var $form yii\widgets\ActiveForm */
/* @var $productList array */
?>

<div class="return-record-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'product_id')->dropDownList($productList, ['prompt' => '请选择理财产品']) ?>

    <?= $form->field($model, 'total_amount')->textInput(['type' => 'number', 'step' => '0.01', 'min' => '0.01'])->hint('输入该产品产生的总收益金额') ?>

    <?= $form->field($model, 'return_date')->widget(DatePicker::class, [
        'dateFormat' => 'yyyy-MM-dd',
        'options' => ['class' => 'form-control'],
    ]) ?>

    <?= $form->field($model, 'notes')->textarea(['rows' => 3]) ?>

    <div class="alert alert-warning">
        <strong>注意：</strong>保存后，系统将自动计算该产品中各基金的投资比例，并按比例分配收益到各基金账户。
    </div>

    <div class="form-group">
        <?= Html::submitButton('保存并分配', ['class' => 'btn btn-success']) ?>
        <?= Html::a('取消', ['index'], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
