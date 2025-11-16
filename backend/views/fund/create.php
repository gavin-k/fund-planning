<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Fund */

$this->title = '创建基金';
$this->params['breadcrumbs'][] = ['label' => '基金管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="fund-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
