<?php

class ObjectCest
{
    public function _before(ApiTester $I) {
    }

    public function _after(ApiTester $I) {
    }

	public function objects(ApiTester $I)
	{
		$I->wantTo('Get all recreation objects');
		$I->sendGET('/recreation/objects');
		$I->seeResponseCodeIs(200);
	}

	public function object(ApiTester $I)
	{
		$I->wantTo('Get a specific recreation objects');
		$I->sendGET('/recreation/objects/337'); // TODO Make dynamic
		$I->seeResponseCodeIs(200);
	}

	public function nonexistingObject(ApiTester $I)
	{
		$I->wantTo('Non-existing objects are not returned');
		$I->sendGET('/recreation/objects/999999');
		$I->seeResponseCodeIs(404);
	}

	public function forbiddenObject(ApiTester $I)
	{
		$I->wantTo('Non-accessable objects are not returned');
		$I->sendGET('/recreation/objects/1');
		$I->seeResponseCodeIs(404);
	}
}
