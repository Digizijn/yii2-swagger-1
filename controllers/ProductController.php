<?php
namespace app\controllers;
use eo\models\database\Products;


/**
 * Products
 *
 * Retreive products
 *
 * @definition Products
 */
class ProductController extends Rest {
	public function init() {
		$this->modelClass =	\eo\models\database\Products::className();
		$this->modelsNamespace 	=	'\eo\models\database';

		parent::init();
	}


	/**
     * Retreive all products
     *
     * @path /products
     * @method get
	 * @security default
     * @tag products
     * @consumes application/json
	 * @return Products[] successful operation
     * @errors 405 Invalid input
     */
	public function __index($expand = []) {}

	/**
     * Retreive specific product
     *
     * @path /products/{id}
     * @method get
     * @tag products
	 * @security default
     * @consumes application/json
     * @param integer $id
     * @parameter int64 $id Product id to retreive
	 * @return Products successful operation
     * @constraint minimum $id 1
     * @errors 404 Product not found
     */
	public function __view($expand = []) {}
}
