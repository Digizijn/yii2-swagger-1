<?php
namespace app\controllers;
use eo\models\database\RecreationEvents;
use eo\models\database\RecreationEventsComposition;

/**
 * Recreation eventcompositions
 *
 * Recreation event compositions
 *
 * @definition RecreationEventsComposition
 * @definition RecreationEvents
 */
class RecreationEventCompositionController extends Rest {
	public function init() {
		$this->modelClass =	RecreationEventsComposition::className();
		$this->modelsNamespace 	=	'\eo\models\database';

		parent::init();
	}


	/**
	 * Retreive all event compositions
	 *
	 * @path /recreation/events/compositions
	 * @method get
	 * @security default
	 * @tag compositions
	 * @optparameter string[] $expand
	 * @enum $expand event composition
	 * @return RecreationEventsComposition[] successful operation
	 */
	public function actionAll($expand = []) {}


	/**
	 * Retreive specific event composition
	 *
	 * @path /recreation/events/compositions/{id}
	 * @method get
	 * @tag compositions
	 * @security default
	 * @param integer $id
	 * @parameter int64 $id Composition event id to retreive
	 * @constraint minimum $id 1
	 * @optparameter string $expand[]
	 * @enum $expand event composition
	 * @return RecreationEventsComposition successful operation
	 * @errors 404 Composition not found
	 */
	public function actionOne($expand = []) {}



	/**
	 * Retreive event from specific event composition
	 *
	 * @path /recreation/events/compositions/{id}/event
	 * @method get
	 * @tag compositions
	 * @tag events
	 * @security default
	 * @param integer $id
	 * @parameter int64 $id Composition event id to retreive event from
	 * @constraint minimum $id 1
	 * @return RecreationEvents successful operation
	 * @errors 404 Composition not found
	 */
	public function actionEvent() {}


	/**
	 * Create event composition
	 *
	 * @path /recreation/events/compositions
	 * @method post
	 * @tag compositions
	 * @security default
	 * @param RecreationEventsComposition $event_composition
	 * @parameter RecreationEventsComposition $event_composition Composition to create
	 * @return integer successful operation
	 * @errors 400 Could not create event composition
	 * @errors 404 Composition not found
	 * @errors 405 Invalid input
	 */
	public function actionCreate() {}
}