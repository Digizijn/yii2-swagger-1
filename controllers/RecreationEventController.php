<?php
namespace app\controllers;
use DateTime;
use eo\base\EO;
use eo\models\database\Invoice;
use eo\models\database\Products;
use eo\models\database\RecreationEvents;
use eo\models\database\RecreationEventsComposition;
use eo\models\database\RecreationEventsDiscount;
use eo\models\database\RecreationEventsFacility;
use eo\models\database\RecreationEventsProducts;
use eo\models\database\RecreationEventsState;
use eo\models\database\RecreationEventsTerms;
use eo\models\database\RecreationObject;
use eo\models\database\RecreationObjectFacility;
use eo\models\database\RecreationPackage;
use eo\models\database\RecreationRentalType;
use eo\models\database\RecreationSetting;
use eo\models\database\Relation;
use eo\models\database\RelationConnectionType;
use eo\models\database\WebsitePagesRecreationConnection;
use eo\models\database\WifiRadiusGroup;
use eo\models\database\WifiRadiusRules;
use eo\models\price\ProductPrice;
use eo\models\price\RecreationObjectPrice;
use SoapClient;
use SoapFault;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use yii\base\Exception;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use app\models\RecreationEventCreateFacilitiesRequest;
use app\models\RecreationEventCreateExtrasRequest;
use app\models\RecreationEventCreateCompositionRequest;

/**
 * Recreation events
 *
 * Retreive recreation events
 *
 * @definition RecreationEvents
 * @definition RecreationComposition
 * @definition RecreationEventsComposition
 * @definition RecreationObjectFacility
 * @definition RecreationEventsFacility
 * @definition RecreationEventsProducts
 * @definition RecreationObject
 * @definition RecreationPackage
 * @definition RecreationRentalType
 * @definition Products
 * @definition Relation
 * @definition Invoice
 */
class RecreationEventController extends Rest {
	public function init() {
		$this->modelClass =	\eo\models\database\RecreationEvents::className();
		$this->modelsNamespace 	=	'\eo\models\database';

		parent::init();
	}


	/**
     * Retreive all events
     *
     * @path /recreation/events
     * @method get
	 * @security default
     * @tag events
	 * @optparameter string[] $expand
	 * @enum $expand object invoices compositions products terms package rentalType facilities houseguests subreservations relation state event_mail_content
     * @errors 405 Invalid input
	 * @return RecreationEvents[] successful operation
     */
	public function actionAll($expand = []) {}


	/**
     * Retreive specific event
     *
     * @path /recreation/events/{id}
     * @method get
	 * @security default
     * @tag events
	 * @return RecreationEvents successful operation
     * @param integer $id
     * @parameter int64 $id Event id to retreive
     * @constraint minimum $id 1
	 * @optparameter string[] $expand
	 * @enum $expand object invoices compositions products terms package rentalType facilities houseguests subreservations relation state event_mail_content
     * @errors 404 Object not found
     */
	public function actionOne($expand = []) {}


	/**
	 * Retreive object from specific event
	 *
	 * @path /recreation/events/{id}/object
	 * @method get
	 * @security default
	 * @tag events
	 * @tag objects
	 * @param integer $id
	 * @parameter int64 $id Event id to retreive object from
	 * @constraint minimum $id 1
	 * @return RecreationObject successful operation
	 * @errors 404 Object not found
	 */
	public function actionObject() {}


	/**
	 * Retreive invoices from specific event
	 *
	 * @path /recreation/events/{id}/invoices
	 * @method get
	 * @security default
	 * @tag events
	 * @tag invoices
	 * @param integer $id
	 * @parameter int64 $id Event id to retreive invoices from
	 * @constraint minimum $id 1
	 * @return Invoice[] successful operation
	 * @errors 404 Object not found
	 */
	public function actionInvoices() {}


	/**
	 * Retreive products from specific event
	 *
	 * @path /recreation/events/{id}/products
	 * @method get
	 * @security default
	 * @tag events
	 * @tag products
	 * @param integer $id
	 * @parameter int64 $id Event id to retreive products from
	 * @constraint minimum $id 1
	 * @return Products[] successful operation
	 * @errors 404 Object not found
	 */
	public function actionProducts() {}


