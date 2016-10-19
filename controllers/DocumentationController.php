<?php
namespace app\controllers;


class DocumentationController extends Controller{

	public function actionIndex(){
		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

		$controllerArray = 	array(
								ProductController::className(),
								RecreationObjectController::className(),
								RecreationObjectFacilitiesController::className(),
								RecreationObjectTypeController::className(),
								RecreationPackageController::className(),
								RecreationRentalPeriodController::className(),
								RecreationPeriodController::className(),
								RecreationCompositionController::className(),
								RecreationFloormapController::className(),
								ProductController::className(),
								RecreationBookingController::className(),
								RecreationEventController::className(),

								RecreationRentalTypeController::className(),

								//RelationController::className()
							);

		$json = [];
		foreach($controllerArray as $controller){
			$r = new $controller('documentation', $this->module);
			$json = $r->generator->getJson('', $json);
		}

		return $json;
	}
}