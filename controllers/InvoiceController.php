<?php
namespace app\controllers;
use eo\base\EO;
use eo\models\database\ApiSettings;
use eo\models\database\Invoice;
use eo\models\database\Journal;
use eo\models\database\JournalTransaction;
use eo\models\database\JournalTransactionLine;
use eo\models\database\JournalTransactionPayment;
use eo\models\database\PaymentConnection;
use eo\models\database\Relation;
use Yii;
use yii\db\Expression;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;


/**
 * Invoices
 *
 * Invoices
 *
 * @definition Invoice
 * @definition InvoiceProduct
 */
class InvoiceController extends Rest {
	public function init() {
		$this->modelClass =	\eo\models\database\Invoice::className();
		$this->modelsNamespace 	=	'\eo\models\database';

		parent::init();
	}

	/**
	 * Retreive all invoices
	 *
	 * @path /invoices
	 * @method get
	 * @security default
	 * @tag invoices
	 * @optparameter string[] $expand
	 * @enum $expand products relation
	 * @errors 405 Invalid input
	 * @return Invoice[] successful operation
	 */
	public function actionAll($expand = []) {}

	/**
	 * Retreive specific invoice
	 *
	 * @path /invoices/{id}
	 * @method get
	 * @tag invoices
	 * @security default
	 * @param integer $id
	 * @parameter int64 $id Invoice id to retreive
	 * @optparameter string $expand[]
	 * @enum $expand products relation
	 * @return Invoice successful operation
	 * @constraint minimum $id 1
	 * @errors 404 Relation not found
	 */
	public function actionOne($expand = []) {}

}
