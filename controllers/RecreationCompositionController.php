<?php
namespace app\controllers;
use eo\models\database\RecreationComposition;

/**
 * Recreation compositions
 *
 * Retreive recreation compositions
 *
 * @definition RecreationComposition
 */
class RecreationCompositionController extends Rest {
	public function init() {
		$this->modelClass =	\eo\models\database\RecreationComposition::className();
		$this->modelsNamespace 	=	'\eo\models\database';

		parent::init();
	}

	/**
     * Retreive all compositions
     *
     * @path /recreation/compositions
     * @method get
	 * @security default
     * @tag compositions
     * @optparameter string[] $expand
	 * @enum $expand excluded
     * @errors 405 Invalid input
	 * @return RecreationComposition[] successful operation
     */
	public function __index($expand = []) {}

	/**
     * Retreive specific composition
     *
     * @path /recreation/compositions/{id}
     * @method get
     * @tag compositions
	 * @security default
     * @param integer $id
     * @parameter int64 $id Composition id to retreive
     * @optparameter string $expand[]
	 * @enum $expand excluded
	 * @return RecreationComposition successful operation
     * @constraint minimum $id 1
     * @errors 404 Composition not found
     */
	public function __view($expand = []) {}
}