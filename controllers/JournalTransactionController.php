<?php

namespace app\controllers;


use eo\base\EO;
use eo\models\database\ApiSettings;
use eo\models\database\Invoice;
use eo\models\database\Journal;
use eo\models\database\JournalTransaction;
use eo\models\database\JournalTransactionLine;
use eo\models\database\JournalTransactionPayment;
use yii\db\Expression;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;


/**
 * JournalTransaction
 *
 * JournalTransaction
 *
 * @definition Invoice
 */
class JournalTransactionController extends Rest {
	public function init() {
		$this->modelClass 		= '\eo\models\database\JournalTransaction'; // Dummy
		$this->modelsNamespace 	= '\eo\models\database';

		parent::init();
	}

	/**
	 * Create transaction
	 *
	 * @path /transactions
	 * @method post
	 * @tag transactions
	 * @security default
	 * @param double $amount
	 * @parameter double $amount Amount paid
	 * @param string $date
	 * @parameter string $date Date of payment
	 * @param string $ref
	 * @parameter string $ref Ref to payment
	 * @param integer[] $invoice_ids
	 * @parameter int64[] $invoice_ids Invoice ids towards this transaction is made
	 * @return integer successful operation
	 * @constraint minimum $id 1
	 * @errors 404 Invoice(s) not found
	 */
	public function actionSave($amount, $date, $ref, array $invoice_ids = []) {
		$user		= EO::$app->user;
		$identity	= $user->identity;
		$userId 	= $identity->user_id;

		if (empty($invoice_ids)) {
			throw new NotFoundHttpException('Geen factureren meegegeven');
		}

		$invoices = Invoice::find()->andWhere(['invoice_id' => $invoice_ids])->all();
		foreach ($invoices as $invoice) {
			if (empty($invoice_ids)) {
				throw new NotFoundHttpException('Factuur niet gevonden');
			}

//			if (!$finalize && $invoice->transaction_id <= 0) {
//				throw new BadRequestHttpException('Factuur is nog niet definitief');
//			}
		}

		// Kruisposten/instelling api
		$setting = ApiSettings::find()->andWhere(['user_id' => $userId])->one();
		if (empty($setting)) {
			throw new ServerErrorHttpException('Geen API instellingen beschikbaar');
		}

		$ledger_nr = $setting->api_payment_ledger;

		// Memoriaal
		$journal_type_memo	= 'memo';
		$journal_mem	= Journal::find()->andWhere(['journal_type' => $journal_type_memo])->cache()->one();

		// Debiteuren
		$journal_type_sales	= 'sales';
		$journal_sales	= Journal::find()->andWhere(['journal_type' => $journal_type_sales])->cache()->one();


		foreach ($invoices as $invoice) {
			$booknr			= Journal::getLastBooknr($journal_mem->journal_id);
			$relation		= $invoice->relation;
			$invoiceAmount	= $invoice->getPayablePrice()->getInclusive();

			if (empty($journal_mem)) {
				throw new ServerErrorHttpException('Geen memoriaalboek');
			}

			if (empty($journal_sales)) {
				throw new ServerErrorHttpException('Geen verkoopboek');
			}

			if (empty($ledger_nr)) {
				throw new ServerErrorHttpException('Geen grootboek');
			}

			if (empty($booknr)) {
				throw new ServerErrorHttpException('Kan geen boekstuknummer aanmaken');
			}

			if (empty($relation)) {
				throw new ServerErrorHttpException('Geen relatie gekoppeld aan factuur');
			}

			$description	= Yii::t('payment', 'Betaling factuur {factuurnummer}', [
				'{factuurnummer}' 	=> $invoice->invoice_facnr
			]);

			$invoicenumber	= $invoice->invoice_facnr;
			$relationId		= $relation->relation_id;

			$paid			= $amount;
			if($paid > $invoiceAmount){
				$paid	= $invoiceAmount;
			}
			$amount	-= $paid;

			//$paid			= $this->payment_paid_amount;
			if ($paid > 0) {
				$creditAmount	= 0;
				$debitAmount	= $paid;
			} else {
				$creditAmount	= abs($paid);
				$debitAmount	= 0;
			}

			// Transaction
			$transaction	= new JournalTransaction();
			$transaction->transaction_id_old		= 0;
			$transaction->journal_id				= $journal_mem->journal_id;
			$transaction->company_id				= EO::param('company_id');
			$transaction->transaction_booknumber	= $booknr;
			$transaction->transaction_invoicenumber	= $invoicenumber;
			$transaction->transaction_description	= $description;
			$transaction->transaction_reference		= $ref ?? $description;
			$transaction->transaction_date			= new Expression('NOW()'); // TODO date
			$transaction->transaction_createdate	= new Expression('NOW()');
			$transaction->transaction_createuser	= $userId;
			$transaction->transaction_changedate	= new Expression('NOW()');
			$transaction->transaction_changeuser	= $userId;

			// Transaction line
			if ($transaction->save()) {
				$transaction_id		= $transaction->transaction_id;
				$line1	= new JournalTransactionLine();
				$line1->transaction_id	= $transaction_id;
				$line1->ledger_nr		= $journal_sales->ledger_nr;
				$line1->vat_id			= 0;
				$line1->relation_id		= $relationId;
				$line1->project_id		= 0;
				$line1->file_id			= 0;
				$line1->line_date		= new Expression('NOW()');
				$line1->line_reportdate	= new Expression('NOW()');
				$line1->line_description= $description;
				$line1->line_debit		= $debitAmount;
				$line1->line_credit		= $creditAmount;
				$line1->line_vat_perc	= 0;
				$line1->line_vat_debit	= 0;
				$line1->line_vat_credit	= 0;
				$line1->line_vat_locked	= '';
				$line1->line_order		= 1;

				$line2	= new JournalTransactionLine();
				$line2->transaction_id	= $transaction_id;
				$line2->ledger_nr		= $ledger_nr;
				$line2->vat_id			= 0;
				$line2->relation_id		= 0;
				$line2->project_id		= 0;
				$line2->file_id			= 0;
				$line2->line_date		= new Expression('NOW()');
				$line2->line_reportdate	= new Expression('NOW()');
				$line2->line_description= $description;
				$line2->line_debit		= $creditAmount;
				$line2->line_credit		= $debitAmount;
				$line2->line_vat_perc	= 0;
				$line2->line_vat_debit	= 0;
				$line2->line_vat_credit	= 0;
				$line2->line_vat_locked	= '';
				$line2->line_order		= 2;

				if ($line1->save() && $line2->save()) {
					$payment	= new JournalTransactionPayment();
					$payment->transaction_id	= $invoice->transaction_id;
					$payment->parent_id			= $line1->line_id;
					$payment->payment_debit		= $debitAmount;
					$payment->payment_credit	= $creditAmount;
					$payment->payment_createdate= new Expression('NOW()');
					$payment->payment_createuser= $userId;
					$payment->payment_changedate= new Expression('NOW()');
					$payment->payment_changeuser= $userId;
					if ($payment->save()) {
						// Log 'Betaling #'.$this->payment_id.' afgeletterde transactie #'.$transaction_id.' factuur #'.$invoice->invoice_id.' nr '.$invoice->invoice_facnr);
					} else {
						// Log 'Fout bij verwerken online betaling transactie #'.$transaction_id.', factuur #'.$invoicenumber.',transaction payment niet opgeslagen: '.print_r($payment->getErrors(), true));
					}
				} else {
					// Log 'Fout bij verwerken online betaling transactie #'.$transaction_id.', factuur #'.$invoicenumber.', transaction-regel niet opgeslagen: '.print_r($line1->getErrors(), true).PHP_EOL.print_r($line2->getErrors(), true));
				}
			} else {
				// Log 'Fout bij verwerken online betaling factuur #'.$invoicenumber.', transaction niet opgeslagen: '.print_r($transaction->getErrors(), true));
			}
		}
	}
}