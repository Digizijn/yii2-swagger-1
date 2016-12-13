<?php

class JournalTransactionCest
{
	public function create(ApiTester $I)
	{
		$I->wantTo('Add a payment');
		$I->haveHttpHeader('Content-Type', 'application/json');
		$I->sendPOST('/transactions', [
			'amount' 		=> 2,
			'date'			=> '2017-01-01',
			'ref'			=> 'TEST',
			'invoice_ids' => [
				1,
				2,
				3,
			],
		]);
		$I->seeResponseCodeIs(200);
	}
}
