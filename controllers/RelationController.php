<?php
namespace app\controllers;
use yii\rest\ActiveController;

class RelationController extends ActiveController { // TODO EO
	public $modelClass = '\eo\models\database\Relation';
	public function init() {
		$this->modelClass =	\eo\models\database\Relation::className();

		parent::init();
	}
}
