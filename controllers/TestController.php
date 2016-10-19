<?php

namespace app\controllers;


use eo\models\database\RecreationObject;
use yii\helpers\ArrayHelper;

class TestController extends Controller
{
	public function actionIndex() {
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;

		$objects = RecreationObject::find()->all();
		foreach ($objects as $object) {
			$ot = $object->objectType;

			if (empty($ot)) {
				var_dump($object);die();
			}

			ArrayHelper::merge(
				$object->getFacilities()->all(),
				$ot->getFacilities()->all()
			);
		}

		return $this->render('dummy');
	}
}