	/**
	 * Retreive package from specific event
	 *
	 * @path /recreation/events/{id}/package
	 * @method get
	 * @security default
	 * @tag events
	 * @tag packages
	 * @param integer $id
	 * @parameter int64 $id Event id to retreive package from
	 * @constraint minimum $id 1
	 * @return RecreationPackage successful operation
	 * @errors 404 Object not found
	 */
	public function actionPackage() {}


	/**
	 * Retreive rentaltype from specific event
	 *
	 * @path /recreation/events/{id}/rentaltype
	 * @method get
	 * @security default
	 * @tag events
	 * @tag rentaltypes
	 * @param integer $id
	 * @parameter int64 $id Event id to retreive rentaltype from
	 * @constraint minimum $id 1
	 * @return RecreationRentalType successful operation
	 * @errors 404 Object not found
	 */
	public function actionRentaltype() {}


	/**
	 * Retreive guests relations from specific event
	 *
	 * @path /recreation/events/{id}/guests
	 * @method get
	 * @security default
	 * @tag events
	 * @tag relations
	 * @param integer $id
	 * @parameter int64 $id Event id to retreive guests relations from
	 * @constraint minimum $id 1
	 * @optparameter string[] $expand
	 * @enum $expand object invoices compositions products terms package rentalType facilities houseguests subreservations relation
	 * @return Relation[] successful operation
	 * @errors 404 Object not found
	 */
	public function actionGuests() {}


	/**
	 * Retreive relation from specific event
	 *
	 * @path /recreation/events/{id}/relation
	 * @method get
	 * @security default
	 * @tag events
	 * @tag relations
	 * @param integer $id
	 * @parameter int64 $id Event id to retreive relation from
	 * @constraint minimum $id 1
	 * @optparameter string[] $expand
	 * @enum $expand object invoices compositions products terms package rentalType facilities houseguests subreservations relation
	 * @return Relation successful operation
	 * @errors 404 Object not found
	 */
	public function actionRelation() {}


