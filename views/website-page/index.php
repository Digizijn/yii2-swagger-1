<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Website Pages');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="website-pages-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Website Pages'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'page_id',
            'company_id',
            'site_id',
            'parent_id',
            'album_id',
            // 'type_id',
            // 'language_id',
            // 'project_id',
            // 'page_name',
            // 'page_menuname',
            // 'page_title',
            // 'page_language',
            // 'page_content:ntext',
            // 'page_keywords',
            // 'page_description',
            // 'page_enddate',
            // 'page_createdate',
            // 'page_date',
            // 'page_order',
            // 'page_active',
            // 'page_level',
            // 'page_rootonly',
            // 'page_show_loggedin',
            // 'page_personal',
            // 'page_hidden',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
