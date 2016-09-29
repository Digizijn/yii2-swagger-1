<?php
namespace app\controllers;
use \yii\rest\ActiveController;

abstract class RestController extends ActiveController {
	public function behaviors() {
		$behaviors = parent::behaviors();

		unset($behaviors['contentNegotiator']['formats']['application/xml']);
		$behaviors['contentNegotiator']['formats']['application/jsonp'] = \yii\web\Response::FORMAT_JSONP;

		return $behaviors;
	}

}