<?php
namespace app\controllers;
use yii\web\Controller;

class SwaggerController extends Controller
{
	public function actionIndex() {
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;

    	$this->layout	= '@vendor/machour/yii2-swagger-ui/views/layouts/main';

        return $this->render('@vendor/machour/yii2-swagger-ui/views/site/index');
	}
}