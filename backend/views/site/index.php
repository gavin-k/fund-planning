<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $totalAssets float */
/* @var $funds common\models\Fund[] */
/* @var $totalInvestment float */
/* @var $monthlyIncome float */
/* @var $monthlyReturn float */
/* @var $recentIncomes common\models\Income[] */
/* @var $recentInvestments common\models\Investment[] */

$this->title = '理财计划 Dashboard';
?>

<div class="site-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <!-- 总资产概览区 -->
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="glyphicon glyphicon-piggy-bank" style="font-size: 48px;"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div style="font-size: 24px;">¥<?= number_format($totalAssets, 2) ?></div>
                            <div>总资产</div>
                        </div>
                    </div>
                </div>
                <a href="<?= Url::to(['fund/index']) ?>">
                    <div class="panel-footer">
                        <span class="pull-left">查看详情</span>
                        <span class="pull-right"><i class="glyphicon glyphicon-circle-arrow-right"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="glyphicon glyphicon-briefcase" style="font-size: 48px;"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div style="font-size: 24px;">¥<?= number_format($totalInvestment, 2) ?></div>
                            <div>总投资</div>
                        </div>
                    </div>
                </div>
                <a href="<?= Url::to(['investment/index']) ?>">
                    <div class="panel-footer">
                        <span class="pull-left">查看详情</span>
                        <span class="pull-right"><i class="glyphicon glyphicon-circle-arrow-right"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="glyphicon glyphicon-arrow-down" style="font-size: 48px;"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div style="font-size: 24px;">¥<?= number_format($monthlyIncome, 2) ?></div>
                            <div>本月收入</div>
                        </div>
                    </div>
                </div>
                <a href="<?= Url::to(['income/index']) ?>">
                    <div class="panel-footer">
                        <span class="pull-left">查看详情</span>
                        <span class="pull-right"><i class="glyphicon glyphicon-circle-arrow-right"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="glyphicon glyphicon-arrow-up" style="font-size: 48px;"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div style="font-size: 24px;">¥<?= number_format($monthlyReturn, 2) ?></div>
                            <div>本月收益</div>
                        </div>
                    </div>
                </div>
                <a href="<?= Url::to(['return/index']) ?>">
                    <div class="panel-footer">
                        <span class="pull-left">查看详情</span>
                        <span class="pull-right"><i class="glyphicon glyphicon-circle-arrow-right"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- 各基金余额一览 -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="glyphicon glyphicon-folder-open"></i> 各基金余额一览
            </h3>
        </div>
        <div class="panel-body">
            <?php if (!empty($funds)): ?>
                <?php foreach ($funds as $fund): ?>
                    <?php
                        $percentage = $totalAssets > 0 ? ($fund->balance / $totalAssets * 100) : 0;
                        $progressBarClass = 'progress-bar-success';
                        if ($percentage < 5) {
                            $progressBarClass = 'progress-bar-danger';
                        } elseif ($percentage < 10) {
                            $progressBarClass = 'progress-bar-warning';
                        }
                    ?>
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-md-3">
                            <strong><?= Html::a(Html::encode($fund->name), ['fund/view', 'id' => $fund->id]) ?></strong>
                            <small class="text-muted">(分配比例: <?= $fund->allocation_percentage ?>%)</small>
                        </div>
                        <div class="col-md-3 text-right">
                            <span class="text-success" style="font-size: 16px;">
                                <strong>¥<?= number_format($fund->balance, 2) ?></strong>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <div class="progress" style="margin-bottom: 0;">
                                <div class="progress-bar <?= $progressBarClass ?>"
                                     role="progressbar"
                                     style="width: <?= $percentage ?>%; min-width: 2em;">
                                    <?= number_format($percentage, 1) ?>%
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">暂无基金数据，请先 <?= Html::a('创建基金', ['fund/create']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- 最近交易记录 -->
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-list-alt"></i> 最近收入记录
                    </h3>
                </div>
                <div class="panel-body">
                    <?php if (!empty($recentIncomes)): ?>
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>日期</th>
                                    <th class="text-right">金额</th>
                                    <th>备注</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentIncomes as $income): ?>
                                    <tr>
                                        <td><?= Html::encode($income->income_date) ?></td>
                                        <td class="text-right">
                                            <strong class="text-success">+¥<?= number_format($income->amount, 2) ?></strong>
                                        </td>
                                        <td>
                                            <?= Html::encode($income->note ? mb_substr($income->note, 0, 20) . (mb_strlen($income->note) > 20 ? '...' : '') : '-') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?= Html::a('查看全部 »', ['income/index'], ['class' => 'btn btn-sm btn-success']) ?>
                    <?php else: ?>
                        <p class="text-muted">暂无收入记录</p>
                        <?= Html::a('<i class="glyphicon glyphicon-plus"></i> 记录收入', ['income/create'], ['class' => 'btn btn-sm btn-success']) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-transfer"></i> 最近投资记录
                    </h3>
                </div>
                <div class="panel-body">
                    <?php if (!empty($recentInvestments)): ?>
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>日期</th>
                                    <th>基金</th>
                                    <th>产品</th>
                                    <th class="text-right">金额</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentInvestments as $investment): ?>
                                    <tr>
                                        <td><?= date('Y-m-d', $investment->created_at) ?></td>
                                        <td><?= Html::encode($investment->fund->name ?? '-') ?></td>
                                        <td><?= Html::encode($investment->product->name ?? '-') ?></td>
                                        <td class="text-right">¥<?= number_format($investment->amount, 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?= Html::a('查看全部 »', ['investment/index'], ['class' => 'btn btn-sm btn-info']) ?>
                    <?php else: ?>
                        <p class="text-muted">暂无投资记录</p>
                        <?= Html::a('<i class="glyphicon glyphicon-plus"></i> 新建投资', ['investment/create'], ['class' => 'btn btn-sm btn-info']) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 快捷操作 -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="glyphicon glyphicon-flash"></i> 快捷操作
            </h3>
        </div>
        <div class="panel-body text-center">
            <?= Html::a(
                '<i class="glyphicon glyphicon-plus"></i> 记录收入',
                ['income/create'],
                ['class' => 'btn btn-success btn-lg', 'style' => 'margin: 5px;']
            ) ?>
            <?= Html::a(
                '<i class="glyphicon glyphicon-transfer"></i> 新建投资',
                ['investment/create'],
                ['class' => 'btn btn-info btn-lg', 'style' => 'margin: 5px;']
            ) ?>
            <?= Html::a(
                '<i class="glyphicon glyphicon-arrow-up"></i> 记录收益',
                ['return/create'],
                ['class' => 'btn btn-warning btn-lg', 'style' => 'margin: 5px;']
            ) ?>
            <?= Html::a(
                '<i class="glyphicon glyphicon-folder-open"></i> 管理基金',
                ['fund/index'],
                ['class' => 'btn btn-primary btn-lg', 'style' => 'margin: 5px;']
            ) ?>
            <?= Html::a(
                '<i class="glyphicon glyphicon-shopping-cart"></i> 管理产品',
                ['product/index'],
                ['class' => 'btn btn-default btn-lg', 'style' => 'margin: 5px;']
            ) ?>
        </div>
    </div>
</div>

<style>
.panel-heading a {
    text-decoration: none;
}
.panel-footer {
    background-color: #fff;
}
.panel-footer a {
    color: #999;
}
.panel-footer a:hover {
    color: #333;
}
</style>
