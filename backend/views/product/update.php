<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\InvestmentProduct */

$this->title = '更新理财产品: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => '理财产品管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '更新';
?>
<div class="investment-product-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
