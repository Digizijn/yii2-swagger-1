<?php

class ObjectTypeCest
{
    public function _before(ApiTester $I) {
    }

    public function _after(ApiTester $I) {
    }

	public function objects(ApiTester $I)
	{
		$I->wantTo('Get all recreation objecttypes');
		$I->sendGET('/recreation/objecttypes');
		$I->seeResponseCodeIs(200);
	}

	public function object(ApiTester $I)
	{
		$I->wantTo('Get a specific recreation objecttype');
		$I->sendGET('/recreation/objecttypes/52'); // TODO Make dynamic
		$I->seeResponseCodeIs(200);
	}

	public function nonexistingObject(ApiTester $I)
	{
		$I->wantTo('Non-existing objecttypes are not returned');
		$I->sendGET('/recreation/objecttypes/999999');
		$I->seeResponseCodeIs(404);
	}

	public function forbiddenObject(ApiTester $I)
	{
		$I->wantTo('Non-accessable objecttypes are not returned');
		$I->sendGET('/recreation/objecttypes/1');
		$I->seeResponseCodeIs(404);
	}
}
