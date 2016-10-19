<?php
namespace app\controllers;
use eo\models\database\RecreationObjectFacility;
use eo\models\database\RecreationObjectFacilityPeriod;
use eo\models\database\RecreationRentalPeriod;

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
     * @errors 405 Invalid input
	 * @return RecreationRentalPeriod[] successful operation
     */
	public function __index() {}

	/**
     * Retreive specific rental period
     *
     * @path /recreation/rentalperiods/{id}
     * @method get
	 * @security default
     * @tag rentalperiods
	 * @return RecreationRentalPeriod successful operation
     * @param integer $id
     * @parameter int64 $id Rental period id to retreive
     * @constraint minimum $id 1
     * @errors 404 Rental period not found
     */
	public function __view() {}
}