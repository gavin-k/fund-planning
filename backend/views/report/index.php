<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $availableMonths array */
/* @var $availableYears array */
/* @var $quarters array */

$this->title = '财务报表';
$this->params['breadcrumbs'][] = $this->title;

$currentMonth = date('Y-m');
$currentYear = date('Y');
$currentQuarter = ceil(date('n') / 3);
?>

<div class="report-index">

    <div class="page-header">
        <h1><?= Html::encode($this->title) ?></h1>
        <p class="lead">生成和查看各类财务报表，分析您的财务状况</p>
    </div>

    <hr>

    <!-- 快捷报表入口 -->
    <div class="row">
        <!-- 月度报表 -->
        <div class="col-md-3">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-calendar"></i> 月度报表
                    </h3>
                </div>
                <div class="panel-body text-center">
                    <i class="glyphicon glyphicon-file" style="font-size: 60px; color: #337ab7;"></i>
                    <h4 style="margin: 20px 0;">查看每月财务数据</h4>
                    <p class="text-muted">收入、支出、收益等详细数据</p>
                    <?= Html::a(
                        '<i class="glyphicon glyphicon-eye-open"></i> 查看本月报表',
                        ['monthly', 'month' => $currentMonth],
                        ['class' => 'btn btn-primary btn-block btn-lg']
                    ) ?>
                </div>
                <div class="panel-footer">
                    <div class="form-group" style="margin: 10px 0;">
                        <label>选择月份：</label>
                        <select class="form-control" id="month-selector">
                            <?php foreach ($availableMonths as $month): ?>
                                <option value="<?= $month ?>" <?= $month === $currentMonth ? 'selected' : '' ?>>
                                    <?= $month ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn btn-default btn-block" onclick="viewMonthlyReport()">
                        <i class="glyphicon glyphicon-search"></i> 查看选定月份
                    </button>
                </div>
            </div>
        </div>

        <!-- 季度报表 -->
        <div class="col-md-3">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-th"></i> 季度报表
                    </h3>
                </div>
                <div class="panel-body text-center">
                    <i class="glyphicon glyphicon-stats" style="font-size: 60px; color: #5cb85c;"></i>
                    <h4 style="margin: 20px 0;">季度财务汇总</h4>
                    <p class="text-muted">每季度收入支出趋势分析</p>
                    <?= Html::a(
                        '<i class="glyphicon glyphicon-eye-open"></i> 查看本季报表',
                        ['quarterly', 'year' => $currentYear, 'quarter' => $currentQuarter],
                        ['class' => 'btn btn-success btn-block btn-lg']
                    ) ?>
                </div>
                <div class="panel-footer">
                    <div class="row" style="margin: 10px 0;">
                        <div class="col-xs-6">
                            <label>年份：</label>
                            <select class="form-control" id="quarter-year-selector">
                                <?php foreach ($availableYears as $year): ?>
                                    <option value="<?= $year ?>" <?= $year == $currentYear ? 'selected' : '' ?>>
                                        <?= $year ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-xs-6">
                            <label>季度：</label>
                            <select class="form-control" id="quarter-selector">
                                <?php foreach ($quarters as $q => $name): ?>
                                    <option value="<?= $q ?>" <?= $q == $currentQuarter ? 'selected' : '' ?>>
                                        Q<?= $q ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button class="btn btn-default btn-block" onclick="viewQuarterlyReport()">
                        <i class="glyphicon glyphicon-search"></i> 查看选定季度
                    </button>
                </div>
            </div>
        </div>

        <!-- 年度报表 -->
        <div class="col-md-3">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-book"></i> 年度报表
                    </h3>
                </div>
                <div class="panel-body text-center">
                    <i class="glyphicon glyphicon-duplicate" style="font-size: 60px; color: #f0ad4e;"></i>
                    <h4 style="margin: 20px 0;">全年财务总结</h4>
                    <p class="text-muted">年度收支、资产增长分析</p>
                    <?= Html::a(
                        '<i class="glyphicon glyphicon-eye-open"></i> 查看本年报表',
                        ['annual', 'year' => $currentYear],
                        ['class' => 'btn btn-warning btn-block btn-lg']
                    ) ?>
                </div>
                <div class="panel-footer">
                    <div class="form-group" style="margin: 10px 0;">
                        <label>选择年份：</label>
                        <select class="form-control" id="year-selector">
                            <?php foreach ($availableYears as $year): ?>
                                <option value="<?= $year ?>" <?= $year == $currentYear ? 'selected' : '' ?>>
                                    <?= $year ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn btn-default btn-block" onclick="viewAnnualReport()">
                        <i class="glyphicon glyphicon-search"></i> 查看选定年份
                    </button>
                </div>
            </div>
        </div>

        <!-- 自定义报表 -->
        <div class="col-md-3">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-filter"></i> 自定义报表
                    </h3>
                </div>
                <div class="panel-body text-center">
                    <i class="glyphicon glyphicon-cog" style="font-size: 60px; color: #5bc0de;"></i>
                    <h4 style="margin: 20px 0;">自定义周期</h4>
                    <p class="text-muted">灵活选择任意时间范围</p>
                    <?= Html::a(
                        '<i class="glyphicon glyphicon-plus"></i> 创建自定义报表',
                        ['custom'],
                        ['class' => 'btn btn-info btn-block btn-lg']
                    ) ?>
                </div>
                <div class="panel-footer">
                    <ul class="list-unstyled" style="margin: 10px 0; font-size: 12px;">
                        <li><i class="glyphicon glyphicon-ok text-success"></i> 任意日期范围</li>
                        <li><i class="glyphicon glyphicon-ok text-success"></i> 灵活对比分析</li>
                        <li><i class="glyphicon glyphicon-ok text-success"></i> 专项财务报告</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- 功能说明 -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="glyphicon glyphicon-question-sign"></i> 报表功能说明
            </h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <h4>报表内容包括：</h4>
                    <ul>
                        <li><strong>收入统计</strong>：总收入、收入来源、平均收入</li>
                        <li><strong>投资统计</strong>：投资总额、按基金/产品分类、投资频次</li>
                        <li><strong>收益统计</strong>：总收益、收益率、按基金/产品分类</li>
                        <li><strong>资产概览</strong>：期初/期末资产、净增长、增长率</li>
                        <li><strong>基金明细</strong>：各基金余额、投资、收益、收益率</li>
                        <li><strong>预算执行</strong>：预算完成情况、超支预警</li>
                        <li><strong>关键指标</strong>：储蓄率、投资收益率、资产增长率等</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h4>使用建议：</h4>
                    <ul>
                        <li><strong>月度报表</strong>：建议每月查看，及时了解当月财务状况</li>
                        <li><strong>季度报表</strong>：用于中期财务回顾和调整投资策略</li>
                        <li><strong>年度报表</strong>：全年总结，制定下一年度财务目标</li>
                        <li><strong>自定义报表</strong>：适用于特定时期的专项分析</li>
                    </ul>
                    <div class="alert alert-info" style="margin-top: 20px;">
                        <strong><i class="glyphicon glyphicon-info-sign"></i> 提示：</strong>
                        月度和年度报表包含同比/环比数据，帮助您分析财务趋势。
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function viewMonthlyReport() {
    var month = document.getElementById('month-selector').value;
    window.location.href = '<?= Url::to(['monthly']) ?>?month=' + month;
}

function viewQuarterlyReport() {
    var year = document.getElementById('quarter-year-selector').value;
    var quarter = document.getElementById('quarter-selector').value;
    window.location.href = '<?= Url::to(['quarterly']) ?>?year=' + year + '&quarter=' + quarter;
}

function viewAnnualReport() {
    var year = document.getElementById('year-selector').value;
    window.location.href = '<?= Url::to(['annual']) ?>?year=' + year;
}
</script>
