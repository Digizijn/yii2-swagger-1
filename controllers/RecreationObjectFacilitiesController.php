<?php
/**
 * Created by PhpStorm.
 * User: Robert
 * Date: 28-9-2016
 * Time: 10:04
 */

namespace app\controllers;


class RecreationObjectFacilitiesController extends RestController {
	public function init() {
		$this->modelClass =	\eo\models\database\RecreationObjectFacility::className();

		parent::init();
	}
}