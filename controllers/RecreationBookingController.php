<?php
namespace app\controllers;

use app\controllers\Rest;
use DateInterval;
use DateTime;
use eo\base\EO;
use eo\models\database\Products;
use eo\models\database\RecreationEvents;
use eo\models\database\RecreationEventsComposition;
use eo\models\database\RecreationObject;
use eo\models\database\RecreationPackage;
use eo\models\database\RecreationPricingResponse;
use eo\models\database\RecreationAvailibilityResponse;
use eo\models\database\RecreationObjectType;
use eo\models\database\RecreationRentalPeriod;
use eo\models\database\RecreationRentalType;
use eo\models\RecreationBooking;
use eo\models\RecreationObjectPrice;
use machour\yii2\swagger\api\ApiController;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

/**
 * Recreation bookings
 *
 * Retreive booking information
 *
 * @definition RecreationObject
 * @definition RecreationObjectType
 * @definition RecreationPeriod
 * @definition RecreationPeriodPrice
 * @definition RecreationObjectTypeImage
 * @definition RecreationObjectFacility
 * @definition RecreationRentalType
 * @definition RecreationObjectTypeDescriptionTranslation
 * @definition RecreationAvailibilityResponse
 * @definition RecreationPricingResponse
 * @definition RecreationPackage
 * @definition RecreationEventsComposition
 * @definition Products
 */
class RecreationBookingController extends Rest {
	public function init() {
		$this->modelClass 		= '\eo\models\database\RecreationBooking'; // Dummy
		$this->modelsNamespace 	= '\eo\models\database';

		parent::init();
	}

	public function actions() {
		return [
			'nights'
		];
	}


	public function getSecurityDefinitions() {
		return ['default'];
	}

	/**
     * Retreive number of nights available based on objecttype
	 *
     * @path /recreation/booking/nights
     * @method get
     * @tag booking
	 * @security default
	 * @return integer[] successful operation
     * @param integer $type_id
     * @parameter int64 $type_id Objecttype id
     * @constraint minimum $type_id 1
     * @constraint minimum $facility_ids 1
     * @errors 404 Not found
     */
	public function actionNights($type_id) {
		$type = RecreationObjectType::find()->where(['type_id' => $type_id])->one();
		if (empty($type)) {
			throw new \yii\web\NotFoundHttpException('Objecttype niet gevonden');
		}

		$booking = new RecreationBooking();
		$booking->setObjectType($type);

		$types	= $type->rentalTypes;
		if (!empty($types)) {
			$booking->setAllowedRentalTypes($types);
		}

		return $booking->getNightsAway();
	}