	/**
	 * Calculate price for booking
	 *
	 * @path /recreation/events/calculate
	 * @method post
	 * @security default
	 * @tag events
	 * @param RecreationEventsComposition[] $compositions
	 * @parameter RecreationEventsComposition[] $composition Compositions to use
	 * @param string $type Object type
	 * @parameter date-time $type Object type
	 * @param string $arrival Arrival date
	 * @parameter date-time $arrival Arrival date
	 * @param string $departure Departure date
	 * @parameter date-time $departure Departure date
	 * @optparam integer $object_id
	 * @optparameter int64 $object_id Object to use
	 * @constraint $object_id minimum 1
	 * @optparam RecreationEventsFacility[] $facilities
	 * @optparameter RecreationEventsFacility[] $facilities Facilities to use
	 * @optparameter integer[] $rental_ids
	 * @optparam integer $block_id
	 * @optparameter int64 $block_id Option/block to use
	 * @optparameter string $source Source for request
	 * @enum $source website
	 * @return RecreationObjectPrice successful operation
	 * @errors 404 Object not found
	 */
	public function actionCalculate(array $compositions, $arrival, $departure, $object_id = null, $block_id = null, array $facilities = [], array $extras = [], array $rental_ids = [], $source = 'website') {
		$user			= EO::$app->user;
		$arrivalDate 	= new DateTime($arrival);
		$departureDate	= new DateTime($departure);
		$now			= new DateTime();

//		if (empty($type)) {
//			throw new BadRequestHttpException('No objecttype received');
//		}

		if (empty($arrivalDate) || $arrivalDate < $now) {
			throw new BadRequestHttpException('Invalid arrival date');
		}

		if (empty($departureDate) || $departureDate < $arrivalDate) {
			throw new BadRequestHttpException('Invalid depature date');
		}

		$relation_id = 0;
		if (!empty($user->relation)) {
			$relation_id = $user->relation->relation_id;
		}

		$object = RecreationObject::findOne($object_id);
		if (empty($object)) {
			throw new NotFoundHttpException('Object niet gevonden');
		}

		if (empty($rental_ids)) {
			$rental_ids = array_values(ArrayHelper::map($object->rentalConnection, 'rental_id', 'rental_id'));
		}


		if (!empty($rental_ids)) {
			// Only rentaltypes that are linked to websitepages
			if ($source === 'website') {
				$websiteRentals = WebsitePagesRecreationConnection::find()->select('rental_id')
					->andWhere(['type_id' => $object->type_id])
					->andWhere(['rental_id' => $rental_ids])
					->asArray()
					->all();

				$rental_ids = array_values(ArrayHelper::map($websiteRentals, 'rental_id', 'rental_id'));
			}
		}

		if (empty($rental_ids)) {
			throw new BadRequestHttpException('No valid rental type(s) (wrong source?)');
		}

		$event = new RecreationEvents();


		$relation = Relation::findOne($relation_id);

		$event->company_id						= EO::param('company_id');
		$event->object_id						= $object_id;
		$event->setObjectType($object->type);
		if (!empty($rental_ids)) {
			$event->rental_id						= $rental_ids[0];
		}
		$event->setAllowedRentalIDs($rental_ids);
		$event->setArrivalDate($arrivalDate);
		$event->setDepartureDate($departureDate);
		$event->setEventRelation($relation);
		$event->event_amount_persons			= 2;

		$event->setPeriodPrices(false);

		$prices = $event->getPeriodPrices();
		if (empty($prices)) {
			return [];
		}

		// samenstelling
		$eventCompositions = [];
		if(count($compositions) > 0){
			foreach($compositions AS $composition) {
				$eventComposition					= new RecreationEventsComposition;
				$eventComposition->composition_id	= $composition['composition_id'];
				$eventComposition->conn_amount		= $composition['amount'];

				$eventCompositions[]	= $eventComposition;
			}
		}
		$event->setComposition($eventCompositions);

		// Gaat niet altijd goed ja/nee ea
		$eventFacilities = [];
		foreach ($facilities as $facility) {
			$objectFacility	= RecreationObjectFacility::findOne($facility['facility_id']);
			if (!empty($objectFacility)) {
				$eventFacility					= new RecreationEventsFacility();
				$eventFacility->facility_id		= $facility['facility_id'];
				$eventFacility->conn_amount		= $facility['amount'];
				$eventFacility->conn_excl		= $objectFacility->getExclusive(true);
				$eventFacility->conn_vat		= $objectFacility->getVatAmount(true);

				$eventFacilities[]  = $eventFacility;
			}
		}
		$event->setFacilities($eventFacilities);

		// TODO werkt nog niet
		$productOrder	= 0;
		$eventProducts = [];
		$extras = ArrayHelper::merge($extras, RecreationBookingController::getExtras($object->type, $event->rentalType, $event->getAmountNights(), $event->event_amount_persons, null)); // TODO FIXME package
		foreach ($extras AS $extra) {
			/** @var Products $objectExtra */
			$objectExtra = Products::findOne($extra['art_id']);
			if (empty($objectExtra)) {
				throw new ServerErrorHttpException('Product niet gekoppeld voor #'.$extra['art_id']);
			}
			/** @var ProductPrice $objectExtraPrice */
			$objectExtraPrice =	$objectExtra->getPrice();

			$eventProduct							= new RecreationEventsProducts;
			$eventProduct->product_artid			= $objectExtra->product_id;
			$eventProduct->product_amount			= $extra['amount'] 		?? 1;
			$eventProduct->product_description		= $extra['description'] ?? $objectExtra->art_name;
			$eventProduct->product_price			= $extra['price'] 		?? $objectExtra->getPrice(1);
			$eventProduct->product_excl				= $extra['excl'] 		?? $objectExtraPrice->getExclusive(true);
			$eventProduct->product_vat				= $extra['vat'] 		?? $objectExtraPrice->getVatAmount();
			$eventProduct->product_per				= $extra['per'] 		?? 'p.s.';
			$eventProduct->product_per				= $extra['from'] 		?? null;
			$eventProduct->product_per				= $extra['till'] 		?? null;
			$eventProduct->product_per				= $extra['from_type'] 	?? null;
			$eventProduct->product_bail				= $extra['bail'] 		? 'ja' : 'nee';
			$eventProduct->product_discount			= 0;
			$eventProduct->product_order			= $productOrder++;

			$eventProducts[] = $eventProduct;
		}
		$event->setObjectExtras($eventProducts);

		//alle kortingen opslaan
//		if(!empty($objectDiscountIDs) && is_array($objectDiscountIDs)){
//			foreach($objectDiscountIDs as $objectDiscountID){
//				$eventsDiscount 					= new RecreationEventsDiscount;
//				$eventsDiscount->event_id			= $eventId;
//				$eventsDiscount->discount_id		= $objectDiscountID;
//				$eventsDiscount->conn_createdate    = new Expression('NOW()');
//				$eventsDiscount->conn_createuser    = $userId;
//				$eventsDiscount->conn_changedate    = new Expression('NOW()');
//				$eventsDiscount->conn_changeuser    = $userId;
//				if (!$eventsDiscount->save()) {
//					throw new ServerErrorHttpException('Fout bij opslaan korting: '.print_r($eventsDiscount->getErrors(), true));
//				}
//			}
//		}


		// Samenstelling
		return [
			'price' => $event->getPrice(),
			'terms' => $event->getPaymentTerms()
		];
	}


