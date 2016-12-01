<?php

class RecreationEventCest
{
	public function save(ApiTester $I)
	{
		$I->wantTo('Make a reservation');
		$I->haveHttpHeader('Content-Type', 'application/json');
		$I->sendPOST('/recreation/events', [
			'compositions' => [
				[
					'composition_id' => 22,
					'amount' => 2
				],
				[
					'composition_id' => 20,
					'amount' => 3
				],
				[
					'composition_id' => 28,
					'amount' => 1
				]
			],
			'object_id' 	=> 337,
			'arrival'		=> '2017-01-01',
			'departure'		=> '2017-01-03',
			'relation_id'	=> 140709, // TODO wijzigen
			'facilities' 	=> [
				[
					'facility_id' => 184,
					'amount' => 1
				],
				[
					'facility_id' => 167,
					'amount' => 2
				]
			],
			'extras'		=> [
				[
					'product_id' => 12345,
					'amount' => 2
				]
			], // TODO
			'block_id' 		=> null, // TODO
			'preferable' 	=> true,
			'generate_invoices' => true
		]);

		$I->seeResponseCodeIs(200);
	}
}
