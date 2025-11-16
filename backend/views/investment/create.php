<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Investment */
/* @var $fundList array */
/* @var $productList array */

$this->title = '新增投资';
$this->params['breadcrumbs'][] = ['label' => '投资管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="investment-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'fundList' => $fundList,
        'productList' => $productList,
    ]) ?>

</div>
