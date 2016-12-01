<?php
namespace app\controllers;
use eo\models\database\RecreationPeriod;
use eo\models\database\RecreationPeriodPrice;
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
	 * @return RecreationPeriod[] successful operation
     * @errors 405 Invalid input
     */
	public function actionAll($expand = []) {}


	/**
     * Retreive specific period
     *
     * @path /recreation/periods/{id}
     * @method get
     * @tag periods
	 * @security default
     * @param integer $id
     * @parameter int64 $id Period id to retreive
     * @constraint minimum $id 1
	 * @optparameter string[] $expand
	 * @enum $expand prices
	 * @return RecreationPeriod successful operation
     * @errors 404 Period not found
     */
	public function actionOne($expand = []) {}
}