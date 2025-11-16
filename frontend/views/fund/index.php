<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $funds common\models\Fund[] */
/* @var $totalAssets float */
/* @var $totalInvestment float */

$this->title = '我的基金';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="fund-index-frontend">
    <h1><?= Html::encode($this->title) ?></h1>

    <!-- 总资产概览 -->
    <div class="row" style="margin-bottom: 20px;">
        <div class="col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-piggy-bank"></i> 总资产
                    </h3>
                </div>
                <div class="panel-body text-center">
                    <h2 style="margin: 10px 0;">¥<?= number_format($totalAssets, 2) ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-briefcase"></i> 总投资
                    </h3>
                </div>
                <div class="panel-body text-center">
                    <h2 style="margin: 10px 0;">¥<?= number_format($totalInvestment, 2) ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- 基金卡片列表 -->
    <div class="row">
        <?php foreach ($funds as $fund): ?>
            <?php
                $percentage = $totalAssets > 0 ? ($fund->balance / $totalAssets * 100) : 0;
                $investedAmount = $fund->getInvestedAmount();
                $availableBalance = $fund->getAvailableBalance();
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <strong><?= Html::encode($fund->name) ?></strong>
                            <small class="pull-right">分配比例: <?= $fund->allocation_percentage ?>%</small>
                        </h3>
                    </div>
                    <div class="panel-body">
                        <div style="margin-bottom: 15px;">
                            <div class="row">
                                <div class="col-xs-6 text-center">
                                    <div style="font-size: 12px; color: #999;">当前余额</div>
                                    <div style="font-size: 24px; color: #5cb85c;">
                                        <strong>¥<?= number_format($fund->balance, 2) ?></strong>
                                    </div>
                                </div>
                                <div class="col-xs-6 text-center">
                                    <div style="font-size: 12px; color: #999;">占比</div>
                                    <div style="font-size: 24px; color: #337ab7;">
                                        <strong><?= number_format($percentage, 1) ?>%</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <table class="table table-condensed" style="margin-bottom: 0;">
                            <tr>
                                <td>已投资金额:</td>
                                <td class="text-right"><strong>¥<?= number_format($investedAmount, 2) ?></strong></td>
                            </tr>
                            <tr>
                                <td>可用余额:</td>
                                <td class="text-right"><strong>¥<?= number_format($availableBalance, 2) ?></strong></td>
                            </tr>
                        </table>
                    </div>
                    <div class="panel-footer text-center">
                        <?= Html::a(
                            '<i class="glyphicon glyphicon-eye-open"></i> 查看详情',
                            ['view', 'id' => $fund->id],
                            ['class' => 'btn btn-sm btn-primary']
                        ) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($funds)): ?>
        <div class="alert alert-info text-center">
            <h4>暂无基金数据</h4>
            <p>请联系管理员创建基金账户。</p>
        </div>
    <?php endif; ?>
</div>

<style>
.panel {
    transition: transform 0.2s;
}
.panel:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>