	/**
     * Retreive object availability per date
	 *
     * @path /recreation/booking/availibility
     * @method get
     * @tag booking
	 * @security default
	 * @return RecreationAvailibilityResponse[] successful operation
     * @param integer $type_id
     * @parameter int64 $type_id Objecttype id
     * @constraint minimum $type_id 1
     * @param string $arrival Arrival date
     * @parameter date-time $arrival Arrival date
     * @param string $arrival Departure date
     * @parameter date-time $departure Departure date
     * @param integer[] $rental_types Rental types
     * @optparameter int64[] $rental_types Rental types
     * @param integer[] $object_id Object id
     * @optparameter int64[] $object_id Object id
     * @errors 404 Not found
     */
	public function actionAvailability($type_id, $arrival, $departure, $rental_types = [], $object_id = null) {
		// FIXME geretourneerd alleen de maand van aankomstdatum, moet alles van begin tot eind
		$type			= RecreationObjectType::findOne($type_id);
		$arrivalDate 	= new DateTime($arrival);
		$departureDate	= new DateTime($departure);
		$now			= new DateTime();

		if (empty($type)) {
			throw new BadRequestHttpException('No objecttype received');
		}

		if (empty($arrivalDate) || $arrivalDate < $now) {
			throw new BadRequestHttpException('Invalid arrival date');
		}

		if (empty($departureDate) || $departureDate < $arrivalDate) {
			throw new BadRequestHttpException('Invalid depature date');
		}

		$booking = new RecreationBooking();
		$booking->setObjectType($type);
		$booking->bookdateFrom	= $arrivalDate;
		$booking->nightsAway	= 0;

		if (!empty($rental_types)) {
			$booking->setAllowedRentalTypes($rental_types); // TODO zijn nog id's
		}

		$objects = [];
		if (!empty($object_id)) {
			$object 	= RecreationObject::findOne((int)$object_id);
			if (!empty($object)) {
				$objects	= [$object];
			}
		}

		// Indien
		if (empty($objects) && !empty($type_id)) {
			$dates = $booking->getAvailableDates();
			if (!empty($dates)) {
				$objects = $booking->getAvailableObjects();
			}
		}

		if (!empty($objects)) {
			$booking->preferenceObjectID = ArrayHelper::map($objects, 'object_id', 'object_id');
		}

		// FIXME BUG krijgt alleen beschikbaarheid NA gevonden reservering
		$availableDates = $booking->getAvailableDates();

		$actualDates = array_filter(
			$availableDates,
			function($v) use ($departureDate, $arrivalDate) {
				return ($v['date'] >= $arrivalDate && $v['date'] <= $departureDate);
			}
		);

		$response = [];
		// TODO Dit kan vast handiger......
		foreach ($actualDates as $actualDate) {
			$object = $actualDate['object'];
			$date 	= $actualDate['date'];

			if (!isset($response[$object->object_id])) {
				$val = [];
				$val['object_id']	= (int)$object_id;
				$val['availability']	= [];

				$response[$object->object_id] = $val;
			}

			$response[$object->object_id]['availability'][] =  $date->format('Y-m-d');
		}

		return array_values($response);
	}


