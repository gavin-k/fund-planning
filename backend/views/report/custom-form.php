<?php

use yii\helpers\Html;
use kartik\date\DatePicker;

/* @var $this yii\web\View */

$this->title = '自定义周期报表';
$this->params['breadcrumbs'][] = ['label' => '财务报表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="report-custom-form">

    <div class="page-header">
        <h1>
            <i class="glyphicon glyphicon-filter"></i> <?= Html::encode($this->title) ?>
        </h1>
    </div>

    <hr>

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-cog"></i> 设置报表周期
                    </h3>
                </div>
                <div class="panel-body">
                    <form method="get" action="<?= yii\helpers\Url::to(['custom']) ?>" class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">开始日期：</label>
                            <div class="col-sm-9">
                                <?= DatePicker::widget([
                                    'name' => 'start_date',
                                    'value' => date('Y-m-01'),
                                    'options' => ['placeholder' => '选择开始日期...'],
                                    'pluginOptions' => [
                                        'autoclose' => true,
                                        'format' => 'yyyy-mm-dd',
                                        'todayHighlight' => true,
                                    ],
                                ]) ?>
                                <p class="help-block">选择报表周期的开始日期</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">结束日期：</label>
                            <div class="col-sm-9">
                                <?= DatePicker::widget([
                                    'name' => 'end_date',
                                    'value' => date('Y-m-d'),
                                    'options' => ['placeholder' => '选择结束日期...'],
                                    'pluginOptions' => [
                                        'autoclose' => true,
                                        'format' => 'yyyy-mm-dd',
                                        'todayHighlight' => true,
                                    ],
                                ]) ?>
                                <p class="help-block">选择报表周期的结束日期</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-9">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="glyphicon glyphicon-search"></i> 生成报表
                                </button>
                                <?= Html::a(
                                    '<i class="glyphicon glyphicon-remove"></i> 取消',
                                    ['index'],
                                    ['class' => 'btn btn-default btn-lg']
                                ) ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 快捷选择 -->
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-flash"></i> 快捷选择
                    </h3>
                </div>
                <div class="panel-body">
                    <p>选择常用的时间范围：</p>
                    <div class="btn-group-vertical btn-block" role="group">
                        <?php
                        $shortcuts = [
                            ['label' => '最近7天', 'start' => date('Y-m-d', strtotime('-6 days')), 'end' => date('Y-m-d')],
                            ['label' => '最近30天', 'start' => date('Y-m-d', strtotime('-29 days')), 'end' => date('Y-m-d')],
                            ['label' => '最近90天', 'start' => date('Y-m-d', strtotime('-89 days')), 'end' => date('Y-m-d')],
                            ['label' => '本周', 'start' => date('Y-m-d', strtotime('monday this week')), 'end' => date('Y-m-d')],
                            ['label' => '上周', 'start' => date('Y-m-d', strtotime('monday last week')), 'end' => date('Y-m-d', strtotime('sunday last week'))],
                            ['label' => '本月至今', 'start' => date('Y-m-01'), 'end' => date('Y-m-d')],
                            ['label' => '上月全月', 'start' => date('Y-m-01', strtotime('first day of last month')), 'end' => date('Y-m-t', strtotime('last day of last month'))],
                            ['label' => '今年至今', 'start' => date('Y-01-01'), 'end' => date('Y-m-d')],
                        ];
                        foreach ($shortcuts as $shortcut):
                        ?>
                            <?= Html::a(
                                '<i class="glyphicon glyphicon-calendar"></i> ' . $shortcut['label'] .
                                ' <small>(' . $shortcut['start'] . ' ~ ' . $shortcut['end'] . ')</small>',
                                ['custom', 'start_date' => $shortcut['start'], 'end_date' => $shortcut['end']],
                                ['class' => 'btn btn-default btn-block text-left', 'style' => 'margin-bottom: 5px;']
                            ) ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- 使用说明 -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-question-sign"></i> 使用说明
                    </h3>
                </div>
                <div class="panel-body">
                    <h4>自定义周期报表的优势：</h4>
                    <ul>
                        <li><strong>灵活性</strong>：可以选择任意日期范围，不受月/季/年限制</li>
                        <li><strong>对比分析</strong>：选择特定时期进行财务对比</li>
                        <li><strong>专项报告</strong>：为特定项目或目标生成专项财务报告</li>
                        <li><strong>趋势追踪</strong>：跟踪特定时期内的财务趋势变化</li>
                    </ul>

                    <h4>适用场景：</h4>
                    <ul>
                        <li>项目周期财务分析（如某个投资项目的全周期收益）</li>
                        <li>特殊时期对比（如节假日期间的消费分析）</li>
                        <li>跨年度分析（如分析最近12个月的数据）</li>
                        <li>短期密集追踪（如最近一周的投资活动）</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>
