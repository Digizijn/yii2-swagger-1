<?php
namespace app\controllers;
use eo\models\database\RecreationEvents;

/**
 * Recreation events
 *
 * Retreive recreation events
 *
 * @definition RecreationEvents
 */
class RecreationEventController extends Rest {
	public function init() {
		$this->modelClass =	\eo\models\database\RecreationEvents::className();
		$this->modelsNamespace 	=	'\eo\models\database';

		parent::init();
	}

	/**
     * Retreive all events
     *
     * @path /recreation/events
     * @method get
	 * @security default
     * @tag events
	 * @param string $arrival Arrival date
     * @optparameter date-time $arrival Arrival date
     * @param string $arrival Departure date
     * @optparameter date-time $departure Departure date
     * @errors 405 Invalid input
	 * @return RecreationEvents[] successful operation
     */
	public function actionAllRecreationEvents(){}

	/**
     * Retreive specific event
     *
     * @path /recreation/event/{id}
     * @method get
	 * @security default
     * @tag events
	 * @return RecreationEvents successful operation
     * @param integer $id
     * @parameter int64 $id Event id to retreive
     * @constraint minimum $id 1
     * @errors 404 Object not found
     */
	public function actionRecreationEventById() {}


	/**
     * Save booking
     *
     * @path /recreation/event
     * @method post
	 * @security default
     * @tag events
	 * @return RecreationEvents successful operation
     * @param RecreationEvents $event
     * @parameter RecreationEvents $event Event to save
	 * @param boolean $alleendoorrekenen
	 * @parameter boolean $alleendoorrekenen Only price
     * @errors 404 Object not found
     */
	public function actionSaveBooking(){}



	/**
     * Add payment
	 *
     * @path /recreation/event/{id}/add-payment
     * @method post
     * @tag events
	 * @security default
	 * @return boolean successful operation
     * @param integer $id
     * @parameter int64 $id Event id
     * @constraint minimum $id 1
     * @param double $amount
     * @parameter double $amount Amount
     * @constraint minimum $amount 0
     * @param string $date
     * @parameter date-time $date Datum
     * @param string $ref
     * @optparameter string $ref Referentie
     * @errors 404 Not found
     */
	public function actionBlockCancel($id, $amount, $date, $ref = '') {}
}
