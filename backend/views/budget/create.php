<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Budget */
/* @var $fundList array */

$this->title = '创建预算';
$this->params['breadcrumbs'][] = ['label' => '预算管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="budget-create">

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
                <i class="glyphicon glyphicon-question-sign"></i> 如何使用预算管理？
            </h3>
        </div>
        <div class="panel-body">
            <h4>什么是预算管理？</h4>
            <p>预算管理帮助您控制投资支出，设定月度、季度或年度的投资上限。系统会自动追踪实际支出，并在接近或超出预算时发出警告。</p>

            <h4>使用步骤：</h4>
            <ol>
                <li><strong>选择范围</strong>：选择"全局预算"监控所有投资，或选择具体基金进行单独控制</li>
                <li><strong>设置周期</strong>：选择月度、季度或年度预算周期，系统会自动填充对应的起止日期</li>
                <li><strong>设定金额</strong>：输入本周期的预算总额</li>
                <li><strong>自动追踪</strong>：系统根据投资记录自动计算实际支出</li>
                <li><strong>监控预警</strong>：在Dashboard查看预算使用情况和预警提醒</li>
            </ol>

            <h4>预算状态说明：</h4>
            <ul>
                <li><span class="label label-success">正常</span>：使用率 &lt; 70%，预算充足</li>
                <li><span class="label label-info">良好</span>：使用率 70% ~ 90%，在合理范围内</li>
                <li><span class="label label-warning">警告</span>：使用率 90% ~ 100%，接近预算上限</li>
                <li><span class="label label-danger">超支</span>：使用率 &gt; 100%，已超出预算</li>
            </ul>

            <h4>智能功能：</h4>
            <ul>
                <li><strong>自动计算</strong>：实际金额根据投资记录自动更新</li>
                <li><strong>实时监控</strong>：在Dashboard实时显示预算使用情况</li>
                <li><strong>多级预警</strong>：使用率达到 90% 和 100% 时自动提醒</li>
                <li><strong>历史分析</strong>：支持多个预算周期，便于对比分析</li>
            </ul>

            <h4>使用建议：</h4>
            <ul>
                <li>首次使用建议先创建"全局预算"，了解整体支出情况</li>
                <li>对于重点关注的基金，可以单独设置预算进行精细化管理</li>
                <li>建议每月月初设置当月预算，定期查看执行情况</li>
                <li>预算超支时，系统会在Dashboard显示警告，请及时调整投资计划</li>
            </ul>
        </div>
    </div>

</div>
