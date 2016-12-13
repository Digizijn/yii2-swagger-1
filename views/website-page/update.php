<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\WebsitePages */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Website Pages',
]) . $model->page_id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Website Pages'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->page_id, 'url' => ['view', 'id' => $model->page_id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="website-pages-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
