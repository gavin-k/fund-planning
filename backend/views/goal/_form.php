<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\FinancialGoal;

/* @var $this yii\web\View */
/* @var $model common\models\FinancialGoal */
/* @var $form yii\widgets\ActiveForm */
/* @var $fundList array */
?>

<div class="financial-goal-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => '例如：买车、旅游、买房等']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'fund_id')->dropDownList($fundList, [
                'prompt' => '请选择关联基金（可选）',
                'options' => ['' => ['disabled' => true]],
            ])->hint('选择基金后可以自动同步当前金额') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'target_amount')->textInput([
                'type' => 'number',
                'step' => '0.01',
                'min' => '0',
                'placeholder' => '0.00'
            ])->hint('目标需要达到的金额') ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'current_amount')->textInput([
                'type' => 'number',
                'step' => '0.01',
                'min' => '0',
                'placeholder' => '0.00'
            ])->hint('当前已达到的金额') ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'target_date')->textInput(['type' => 'date'])->hint('希望完成目标的日期') ?>
        </div>
    </div>

    <?= $form->field($model, 'description')->textarea(['rows' => 3, 'placeholder' => '描述一下这个目标的意义...']) ?>

    <?php if (!$model->isNewRecord): ?>
        <?= $form->field($model, 'status')->dropDownList(FinancialGoal::getStatusList()) ?>
    <?php endif; ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '<i class="glyphicon glyphicon-plus"></i> 创建目标' : '<i class="glyphicon glyphicon-floppy-disk"></i> 保存', [
            'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'
        ]) ?>
        <?= Html::a('<i class="glyphicon glyphicon-remove"></i> 取消', ['index'], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

    <?php if (!$model->isNewRecord && $model->fund_id): ?>
        <div class="alert alert-info" style="margin-top: 20px;">
            <i class="glyphicon glyphicon-info-sign"></i>
            <strong>提示：</strong>
            此目标已关联基金 "<?= Html::encode($model->fund->name) ?>"，
            您可以在详情页点击"同步金额"按钮自动从基金余额更新当前金额。
        </div>
    <?php endif; ?>
</div>
