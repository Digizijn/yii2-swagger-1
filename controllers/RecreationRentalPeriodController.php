<?php
namespace app\controllers;
use eo\models\database\Products;
use eo\models\database\RecreationObjectFacility;
use eo\models\database\RecreationObjectFacilityPeriod;
use eo\models\database\RecreationPeriodPrice;
use eo\models\database\RecreationRentalPeriod;
use eo\models\database\RecreationRentalPrice;
use eo\models\database\RecreationRentalType;

/**
 * Recreation object rental periods
 *
 * Retreive recreation object rental periods
 *
 * @definition RecreationRentalPeriod
 */
class RecreationRentalPeriodController extends Rest {
	public function init() {
		$this->modelClass =	\eo\models\database\RecreationRentalPeriod::className();
		$this->modelsNamespace 	=	'\eo\models\database';

		parent::init();
	}

	/**
     * Retreive all rental periods
     *
     * @path /recreation/rentalperiods
     * @method get
	 * @security default
     * @tag rentalperiods
	 * @optparameter string[] $expand
	 * @enum $expand rentalType prices product
	 * @return RecreationRentalPeriod[] successful operation
     * @errors 405 Invalid input
     */
	public function actionAll($expand = []) {}


	/**
     * Retreive specific rental period
     *
     * @path /recreation/rentalperiods/{id}
     * @method get
	 * @security default
     * @tag rentalperiods
     * @param integer $id
     * @parameter int64 $id Rental period id to retreive
     * @constraint minimum $id 1
	 * @optparameter string[] $expand
	 * @enum $expand rentalType prices product
	 * @return RecreationRentalPeriod successful operation
     * @errors 404 Rental period not found
     */
	public function actionOne($expand = []) {}


	/**
	 * Retreive rentaltype from specific rental period
	 *
	 * @path /recreation/rentalperiods/{id}/rentaltype
	 * @method get
	 * @security default
	 * @tag rentalperiods
	 * @tag rentaltypes
	 * @param integer $id
	 * @parameter int64 $id Rental period id to retreive rentaltype from
	 * @constraint minimum $id 1
	 * @return RecreationRentalType successful operation
	 * @errors 404 Rental period not found
	 */
	public function actionRentaltype() {}


//	/**
//	 * Retreive prices from specific rental period
//	 *
//	 * @path /recreation/rentalperiods/{id}/prices
//	 * @method get
//	 * @security default
//	 * @tag rentalperiods
//	 * @tag rentaltypes
//	 * @param integer $id
//	 * @parameter int64 $id Rental period id to retreive prices from
//	 * @constraint minimum $id 1
//	 * @return RecreationPeriodPrice[] successful operation
//	 * @errors 404 Rental period not found
//	 */
//	public function actionPrices() {}


	/**
	 * Retreive product from specific rental period
	 *
	 * @path /recreation/rentalperiods/{id}/product
	 * @method get
	 * @security default
	 * @tag rentalperiods
	 * @tag products
	 * @param integer $id
	 * @parameter int64 $id Rental period id to retreive product from
	 * @constraint minimum $id 1
	 * @return Products successful operation
	 * @errors 404 Rental period not found
	 */
	public function actionProduct() {}
}