	/**
     * Retreive object availability per date
	 *
     * @path /recreation/booking/pricing
     * @method get
     * @tag booking
	 * @security default
	 * @return RecreationPricingResponse[] successful operation
     * @param integer[] $type_id
     * @parameter int64[] $type_id Objecttype id
     * @constraint minimum $type_id 1
     * @param string $arrival Arrival date
     * @optparameter date-time $arrival Arrival date
     * @param integer $arrival Departure date
     * @optparameter date-time $departure Departure date
     * @param integer $min_nights Min nights
     * @optparameter int64 $min_nights Min nights
     * @param integer $max_nights Max nights
     * @optparameter int64 $max_nights Max nights
     * @param integer $persons Persons
     * @optparameter int64 $persons Persons
     * @errors 404 Not found
     */
	public function actionPricing($type_id = [], $arrival, $departure, $min_nights = 1, $max_nights = 21, $persons = 2) {
		$type			= RecreationObjectType::findOne($type_id);
		$arrivalDate 	= new DateTime($arrival);
		$departureDate	= new DateTime($departure);
		$now			= new DateTime();

		if (empty($type)) {
			throw new BadRequestHttpException('No objecttype received');
		}

		if (empty($arrivalDate) || $arrivalDate < $now) {
			throw new BadRequestHttpException('Invalid arrival date');
		}

		if (empty($departureDate) || $departureDate < $arrivalDate) {
			throw new BadRequestHttpException('Invalid depature date');
		}

		$bookablePeriods	= [];
		$rentalTypes		= RecreationRentalType::find()->cache()->innerJoinWith(['rentalTypeConnection.objects.objectType' => function($q) use($type) {
			$modelClass = $q->modelClass;
			$q->andWhere([$modelClass::tableName().'.type_id' => $type->type_id]);
		}])->all();

		$rentalIDs			= [];
		if (count($rentalTypes) > 0) {
			$rentalIDs		= array_keys(ArrayHelper::map($rentalTypes, 'rental_id', 'rental_name'));
		}

		if (count($rentalIDs) > 0) {
//			die(RecreationRentalPeriod::find()->select('*')->cache()->innerJoinWith('rental', true)->andWhere([RecreationRentalPeriod::tableName().'.rental_id' => $rentalIDs])->createCommand()->rawSql);
			$rentalPeriods	= RecreationRentalPeriod::find()->cache()->innerJoinWith('rental', true)->andWhere([RecreationRentalPeriod::tableName().'.rental_id' => $rentalIDs]);

			$maxNighs	= (int)$max_nights;
			if($maxNighs <= 0){
				$maxNighs	= 21;
			}

			foreach ($rentalPeriods->each(5) as $rentalPeriod) {
				$rentals = $rentalPeriod->rental->rental_id;
				for ($nights=$min_nights; $nights<=$maxNighs; $nights++) {
					if ($nights >= (int)$rentalPeriod->rental->rental_min_nights
					 && $nights <= (int)$rentalPeriod->rental->rental_max_nights) {
						foreach (new \DatePeriod($arrivalDate, new DateInterval('P1D'), $departureDate) as $startDate) { // TODO Optmisaliseren
							$periodPrices	= $rentalPeriod->getPeriodPrices($arrivalDate, $nights, $rentalPeriod->rental, $type); // TODO $rentals?
							if (!empty($periodPrices)) {
								$price	= 0;
								foreach ($periodPrices as $periodPrice) {
									$price	+= $periodPrice->price_amount;
								}

								$enddate	= clone $startDate;
								$enddate->add(new DateInterval('P'.$nights.'D'));

//								if($nights % 7 == 0){
//									$weeks				= $nights / 7;
//									$formattedNights	= $weeks.' '.($weeks == 1 ? EO::t('recreation', 'week') : EO::t('recreation', 'weken'));
//								} else {
//									$formattedNights	= $nights.' '.($nights == 1 ? EO::t('recreation', 'nacht') : EO::t('recreation', 'nachten'));
//								}
//
//								if(array_key_exists($nights, $bookablePrices)){
//									// controleren op goedkoopste prijs
//									$otherPrice 	= $bookablePrices[$nights]['raw']['price'];
//									if($price < $otherPrice){
//										$prices[]= $price;
//									}
//								} else {
									// eventuele korting ophalen
									$event	= new RecreationEvents();
									$event->event_amount_persons = $persons;
									$event->setObjectType($type);
									$event->setArrivalDate($startDate);
									$event->setDepartureDate($enddate);
									$event->setAllowedRentalIDs([$rentalPeriod->cache()->rental->rental_id]); // $rentalPeriod->rental
									$event->setPeriodPrices(false);

									$objectPrice	= $event->getObjectPrice();

									$price			= $objectPrice->getInclusive(true);
									$fromprice		= $objectPrice->getInclusive(false);

									if ($price > 0) {
										$bookablePrices[]	= [
											'nights'		=> $nights,
											'arrivaldate'	=> $startDate->format('Y-m-d'),
											'departuredate'	=> $enddate->format('Y-m-d'),
											'discounted'	=> (float)($price - $fromprice),
											'price'			=> (float)$price,
										];
									}
//								}
							}
						}
					}
				}
			}
		}

		//arrangementen erbij zoeken
		$packages			= RecreationPackage::find()
			->innerJoinWith('websitePageConnections')
			->byObjectType($type->type_id)
			->byDate($arrivalDate)
			->all();

		foreach($packages as $package){
			$isValidArrivalDate		= false;
			$packageArrivalDates	= $package->getArrivalDates();
			foreach($packageArrivalDates as $packageArrivalDate){
				if($packageArrivalDate->format('Y-m-d') === $arrivalDate->format('Y-m-d')){
					$isValidArrivalDate	= true;
					break;
				}
			}

			if($isValidArrivalDate === true) {
				$nights	= $package->package_nights;
//				if($nights % 7 == 0){
//					$weeks				= $nights / 7;
//					$formattedNights	= $weeks.' '.($weeks == 1 ? EO::t('recreation', 'week') : EO::t('recreation', 'weken'));
//				} else {
//					$formattedNights	= $nights.' '.($nights == 1 ? EO::t('recreation', 'nacht') : EO::t('recreation', 'nachten'));
//				}

				$enddate	= $package->getTillDate($arrivalDate);

				//eventuele korting ophalen
				$event	= new RecreationEvents;
				$event->setObjectType($type);
				$event->setArrivalDate($arrivalDate);
				$event->setDepartureDate($enddate);
				$event->setPackage($package);
				$event->setPeriodPrices(false);

				$objectPrice	= $event->getObjectPrice();

				$price			= $objectPrice->getInclusive(true);
				$fromprice		= $objectPrice->getInclusive(false);


				$bookablePackages[]	= [
					'packageID'		=> (int)$package->package_id,
					'nights'		=> $nights,
					'arrivaldate'	=> $arrivalDate->format('Y-m-d'),
					'departuredate'	=> $enddate->format('Y-m-d'),
					'discounted'	=> (float)($price - $fromprice),
					'price'			=> (float)$price,
				];
			}
		}

		return $bookablePrices ?? [];
	}



