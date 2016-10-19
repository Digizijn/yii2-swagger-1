<?php
namespace app\controllers;
use eo\models\database\RecreationPackage;

/**
 * Recreation packages
 *
 * Retreive recreation packages
 *
 * @definition RecreationPackage
 * @definition RecreationPeriod
 * @definition RecreationObject
 * @definition RecreationObjectType
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
	 *
	 * @optparameter date-time $from
	 * @optparameter date-time $till
	 * @optparameter int32 $objecttype
     * @optparameter string[] $expand
	 * @enum $expand periods
     * @errors 405 Invalid input
	 * @return RecreationPackage[] successful operation
     */
	public function __index($expand = [], $from = null, $till = null, $objecttype = null) {}

	/**
     * Retreive specific package
     *
     * @path /recreation/packages/{id}
     * @method get
     * @tag packages
	 * @security default
	 * @return RecreationPackage successful operation
     * @param integer $id
     * @parameter int64 $id Package id to retreive
     * @optparameter string $expand[]
	 * @enum $expand periods
     * @constraint minimum $id 1
     * @errors 404 Object not found
     */
	public function __view($expand = []) {}
}