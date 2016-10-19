<?php
namespace app\controllers;


class InvoiceController extends Rest { // TODO EO
	public function init() {
		$this->modelClass =	\eo\models\database\Invoice::className();

		parent::init();
	}
}
