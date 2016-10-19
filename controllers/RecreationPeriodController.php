<?php
namespace app\controllers;
use eo\models\database\RecreationPeriod;
use eo\models\database\RecreationRentalPeriod;

/**
 * Recreation object periods
 *
 * Retreive recreation object periods
 *
 * @definition RecreationPeriod
 * @definition RecreationRentalPeriod
 */
class RecreationPeriodController extends Rest {
	public function init() {
		$this->modelClass =	\eo\models\database\RecreationPeriod::className();
		$this->modelsNamespace 	=	'\eo\models\database';

		parent::init();
	}

	/**
     * Retreive all periods
     *
     * @path /recreation/periods
     * @method get
     * @tag periods
	 * @security default
	 * @optparameter string[] $expand
	 * @enum $expand prices
     * @errors 405 Invalid input
	 * @return RecreationPeriod[] successful operation
     */
	public function __index($expand = []) {}

	/**
     * Retreive specific period
     *
     * @path /recreation/periods/{id}
     * @method get
     * @tag periods
	 * @security default
	 * @return RecreationPeriod successful operation
     * @param integer $id
     * @parameter int64 $id Period id to retreive
     * @constraint minimum $id 1
     * @errors 404 Period not found
     */
	public function __view($expand = []) {}
}