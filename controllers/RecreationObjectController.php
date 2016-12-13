<?php
namespace app\controllers;
use eo\base\EO;
use eo\models\database\RecreationObject;
use eo\models\database\RecreationObjectFacility;
use eo\models\database\RecreationObjectType;
use eo\models\database\RecreationRentalPeriod;
use eo\models\database\RecreationRentalType;
use eo\models\database\Relation;
use eo\models\database\RelationConnectionType;
use eo\models\database\RelationType;
use yii\db\Expression;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * Recreation objects
 *
 * Retreive recreation objects
 *
 * @definition RecreationObject
 * @definition RecreationObjectType
 * @definition RecreationObjectFacility
 * @definition RecreationRentalPeriod
 * @definition RecreationRentalType
 */
class RecreationObjectController extends Rest {
	public function init() {
		$this->modelClass =	\eo\models\database\RecreationObject::className();
		$this->modelsNamespace 	=	'\eo\models\database';

		parent::init();
	}

	/**
     * Retreive all objects
     *
     * @path /recreation/objects
     * @method get
	 * @security default
     * @tag objects
     * @optparameter string[] $expand
	 * @enum $expand objectType facilities rentalperiods rentaltypes
	 * @return RecreationObject[] successful operation
	 * @errors 405 Invalid input
     */
	public function actionAll(){}


	/**
     * Retreive specific object
     *
     * @path /recreation/objects/{id}
     * @method get
	 * @security default
     * @tag objects
     * @param integer $id
     * @parameter int64 $id Object id to retreive
	 * @constraint minimum $id 1
     * @optparameter string[] $expand
	 * @enum $expand objectType facilities rentalperiods rentaltypes
	 * @return RecreationObject successful operation
     * @errors 404 Object not found
     */
	public function actionOne() {}


	/**
	 * Retreive objecttype from specific object
	 *
	 * @path /recreation/objects/{id}/objecttype
	 * @method get
	 * @security default
	 * @tag objects
	 * @tag objecttypes
	 * @param integer $id
	 * @parameter int64 $id Object id to retreive objecttype from
	 * @constraint minimum $id 1
	 * @optparameter string[] $expand
	 * @enum $expand objectType
	 * @return RecreationObjectType successful operation
	 * @errors 404 Object not found
	 */
	public function actionObjecttype() {}


	/**
	 * Retreive facilities from specific object
	 *
	 * @path /recreation/objects/{id}/facilities
	 * @method get
	 * @security default
	 * @tag objects
	 * @tag facilities
	 * @param integer $id
	 * @parameter int64 $id Object id to retreive facilities from
	 * @constraint minimum $id 1
	 * @return RecreationObjectFacility[] successful operation
	 * @errors 404 Object not found
	 */
	public function actionFacilities() {}


	/**
	 * Retreive rentalperiod from specific object
	 *
	 * @path /recreation/objects/{id}/rentalperiods
	 * @method get
	 * @security default
	 * @tag objects
	 * @tag rentalperiods
	 * @param integer $id
	 * @parameter int64 $id Object id to retreive rentalperiods from
	 * @constraint minimum $id 1
	 * @return RecreationRentalPeriod[] successful operation
	 * @errors 404 Object not found
	 */
	public function actionRentalperiods() {}


	/**
	 * Retreive rentaltypes from specific object
	 *
	 * @path /recreation/objects/{id}/rentaltypes
	 * @method get
	 * @security default
	 * @tag objects
	 * @tag rentaltypes
	 * @param integer $id
	 * @parameter int64 $id Object id to retreive rentaltypes from
	 * @constraint minimum $id 1
	 * @return RecreationRentalType[] successful operation
	 * @errors 404 Object not found
	 */
	public function actionRentaltypes() {}


	/**
	 * Create information request on this object for relation
	 *
	 * @path /recreation/objects/{id}/information-request
	 * @method post
	 * @security default
	 * @tag objects
	 * @param integer $id
	 * @constraint minimum $id 1
	 * @parameter int64 $id Object id to request info on
	 * @param integer $relation_id
	 * @parameter int64 $relation_id Relation to send information to
	 * @constraint minimum $relation_id 1
	 * @param string $per
	 * @parameter string $per Send information per mail or post
	 * @enum $per mail post
	 * @return boolean successful operation
	 * @errors 404 Relation not found
	 */
	public function actionInformationRequest($id, $relation_id, $per = 'mail') {
		$relation = Relation::findOne($relation_id);
		if (empty($relation)) {
			throw new NotFoundHttpException('Relatie niet gevonden');
		}

		$object = RecreationObject::findOne($id);
		if (empty($object)) {
			throw new NotFoundHttpException('Object niet gevonden');
		}

		if (!in_array($per, ['mail', 'post'], true)) {
			throw new BadRequestHttpException('Ongeldige per');
		}

		$relation->relation_factuur_verstuur	= $per;
		$relation->save();

		$type = RelationType::find()->andWhere(['type_name' => 'brochure'])->one();
		if (empty($type)) {
			throw new ServerErrorHttpException('Relation-type brochure does not exist');
		}

		$conn = RelationConnectionType::find()->andWhere(['type_id' => $type->type_id])->one();
		if (empty($conn)) {
			$conn = new RelationConnectionType();
			$conn->type_id				= $type->type_id;
			$conn->relation_id			= $relation->relation_id;
			$conn->contype_createdate	= new Expression('NOW()');
			if ($conn->save()) {
				throw new ServerErrorHttpException('Kan relatietype niet opslaan');
			}
		} else {
			$conn->contype_createdate	= new Expression('NOW()');
			if ($conn->save()) {
				throw new ServerErrorHttpException('Kan relatietype niet opslaan');
			}
		}

		$relation->relation_custom_1 .= $object->object_name;
		$relation->save();

		return true;
		// TODO mail
//		$mail      = new EOMailer('mail_text_brochure');
//		$mail->addForm($brochureForm);
//
//		if(YII_DEBUG){
//			$mail->AddAddress(Yii::app()->params['adminEmail']);
//		} else {
//			$mail->AddAddress(Yii::app()->params['companyEmail'], Yii::app()->name);
//			$mail->AddBCC(Yii::app()->params['adminEmail']);
//		}
//
//
//		if ($mail->send()){
//			Yii::app()->user->setFlash('success', 'Uw aanvraag is verzonden');
//			$this->refresh();
//		} else {
//			Yii::app()->user->setFlash('danger', 'Fout tijdens het verzenden van de aanvraag '.$mail->getError());
//		}
	}
}
