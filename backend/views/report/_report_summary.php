<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $report array */
?>

<!-- 报表头部信息 -->
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">
            <i class="glyphicon glyphicon-info-sign"></i> 报表概览
        </h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-3">
                <strong>报表周期：</strong> <?= Html::encode($report['period']) ?>
            </div>
            <div class="col-md-3">
                <strong>开始日期：</strong> <?= Html::encode($report['start_date']) ?>
            </div>
            <div class="col-md-3">
                <strong>结束日期：</strong> <?= Html::encode($report['end_date']) ?>
            </div>
            <div class="col-md-3">
                <strong>生成时间：</strong> <?= Html::encode($report['generated_at']) ?>
            </div>
        </div>
    </div>
</div>

<!-- 关键指标卡片 -->
<div class="row">
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="panel panel-success">
            <div class="panel-body text-center">
                <div style="font-size: 28px; font-weight: bold; color: #5cb85c;">
                    ¥<?= number_format($report['income']['total'], 2) ?>
                </div>
                <div style="color: #777; margin-top: 5px;">总收入</div>
                <small class="text-muted"><?= $report['income']['count'] ?> 笔</small>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="panel panel-info">
            <div class="panel-body text-center">
                <div style="font-size: 28px; font-weight: bold; color: #5bc0de;">
                    ¥<?= number_format($report['investment']['total'], 2) ?>
                </div>
                <div style="color: #777; margin-top: 5px;">总投资</div>
                <small class="text-muted"><?= $report['investment']['count'] ?> 笔</small>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="panel panel-warning">
            <div class="panel-body text-center">
                <div style="font-size: 28px; font-weight: bold; color: #f0ad4e;">
                    ¥<?= number_format($report['return']['total'], 2) ?>
                </div>
                <div style="color: #777; margin-top: 5px;">总收益</div>
                <small class="text-muted"><?= $report['return']['count'] ?> 笔</small>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="panel panel-primary">
            <div class="panel-body text-center">
                <div style="font-size: 28px; font-weight: bold; color: #337ab7;">
                    <?= number_format($report['key_metrics']['savings_rate'], 1) ?>%
                </div>
                <div style="color: #777; margin-top: 5px;">储蓄率</div>
                <small class="text-muted">投资/收入</small>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="panel panel-<?= $report['key_metrics']['investment_return_rate'] >= 0 ? 'success' : 'danger' ?>">
            <div class="panel-body text-center">
                <div style="font-size: 28px; font-weight: bold; color: <?= $report['key_metrics']['investment_return_rate'] >= 0 ? '#5cb85c' : '#d9534f' ?>;">
                    <?= number_format($report['key_metrics']['investment_return_rate'], 2) ?>%
                </div>
                <div style="color: #777; margin-top: 5px;">投资收益率</div>
                <small class="text-muted">收益/投资</small>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="panel panel-<?= $report['assets']['growth_rate'] >= 0 ? 'success' : 'danger' ?>">
            <div class="panel-body text-center">
                <div style="font-size: 28px; font-weight: bold; color: <?= $report['assets']['growth_rate'] >= 0 ? '#5cb85c' : '#d9534f' ?>;">
                    <?= number_format($report['assets']['growth_rate'], 2) ?>%
                </div>
                <div style="color: #777; margin-top: 5px;">资产增长率</div>
                <small class="text-muted">(期末-期初)/期初</small>
            </div>
        </div>
    </div>
</div>

