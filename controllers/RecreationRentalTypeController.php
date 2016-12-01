<?php
namespace app\controllers;
use eo\models\database\Products;
use eo\models\database\RecreationObject;
use eo\models\database\RecreationObjectType;
use eo\models\database\RecreationRentalPeriod;
use eo\models\database\RecreationRentalType;

/**
 * Everyoffice API
 *
 * Everyoffice API
 *
 * @definition RecreationRentalType
 * @definition RecreationRentalPeriod
 * @definition RecreationObject
 * @definition Products
 * @definition RecreationObjectType
 */
class RecreationRentalTypeController extends Rest { // TODO EO
	public function init() {
		$this->modelClass =	\eo\models\database\RecreationRentalType::className();
		$this->modelsNamespace 	=	'\eo\models\database';

		parent::init();
	}


	/**
	 * Retreive all rental types
	 *
	 * @path /recreation/rentaltypes
	 * @method get
	 * @security default
	 * @tag rentaltypes
	 * @optparameter string[] $expand
	 * @enum $expand rentalperiods objects product objecttypes translations paymentmethods
	 * @errors 405 Invalid input
	 * @return RecreationRentalType[] successful operation
	 */
	public function actionAll($expand = []) {}

	/**
	 * Retreive specific rental type
	 *
	 * @path /recreation/rentaltypes/{id}
	 * @method get
	 * @tag rentaltypes
	 * @security default
	 * @param integer $id
	 * @parameter int64 $id Rental type id to retreive
	 * @constraint minimum $id 1
	 * @optparameter string $expand[]
	 * @enum $expand rentalperiods objects product objecttypes translations paymentmethods
	 * @return RecreationRentalType successful operation
	 * @errors 404 Rental type not found
	 */
	public function actionOne($expand = []) {}


	/**
	 * Retreive rentalperiods from specific rental type
	 *
	 * @path /recreation/rentaltypes/{id}/rentalperiods
	 * @method get
	 * @tag rentaltypes
	 * @tag rentalperiods
	 * @security default
	 * @param integer $id
	 * @parameter int64 $id Rental type id to retreive rentalperiods from
	 * @constraint minimum $id 1
	 * @return RecreationRentalPeriod successful operation
	 * @errors 404 Rental type not found
	 */
	public function actionRentalperiods() {}


	/**
	 * Retreive objects from specific rental type
	 *
	 * @path /recreation/rentaltypes/{id}/objects
	 * @method get
	 * @tag rentaltypes
	 * @tag objects
	 * @security default
	 * @param integer $id
	 * @parameter int64 $id Rental type id to retreive objects from
	 * @constraint minimum $id 1
	 * @return RecreationObject successful operation
	 * @errors 404 Rental type not found
	 */
	public function actionObjects() {}


	/**
	 * Retreive product from specific rental type
	 *
	 * @path /recreation/rentaltypes/{id}/product
	 * @method get
	 * @tag rentaltypes
	 * @tag products
	 * @security default
	 * @param integer $id
	 * @parameter int64 $id Rental type id to retreive product from
	 * @constraint minimum $id 1
	 * @return Products successful operation
	 * @errors 404 Rental type not found
	 */
	public function actionProduct() {}


	/**
	 * Retreive objecttypes from specific rental type
	 *
	 * @path /recreation/rentaltypes/{id}/objecttypes
	 * @method get
	 * @tag rentaltypes
	 * @tag objecttypes
	 * @security default
	 * @param integer $id
	 * @parameter int64 $id Rental type id to retreive objecttypes from
	 * @constraint minimum $id 1
	 * @return RecreationObjectType successful operation
	 * @errors 404 Rental type not found
	 */
	public function actionObjecttypes() {}

}