	/**
     * Retreive first available object
	 *
     * @path /recreation/booking/first-available
     * @method get
     * @tag booking
	 * @security default
	 * @return integer successful operation
     * @param integer $type_id
     * @parameter int64 $type_id Objecttype id
     * @constraint minimum $type_id 1
     * @param string $arrival Arrival date
     * @parameter date-time $arrival Arrival date
     * @param string $arrival Departure date
     * @parameter date-time $departure Departure
     * @param integer[] $rental_types Rental types
     * @optparameter int64[] $rental_types Rental types
     * @param integer[] $facility_ids Facilities
     * @optparameter int64[] $facility_ids Facilities
     * @param RecreationEventsComposition[] $compositions Composition of guests
     * @optparameter RecreationEventsComposition[] $compositions Composition of guests
     * @errors 404 Not found
     */
	public function actionFirstAvailable($type_id, $arrival, $departure, $rental_types = [], $facility_ids = [], $compositions = []) {
		$type			= RecreationObjectType::findOne($type_id);
		$arrivalDate 	= new DateTime($arrival);
		$departureDate	= new DateTime($departure);
		$now			= new DateTime();

		if (empty($type)) {
			throw new BadRequestHttpException('No objecttype received');
		}

		if (empty($arrivalDate) || $arrivalDate < $now) {
			throw new BadRequestHttpException('Invalid arrival date');
		}

		if (empty($departureDate) || $departureDate < $arrivalDate) {
			throw new BadRequestHttpException('Invalid depature date');
		}

		$booking = new RecreationBooking();
		$booking->setObjectType($type);
		$booking->bookdateFrom	= $arrivalDate;
		$booking->nightsAway	= (int)$arrivalDate->diff($departureDate)->format('%R%a');

		if (!empty($rental_types)) {
			$booking->setAllowedRentalTypes($rental_types);
		}

		return $booking->getAvailableObject() ?? [];
	}


	/**
     * Retreive Block object for reservation
	 *
     * @path /recreation/booking/block
     * @method get
     * @tag booking
	 * @security default
	 * @return integer successful operation
     * @param integer $object_id
     * @parameter int64 $object_id Object id
     * @constraint minimum $object_id 1
     * @param string $arrival Arrival date
     * @parameter date-time $arrival Arrival date
     * @param string $arrival Departure date
     * @parameter date-time $departure Departure
     * @param integer $minutes Minutes
     * @optparameter int64 $minutes Minutes
     * @errors 404 Not found
     */
	public function actionBlock($object_id, $arrival, $departure, $minutes = 60) {}


	/**
     * Retreive end blocking object
	 *
     * @path /recreation/booking/block-cancel
     * @method get
     * @tag booking
	 * @security default
	 * @return boolean successful operation
     * @param integer $block_id
     * @parameter int64 $block_id Block id
     * @constraint minimum $block_id 1
     * @errors 404 Not found
     */
	public function actionBlockCancel($block_id) {}


	/**
     * Retreive products
	 *
     * @path /recreation/booking/products
     * @method get
     * @tag booking
	 * @security default
	 * @return Products[] successful operation
     * @param integer $type_id
     * @parameter int64 $type_id Type
     * @constraint minimum $type_id 1
     * @errors 404 Not found
     */
	public function actionProducts($type_id) {}

}