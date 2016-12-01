<?php
namespace app\controllers;
use eo\models\database\RecreationComposition;
use eo\models\database\RecreationObject;
use eo\models\database\RecreationObjectFacility;
use eo\models\database\RecreationObjectType;
use eo\models\database\RecreationPackage;


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
 * @definition RecreationPackage
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
	 * @enum $expand facilities rentalTypes objects composition packages
     * @errors 405 Invalid input
	 * @return RecreationObjectType[] successful operation
     */
	public function actionAll($expand = []) {}

	/**
     * Retreive specific objecttype
     *
     * @path /recreation/objecttypes/{id}
     * @method get
     * @tag objecttypes
	 * @security default
     * @param integer $id
     * @parameter int64 $id Objecttype id to retreive
	 * @constraint minimum $id 1
     * @optparameter string[] $expand
	 * @enum $expand facilities rentalTypes objects composition packages
	 * @return RecreationObjectType successful operation
     * @errors 404 Objecttype not found
     */
	public function actionOne($expand = []) {}


	/**
	 * Retreive facilities from specific objecttype
	 *
	 * @path /recreation/objecttypes/{id}/facilities
	 * @method get
	 * @tag objecttypes
	 * @tag facilities
	 * @security default
	 * @param integer $id
	 * @parameter int64 $id Objecttype id to retreive facilities from
	 * @constraint minimum $id 1
	 * @return RecreationObjectFacility[] successful operation
	 * @errors 404 Objecttype not found
	 */
	public function actionFacilities() {}


	/**
	 * Retreive rentalTypes from specific objecttype
	 *
	 * @path /recreation/objecttypes/{id}/rentaltypes
	 * @method get
	 * @tag objecttypes
	 * @tag rentaltypes
	 * @security default
	 * @param integer $id
	 * @parameter int64 $id Objecttype id to retreive rentalTypes from
	 * @constraint minimum $id 1
	 * @return RecreationObjectFacility[] successful operation
	 * @errors 404 Objecttype not found
	 */
	public function actionRentalTypes() {}


	/**
	 * Retreive objects from specific objecttype
	 *
	 * @path /recreation/objecttypes/{id}/objects
	 * @method get
	 * @tag objecttypes
	 * @tag objects
	 * @security default
	 * @param integer $id
	 * @parameter int64 $id Objecttype id to retreive objects from
	 * @constraint minimum $id 1
	 * @return RecreationObject[] successful operation
	 * @errors 404 Objecttype not found
	 */
	public function actionObjects() {}

	/**
	 * Retreive compositions from specific objecttype
	 *
	 * @path /recreation/objecttypes/{id}/composition
	 * @method get
	 * @tag objecttypes
	 * @tag compositions
	 * @security default
	 * @param integer $id
	 * @parameter int64 $id Objecttype id to retreive compositions from
	 * @constraint minimum $id 1
	 * @return RecreationComposition[] successful operation
	 * @errors 404 Objecttype not found
	 */
	public function actionComposition() {}


	/**
	 * Retreive packages from specific objecttype
	 *
	 * @path /recreation/objecttypes/{id}/packages
	 * @method get
	 * @tag objecttypes
	 * @tag packages
	 * @security default
	 * @param integer $id
	 * @parameter int64 $id Objecttype id to retreive packages from
	 * @constraint minimum $id 1
	 * @return RecreationPackage[] successful operation
	 * @errors 404 Objecttype not found
	 */
	public function actionPackages() {}
}