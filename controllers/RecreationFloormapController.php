<?php
namespace app\controllers;
use eo\models\database\RecreationComposition;
use eo\models\database\RecreationFloormap;

/**
 * Recreation floormap
 *
 * Retreive recreation floormap
 *
 * @definition RecreationFloormap
 * @definition RecreationFloormapMarker
 */
class RecreationFloormapController extends Rest {
	public function init() {
		$this->modelClass =	\eo\models\database\RecreationFloormap::className();
		$this->modelsNamespace 	=	'\eo\models\database';

		parent::init();
	}

	/**
     * Retreive all floormaps
     *
     * @path /recreation/floormaps
     * @method get
     * @tag floormaps
     * @optparameter string[] $expand
	 * @enum $expand markers
     * @errors 405 Invalid input
	 * @return RecreationFloormap[] successful operation
     */
	public function __index($expand = []) {}

	/**
     * Retreive specific floormaps
     *
     * @path /recreation/floormaps/{id}
     * @method get
     * @tag floormaps
	 * @security default
     * @param integer $id
     * @parameter int64 $id Floormap id to retreive
     * @optparameter string $expand[]
	 * @enum $expand markers
	 * @return RecreationFloormap successful operation
     * @constraint minimum $id 1
     * @errors 404 Floormap not found
     */
	public function __view($expand = []) {}
}