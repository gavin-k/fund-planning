<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ReturnRecord */
/* @var $productList array */

$this->title = '更新收益: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => '收益管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '更新';
?>
<div class="return-record-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'productList' => $productList,
    ]) ?>

</div>
