<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\InvestmentProduct */

$this->title = '创建理财产品';
$this->params['breadcrumbs'][] = ['label' => '理财产品管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="investment-product-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
