<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ReturnRecord */
/* @var $productList array */

$this->title = '记录收益';
$this->params['breadcrumbs'][] = ['label' => '收益管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="return-record-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'productList' => $productList,
    ]) ?>

</div>
