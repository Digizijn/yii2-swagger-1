<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\WebsitePages */

$this->title = Yii::t('app', 'Create Website Pages');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Website Pages'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="website-pages-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
