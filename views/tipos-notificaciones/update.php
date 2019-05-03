<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\Models\TiposNotificaciones */

$this->title = 'Update Tipos Notificaciones: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Tipos Notificaciones', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="tipos-notificaciones-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
