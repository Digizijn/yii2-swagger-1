<?php
/**
 * Created by PhpStorm.
 * User: Robert
 * Date: 28-9-2016
 * Time: 10:04
 */

namespace app\controllers;
use eo\models\database\Products;
use eo\models\database\RecreationObject;
use eo\models\database\RecreationObjectFacility;
use eo\models\database\RecreationObjectType;

/**
 * Recreation object facilities
 *
 * Retreive recreation object facilities
 *
 * @definition RecreationObjectFacility
 * @definition Products
 * @definition RecreationObject
 * @definition RecreationObjectType
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
	 * @enum $expand objects objecttypes product
     * @errors 405 Invalid input
	 * @return RecreationObjectFacility[] successful operation
     */
	public function actionAll() {}


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
	 * @enum $expand objects objecttypes product
     * @constraint minimum $id 1
     * @errors 404 Object not found
     */
	public function actionOne() {}


	/**
	 * Retreive objects from specific objectfacilities
	 *
	 * @path /recreation/facilities/{id}
	 * @method get
	 * @tag facilities
	 * @tag objects
	 * @security default
	 * @param integer $id
	 * @parameter int64 $id Objectfacility id to retreive objects from
	 * @constraint minimum $id 1
	 * @return RecreationObject successful operation
	 * @errors 404 Object not found
	 */
	public function actionObjects() {}


	/**
	 * Retreive objecttypes from specific objectfacilities
	 *
	 * @path /recreation/facilities/{id}/objecttypes
	 * @method get
	 * @tag facilities
	 * @tag objecttypes
	 * @security default
	 * @param integer $id
	 * @parameter int64 $id Objectfacility id to retreive objecttypes from
	 * @constraint minimum $id 1
	 * @return RecreationObjectType successful operation
	 * @errors 404 Object not found
	 */
	public function actionoObjecttypes() {}


	/**
	 * Retreive product from specific objectfacilities
	 *
	 * @path /recreation/facilities/{id}/product
	 * @method get
	 * @tag facilities
	 * @tag products
	 * @security default
	 * @param integer $id
	 * @parameter int64 $id Objectfacility id to retreive product from
	 * @constraint minimum $id 1
	 * @return Products successful operation
	 * @errors 404 Object not found
	 */
	public function actionProduct() {}
}