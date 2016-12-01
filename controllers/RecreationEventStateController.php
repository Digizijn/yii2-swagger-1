<?php
namespace app\controllers;
use eo\models\database\RecreationComposition;
use eo\models\database\RecreationEventsState;
use eo\models\database\RecreationFloormap;

/**
 * Recreation event states
 *
 * Retreive recreation event states
 *
 * @definition RecreationEventsState
 */
class RecreationEventStateController extends Rest {
	public function init() {
		$this->modelClass =	\eo\models\database\RecreationEventsState::className();
		$this->modelsNamespace 	=	'\eo\models\database';

		parent::init();
	}

	/**
     * Retreive all event states
     *
     * @path /recreation/eventstates
     * @method get
     * @tag eventstates
	 * @return RecreationEventsState[] successful operation
     * @errors 405 Invalid input
     */
	public function actionAll() {}

	/**
     * Retreive specific event state
     *
     * @path /recreation/eventstates/{id}
     * @method get
     * @tag eventstates
	 * @security default
     * @param integer $id
     * @parameter int64 $id Floormap id to retreive
	 * @constraint minimum $id 1
	 * @return RecreationEventsState successful operation
     * @errors 404 Eventstate not found
     */
	public function actionOne() {}
}