<?php
/**
 * Created by PhpStorm.
 * User: Robert
 * Date: 28-9-2016
 * Time: 10:04
 */

namespace app\controllers;
use eo\models\database\RecreationObjectFacility;

/**
 * Recreation object facilities
 *
 * Retreive recreation object facilities
 *
 * @definition RecreationObjectFacility
 * @schemes https
 */
class RecreationObjectFacilitiesController extends Rest {
	public function init() {
		$this->modelClass =	\eo\models\database\RecreationObjectFacility::className();
		$this->modelsNamespace 	=	'\eo\models\database';

		parent::init();
	}

	/**
     * Retreive all objectfacilities
     *
     * @path /recreation/facilities
     * @method get
	 * @security default
     * @tag facilities
     * @optparameter string[] $expand
	 * @enum $expand types objects product
     * @errors 405 Invalid input
	 * @return RecreationObjectFacility[] successful operation
     */
	public function __index() {}

	/**
     * Retreive specific objectfacilities
     *
     * @path /recreation/facilities/{id}
     * @method get
     * @tag facilities
	 * @security default
	 * @return RecreationObjectFacility successful operation
     * @param integer $id
     * @parameter int64 $id Objectfacility id to retreive
     * @optparameter string[] $expand
	 * @enum $expand types objects product
     * @constraint minimum $id 1
     * @errors 404 Object not found
     */
	public function __view() {}
}