<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\WebsitePages */

$this->title = $model->page_id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Website Pages'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="website-pages-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->page_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->page_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'page_id',
            'company_id',
            'site_id',
            'parent_id',
            'album_id',
            'type_id',
            'language_id',
            'project_id',
            'page_name',
            'page_menuname',
            'page_title',
            'page_language',
            'page_content:ntext',
            'page_keywords',
            'page_description',
            'page_enddate',
            'page_createdate',
            'page_date',
            'page_order',
            'page_active',
            'page_level',
            'page_rootonly',
            'page_show_loggedin',
            'page_personal',
            'page_hidden',
        ],
    ]) ?>

</div>