<!-- 资产概览 -->
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">
            <i class="glyphicon glyphicon-piggy-bank"></i> 资产概览
        </h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-3 text-center">
                <div style="padding: 20px; background: #f9f9f9; border-radius: 5px;">
                    <div style="font-size: 32px; font-weight: bold; color: #337ab7;">
                        ¥<?= number_format($report['assets']['beginning_total'], 2) ?>
                    </div>
                    <div style="color: #777; margin-top: 10px;">期初总资产</div>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div style="padding: 20px; background: #f9f9f9; border-radius: 5px;">
                    <div style="font-size: 32px; font-weight: bold; color: #5cb85c;">
                        ¥<?= number_format($report['assets']['ending_total'], 2) ?>
                    </div>
                    <div style="color: #777; margin-top: 10px;">期末总资产</div>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div style="padding: 20px; background: #f9f9f9; border-radius: 5px;">
                    <div style="font-size: 32px; font-weight: bold; color: <?= $report['assets']['net_growth'] >= 0 ? '#5cb85c' : '#d9534f' ?>;">
                        <?= $report['assets']['net_growth'] >= 0 ? '+' : '' ?>¥<?= number_format($report['assets']['net_growth'], 2) ?>
                    </div>
                    <div style="color: #777; margin-top: 10px;">净增长</div>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div style="padding: 20px; background: #f9f9f9; border-radius: 5px;">
                    <div style="font-size: 32px; font-weight: bold; color: <?= $report['key_metrics']['net_cash_flow'] >= 0 ? '#5cb85c' : '#d9534f' ?>;">
                        <?= $report['key_metrics']['net_cash_flow'] >= 0 ? '+' : '' ?>¥<?= number_format($report['key_metrics']['net_cash_flow'], 2) ?>
                    </div>
                    <div style="color: #777; margin-top: 10px;">净现金流</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 收入投资收益明细 -->
