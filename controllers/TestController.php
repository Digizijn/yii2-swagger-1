<?php

namespace app\controllers;


use eo\base\EO;
use eo\models\database\RecreationObject;
use yii\helpers\ArrayHelper;

class TestController extends Controller
{
	public function actionIndex() {
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;

		return EO::t('facilities', 'facilities');
	}
}