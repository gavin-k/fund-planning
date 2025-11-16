<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\FinancialGoal */
/* @var $fundList array */

$this->title = '创建财务目标';
$this->params['breadcrumbs'][] = ['label' => '财务目标', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="financial-goal-create">

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

    <!-- 使用指南 -->
    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="glyphicon glyphicon-question-sign"></i> 如何使用财务目标？
            </h3>
        </div>
        <div class="panel-body">
            <h4>什么是财务目标？</h4>
            <p>财务目标帮助您设定具体的储蓄目标，例如：买车、旅游、买房等。系统会自动追踪进度，并给出智能建议。</p>

            <h4>使用步骤：</h4>
            <ol>
                <li><strong>设定目标</strong>：填写目标名称、金额和目标日期</li>
                <li><strong>关联基金</strong>（可选）：选择一个基金，系统会自动追踪该基金的余额</li>
                <li><strong>追踪进度</strong>：系统自动计算完成度、剩余天数和建议月储蓄额</li>
                <li><strong>查看建议</strong>：根据当前进度，系统会给出智能建议</li>
                <li><strong>达成目标</strong>：完成后点击"标记为完成"</li>
            </ol>

            <h4>智能功能：</h4>
            <ul>
                <li><strong>自动同步金额</strong>：关联基金后，可一键同步当前金额</li>
                <li><strong>建议月储蓄</strong>：系统计算每月应该存多少钱才能按时完成</li>
                <li><strong>预测完成日期</strong>：基于当前速度，预测实际完成日期</li>
                <li><strong>延期提醒</strong>：目标延期时自动提醒</li>
            </ul>
        </div>
    </div>

</div>
