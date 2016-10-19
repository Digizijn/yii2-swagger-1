<?php
namespace app\controllers;
use eo\models\database\RecreationComposition;
use eo\models\database\RecreationObjectFacility;
use eo\models\database\RecreationObjectType;


/**
 * Recreation object types
 *
 * Retreive recreation objecttypes
 *
 * @definition RecreationObject
 * @definition RecreationObjectType
 * @definition RecreationPeriod
 * @definition RecreationPeriodPrice
 * @definition RecreationObjectTypeImage
 * @definition RecreationObjectFacility
 * @definition RecreationRentalType
 * @definition RecreationObjectTypeDescriptionTranslation
 */
class RecreationObjectTypeController extends Rest { // TODO EO
	public function init() {
		$this->modelClass =	\eo\models\database\RecreationObjectType::className();
		$this->modelsNamespace 	=	'\eo\models\database';

		parent::init();
	}

	/**
     * Retreive all objecttypes
     *
     * @path /recreation/objecttypes
     * @method get
	 * @security default
     * @tag objecttypes
     * @optparameter string[] $expand
	 * @enum $expand facilities rentalTypes objects composition
     * @errors 405 Invalid input
	 * @return RecreationObjectType[] successful operation
     */
	public function actionAll($expand = null) {}

	/**
     * Retreive specific objecttype
     *
     * @path /recreation/objecttypes/{id}
     * @method get
     * @tag objecttypes
	 * @security default
	 * @return RecreationObjectType successful operation
     * @param integer $id
     * @parameter int64 $id Objecttype id to retreive
     * @optparameter string[] $expand
	 * @enum $expand facilities rentalTypes objects composition
     * @constraint minimum $id 1
     * @errors 404 Objecttype not found
     */
	public function actionOne($expand = null) {}


	/**
     * Retreive specific objecttype facilities
     *
     * @path /recreation/objecttypes/{id}/facilities
     * @method get
     * @tag objecttypes
	 * @security default
	 * @return RecreationObjectFacility[] successful operation
     * @param integer $id
     * @parameter int64 $id Objecttype id to retreive facilities from
     * @constraint minimum $id 1
     * @errors 404 Objecttype not found
     */
	public function actionFacilities($expand = null) {}



	/**
     * Retreive specific objecttype composition
     *
     * @path /recreation/objecttypes/{id}/composition
     * @method get
     * @tag objecttypes
	 * @security default
	 * @return RecreationComposition[] successful operation
     * @param integer $id
     * @parameter int64 $id Objecttype id to retreive compositions from
     * @constraint minimum $id 1
     * @errors 404 Objecttype not found
     */
	public function actionComposition($expand = null) {}
}