<div class="row">
    <!-- 收入明细 -->
    <div class="col-md-4">
        <div class="panel panel-success">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="glyphicon glyphicon-arrow-down"></i> 收入明细
                </h3>
            </div>
            <div class="panel-body">
                <div class="list-group">
                    <div class="list-group-item">
                        <strong>总收入：</strong>
                        <span class="pull-right text-success">
                            <strong>¥<?= number_format($report['income']['total'], 2) ?></strong>
                        </span>
                    </div>
                    <div class="list-group-item">
                        <strong>收入笔数：</strong>
                        <span class="pull-right"><?= $report['income']['count'] ?> 笔</span>
                    </div>
                    <div class="list-group-item">
                        <strong>平均收入：</strong>
                        <span class="pull-right">¥<?= number_format($report['income']['average'], 2) ?></span>
                    </div>
                </div>
                <?php if (!empty($report['income']['by_source'])): ?>
                    <h5 style="margin-top: 15px;">收入来源分布：</h5>
                    <?php foreach (array_slice($report['income']['by_source'], 0, 5) as $source => $amount): ?>
                        <div style="margin-bottom: 5px;">
                            <small><?= Html::encode($source) ?></small>
                            <div class="progress" style="margin-bottom: 0;">
                                <div class="progress-bar progress-bar-success"
                                     style="width: <?= $report['income']['total'] > 0 ? ($amount / $report['income']['total'] * 100) : 0 ?>%;">
                                    ¥<?= number_format($amount, 2) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 投资明细 -->
    <div class="col-md-4">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="glyphicon glyphicon-transfer"></i> 投资明细
                </h3>
            </div>
            <div class="panel-body">
                <div class="list-group">
                    <div class="list-group-item">
                        <strong>总投资：</strong>
                        <span class="pull-right text-info">
                            <strong>¥<?= number_format($report['investment']['total'], 2) ?></strong>
                        </span>
                    </div>
                    <div class="list-group-item">
                        <strong>投资笔数：</strong>
                        <span class="pull-right"><?= $report['investment']['count'] ?> 笔</span>
                    </div>
                    <div class="list-group-item">
                        <strong>平均投资：</strong>
                        <span class="pull-right">¥<?= number_format($report['investment']['average'], 2) ?></span>
                    </div>
                </div>
                <?php if (!empty($report['investment']['by_fund'])): ?>
                    <h5 style="margin-top: 15px;">按基金分布：</h5>
                    <?php foreach (array_slice($report['investment']['by_fund'], 0, 5) as $fund => $data): ?>
                        <div style="margin-bottom: 5px;">
                            <small><?= Html::encode($fund) ?> (<?= $data['count'] ?>笔)</small>
                            <div class="progress" style="margin-bottom: 0;">
                                <div class="progress-bar progress-bar-info"
                                     style="width: <?= $report['investment']['total'] > 0 ? ($data['amount'] / $report['investment']['total'] * 100) : 0 ?>%;">
                                    ¥<?= number_format($data['amount'], 2) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 收益明细 -->
    <div class="col-md-4">
        <div class="panel panel-warning">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="glyphicon glyphicon-arrow-up"></i> 收益明细
                </h3>
            </div>
            <div class="panel-body">
                <div class="list-group">
                    <div class="list-group-item">
                        <strong>总收益：</strong>
                        <span class="pull-right text-warning">
                            <strong>¥<?= number_format($report['return']['total'], 2) ?></strong>
                        </span>
                    </div>
                    <div class="list-group-item">
                        <strong>收益笔数：</strong>
                        <span class="pull-right"><?= $report['return']['count'] ?> 笔</span>
                    </div>
                    <div class="list-group-item">
                        <strong>平均收益：</strong>
                        <span class="pull-right">¥<?= number_format($report['return']['average'], 2) ?></span>
                    </div>
                </div>
                <?php if (!empty($report['return']['by_fund'])): ?>
                    <h5 style="margin-top: 15px;">按基金分布：</h5>
                    <?php foreach (array_slice($report['return']['by_fund'], 0, 5) as $fund => $data): ?>
                        <div style="margin-bottom: 5px;">
                            <small><?= Html::encode($fund) ?> (<?= $data['count'] ?>笔)</small>
                            <div class="progress" style="margin-bottom: 0;">
                                <div class="progress-bar progress-bar-warning"
                                     style="width: <?= $report['return']['total'] > 0 ? ($data['amount'] / $report['return']['total'] * 100) : 0 ?>%;">
                                    ¥<?= number_format($data['amount'], 2) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- 基金明细 -->
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">
            <i class="glyphicon glyphicon-folder-open"></i> 基金明细
        </h3>
    </div>
    <div class="panel-body">
        <table class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>基金名称</th>
                    <th class="text-right">当前余额</th>
                    <th class="text-center">分配比例</th>
                    <th class="text-right">本期投资</th>
                    <th class="text-right">本期收益</th>
                    <th class="text-center">收益率</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($report['fund_details'] as $fund): ?>
                    <tr>
                        <td><strong><?= Html::encode($fund['name']) ?></strong></td>
                        <td class="text-right">¥<?= number_format($fund['current_balance'], 2) ?></td>
                        <td class="text-center"><?= $fund['allocation_percent'] ?>%</td>
                        <td class="text-right">¥<?= number_format($fund['period_investment'], 2) ?></td>
                        <td class="text-right">¥<?= number_format($fund['period_return'], 2) ?></td>
                        <td class="text-center">
                            <span class="label label-<?= $fund['return_rate'] >= 5 ? 'success' : ($fund['return_rate'] >= 0 ? 'info' : 'danger') ?>">
                                <?= number_format($fund['return_rate'], 2) ?>%
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 预算执行情况 -->
<?php if (!empty($report['budget_execution'])): ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="glyphicon glyphicon-dashboard"></i> 预算执行情况
            </h3>
        </div>
        <div class="panel-body">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>预算名称</th>
                        <th>周期类型</th>
                        <th class="text-right">预算金额</th>
                        <th class="text-right">实际金额</th>
                        <th class="text-center">使用率</th>
                        <th class="text-center">状态</th>
                        <th class="text-right">剩余/超支</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report['budget_execution'] as $budget): ?>
                        <tr>
                            <td><?= Html::encode($budget['fund_name']) ?></td>
                            <td><?= Html::encode($budget['period_type']) ?></td>
                            <td class="text-right">¥<?= number_format($budget['budget_amount'], 2) ?></td>
                            <td class="text-right">¥<?= number_format($budget['actual_amount'], 2) ?></td>
                            <td class="text-center"><?= number_format($budget['usage_rate'], 1) ?>%</td>
                            <td class="text-center">
                                <span class="label label-<?= $budget['is_over_budget'] ? 'danger' : 'success' ?>">
                                    <?= Html::encode($budget['status']) ?>
                                </span>
                            </td>
                            <td class="text-right <?= $budget['is_over_budget'] ? 'text-danger' : 'text-success' ?>">
                                <?= $budget['is_over_budget'] ? '-' : '+' ?>¥<?= number_format(abs($budget['remaining']), 2) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
