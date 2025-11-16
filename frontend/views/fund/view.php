<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $fund common\models\Fund */
/* @var $investments common\models\Investment[] */
/* @var $incomeDistributions common\models\IncomeDistribution[] */
/* @var $returnDistributions common\models\ReturnDistribution[] */

$this->title = $fund->name;
$this->params['breadcrumbs'][] = ['label' => '我的基金', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="fund-view-frontend">
    <h1><?= Html::encode($this->title) ?></h1>

    <!-- 基金概况 -->
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="glyphicon glyphicon-folder-open"></i> 基金概况
            </h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-3 text-center">
                    <div style="font-size: 14px; color: #999;">当前余额</div>
                    <div style="font-size: 32px; color: #5cb85c; margin: 10px 0;">
                        <strong>¥<?= number_format($fund->balance, 2) ?></strong>
                    </div>
                </div>
                <div class="col-md-3 text-center">
                    <div style="font-size: 14px; color: #999;">已投资金额</div>
                    <div style="font-size: 32px; color: #337ab7; margin: 10px 0;">
                        <strong>¥<?= number_format($fund->getInvestedAmount(), 2) ?></strong>
                    </div>
                </div>
                <div class="col-md-3 text-center">
                    <div style="font-size: 14px; color: #999;">可用余额</div>
                    <div style="font-size: 32px; color: #f0ad4e; margin: 10px 0;">
                        <strong>¥<?= number_format($fund->getAvailableBalance(), 2) ?></strong>
                    </div>
                </div>
                <div class="col-md-3 text-center">
                    <div style="font-size: 14px; color: #999;">分配比例</div>
                    <div style="font-size: 32px; color: #d9534f; margin: 10px 0;">
                        <strong><?= $fund->allocation_percentage ?>%</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 投资记录 -->
    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="glyphicon glyphicon-transfer"></i> 投资记录
                <span class="badge"><?= count($investments) ?></span>
            </h3>
        </div>
        <div class="panel-body">
            <?php if (!empty($investments)): ?>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>日期</th>
                            <th>理财产品</th>
                            <th class="text-right">投资金额</th>
                            <th>状态</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($investments as $investment): ?>
                            <tr>
                                <td><?= date('Y-m-d', $investment->created_at) ?></td>
                                <td><?= Html::encode($investment->product->name ?? '-') ?></td>
                                <td class="text-right">¥<?= number_format($investment->amount, 2) ?></td>
                                <td>
                                    <?php if ($investment->status == \common\models\Investment::STATUS_ACTIVE): ?>
                                        <span class="label label-success">生效中</span>
                                    <?php else: ?>
                                        <span class="label label-default">已赎回</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">暂无投资记录</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <!-- 收入分配记录 -->
        <div class="col-md-6">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-arrow-down"></i> 收入分配记录
                        <span class="badge"><?= count($incomeDistributions) ?></span>
                    </h3>
                </div>
                <div class="panel-body">
                    <?php if (!empty($incomeDistributions)): ?>
                        <table class="table table-condensed table-hover">
                            <thead>
                                <tr>
                                    <th>日期</th>
                                    <th class="text-right">金额</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($incomeDistributions as $dist): ?>
                                    <tr>
                                        <td><?= date('Y-m-d', $dist->created_at) ?></td>
                                        <td class="text-right text-success">
                                            <strong>+¥<?= number_format($dist->amount, 2) ?></strong>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">暂无收入分配记录</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 收益分配记录 -->
        <div class="col-md-6">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-arrow-up"></i> 收益分配记录
                        <span class="badge"><?= count($returnDistributions) ?></span>
                    </h3>
                </div>
                <div class="panel-body">
                    <?php if (!empty($returnDistributions)): ?>
                        <table class="table table-condensed table-hover">
                            <thead>
                                <tr>
                                    <th>日期</th>
                                    <th class="text-right">金额</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($returnDistributions as $dist): ?>
                                    <tr>
                                        <td><?= date('Y-m-d', $dist->created_at) ?></td>
                                        <td class="text-right text-warning">
                                            <strong>+¥<?= number_format($dist->amount, 2) ?></strong>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">暂无收益分配记录</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <p>
        <?= Html::a('<i class="glyphicon glyphicon-chevron-left"></i> 返回列表', ['index'], ['class' => 'btn btn-default']) ?>
    </p>
</div>
