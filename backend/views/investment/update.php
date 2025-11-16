<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Investment */
/* @var $fundList array */
/* @var $productList array */

$this->title = '更新投资: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => '投资管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '更新';
?>
<div class="investment-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'fundList' => $fundList,
        'productList' => $productList,
    ]) ?>

</div>
