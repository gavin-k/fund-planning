<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Budget;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model common\models\Budget */
/* @var $form yii\widgets\ActiveForm */
/* @var $fundList array */
?>

<div class="budget-form">

    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-9\">{input}\n{hint}\n{error}</div>",
            'labelOptions' => ['class' => 'col-lg-3 control-label'],
        ],
    ]); ?>

    <?= $form->field($model, 'fund_id')->dropDownList($fundList, [
        'prompt' => '请选择...',
    ])->hint('选择"全局预算"将统计所有基金的投资，选择具体基金将只统计该基金的投资') ?>

    <?= $form->field($model, 'period_type')->dropDownList(Budget::getPeriodTypeList(), [
        'prompt' => '请选择周期类型...',
        'id' => 'period-type',
    ])->hint('选择预算的时间周期类型') ?>

    <?= $form->field($model, 'start_date')->widget(DatePicker::classname(), [
        'options' => ['placeholder' => '选择开始日期...'],
        'pluginOptions' => [
            'autoclose' => true,
            'format' => 'yyyy-mm-dd',
            'todayHighlight' => true,
        ],
    ])->hint('预算周期的开始日期') ?>

    <?= $form->field($model, 'end_date')->widget(DatePicker::classname(), [
        'options' => ['placeholder' => '选择结束日期...'],
        'pluginOptions' => [
            'autoclose' => true,
            'format' => 'yyyy-mm-dd',
            'todayHighlight' => true,
        ],
    ])->hint('预算周期的结束日期') ?>

    <?= $form->field($model, 'budget_amount')->textInput([
        'type' => 'number',
        'step' => '0.01',
        'min' => '0',
        'placeholder' => '输入预算金额...',
    ])->hint('设置本周期的预算总额（单位：元）') ?>

    <?= $form->field($model, 'status')->dropDownList(Budget::getStatusList(), [
        'prompt' => '请选择状态...',
    ])->hint('激活状态的预算会在Dashboard显示监控信息') ?>

    <?php if (!$model->isNewRecord): ?>
        <?= $form->field($model, 'actual_amount')->textInput([
            'type' => 'number',
            'step' => '0.01',
            'readonly' => true,
        ])->hint('实际金额由系统自动计算，也可以点击"更新实际金额"按钮手动刷新') ?>
    <?php endif; ?>

    <div class="form-group">
        <div class="col-lg-offset-3 col-lg-9">
            <?= Html::submitButton(
                '<i class="glyphicon glyphicon-' . ($model->isNewRecord ? 'plus' : 'ok') . '"></i> ' .
                ($model->isNewRecord ? '创建预算' : '保存修改'),
                ['class' => $model->isNewRecord ? 'btn btn-success btn-lg' : 'btn btn-primary btn-lg']
            ) ?>
            <?= Html::a('取消', ['index'], ['class' => 'btn btn-default btn-lg']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
// JavaScript for period type quick selection
$js = <<<JS
$(document).ready(function() {
    $('#period-type').change(function() {
        var periodType = $(this).val();
        var today = new Date();
        var startDate, endDate;

        if (periodType == 'month') {
            // 当月第一天和最后一天
            startDate = new Date(today.getFullYear(), today.getMonth(), 1);
            endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        } else if (periodType == 'quarter') {
            // 当季第一天和最后一天
            var quarter = Math.floor(today.getMonth() / 3);
            startDate = new Date(today.getFullYear(), quarter * 3, 1);
            endDate = new Date(today.getFullYear(), quarter * 3 + 3, 0);
        } else if (periodType == 'year') {
            // 当年第一天和最后一天
            startDate = new Date(today.getFullYear(), 0, 1);
            endDate = new Date(today.getFullYear(), 11, 31);
        }

        if (startDate && endDate) {
            // 格式化日期为 YYYY-MM-DD
            var formatDate = function(date) {
                var year = date.getFullYear();
                var month = String(date.getMonth() + 1).padStart(2, '0');
                var day = String(date.getDate()).padStart(2, '0');
                return year + '-' + month + '-' + day;
            };

            $('#budget-start_date').val(formatDate(startDate));
            $('#budget-end_date').val(formatDate(endDate));
        }
    });
});
JS;
$this->registerJs($js);
?>
