<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\FinancialGoal */
/* @var $fundList array */

$this->title = '编辑财务目标: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => '财务目标', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '编辑';
?>
<div class="financial-goal-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <hr>

    <div class="panel panel-default">
        <div class="panel-body">
            <?= $this->render('_form', [
                'model' => $model,
                'fundList' => $fundList,
            ]) ?>
        </div>
    </div>

</div>
