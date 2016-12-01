<?php
namespace app\controllers;
use eo\models\database\Products;
use eo\models\database\RecreationObjectType;
use eo\models\database\RecreationPackage;
use eo\models\database\RecreationPackageImage;
use eo\models\database\RecreationPeriod;
use eo\models\database\RecreationRentalType;

/**
 * Recreation packages
 *
 * Retreive recreation packages
 *
 * @definition RecreationPackage
 * @definition RecreationPeriod
 * @definition RecreationObject
 * @definition RecreationObjectType
 * @definition RecreationRentalType
 * @definition Products
 */
class RecreationPackageController extends Rest { // TODO EO
	public function init() {
		$this->modelClass =	\eo\models\database\RecreationPackage::className();
		$this->modelsNamespace 	=	'\eo\models\database';

		parent::init();
	}

	/**
     * Retreive all packages
     *
     * @path /recreation/packages
     * @method get
	 * @security default
     * @tag packages
	 * @optparameter date-time $from
	 * @optparameter date-time $till
	 * @optparameter int32 $objecttype
     * @optparameter string[] $expand
	 * @enum $expand periods objecttype images rentaltype product
	 * @return RecreationPackage[] successful operation
     * @errors 405 Invalid input
     */
	public function actionAll($expand = [], $from = null, $till = null, $objecttype = null) {}


	/**
     * Retreive specific package
     *
     * @path /recreation/packages/{id}
     * @method get
     * @tag packages
	 * @security default
     * @param integer $id
     * @parameter int64 $id Package id to retreive
	 * @constraint minimum $id 1
     * @optparameter string $expand[]
	 * @enum $expand periods objecttype images rentaltype product
	 * @return RecreationPackage successful operation
     * @errors 404 Object not found
     */
	public function actionOne($expand = []) {}


	// TODO wat voor periods?
	/**
	 * Retreive periods from specific package
	 *
	 * @path /recreation/packages/{id}/periods
	 * @method get
	 * @tag packages
	 * @tag periods
	 * @security default
	 * @param integer $id
	 * @parameter int64 $id Package id to retreive periods from
	 * @constraint minimum $id 1
	 * @return RecreationPeriod[] successful operation
	 * @errors 404 Object not found
	 */
	public function actionPeriods() {}


	/**
	 * Retreive objecttypes from specific package
	 *
	 * @path /recreation/packages/{id}/objecttypes
	 * @method get
	 * @tag packages
	 * @tag objecttypes
	 * @security default
	 * @param integer $id
	 * @parameter int64 $id Package id to retreive objecttypes from
	 * @constraint minimum $id 1
	 * @return RecreationObjectType[] successful operation
	 * @errors 404 Object not found
	 */
	public function actionObjecttypes() {}


	/**
	 * Retreive rentaltype from specific package
	 *
	 * @path /recreation/packages/{id}/rentaltype
	 * @method get
	 * @tag packages
	 * @tag rentaltype
	 * @security default
	 * @param integer $id
	 * @parameter int64 $id Package id to retreive rentaltype from
	 * @constraint minimum $id 1
	 * @return RecreationRentalType[] successful operation
	 * @errors 404 Object not found
	 */
	public function actionRentaltype($expand = []) {}


	/**
	 * Retreive product from specific package
	 *
	 * @path /recreation/packages/{id}/product
	 * @method get
	 * @tag packages
	 * @tag products
	 * @security default
	 * @param integer $id
	 * @parameter int64 $id Package id to retreive product from
	 * @constraint minimum $id 1
	 * @return Products[] successful operation
	 * @errors 404 Object not found
	 */
	public function actionProduct($expand = []) {}
}