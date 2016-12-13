<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\WebsitePages */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="website-pages-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'company_id')->textInput() ?>

    <?= $form->field($model, 'site_id')->textInput() ?>

    <?= $form->field($model, 'parent_id')->textInput() ?>

    <?= $form->field($model, 'album_id')->textInput() ?>

    <?= $form->field($model, 'type_id')->textInput() ?>

    <?= $form->field($model, 'language_id')->textInput() ?>

    <?= $form->field($model, 'project_id')->textInput() ?>

    <?= $form->field($model, 'page_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'page_menuname')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'page_title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'page_language')->dropDownList([ 'Nederlands' => 'Nederlands', 'Engels' => 'Engels', 'Duits' => 'Duits', 'Frans' => 'Frans', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'page_content')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'page_keywords')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'page_description')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'page_enddate')->textInput() ?>

    <?= $form->field($model, 'page_createdate')->textInput() ?>

    <?= $form->field($model, 'page_date')->textInput() ?>

    <?= $form->field($model, 'page_order')->textInput() ?>

    <?= $form->field($model, 'page_active')->dropDownList([ 'ja' => 'Ja', 'nee' => 'Nee', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'page_level')->textInput() ?>

    <?= $form->field($model, 'page_rootonly')->dropDownList([ 'ja' => 'Ja', 'nee' => 'Nee', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'page_show_loggedin')->dropDownList([ 'ja' => 'Ja', 'nee' => 'Nee', 'altijd' => 'Altijd', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'page_personal')->dropDownList([ 'ja' => 'Ja', 'nee' => 'Nee', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'page_hidden')->dropDownList([ 'ja' => 'Ja', 'nee' => 'Nee', ], ['prompt' => '']) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
