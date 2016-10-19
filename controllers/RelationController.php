<?php
namespace app\controllers;


class RelationController extends Rest { // TODO EO
	public function init() {
		$this->modelClass =	\eo\models\database\Relation::className();

		parent::init();
	}
}
