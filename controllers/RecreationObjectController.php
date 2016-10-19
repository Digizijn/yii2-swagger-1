<?php
namespace app\controllers;
use eo\base\EO;
use eo\models\database\RecreationObject;

/**
 * Recreation objects
 *
 * Retreive recreation objects
 *
 * @definition RecreationObject
 */
class RecreationObjectController extends Rest {
	public function init() {
		$this->modelClass =	\eo\models\database\RecreationObject::className();
		$this->modelsNamespace 	=	'\eo\models\database';

		parent::init();
	}

	/**
     * Retreive all objects
     *
     * @path /recreation/objects
     * @method get
	 * @security default
     * @tag objects
     * @optparameter string[] $expand
	 * @enum $expand objectType
     * @errors 405 Invalid input
	 * @return RecreationObject[] successful operation
     */
	public function actionAllRecreationObjects(){}

	/**
     * Retreive specific object
     *
     * @path /recreation/objects/{id}
     * @method get
	 * @security default
     * @tag objects
	 * @return RecreationObject successful operation
     * @param integer $id
     * @parameter int64 $id Object id to retreive
     * @optparameter string[] $expand
	 * @enum $expand objectType
     * @constraint minimum $id 1
     * @errors 404 Object not found
     */
	public function actionRecreationObjectById() {}

}
