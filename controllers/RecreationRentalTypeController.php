<?php
namespace app\controllers;


class RecreationRentalTypeController extends Rest { // TODO EO
	public function init() {
		$this->modelClass =	\eo\models\database\RecreationRentalType::className();
		$this->modelsNamespace 	=	'\eo\models\database';

		parent::init();
	}
}