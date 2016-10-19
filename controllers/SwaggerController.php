<?php
/**
 * Created by PhpStorm.
 * User: Robert
 * Date: 29-9-2016
 * Time: 12:56
 */

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