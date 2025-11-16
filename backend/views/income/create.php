<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Income */

$this->title = '记录收入';
$this->params['breadcrumbs'][] = ['label' => '收入管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="income-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
