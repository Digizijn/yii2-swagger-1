<?php
namespace app\controllers;

use app\controllers\Rest;
use eo\base\EO;
use eo\models\database\Relation;
use eo\models\database\RelationsMailingRegistrations;
use eo\models\database\RelationsMailingTypes;
use yii\db\Expression;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * Products
 *
 * Retreive products
 *
 * @definition RelationsMailingTypes
 */
class MailingTypeController extends Rest {
	public function init() {
		$this->modelClass =	\eo\models\database\RelationsMailingTypes::className();
		$this->modelsNamespace 	=	'\eo\models\database';

		parent::init();
	}


	/**
	 * Retreive all mailingtypes
	 *
	 * @path /mailingtypes
	 * @method get
	 * @security default
	 * @tag mailingtypes
	 * @consumes application/json
	 * @return RelationsMailingTypes[] successful operation
	 * @errors 405 Invalid input
	 */
	public function actionAll($expand = []) {}

	/**
	 * Retreive specific mailing type
	 *
	 * @path /mailingtypes/{id}
	 * @method get
	 * @tag mailingtypes
	 * @security default
	 * @consumes application/json
	 * @param integer $id
	 * @parameter int64 $id Mailingtype id to retreive
	 * @return RelationsMailingTypes successful operation
	 * @constraint minimum $id 1
	 * @errors 404 Mailingtype not found
	 */
	public function actionOne($expand = []) {}


	/**
	 * Subscribe to mailing type
	 *
	 * @path /mailingtypes/{id}/subcribe
	 * @method post
	 * @tag mailingtypes
	 * @security default
	 * @consumes application/json
	 * @param integer $id
	 * @parameter int64 $id Mailingtype to subscribe to
	 * @parameter int64 $relation_id Relation to subscribe
	 * @return RelationsMailingTypes successful operation
	 * @constraint minimum $id 1
	 * @errors 404 Mailingtype not found
	 */
	public function actionSubscribe($id, $relation_id) {
		$mailingtype = RelationsMailingTypes::findOne($id);
		if (empty($mailingtype)) {
			throw new NotFoundHttpException('Mailing-type not found');
		}

		$relation = Relation::findOne($relation_id);
		if (empty($relation)) {
			throw new NotFoundHttpException('Relation not found');
		}

		// Check valide e-mail
		$email = $relation->relation_email ?? $relation->relation_email_2 ?? $relation->relation_email_3;
		if (empty($email) || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
			throw new NotFoundHttpException('Ongeldige e-mail '.$email);
		}

		$registration = RelationsMailingRegistrations::find()
			->andWhere(['type_id' => $id])
			->andWhere(['relation_id' => $relation_id])
			->one();

		if (empty($registration)) {
			$registration = new RelationsMailingRegistrations();
			$registration->registration_createdate	= new Expression('NOW()');
			$registration->registration_createuser	= EO::$app->user->getIdentity()->user_id;
			$registration->relation_id				= $relation_id;
			$registration->type_id					= $id;
		} else {
			if ($registration->registration_state === 'aangemeld') {
				throw new BadRequestHttpException('Relation is already subscribed');
			}
			$registration->registration_changedate	= new Expression('NOW()');
			$registration->registration_changeuser	= EO::$app->user->getIdentity()->user_id;
		}

		$registration->registration_state		= 'aangemeld';

		if (!$registration->save()) {
			throw new ServerErrorHttpException('Cannot save subscription');
		}

		$response 	= EO::$app->getResponse();
		$response->setStatusCode(201);

		return null;
	}
	/**
	 * Unsubscribe relation from mailing type
	 *
	 * @path /mailingtypes/{id}/unsubcribe
	 * @method post
	 * @tag mailingtypes
	 * @security default
	 * @consumes application/json
	 * @param integer $id
	 * @parameter int64 $id Mailingtype id to unsubscribe from
	 * @parameter int64 $relation_id Relation to unsubscribe
	 * @return RelationsMailingTypes successful operation
	 * @constraint minimum $id 1
	 * @errors 404 Mailingtype not found
	 */
	public function actionUnsubscribe($id, $relation_id) {
		$mailingtype = RelationsMailingTypes::findOne($id);
		if (empty($mailingtype)) {
			throw new NotFoundHttpException('Mailing-type not found');
		}

		$relation = Relation::findOne($relation_id);
		if (empty($relation)) {
			throw new NotFoundHttpException('Relation not found');
		}

		$registration = RelationsMailingRegistrations::find()
			->andWhere(['type_id' => $id])
			->andWhere(['relation_id' => $relation_id])
			->one();

		if (empty($registration)) {
			throw new NotFoundHttpException('Relation is not subscribed');
		}

		if ($registration->registration_state !== 'aangemeld') {
			throw new NotFoundHttpException('Relation is already unsubscribed');
		}

		$registration->registration_state = 'afgemeld';
		if (!$registration->save()) {
			throw new ServerErrorHttpException('Cannot unsubscribe');
		}

		return null;
	}
}