	/**
     * Save event
     *
     * @path /recreation/events
     * @method post
	 * @security default
     * @tag events
	 * @param RecreationEventsComposition[] $compositions
	 * @parameter RecreationEventsComposition $compositions
	 * @optparam integer $block_id
	 * @optparameter integer $block_id
	 * @param integer $object_id
	 * @param string $arrival Arrival date
	 * @param string $departure Departure date
	 * @param integer $relation_id
	 * @param array $facilities facility_id + conn_amount
	 * @param array $extras product_id + conn_amount
	 * @optparam int[] $rental_ids
	 * @optparam boolean $preferable
	 * @optparam boolean $generate_invoices
	 * @parameter array $event
	 * @return RecreationEvents successful operation
     * @errors 404 Object not found
     */
	public function actionSave(array $compositions, $block_id = null, $object_id, $preferable = false, $arrival, $departure, $package = null, $relation_id, array $facilities = array(), array $extras = array(), array $rental_ids = [], array $guest_ids = array(), $generate_invoices = true) {
		$user			= EO::$app->user;
		$arrivalDate 	= new DateTime($arrival);
		$departureDate	= new DateTime($departure);
		$now			= new DateTime();
		$userId			= $user->identity->user_id;
		$totalPrice		= 0;

		if (empty($object_id)) {
			throw new BadRequestHttpException('No object received');
		}

		$object = RecreationObject::findOne($object_id);
		if (empty($object)) {
			throw new NotFoundHttpException('Object niet gevonden');
		}

		if (empty($arrivalDate) || $arrivalDate < $now) {
			throw new BadRequestHttpException('Invalid arrival date');
		}

		if (empty($departureDate) || $departureDate < $arrivalDate) {
			throw new BadRequestHttpException('Invalid depature date');
		}

		$relation = Relation::findOne($relation_id);
		if (empty($relation)) {
			throw new NotFoundHttpException('Relatie niet gevonden');
		}

		if (empty($compositions)) {
			throw new BadRequestHttpException('Samenstelling mag niet leeg zijn');
		}

		$bookingState  = RecreationEventsState::find()->where(['state_type' => RecreationEventsState::STATE_DEFAULT])->one();
		if (empty($bookingState)) {
			throw new BadRequestHttpException('Reservering-status niet beschikbaar');
		}

		if (empty($rental_ids)) {
			$rental_ids = array_values(ArrayHelper::map($object->rentalConnection, 'rental_id', 'rental_id'));
		}


		$eventPackage = null;
		if (!empty($package)) {
			$eventPackage = RecreationPackage::findOne($package);
		}

		// Reserverering (met optie)
		if (!empty($block_id)) {
			$optionState  = RecreationEventsState::find()->where(['state_type' => RecreationEventsState::STATE_OPTION])->one();
			if (empty($optionState)) {
				throw new BadRequestHttpException('Opties/blokkering niet beschikbaar');
			}

			$event = RecreationEvents::find()->andWhere(['event_id' => $block_id])->one();
			if (empty($event)) {
				throw new NotFoundHttpException('Blokkering/optie niet gevonden');
			}

			if (!empty($event) && $event->state_id !== $optionState->state_id) {
				throw new BadRequestHttpException('Blokkering/optie is al verlopen of gebruikt');
			}
		} else {
			// Reservering aanmaken
			$event = new RecreationEvents();

			if ($object->isAvailable($arrivalDate, $event->getNightsAway()) === false){
				throw new BadRequestHttpException('De geselecteerde periode is niet meer beschikbaar.');
			}

			$event->event_createdate				= new Expression('NOW()');
			$event->event_createuser				= $userId;
		}

		$event->event_changeuser		= new Expression('NOW()');
		$event->event_changeuser		= $user->identity->user_id;

		$event->setObject($object);
		$event->setObjectType($object->objectType);
		$event->setArrivalDate($arrivalDate);
		$event->setDepartureDate($departureDate);
		$event->setAllowedRentalIDs($rental_ids);
//		$event->setComposition($composition); // TODO?
		$event->setPackage($eventPackage);
		$event->setPeriodPrices();

		if((count($event->getPeriodPrices()) === 0)){ //  && empty($eventPackage) // TODO
			throw new BadRequestHttpException(EO::t('recreation', 'De geselecteerde periode is niet meer beschikbaar.'));
		}

		//kortingsgroep zetten als er een kortingscode is ingevoerd
//		$event->setDiscountGroup($model->getDiscountGroup()); // TODO

		// Validatie faciliteiten en extra's
		foreach ($extras AS $extra) {
			/** @var Products $objectExtra */
			$objectExtra = Products::findOne($extra['product_id']);
			if (empty($objectExtra)) {
				throw new BadRequestHttpException('Ongeldige facturatieregel');
			}
		}

		// Validatie composition
		foreach($compositions AS $composition) {
			if (empty($composition['composition_id'])) {
				throw new BadRequestHttpException('Ongeldige samenstelling');
			}

			if (empty($composition['amount']) || $composition['amount'] <= 0) {
				throw new BadRequestHttpException('Ongeldig aantal (amount) samenstelling');
			}
		}


		$objectType			= $object->objectType;
		$rentalType			= $event->rentalType;

		$eventId 			= $event->event_id; 		// TODO Dirty hack om prijs te herberekenen na blokkeren
		$event->event_id	= 0;  						// TODO Dirty hack om prijs te herberekenen na blokkeren
		$price				= $event->getObjectPrice();

//		$eventProductID		= $price->getProduct()->product_id;
		$objectPrice		= $price->getInclusive(false);
		$objectPriceExcl	= $price->getExclusive(false);
		$objectPriceVat		= $objectPrice - $objectPriceExcl;
		$objectDiscount		= $price->getDiscountAmount(true);
		$objectDiscountExcl	= $price->getDiscountAmount(false);
		$objectDiscountVat	= $objectDiscount - $objectDiscountExcl;

		$event->event_id 	= $eventId; 				// TODO Dirty hack om prijs te herberekenen na blokkeren

		$checkin	= $objectType->type_checkin;
		$checkout	= $objectType->type_checkout;
		if(!empty($eventPackage) && !empty($eventPackage->package_checkin) && !empty($eventPackage->package_checkout)){
			$checkin	= $eventPackage->package_checkin;
			$checkout	= $eventPackage->package_checkout;
		}

		$setting_invoice_meter_readings_days 	= 0;
		$recreationSettings                     = RecreationSetting::find()->cache()->one();
		if(!empty($recreationSettings)){
			$setting_invoice_meter_readings_days    = $recreationSettings['setting_invoice_meter_readings_days'];
		}

		if($event->getNightsAway() >= $setting_invoice_meter_readings_days){
			$invoice_meter_readings		= 'ja';
		} else {
			$invoice_meter_readings		= 'nee';
		}

		$event->company_id						= EO::param('company_id');
		$event->object_id						= $object_id;
		$event->rental_id						= $rentalType->rental_id;
		$event->relation_id						= $relation_id;
		$event->state_id						= $bookingState->state_id;
		$event->event_reservation_nr			= RecreationEvents::nextReservationNr($arrivalDate);
		$event->event_arrivaldate				= $arrivalDate->format('Y-m-d').' '.$checkin;
		$event->event_departuredate				= $departureDate->format('Y-m-d').' '.$checkout;
		$event->event_preferencebooking			= $preferable ? 'ja' : 'nee';
		$event->event_amount_persons			= 1;
		$event->event_objectprice		        = $objectPrice;
		$event->event_objectdiscount	        = $objectDiscount;
		$event->event_objectprice_exclusive		= $objectPriceExcl;
		$event->event_objectdiscount_exclusive	= $objectDiscountExcl;
		$event->event_objectprice_vat	        = $objectPriceVat;
		$event->event_objectdiscount_vat        = $objectDiscountVat;
		$event->event_source			        = 'website';
		$event->event_objectprice_manual		= 'ja';
		$event->event_invoice_meter_readings    = $invoice_meter_readings;

		$transaction = EO::$app->db->beginTransaction();
		try {
			if (!$event->save()) {
				throw new ServerErrorHttpException('Kan reservering niet opslaan '.print_r($event->getErrors(), true));
			}

			$eventId = $event->event_id;

			// samenstelling
			if(count($compositions) > 0){
				foreach($compositions AS $composition) {
					$objectComposition	= RecreationObjectFacility::findOne($composition['composition_id']);

					if (!empty($objectComposition)) {
						$eventComposition					= new RecreationEventsComposition;
						$eventComposition->event_id			= $eventId;
						$eventComposition->composition_id	= $composition['composition_id'];
						$eventComposition->conn_amount		= $composition['amount'];
						if (!$eventComposition->save()) {
							throw new ServerErrorHttpException('Kon samenstelling niet opslaan '.print_r($eventComposition->getErrors(), true));
						}
					} else {
						throw new BadRequestHttpException('Ongeldige samenstelling #'.$composition['composition_id']);
					}
				}
			}
			// setComposition // TODO

			// Gaat niet altijd goed ja/nee ea
			foreach ($facilities as $facility) {
				$objectFacility	= RecreationObjectFacility::findOne($facility['facility_id']);
				if (!empty($objectFacility)) {
					$eventFacility					= new RecreationEventsFacility();
					$eventFacility->event_id		= $eventId;
					$eventFacility->facility_id		= $facility['facility_id'];
					$eventFacility->conn_amount		= $facility['amount'];
					$eventFacility->conn_excl		= $objectFacility->getExclusive(true);
					$eventFacility->conn_vat		= $objectFacility->getVatAmount(true);

					if (!$eventFacility->save()) {
						throw new ServerErrorHttpException('Kon faciliteit niet opslaan '.print_r($eventFacility->getErrors(), true));
					}
				}
			}
			// setFacilities // TODO

			// TODO werkt nog niet
			$productOrder	= 0;

			$extras = ArrayHelper::merge($extras, RecreationBookingController::getExtras($object->objectType, $rentalType, $event->getAmountNights(), $event->event_amount_persons, $package));
			foreach ($extras AS $extra) {
				/** @var Products $objectExtra */
				$objectExtra = Products::findOne($extra['art_id']);

				if (!empty($objectExtra)) {
					/** @var ProductPrice $objectExtraPrice */
					$objectExtraPrice =	$objectExtra->getPrice();

					$eventProduct							= new RecreationEventsProducts;
					$eventProduct->event_id					= $eventId;
					$eventProduct->product_artid			= (int)$objectExtra->product_id;
					$eventProduct->product_amount			= $extra['amount'] 		?? 1;
					$eventProduct->product_description		= $extra['description'] ?? $objectExtra->art_name;
					$eventProduct->product_price			= $extra['price'] 		?? $objectExtra->getPrice(1);
					$eventProduct->product_excl				= $extra['excl'] 		?? $objectExtraPrice->getExclusive(true);
					$eventProduct->product_vat				= $extra['vat'] 		?? $objectExtraPrice->getVatAmount();
					$eventProduct->product_per				= $extra['per'] 		?? 'p.s.';
					$eventProduct->product_per				= $extra['from'] 		?? null;
					$eventProduct->product_per				= $extra['till'] 		?? null;
					$eventProduct->product_per				= $extra['from_type'] 	?? null;
					$eventProduct->product_bail				= $extra['bail'] 		? 'ja' : 'nee';
					$eventProduct->product_per				= $extra['per'] 		?? 'p.s.';
					$eventProduct->product_discount			= 0;
					$eventProduct->product_order			= $productOrder++;
					$eventProduct->product_createdate		= new Expression('NOW()');
					$eventProduct->product_createuser		= $userId;
					$eventProduct->product_changedate		= new Expression('NOW()');
					$eventProduct->product_changeuser		= $userId;
					if (!$eventProduct->save()) {
						throw new ServerErrorHttpException('Kon facturatieregel niet opslaan');
					}
				}
			}

			//controleren of de relatie dit relatietype al heeft, anders toevoegen
			if(!empty($this->objectType->relation_type_id)){
				$relationConnectionType = RelationConnectionType::find()
					->andWhere(['relation_id' => $relation->relation_id])
					->andWhere(['type_id'     => $this->objectType->relation_type_id])
					->one();
				if(empty($relationConnectionType)){
					$relationConnectionType = new RelationConnectionType;
					$relationConnectionType->relation_id    = $relation->relation_id;
					$relationConnectionType->type_id        = $this->objectType->relation_type_id;
					if (!$relationConnectionType->save()) {
						throw new ServerErrorHttpException('Fout bij opslaan relatietype: '.print_r($relationConnectionType->getErrors(), true));
					}
				}
			}

			//alle kortingen opslaan
			if(!empty($objectDiscountIDs) && is_array($objectDiscountIDs)){
				foreach($objectDiscountIDs as $objectDiscountID){
					$eventsDiscount 					= new RecreationEventsDiscount;
					$eventsDiscount->event_id			= $eventId;
					$eventsDiscount->discount_id		= $objectDiscountID;
					$eventsDiscount->conn_createdate    = new Expression('NOW()');
					$eventsDiscount->conn_createuser    = $userId;
					$eventsDiscount->conn_changedate    = new Expression('NOW()');
					$eventsDiscount->conn_changeuser    = $userId;
					if (!$eventsDiscount->save()) {
						throw new ServerErrorHttpException('Fout bij opslaan korting: '.print_r($eventsDiscount->getErrors(), true));
					}
				}
			}


			//controleren op wifi gebruiker en eventueel toevoegen
			if(!empty($this->objectType->wifi_group_id)){
				$wifiRadiusGroup    = WifiRadiusGroup::findOne($this->objectType->wifi_group_id);
				if(!empty($wifiRadiusGroup)){
					$wifiRadiusRule = new WifiRadiusRules;
					$wifiRadiusRule->client_id          = $wifiRadiusGroup->client_id;
					$wifiRadiusRule->group_id           = $wifiRadiusGroup->group_id;
					$wifiRadiusRule->user_id            = $relation->user_id;
					$wifiRadiusRule->rule_fromdate      = $arrivalDate->format('Y-m-d').' '.$checkin;
					$wifiRadiusRule->rule_tilldate      = $departureDate->format('Y-m-d').' '.$checkout;
					$wifiRadiusRule->rule_source        = 'recreation_events';
					$wifiRadiusRule->rule_source_id     = $eventId;
					$wifiRadiusRule->rule_createdate    = new Expression('NOW()');
					$wifiRadiusRule->rule_createuser    = $userId;
					$wifiRadiusRule->rule_changedate    = new Expression('NOW()');
					$wifiRadiusRule->rule_changeuser    = $userId;
					if (!$wifiRadiusRule->save()) {
						throw new ServerErrorHttpException('Fout bij opslaan WIFI: '.print_r($wifiRadiusRule->getErrors(), true));
					}
				}
			}

			// betalingstermijnen
			$paymentTerms	            = $event->getPaymentTerms();
			$totalTerm                  = array_sum(array_map(function($term){
				return $term['payAmount'];
			}, $paymentTerms));

			$orginalTotal	= $totalPrice + $totalTerm;
			if(count($paymentTerms) > 0){
				foreach($paymentTerms AS $paymentTerm){
					$term					= new RecreationEventsTerms;
					$term->event_id			= $eventId;
					if(!empty($paymentTerm['term'])){
						$term->term_id			= $paymentTerm['term']->term_id;
						$term->conn_description	= $paymentTerm['term']->term_description;
					}
					$term->conn_max_date		= $paymentTerm['maxDate']->format('Y-m-d');
					if ($orginalTotal > 0) {
						$term->conn_percentage	= ($paymentTerm['payAmount'] / $orginalTotal) * 100;
					} else {
						$term->conn_percentage 	= 100;
					}

					if (!$term->save()) {
						throw new ServerErrorHttpException('Kan betalingstermijn niet aanmaken '.print_r($term->getErrors(), true));
					}
				}
			}

			$transaction->commit();


			$createInvoiceFinal	= EO::param('recreation_event_create_invoice_final');
			if(!empty($generate_invoices) && (bool)$generate_invoices === true) {
				$eoWsUrl			= EO::param('eo_ws_url');
				$apiUsername		= $user->identity->user_name;
				$apiPassword		= $user->identity->user_pass;
				$makeFinale			= (!empty($createInvoiceFinal) && $createInvoiceFinal === true);

				if(!empty($eoWsUrl) && !empty($apiUsername) && !empty($apiPassword)){
					ini_set('soap.wsdl_cache_enabled', 0);
					$client 		= new SoapClient($eoWsUrl.'?wsdl');
					$client->__setLocation($eoWsUrl);
					try {
						$session	= $client->{'eoWSLogin.login'}($apiUsername, $apiPassword, EO::param('company_id'));

						try {
							$createInvoice	= $client->{'eoWSRecreationEventInvoiceCreate.createInvoice'}(
								$session->sessionid,
								$eventId,
								$makeFinale
							);

							if ($createInvoice === false) {
								throw new ServerErrorHttpException('Kon facturen niet aanmaken');
							}
						} catch(SoapFault $e) {
							throw new ServerErrorHttpException('Fout bij aanmaken facturen: '.$eventId.' '.$e->getMessage());
						}
					} catch(SoapFault $e) {
						throw new ServerErrorHttpException('Fout bij inloggen voor aanmaken facturen: '.$e->getMessage());
					}
				}
			}
		} catch (\Exception $e) {
			$transaction->rollBack();
			throw $e;
		}

		$response 	= EO::$app->getResponse();
		$response->setStatusCode(201);
		$response->getHeaders()->set('Location', Url::toRoute(['recreation-events/view', 'id' => $event->event_id], true));

		return $event;
	}


	/**
	 * Add guest to event
	 *
	 * @path /recreation/events/{id}/guests
	 * @method post
	 * @tag events
	 * @tag relations
	 * @security default
	 * @param integer $id
	 * @parameter int64 $id Event id
	 * @param integer $relation_id
	 * @parameter int64 $relation_id Relation id guest to add
	 * @constraint minimum $id 1
	 * @return boolean successful operation
	 * @errors 404 Not found
	 */
	public function actionGuestsAdd($id, $relation_id) {}
}
