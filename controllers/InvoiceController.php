<?php
namespace app\controllers;
use yii\rest\ActiveController;


class RelationController extends RestController { // TODO EO
	public function init() {
		$this->modelClass =	\eo\models\database\Relation::className();

		parent::init();
	}
}
