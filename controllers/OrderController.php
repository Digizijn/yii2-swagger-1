<?php
namespace app\controllers;


class OrderController extends Rest { // TODO EO
	public function init() {
		$this->modelClass =	\eo\models\database\Orders::className();
		$this->modelsNamespace 	=	'\eo\models\database';

		parent::init();
	}
}
