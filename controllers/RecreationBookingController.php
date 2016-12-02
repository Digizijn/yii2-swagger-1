<?php
namespace app\controllers;

use DateInterval;
use DateTime;
use eo\base\database\RecreationEventsLog;
use eo\base\EO;
use eo\models\database\Products;
use eo\models\database\RecreationEvents;
use eo\models\database\RecreationEventsComposition;
use eo\models\database\RecreationEventsState;
use eo\models\database\RecreationObject;
use eo\models\database\RecreationObjectTypeProduct;
use eo\models\database\RecreationPackage;
use eo\models\database\RecreationPackageProduct;
use eo\models\database\RecreationPeriodPrice;
use eo\models\database\RecreationPricingResponse;
use eo\models\database\RecreationAvailibilityResponse;
use eo\models\database\RecreationObjectType;
use eo\models\database\RecreationRentalPeriod;
use eo\models\database\RecreationRentalProduct;
use eo\models\database\RecreationRentalType;
use eo\models\database\RecreationSourceExcludedProduct;
use eo\models\database\WebsitePagesRecreationConnection;
use eo\models\RecreationBooking;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\NotAcceptableHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

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
	// Caching
	protected static $periods 	= [];
	protected static $prices 	= [];
	protected static $sourceExcluded;
	protected static $objectTypeProducts	= [];
	protected static $rentalProducts;
	protected static $productExtras;


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
	 * @path /recreation/booking/availability
	 * @method get
	 * @tag booking
	 * @tag objecttypes
	 * @tag rentaltypes
	 * @tag objects
	 * @security default
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
	 * @return RecreationAvailibilityResponse[] successful operation
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

		if (empty($departureDate) || $departureDate <= $arrivalDate) {
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

		// Indien geen object maar wel een type
//		if (empty($objects) && !empty($type_id)) {
//			$objects = $type->objects;
//			$dates = $booking->getAvailableDates();
//			if (!empty($dates)) {
//				$objects = $booking->getAvailableObjects();
//			}
//		}

		if (!empty($objects)) {
			$booking->preferenceObjectID = ArrayHelper::map($objects, 'object_id', 'object_id');
		}

		// FIXME BUG krijgt alleen beschikbaarheid NA gevonden reservering
		$availableDates = $booking->getAvailableDates(false, [], false);

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
				$val['object_id']		= (int)$object->object_id;
				$val['availability']	= [];//$arrivalDate->format('Y-m-d')]; // TODO FIXME

				$response[$object->object_id] = $val;
			}

			$response[$object->object_id]['availability'][] =  $date->format('Y-m-d');
		}

		return array_values($response);
	}


//	/**
//	 * Retreive object availability per date
//	 *
//	 * @path /recreation/booking/pricing22
//	 * @method get
//	 * @tag booking
//	 * @security default
//	 * @return RecreationPricingResponse[] successful operation
//	 * @param integer[] $type_id
//	 * @parameter int64[] $type_id Objecttype id
//	 * @constraint minimum $type_id 1
//	 * @param string $arrival Arrival date
//	 * @parameter date-time $arrival Arrival date
//	 * @param integer $arrival Departure date
//	 * @parameter date-time $departure Departure date
//	 * @param integer $min_nights Min nights
//	 * @optparameter int64 $min_nights Min nights
//	 * @param integer $max_nights Max nights
//	 * @optparameter int64 $max_nights Max nights
//	 * @param integer $persons Persons
//	 * @optparameter int64 $persons Persons
//	 * @errors 404 Not found
//	 */
//	public function actionPricingOud($type_id = [], $arrival, $departure, $min_nights = 0, $max_nights = 21, $persons = 2) {
//		$type			= RecreationObjectType::findOne($type_id);
//		$arrivalDate 	= new DateTime($arrival);
//		$departureDate	= new DateTime($departure);
//		$now			= new DateTime();
//
//		if (empty($type)) {
//			throw new BadRequestHttpException('No objecttype received');
//		}
//
//		if (empty($arrivalDate) || $arrivalDate < $now) {
//			throw new BadRequestHttpException('Invalid arrival date');
//		}
//
//		if (empty($departureDate) || $departureDate < $arrivalDate) {
//			throw new BadRequestHttpException('Invalid depature date');
//		}
//
//		$bookablePeriods	= [];
//		$rentalTypes		= RecreationRentalType::find()->cache()->innerJoinWith(['rentalTypeConnection.objects.objectType' => function($q) use($type) {
//			$modelClass = $q->modelClass;
//			$q->andWhere([$modelClass::tableName().'.type_id' => $type->type_id]);
//		}])->all();
//
//		$rentalIDs			= [];
//		if (count($rentalTypes) > 0) {
//			$rentalIDs		= array_keys(ArrayHelper::map($rentalTypes, 'rental_id', 'rental_name'));
//		}
//
//		if (count($rentalIDs) > 0) {
////			die(RecreationRentalPeriod::find()->select('*')->cache()->innerJoinWith('rental', true)->andWhere([RecreationRentalPeriod::tableName().'.rental_id' => $rentalIDs])->createCommand()->rawSql);
//			$rentalPeriods	= RecreationRentalPeriod::find()->cache()->innerJoinWith('rental', true)->andWhere([RecreationRentalPeriod::tableName().'.rental_id' => $rentalIDs]);
//
//			$maxNighs	= (int)$max_nights;
//			if($maxNighs <= 0){
//				$maxNighs	= 21;
//			}
//
//			foreach ($rentalPeriods->each(5) as $rentalPeriod) {
//				for ($nights=$min_nights; $nights<=$maxNighs; $nights++) {
//					if ($nights >= (int)$rentalPeriod->rental->rental_min_nights
//						&& $nights <= (int)$rentalPeriod->rental->rental_max_nights) {
//						foreach (new \DatePeriod($arrivalDate, new DateInterval('P1D'), $departureDate) as $startDate) { // TODO Optmisaliseren
//							$periodPrices	= $rentalPeriod->getPeriodPrices($arrivalDate, $nights, $rentalPeriod->rental, $type); // TODO $rentals?
//							if (!empty($periodPrices)) {
//								$price	= 0;
//								foreach ($periodPrices as $periodPrice) {
//									$price	+= $periodPrice->price_amount;
//								}
//
//								$enddate	= clone $startDate;
//								$enddate->add(new DateInterval('P'.$nights.'D'));
//
//								// eventuele korting ophalen
//								$event	= new RecreationEvents();
//								$event->event_amount_persons = $persons;
//								$event->setObjectType($type);
//								$event->setArrivalDate($startDate);
//								$event->setDepartureDate($enddate);
//								$event->setAllowedRentalIDs([$rentalPeriod->cache()->rental->rental_id]); // $rentalPeriod->rental
//								$event->setPeriodPrices(false);
//
//								$objectPrice	= $event->getObjectPrice();
//
//								$price			= $objectPrice->getInclusive(true);
//								$fromprice		= $objectPrice->getInclusive(false);
//
//								if ($price > 0) {
//									$bookablePrices[]	= [
//										'nights'		=> $nights,
//										'arrivaldate'	=> $startDate->format('Y-m-d'),
//										'departuredate'	=> $enddate->format('Y-m-d'),
//										'discounted'	=> (float)($price - $fromprice),
//										'price'			=> (float)$price,
//									];
//								}
//
//								unset($event);
//							}
//						}
//					}
//				}
//			}
//		}
//
//		//arrangementen erbij zoeken
//		$packages			= RecreationPackage::find()
//			->innerJoinWith('websitePageConnections')
//			->byObjectType($type->type_id)
//			->byDate($arrivalDate)
//			->all();
//
//		foreach($packages as $package){
//			$isValidArrivalDate		= false;
//			$packageArrivalDates	= $package->getArrivalDates();
//			foreach($packageArrivalDates as $packageArrivalDate){
//				if($packageArrivalDate->format('Y-m-d') === $arrivalDate->format('Y-m-d')){
//					$isValidArrivalDate	= true;
//					break;
//				}
//			}
//
//			if($isValidArrivalDate === true) {
//				$nights	= $package->package_nights;
////				if($nights % 7 == 0){
////					$weeks				= $nights / 7;
////					$formattedNights	= $weeks.' '.($weeks == 1 ? EO::t('recreation', 'week') : EO::t('recreation', 'weken'));
////				} else {
////					$formattedNights	= $nights.' '.($nights == 1 ? EO::t('recreation', 'nacht') : EO::t('recreation', 'nachten'));
////				}
//
//				$enddate	= $package->getTillDate($arrivalDate);
//
//				//eventuele korting ophalen
//				$event	= new RecreationEvents;
//				$event->setObjectType($type);
//				$event->setArrivalDate($arrivalDate);
//				$event->setDepartureDate($enddate);
//				$event->setPackage($package);
//				$event->setPeriodPrices(false);
//
//				$objectPrice	= $event->getObjectPrice();
//
//				$price			= $objectPrice->getInclusive(true);
//				$fromprice		= $objectPrice->getInclusive(false);
//
//
//				$bookablePackages[]	= [
//					'packageID'		=> (int)$package->package_id,
//					'nights'		=> $nights,
//					'arrivaldate'	=> $arrivalDate->format('Y-m-d'),
//					'departuredate'	=> $enddate->format('Y-m-d'),
//					'discounted'	=> (float)($price - $fromprice),
//					'price'			=> (float)$price,
//				];
//			}
//		}
//
//		return $bookablePrices ?? [];
//	}


	/**
	 * Retreive object availability per date
	 *
	 * @path /recreation/booking/pricing
	 * @method get
	 * @tag booking
	 * @tag objecttypes
	 * @security default
	 * @param integer $type_id
	 * @parameter int64 $type_id Objecttype id
	 * @constraint minimum $type_id 1
	 * @param string $arrival Arrival date
	 * @optparameter date-time $arrival Arrival date
	 * @optparam integer $arrival Departure date
	 * @optparameter date-time $departure Departure date
	 * @optparam integer $min_nights Min nights TODO
	 * @optparameter int64 $min_nights Min nights TODO
	 * @constraint minimum $min_nights 0
	 * @optparam integer $max_nights Max nights
	 * @optparameter int64 $max_nights Max nights
	 * @constraint maximum $max_nights 365
	 * @optparam string $source Source
	 * @optparameter string $source Source TODO
	 * @enum $source website
	 * @return RecreationPricingResponse[] successful operation
	 * @errors 404 Not found
	 */
	public function actionPricing($type_id, $arrival, $departure, $min_nights = 0, $max_nights = 20, $persons = 2, $source = 'website' /* TODO */) {
		$type			= RecreationObjectType::findOne($type_id);
		$arrivalDate 	= new DateTime($arrival);
		$departureDate	= new DateTime($departure);
		$now			= new DateTime();

		// TODO max personen <= type max personen

		if (empty($type)) {
			throw new BadRequestHttpException('No objecttype received');
		}

		if (empty($arrivalDate) || $arrivalDate < $now) {
			throw new BadRequestHttpException('Invalid arrival date');
		}

		if (empty($departureDate) || $departureDate < $arrivalDate) {
			throw new BadRequestHttpException('Invalid depature date');
		}

		$nightsAway		= (int)$arrivalDate->diff($departureDate)->format('%R%a');
		$bookablePeriods	= [];

		// TODO objectType kan eraf?
		$rentalTypesQry	= RecreationRentalType::find()->cache()->innerJoinWith(['rentalTypeConnection.objects.objectType' => function($q) use($type) {
			$modelClass = $q->modelClass;
			$q->andWhere([$modelClass::tableName().'.type_id' => $type->type_id]);
		}]);


		$rentalTypes = $rentalTypesQry->all();

		$rentalIDs		= [];
		if (count($rentalTypes) > 0) {
			$rentalIDs		= array_keys(ArrayHelper::map($rentalTypes, 'rental_id', 'rental_id'));

			if ($source === 'website') {
				$websiteRentals = WebsitePagesRecreationConnection::find()->select('rental_id')
					->andWhere(['type_id' => $type_id])
					->andWhere(['rental_id' => $rentalIDs])
					->asArray()
					->all();

				$websiteRentalIDs = ArrayHelper::map($websiteRentals, 'rental_id', 'rental_id');

				$rentalIDs = array_intersect($rentalIDs, $websiteRentalIDs);
			}
		}

		if (count($rentalIDs) > 0) {
//			die(RecreationRentalPeriod::find()->select('*')->cache()->innerJoinWith('rental', true)->andWhere([RecreationRentalPeriod::tableName().'.rental_id' => $rentalIDs])->createCommand()->rawSql);

			static::$periods	= RecreationRentalPeriod::find()
				->indexBy('period_id')
				->andWhere(['rental_id' => $rentalIDs])
				->all();

			/** @var $periodPrice RecreationPeriodPrice */
			static::$prices = $baseprices = RecreationPeriodPrice::find()
				->innerJoinWith(['period' => function($q) use ($arrivalDate, $departureDate) { // TODO subquery in having?
					$q->andWhere('
							"'.$arrivalDate->format('Y-m-d').'" BETWEEN period_startdate AND DATE_SUB(period_enddate, INTERVAL 1 DAY)
							OR "'.$departureDate->format('Y-m-d').'" BETWEEN period_startdate AND DATE_SUB(period_enddate, INTERVAL 1 DAY)
							OR period_startdate BETWEEN "'.$arrivalDate->format('Y-m-d').'" AND "'.$departureDate->format('Y-m-d').'"
							OR DATE_SUB(period_enddate, INTERVAL 1 DAY) BETWEEN "'.$arrivalDate->format('Y-m-d').'" AND "'.$departureDate->format('Y-m-d').'"
						');
				}])
				->andWhere(['rental_period_id' => ArrayHelper::map(static::$periods, 'period_id', 'period_id')])
				->andWhere(['rental_id' => $rentalIDs])
				->andWhere(['type_id' => $type->type_id])
				->andWhere('price_amount IS NOT NULL')
				->all();

			$rentals = RecreationRentalType::find()
				->select('rental_id, rental_min_nights, rental_max_nights')
				->indexBy('rental_id')
//				->andWhere(['>=', 'rental_min_nights', $min_nights])
				->andWhere(['<=', 'rental_min_nights', $max_nights])
//				->andWhere(['rental_id' => ArrayHelper::map($prices, 'rental_id', 'rental_id')])
				->orderBy('rental_order')
				->all();

			foreach ($baseprices as $price) {
				if (isset(static::$periods[$price->rental_period_id])) {
					$rentalPeriod = static::$periods[$price->rental_period_id];
					/** @var DateTime $startDate */
					foreach (new \DatePeriod($arrivalDate, new DateInterval('P1D'), $departureDate) as $startDate) { // Dep + 1 day?

						$startDateYMD = $startDate->format('Y-m-d');
						if (!empty($price->period)) {
							$period = $price->period;
							if ($period->period_startdate <= $startDateYMD && $period->period_enddate >= $startDateYMD) {
								if (!empty($rentals[$rentalPeriod->rental_id])) {
									/** @var RecreationRentalType $rental */
									$rental 		= $rentals[$rentalPeriod->rental_id];
									$periodEnddate 	= new DateTime($period->period_enddate);
									$possible 		= $this->generatePossiblePeriods(
										$price,
										$rental,
										$rentalPeriod,
										max($min_nights, $rental->rental_min_nights),
										min($max_nights, $startDate->diff($periodEnddate)->format('%a')),  // Binnen periode blijven
										$startDate
									);

									if (!empty($possible)) {
										/** @var RecreationRentalPeriod $v */
										$possibleResponse = array_unique(
											array_map(
												function($p) use ($startDate, $type, $rental, $persons) {
													$nights = array_sum(
														array_map(function($pp) {
															$rentalPeriod = static::$periods[$pp->rental_period_id];
															return $rentalPeriod->period_nights;
														}, $p)
													);

													return [
														'nights' => $nights,
														'rental_id' => $rental->rental_id,
														'price' => array_sum(array_map(function($pp) {
															return $pp->price_amount;
														}, $p)),
														'period_price_id' => array_map(function($pp) {
															return $pp->price_id;
														}, $p),
			//											'tax' => $touristTax * $persons * $nights,
														'products' => static::getExtras($type, $rental, $nights, $persons)
													];
												}, $possible
											), SORT_REGULAR
										);

										$startDateString = $startDate->format('Y-m-d');

										if (isset($bookablePeriods[$startDateString])) {
											// Alleen laagste prijs voor dubbelen
		//									$bookablePeriods[$startDateString] = array_unique(ArrayHelper::merge($bookablePeriods[$startDateString], $possibleResponse), SORT_REGULAR);
		//									$bookablePeriods[$startDateString] = ArrayHelper::merge($possibleResponse, $bookablePeriods[$startDateString]);

											foreach ($bookablePeriods[$startDateString] as $k1 => $p1) {
												foreach ($possibleResponse as $k2 => $p2) {
													if ($p1['nights'] === $p2['nights'] && $p1['price'] < $p2['price']) {
														unset($possibleResponse[$k2]);
													}

													if ($p1['nights'] === $p2['nights'] && $p1['price'] >= $p2['price']) {
														unset($bookablePeriods[$startDateString][$k1]);
													}
												}
											}

											$bookablePeriods[$startDateString] = ArrayHelper::merge($bookablePeriods[$startDateString], $possibleResponse);

										} else {
											$bookablePeriods[$startDateString] = $possibleResponse;
										}

										// TODO Prijs afhankelijk van samenstelling @Michiel

		//								$bookablePeriods = ArrayHelper::merge($bookablePeriods, $possible);
									}
								}
							}
						}
					}

//					$bookablePeriods = array_unique($bookablePeriods, SORT_REGULAR);
				}
			}


			$packages = RecreationPackage::find()
				// Vua recreaction packcage period
				->innerJoinWith([ // ???
					'period' => function($q) use ($arrivalDate, $departureDate) {
						$q->andWhere('
							"'.$arrivalDate->format('Y-m-d').'" BETWEEN period_startdate AND DATE_SUB(period_enddate, INTERVAL 1 DAY)
							OR "'.$departureDate->format('Y-m-d').'" BETWEEN period_startdate AND DATE_SUB(period_enddate, INTERVAL 1 DAY)
							OR period_startdate BETWEEN "'.$arrivalDate->format('Y-m-d').'" AND "'.$departureDate->format('Y-m-d').'"
							OR DATE_SUB(period_enddate, INTERVAL 1 DAY) BETWEEN "'.$arrivalDate->format('Y-m-d').'" AND "'.$departureDate->format('Y-m-d').'"
						');
					}
				])->innerJoinWith([
					'objectTypeConnection' => function($q) use ($type) {
						$q->andWhere(['type_id' => $type->type_id]);
					}
				])
				->andWhere(['rental_id' => $rentalIDs])
				->all();

			// arrival days
			$bookablePackagePeriods = [];
			if (!empty($packages)) {
				/** @var RecreationPackage $package */
				foreach ($packages as $package) {
					$packageDates	= $package->getArrivalDates();

					if (!empty($packageDates)) {
						$firstPossibleDate	= $packageDates[0];
						$lastPossibleDate	= end($packageDates);

						$packageDows	= explode(',', $package->package_arrivaldays);
						if(in_array('0', $packageDows)){
							$packageDows[]	= '7';
						}

						//de aankomstdatum moet voor de einddatum van het arrangement liggen
						if($package->package_extra_days === 'ja') { // $this->nightsAway > 0
							// FIXME kijkt nu alleen naar aankomstdata??? TODO
							// Alle te boeken periodes datums
							foreach ($bookablePeriods as $startDate => $bookableDates) {
								$startDate 	= new DateTime($startDate);
								// Alle boekbare periodes
								foreach ($bookableDates as $bookableDate) {
									$nights = $bookableDate['nights'];
									$endDate	= clone $startDate;
									$endDate->add(new DateInterval('P'.($nights + 1).'D'));

//									if ($bookableDate['nights'] >= $package->package_nights) {
										$packageFirstPossibleDate   = clone $firstPossibleDate;
										$packageFirstPossibleDate->sub(new DateInterval('P'.$nights.'D'));
										$packageLastPossibleDate    = clone $lastPossibleDate;

										if ($endDate >= $packageFirstPossibleDate && $startDate <= $packageLastPossibleDate) {
											// TODO wat als een beschikbare periode (deels) over het arrangement heen valt, prijs?
											// TODO hoe voor én na arragement kunnen verlengen?
											$n = (int)$package->package_nights;
											for ($n = 1; $n <= (int)$package->package_nights; $n++) {
												if(in_array($startDate->format('w'), $packageDows, false)) {
													$packageDate = $bookableDate;

													$packageDate['label'] 		= $package->package_name;
													$packageDate['package'] 	= $package->package_id;
//													$packageDate['org_nights'] 	= $bookableDate['nights'];
//													$packageDate['org_price'] 	= $packageDate['price'];
													$packageDate['nights'] 		+= $n;
													$packageDate['price'] 		+= $package->objectTypeConnection[0]->conn_price;
													// Optimaliseren, kan gecached worden
													$packageDate['products']	= static::getExtras($type, $package->rentalType, $nights, $persons, $package);; // TODO FIXME

													$bookablePackagePeriods[$packageFirstPossibleDate->format('Y-m-d')][] = $packageDate;
												}
											}
										}
//									}
								}
							}
						}


						/** @var DateTime $date */
						foreach ($packageDates as $date) {
							if ($date >= $arrivalDate && $date <= $departureDate) {
								$packageNights	= $package->package_nights;
								$tmpDate		= clone $date;
								$tmpDate->add(new DateInterval('P'.$package->package_nights.'D'));

								if($date > $lastPossibleDate) { // if($tmpDate > $lastPossibleDate) { // TODO FIXME wat moet dit doen?
									$interval 		= $date->diff($lastPossibleDate);
									$packageNights	= $interval->format('%a');
//									var_dump($date);
//									var_dump($lastPossibleDate);
//									var_dump($packageNights);die();
								}

	//							$object			= null;
	//							if(!empty($preferenceObject)){
	//								if($preferenceObject->isAvailable($date, $packageNights)){
	//									$object			= $preferenceObject;
	//								}
	//							} else if(isset($preferenceObjects)){
	//								$object	= null;
	//								if(!empty($preferenceObjects)){
	//									foreach($preferenceObjects as $tmpObject){
	//										if($tmpObject->isAvailable($date, $packageNights)){
	//											$object	= clone $tmpObject;
	//											break;
	//										}
	//									}
	//								}
	//							} else {
//									$object	= RecreationObject::find()->cache()
//										->with('objectParent')
//										->with('objectChilds')
//										->getAvailable($type, null, $date, $packageNights);
	//							}

//								if(!empty($object)) {
								if ($packageNights > 0) {
									if (empty($bookablePackagePeriods[$date->format('Y-m-d')]) || !is_array($bookablePackagePeriods[$date->format('Y-m-d')])) {
										$bookablePackagePeriods[$date->format('Y-m-d')] = [];
									}

									$bookablePackagePeriods[$date->format('Y-m-d')][] = [
										'label' 	=> $package->package_name,
										'package'	=> $package->package_id,
										'nights' 	=> (int)$packageNights,
										'price'		=> $package->objectTypeConnection[0]->conn_price,
										'rental_id'	=> $package->rental_id,
										'products'	=> static::getExtras($type, $package->rentalType, $packageNights, $persons, $package)
									];
								}
//								}
							}
						}
					}
				}
			}
			$bookablePeriods = ArrayHelper::merge($bookablePeriods, $bookablePackagePeriods);
//			$bookablePeriods = $bookablePackagePeriods;
		}


		foreach($bookablePeriods as $date => $dates) {
			uasort($bookablePeriods[$date], function($a, $b) {
				if ($a['nights'] > $b['nights']) return 1;
				if ($a['nights'] < $b['nights']) return -1;
				else return 0;
			});
		}

		if (!YII_ENV_PROD) {
			ksort($bookablePeriods);
		}

		return $bookablePeriods ?? [];
	}


	/** @return array */
	static public function getExtras(RecreationObjectType $type, RecreationRentalType $rentalType, int $nightsAway, int $persons, RecreationPackage $package = null, $source = 'website') {
		if (!empty(static::$productExtras[$type->type_id])) {
			foreach (static::$productExtras[$type->type_id] as $extra) {
				if (
					$extra['rentalType'] == $rentalType->rental_id
				 &&	$extra['nightsAway'] == $nightsAway
				 &&	$extra['persons'] == $persons
				 &&	$extra['package'] == ($package ? $package->package_id : 0)
				 && $extra['source'] == $source
				) {
					return $extra['extras'];
				}
			}
		} else {
			static::$productExtras[$type->type_id] = [];
		}

		$excludeTouristTax	= false;
		$cancellation_fund_percentage	= 0;
		$extras = [];

		$excludedProducts	= [];//$this->getExcludedProducts();

		if (static::$sourceExcluded === null) {
			static::$sourceExcluded = [];
			$products	= RecreationSourceExcludedProduct::find()->cache()
				->andWhere(['exclude_source' => $source])
				->all();

			if(count($products) > 0){
				foreach($products as $product){
					static::$sourceExcluded[]	= $product->product_id;
				}
			}
		}

		$excludedSourceProducts = static::$sourceExcluded;
		$excludeProducts		= false;
		if(!empty($package)){
			$excludeProducts	= ($package->package_exclude_products === 'ja');
		}

//			/** @var $object RecreationObject */
//			$object			= $this->availableObject[0];

		//facturatie opties ophalen
		if($excludeProducts !== true){
			if (!isset(static::$objectTypeProducts[$type->type_id])) {
				static::$objectTypeProducts[$type->type_id]	= RecreationObjectTypeProduct::find()->cache()
					->innerJoinWith(['product' => function($q) {
						$q->joinWith('predefinedType');
					}])
					->andWhere([RecreationObjectTypeProduct::tableName().'.type_id'	=> $type->type_id])
					->all();
			}

			$typeProducts = static::$objectTypeProducts[$type->type_id];

			if(count($typeProducts) > 0){
				/** @var $typeProducts RecreationObjectTypeProduct[] */
				foreach($typeProducts AS $typeProduct){
					if(in_array($typeProduct->product_artid, $excludedSourceProducts, true)){
						continue;	//uitgesloten bron product
					}

					if($excludeTouristTax === (int)$typeProduct->product_artid){
						continue;
					}

//					$isCancellationFund	= false;
//					$predefinedType		= $typeProduct->product->predefinedType;
//					if(!empty($predefinedType)){
//						if($predefinedType->type_name === 'recreation_cancellation_fund'){
//							$isCancellationFund	= true;
//						}
//					}

					$amountOfPersons	= $persons;
					$show				= false;
					switch($typeProduct->product_from_type){
						case 'nachten':
							if(($nightsAway > $typeProduct->product_from || $typeProduct->product_from == 0) && ($nightsAway <= $typeProduct->product_till || $typeProduct->product_till == 0)){
								$show	= true;
							}
							break;
						case 'personen':
							if(($persons > $typeProduct->product_from || $typeProduct->product_from == 0) && ($persons <= $typeProduct->product_till || $typeProduct->product_till == 0)){
								$show				= true;
								$amountOfPersons	= $persons - $typeProduct->product_from;
							}
							break;
						default:
							$show		= true;
							break;
					}

					if($show === true){

//						if($isCancellationFund === true && $cancellation_fund_percentage > 0){
//
//							/** @var $tmpProduct Products */
//							$tmpProduct					= clone $typeProduct->product;
//							$tmpPrice					= $tmpProduct->getPrice();
//
//							$objectPrice					 = $event->getObjectPrice()->getInclusive(false);
//							$objectDiscount					 = $event->getObjectPrice()->getDiscountAmount(true);
//							$objectPrice					-= $objectDiscount;
//							$typeProduct->product_price		 = $objectPrice * ($cancellation_fund_percentage / 100);
//							$tmpPrice->setFixedPrice($typeProduct->product_price);
//
//							$typeExcl		= $tmpPrice->getExclusive(true);
//							$typeVat		= $tmpPrice->getVatAmount(true);
//						} else {
							$typeProduct->setAmountNights($nightsAway);

							$compositionPersons	= $amountOfPersons;
							if(isset($excludedProducts[$typeProduct->product_artid])){
								$compositionPersons	-= $excludedProducts[$typeProduct->product_artid];
								if($compositionPersons < 0){
									$compositionPersons	= 0;
								}
							}

							$typeProduct->setAmountPersons($compositionPersons);

							$typeExcl		= $typeProduct->getExlusive(false); // TODO discounted?
							$typeVat		= $typeProduct->getVatAmount(false); // TODO discounted?
//						}
						$extras[]		= [
							'product_id'	=> $typeProduct->product_id,
							'art_id'		=> $typeProduct->product_artid,
							'per'			=> $typeProduct->product_per,
							'from'			=> $typeProduct->product_from,
							'till'			=> $typeProduct->product_till,
							'from_type'		=> $typeProduct->product_from_type,
							'bail'			=> $typeProduct->product_bail,
							'description'	=> $typeProduct->product_description,
							'price'			=> $typeProduct->product_price,
							'excl'			=> $typeExcl,
							'vat'			=> $typeVat,
							'productType'	=> $typeProduct->product->art_type,
							'productCategory'	=> $typeProduct->product->art_cat,
						];

						unset($typeProduct);
					}
				}
			}
		}

		if(!empty($rentalType) && $excludeProducts !== true) {
			if (!isset(static::$rentalProducts[$rentalType->rental_id])) {
				static::$rentalProducts[$rentalType->rental_id]	= RecreationRentalProduct::find()->cache()
					->innerJoinWith(['product' => function($q) {
						$q->joinWith('predefinedType');
					}])
					->andWhere(['rental_id' => $rentalType->rental_id])
					->all();
			}

			$rentalProducts = static::$rentalProducts[$rentalType->rental_id];

//			if ($rentalType->rental_id == 36) {
//				die(
//				RecreationRentalProduct::find()->cache()
//					->innerJoinWith(['product' => function($q) {
//						$q->joinWith('predefinedType');
//					}])
//					->andWhere(['rental_id' => $rentalType->rental_id])->createCommand()->rawSql
//
//				);
//			}

			if(count($rentalProducts) > 0){
				/** @var $rentalProducts RecreationRentalProduct[] */
				foreach($rentalProducts AS $rentalProduct){
					if (in_array($rentalProduct->product_artid, $excludedSourceProducts, true)) {
						continue;	//uitgesloten bron product
					}
					if ($excludeTouristTax === (int)$rentalProduct->product_artid) {
						continue;
					}

//					$isCancellationFund	= false;
//					$predefinedType		= $rentalProduct->product->predefinedType;
//					if(!empty($predefinedType)){
//						if($predefinedType->type_name === 'recreation_cancellation_fund'){
//							$isCancellationFund	= true;
//						}
//					}

					$compositionPersons	= $persons;
					if(isset($excludedProducts[$rentalProduct->product_artid])){
						$compositionPersons	-= $excludedProducts[$rentalProduct->product_artid];
						if($compositionPersons < 0){
							$compositionPersons	= 0;
						}
					}

					$rentalProduct->setAmountNights($nightsAway);
					$rentalProduct->setAmountPersons($compositionPersons);

//					if($isCancellationFund === true && $cancellation_fund_percentage > 0){
//						/** @var $tmpProduct Products */
//						$tmpProduct					= clone $rentalProduct->product;
//						$tmpPrice					= $tmpProduct->getPrice();
//
//
//						$objectPrice						 = $event->getObjectPrice()->getInclusive(false);
//						$objectDiscount						 = $event->getObjectPrice()->getDiscountAmount(true);
//						$objectPrice						-= $objectDiscount;
//						$rentalProduct->product_price		 = $objectPrice * ($cancellation_fund_percentage / 100);
//						$tmpPrice->setFixedPrice($rentalProduct->product_price);
//
//						$rentalExcl		= $tmpPrice->getExclusive(true);
//						$rentalVat		= $tmpPrice->getVatAmount(true);
//					} else {
						$rentalExcl		= $rentalProduct->getExlusive(false); // TODO discounted?
						$rentalVat		= $rentalProduct->getVatAmount(false); // TODO discounted?
//					}


					$extras[]		= [
						'product_id'	=> $rentalProduct->product_id,
						'art_id'		=> $rentalProduct->product_artid,
						'per'			=> $rentalProduct->product_per,
						'from'			=> '',
						'till'			=> '',
						'from_type'		=> '',
						'bail'			=> $rentalProduct->product_bail,
						'description'	=> $rentalProduct->product_description,
						'price'			=> $rentalProduct->product_price,
						'excl'			=> $rentalExcl,
						'vat'			=> $rentalVat,
						'productType'	=> $rentalProduct->product->art_type,
						'productCategory'	=> $rentalProduct->product->art_cat
					];

					unset($rentalProduct);
				}
			}
		}

		if(!empty($package)){
			$packageProducts	= RecreationPackageProduct::find()->cache()
				->with(['product'])
				->andWhere(['package_id' => $package->package_id])
				->orderBy('conn_isfree DESC')
				->all();

			if(!empty($packageProducts)){
				/** @var $packageProducts RecreationPackageProduct[] */
				foreach($packageProducts AS $packageProduct){
					if (in_array($packageProduct->product_id, $excludedSourceProducts, true)){
						continue; //uitgesloten bron product
					}

					if($excludeTouristTax === (int)$packageProduct->product_id){
						continue;
					}

//						$isCancellationFund	= false;
//						$predefinedType		= $packageProduct->product->predefinedType;
//						if(!empty($predefinedType)){
//							if($predefinedType->type_name == 'recreation_cancellation_fund'){
//								$isCancellationFund	= true;
//							}
//						}

					$amountOfPersons	= $persons;
					$show				= false;
					switch($packageProduct->conn_from_type){
						case 'nachten':
							if(($nightsAway > $packageProduct->conn_from || $packageProduct->conn_from == 0) && ($nightsAway <= $packageProduct->conn_till || $packageProduct->conn_till == 0)){
								$show	= true;
							}
							break;
						case 'personen':
							if(($persons > $packageProduct->conn_from || $packageProduct->conn_from == 0) && ($persons <= $packageProduct->conn_till || $packageProduct->conn_till == 0)){
								$show				= true;
								$amountOfPersons	= $persons - $packageProduct->conn_from;
							}
							break;
						default:
							$show		= true;
							break;
					}

					if ($show === true){//
//							if($isCancellationFund === true && $cancellation_fund_percentage > 0){
//								/** @var $tmpProduct Products */
//								$tmpProduct					= clone $packageProduct->product;
//								$tmpPrice					= $tmpProduct->getPrice();
//
//								$objectPrice					 = $event->getObjectPrice()->getInclusive(false);
//								$objectDiscount					 = $event->getObjectPrice()->getDiscountAmount(true);
//								$objectPrice					-= $objectDiscount;
//								$packageProduct->conn_price      = $objectPrice * ($cancellation_fund_percentage / 100);
//								$tmpPrice->setFixedPrice($packageProduct->conn_price);
//
//								$packageExcl		= $tmpPrice->getExclusive(true);
//								$packageVat		    = $tmpPrice->getVatAmount(true);
//							} else {
							$packageProduct->setAmountNights($nightsAway);

							$compositionPersons	= $amountOfPersons;
							if(isset($excludedProducts[$packageProduct->product_id])){
								$compositionPersons	-= $excludedProducts[$packageProduct->product_id];
								if($compositionPersons < 0){
									$compositionPersons	= 0;
								}
							}

							$packageProduct->setAmountPersons($compositionPersons);

							$packageExcl		= $packageProduct->getExlusive(true);
							$packageVat		    = $packageProduct->getVatAmount(true);
//							}

						$extras[] = [
							'id'				=> $packageProduct->conn_id,
							'art_id'			=> $packageProduct->product_id,
							'per'				=> $packageProduct->conn_per,
							'from'				=> $packageProduct->conn_from,
							'till'				=> $packageProduct->conn_till,
							'from_type'			=> $packageProduct->conn_from_type,
							'bail'				=> $packageProduct->conn_bail,
							'description'		=> $packageProduct->conn_description,
							'price'				=> $packageProduct->conn_price,
							'excl'				=> $packageExcl,
							'vat'				=> $packageVat,
							'productType'		=> $packageProduct->product->art_type,
							'productCategory'	=> $packageProduct->product->art_cat
						];
					}
				}
			}
		}

//		if($excludeProducts !== true){
//			$objectProducts	= RecreationObjectProduct::model()->cache(EO::param('default_cache_time'))->with([
//				'product'	=> [
//					'with'	=> [
//						'predefinedType',]],])->findAllByAttributes([
//				'object_id'	=> $object->object_id,]);
//			if(count($objectProducts) > 0){
//				/** @var $objectProducts RecreationObjectProduct[] */
//				foreach($objectProducts AS $objectProduct){
//					if(in_array($objectProduct->product_artid, $excludedSourceProducts)){
//						continue;	//uitgesloten bron product
//					}
//					if($excludeTouristTax === (int)$objectProduct->product_artid){
//						continue;
//					}
//
//					$isCancellationFund	= false;
//					$predefinedType		= $objectProduct->product->predefinedType;
//					if(!empty($predefinedType)){
//						if($predefinedType->type_name == 'recreation_cancellation_fund'){
//							$isCancellationFund	= true;
//						}
//					}
//
//					$compositionPersons	= $this->getAmountOfPersons();
//					if(isset($excludedProducts[$objectProduct->product_artid])){
//						$compositionPersons	-= $excludedProducts[$objectProduct->product_artid];
//						if($compositionPersons < 0){
//							$compositionPersons	= 0;
//						}
//					}
//
//					$objectProduct->setAmountNights($nightsAway);
//					$objectProduct->setAmountPersons($compositionPersons);
//
//					if($isCancellationFund === true && $cancellation_fund_percentage > 0){
//
//						/** @var $tmpProduct Products */
//						$tmpProduct					= clone $objectProduct->product;
//						$tmpPrice					= $tmpProduct->getPrice();
//
//						$objectPrice					 = $event->getObjectPrice()->getInclusive(false);
//						$objectDiscount					 = $event->getObjectPrice()->getDiscountAmount(true);
//						$objectPrice					-= $objectDiscount;
//						$objectProduct->product_price	 = $objectPrice * ($cancellation_fund_percentage / 100);
//						$tmpPrice->setFixedPrice($objectProduct->product_price);
//
//						$typeExcl		= $tmpPrice->getExclusive(true);
//						$typeVat		= $tmpPrice->getVatAmount(true);
//					} else {
//
//						$typeExcl		= $objectProduct->getExlusive(true);
//						$typeVat		= $objectProduct->getVatAmount(true);
//					}
//
//					$extras[]		= [
//						'id'			=> $objectProduct->product_id,
//						'art_id'		=> $objectProduct->product_artid,
//						'per'			=> $objectProduct->product_per,
//						'from'			=> '',
//						'till'			=> '',
//						'from_type'		=> '',
//						'bail'			=> $objectProduct->product_bail,
//						'description'	=> $objectProduct->product_description,
//						'price'			=> $objectProduct->product_price,
//						'excl'			=> $typeExcl,
//						'vat'			=> $typeVat,
//						'productType'	=> $objectProduct->product->art_type,
//						'productCategory'	=> $objectProduct->product->art_cat,];
//				}
//			}
//		}

//		if(!empty($this->preferenceObjectID)){
//
//			if(!empty($this->objectType->type_preference_product_id)){
//				$preferenceProduct	= Products::model()->cache()->findByPk($this->objectType->type_preference_product_id);
//				$preferencePrice	= new ProductPrice($preferenceProduct);
//				$price				= $preferenceProduct->selling_price;
//				if(!empty($this->objectType->type_preference_price)){
//					$price			= (float)$this->objectType->type_preference_price;
//					$preferencePrice->setFixedPrice($price);
//				}
//				$excl				= $preferencePrice->getExclusive();
//				$vatAmount			= $preferencePrice->getVatAmount();
//				$preferenceName		= $preferenceProduct->art_description;
//			} else {
//				$preferenceProduct	= Products::model()->findByAttributes([
//					'art_type'	=> 'tekst',]);
//				$price				= 0;
//				$excl				= 0;
//				$vatAmount			= 0;
//				$preferenceName		= 'Voorkeursboeking';
//			}
//
//			$extras[]		= [
//				'id'				=> 0,
//				'art_id'			=> $preferenceProduct->product_id,
//				'per'				=> 'p.s.',
//				'from'				=> '',
//				'till'				=> '',
//				'from_type'			=> '',
//				'bail'				=> 'nee',
//				'description'		=> Yii::t('recreation', $preferenceName).' '.$object->object_name,
//				'price'				=> $price,
//				'excl'				=> $excl,
//				'vat'				=> $vatAmount,
//				'productType'		=> $object->rentalType->product->art_type,
//				'productCategory'	=> $object->rentalType->product->art_cat,];
//
//		}

		static::$productExtras[$type->type_id][] = [
			'rentalType' 	=> $rentalType->rental_id,
			'nightsAway' 	=> $nightsAway,
			'persons' 		=> $persons,
			'package' 		=> $package ? $package->package_id : 0,
			'source' 		=> $source,
			'extras' 		=> $extras
		];

		return $extras;
	}


	/** @return array */
	private function generatePossiblePeriods(RecreationPeriodPrice $price, RecreationRentalType $rental, RecreationRentalPeriod $rentalPeriod, int $min_nights, int $max_nights, DateTime $dateStep) {
		if (empty($min_nights)) $min_nights= 0;
		$possible	= [];
		$nightsLeft = $max_nights;

		$startDow    = $dateStep->format('w');
		if($startDow == 7){
			$startDow		    = 0;
		}

		$periodStartdays	= explode(',', $rentalPeriod->period_startdays);
		if(in_array('0', $periodStartdays, false)){
			$periodStartdays[]	= '7';
		}

		// TODO if ($nights >= (int)$rental->rental_min_nights && $nights <= (int)$rental->rental_max_nights) {

		$periods 	= [];
		if(in_array($startDow, $periodStartdays, false) && $rentalPeriod->period_nights <= $nightsLeft){
			$periods[]	 = $price;
			$nightsLeft	-= $rentalPeriod->period_nights;
			if ($max_nights - $nightsLeft >= $min_nights) {
				$possible[]	 = $periods;
			}

			if($nightsLeft > 0 && $rentalPeriod->period_repeat === 'ja' && $rentalPeriod->period_nights > 0){
				while($nightsLeft >= $rentalPeriod->period_nights) { // TODO FIXME check current dow
					$periods[]	 = $price;
					$nightsLeft	-= $rentalPeriod->period_nights;
					if ($max_nights - $nightsLeft >= $min_nights) {
						$possible[]	 = $periods;
					}
				}
			}

			if($nightsLeft > 0 && !empty($rentalPeriod->period_expandable)){
				$expendablePeriods 	= explode(',', $rentalPeriod->period_expandable);
				$expendablePrices 	= array_filter(
					static::$prices,
					function($v) use ($expendablePeriods, $rental) {
						/** @var RecreationPeriodPrice $v */
						return in_array($v->rental_period_id, $expendablePeriods, false)
							&& $v->rental_id === $rental->rental_id; // type
					}
				);

				if (!empty($expendablePrices)) {
//					$missing = array_diff($expendablePeriods, array_keys(static::$periods));
//					if (!empty($missing)) {
//						static::$periods = ArrayHelper::merge(
//							RecreationRentalPeriod::find()->indexBy('period_id')->cache()->andWhere(['period_id' => $missing])->all(),
//							static::$periods
//						);
//					}

					foreach ($expendablePrices as $expendablePrice) {
						$expandableNightsleft	= $nightsLeft;
						$expandablePeriod = static::$periods[$expendablePrice->rental_period_id];

						if ($expandableNightsleft >= $expandablePeriod->period_startdays && $expandablePeriod->period_nights > 0) {
							$currentDow	= ($startDow + ($max_nights - $expandableNightsleft)) % 7;

							$expandableDow	= explode(',', $expandablePeriod->period_startdays);
							if(in_array('0', $expandableDow, false)){
								$expandableDow[]	= '7';
							}

							if (in_array($currentDow, $expandableDow, false)) {
								if ($expandablePeriod->period_repeat === 'ja') {
									while ($expandableNightsleft >= $expandablePeriod->period_nights) {
										$periods[]	 = $expendablePrice;
										$expandableNightsleft	-= $expandablePeriod->period_nights; // TODO
										if ($max_nights - $expandableNightsleft >= $min_nights) {
											$possible[]	 = $periods;
										}
									}
								} else {
									$periods[]	 = $expendablePrice;
									$expandableNightsleft	-= $expandablePeriod->period_nights;
									if ($max_nights - $expandableNightsleft >= $min_nights) {
										$possible[]	 = $periods;
									}
								}
							}
						}
					}
				}
			}
		}

		unset($periods);

		return $possible;

	}


	/** @return array */
	private function generatePossiblePeriodsx(RecreationPeriodPrice $price, RecreationRentalPeriod $rentalPeriod, int $min_nights, int $max_nights, DateTime $dateStep) {
		$possible	= [];
		$nightsLeft = $max_nights;

		$startDow    = $dateStep->format('w');
		if($startDow == 7){
			$startDow		    = 0;
		}

		$periodStartdays	= explode(',', $rentalPeriod->period_startdays);
		if(in_array('0', $periodStartdays, false)){
			$periodStartdays[]	= '7';
		}

		// TODO if ($nights >= (int)$rental->rental_min_nights && $nights <= (int)$rental->rental_max_nights) {

		$periods 	= [];
		$prices 	= [];
		if(in_array($startDow, $periodStartdays, false)){
			$periods[]	 = $rentalPeriod;
			$possible[]	 = $periods;
			$nightsLeft	-= $rentalPeriod->period_nights;

			if($nightsLeft > 0 && $rentalPeriod->period_repeat === 'ja' && $rentalPeriod->period_nights > 0){
				while($nightsLeft >= $rentalPeriod->period_nights){
					$periods[]	 = $rentalPeriod;
					$possible[]	 = $periods;
					$nightsLeft	-= $rentalPeriod->period_nights;
				}
			}

			if($nightsLeft > 0 && !empty($rentalPeriod->period_expandable)){
				/** @var $expandablePeriods RecreationRentalPeriod[] */
				$expendablePeriods = explode(',',$rentalPeriod->period_expandable);
				if (!empty($expendablePeriods)) {
					$missing = array_diff($expendablePeriods, array_keys(static::$periods));
					if (!empty($missing)) {
						static::$periods = ArrayHelper::merge(
							RecreationRentalPeriod::find()->indexBy('period_id')->cache()->andWhere(['period_id' => $missing])->all(),
							static::$periods
						);
					}

					foreach ($expendablePeriods as $expandablePeriodId) {
						$expandablePeriod = static::$periods[$expandablePeriodId];

						if ($nightsLeft >= $expandablePeriod->period_startdays && $expandablePeriod->period_nights > 0) {
							$currentDow	= ($startDow + ($max_nights - $nightsLeft)) % 7;

							$expandableDow	= explode(',', $expandablePeriod->period_startdays);
							if(in_array('0', $expandableDow, false)){
								$expandableDow[]	= '7';
							}

							if (in_array($currentDow, $expandableDow, false)) {
								if ($expandablePeriod->period_repeat === 'ja') {
									while ($nightsLeft >= $expandablePeriod->period_nights) {
										$periods[]	 = $expandablePeriod;
										$possible[]	 = $periods;
										$nightsLeft	-= $expandablePeriod->period_nights; // TODO
									}
								} else {
									$periods[]	 = $expandablePeriod;
									$possible[]	 = $periods;
									$nightsLeft	-= $expandablePeriod->period_nights;
								}
							}
						}
					}
				}
			}
		}

		unset($periods);

		return $possible;

	}

	/**
	 * Retreive first available object
	 *
	 * @path /recreation/booking/first-available
	 * @method get
	 * @tag booking
	 * @tag objecttypes
	 * @tag rentaltypes
	 * @tag compositions
	 * @tag facilities
	 * @security default
	 * @param integer $type_id
	 * @parameter int64 $type_id Objecttype id
	 * @constraint minimum $type_id 1
	 * @param string $arrival Arrival date
	 * @parameter date-time $arrival Arrival date
	 * @param string $arrival Departure date
	 * @parameter date-time $departure Departure
	 * @param integer[] $rental_types Rental types
	 * @optparameter int64[] $rental_types Rental types
	 * @param RecreationEventsComposition[] $compositions Composition of guests
	 * @optparameter string $compositions Composition of guests TODO
	 * @return integer successful operation
	 * @errors 400 No objecttype received
	 * @errors 400 Invalid arrival date
	 * @errors 400 Invalid depature date
	 * @errors 404 Not found
	 */
	// TODO optparameter compositions type
	public function actionFirstAvailable($type_id, $arrival, $departure, $rental_types = [], $compositions = []) {
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
	 * @tag objects
	 * @security default
	 * @param integer $object_id
	 * @parameter int64 $object_id Object id
	 * @constraint minimum $object_id 1
	 * @param string $arrival Arrival date
	 * @parameter date-time $arrival Arrival date
	 * @param string $arrival Departure date
	 * @parameter date-time $departure Departure
	 * @optparameter int64 $package Package id
	 * @param integer $minutes Minutes
	 * @optparameter int64 $minutes Minutes
	 * @constraint minimum $minutes 1
	 * @return integer successful operation
	 * @errors 400 Invalid arrival date, Invalid depature date, Option/block not available, Invalid amount of minutes
	 * @errors 404 Object not found
	 * @errors 500 No rentaltype available, Cant save option
	 */
	public function actionBlock($object_id, $arrival, $departure, $minutes = 60, $package = null) {
		$user	= EO::$app->user;
		$arrivalDate 	= new DateTime($arrival);
		$departureDate	= new DateTime($departure);
		$now			= new DateTime();

		if (empty($arrivalDate) || $arrivalDate < $now) {
			throw new BadRequestHttpException('Invalid arrival date');
		}

		if (empty($departureDate) || $departureDate < $arrivalDate) {
			throw new BadRequestHttpException('Invalid depature date');
		}

		$optionState  = RecreationEventsState::find()->where(['state_type' => 'offerte'])->one();
		if (empty($optionState)) {
			throw new BadRequestHttpException('Option/block not available');
		}

		$relation_id = 0;
		if (!empty($user->identity->relationEo)) {
			$relation_id = $user->identity->relationEo->relation_id;
			if (empty($relation_id)) {
				throw new ServerErrorHttpException('No defaultuser set');
			}
		}


		$object		= RecreationObject::find()->where(['object_id' => $object_id])->one();
		if (empty($object)) {
			throw new NotFoundHttpException('Object not found');
		}

		if (empty($object->rentalConnection)) {
			throw new ServerErrorHttpException('No rentaltype available');
		}

		$type		= RecreationObjectType::find()->where(['type_id' => $object->type_id])->one();
		if (empty($type)) {
			throw new NotFoundHttpException('Object type not found');
		}

		if (!empty($package)) {
			$eventPackage = RecreationPackage::findOne($package);
			if (empty($eventPackage)) {
				throw new BadRequestHttpException('Package not found');
			}
		}

		if ($minutes <= 0 || $minutes > 9999 || !is_numeric($minutes)) {
			throw new BadRequestHttpException('Invalid amount of minutes');
		}

		$totalNights			= (int)$arrivalDate->diff($departureDate)->format('%R%a');

		if (!$object->isAvailable($arrivalDate, $totalNights)) {
			throw new BadRequestHttpException('Object not available');
		}

		$checkin	= $type->type_checkin;
		$checkout	= $type->type_checkout;
		if(!empty($eventPackage) && !empty($eventPackage->package_checkin) && !empty($eventPackage->package_checkout)){
			$checkin	= $eventPackage->package_checkin;
			$checkout	= $eventPackage->package_checkout;
		}

		$expire = clone $now;
		$expire->add(new DateInterval('PT'.$minutes.'M'));
		$event = new RecreationEvents();
		$event->company_id						= EO::param('company_id');
		$event->object_id						= $object_id;
		$event->rental_id						= $object->rentalConnection[0]->rental_id;  // TODO
		$event->relation_id						= $relation_id;
		$event->state_id						= $optionState->state_id;
		$event->event_reservation_nr			= RecreationEvents::nextReservationNr($arrivalDate);
		$event->event_arrivaldate				= $arrivalDate->format('Y-m-d').' '.$checkin;
		$event->event_departuredate				= $departureDate->format('Y-m-d').' '.$checkout;
		$event->event_amount_persons			= 1;
		$event->event_objectprice_exclusive		= 0;
		$event->event_objectprice_vat			= 0;
		$event->event_objectprice_manual		= 'ja';
		$event->event_blockdate					= $expire->format('Y-m-d H:i:s');
		$event->event_createdate				= new Expression('NOW()');
		$event->event_createuser				= $user->identity->user_id;


		if (!$event->save()) {
			throw new ServerErrorHttpException('Cant save option '.print_r($event->getErrors(), true));
		} else {
//			RecreationEventsLog::fromMessage('Reservering geblokkeerd')->save();
		}

		return $event->event_id; // TODO
	}


	/**
	 * Retreive end blocking object
	 *
	 * @path /recreation/booking/block/{block_id}/cancel
	 * @method get
	 * @tag booking
	 * @security default
	 * @param integer $block_id
	 * @parameter int64 $block_id Block id
	 * @constraint minimum $block_id 1
	 * @return boolean successful operation
	 * @errors 400 Invalid input
	 * @errors 404 Not found
	 */
	public function actionBlockCancel($block_id) {
		$event = RecreationEvents::find()->where(['event_id' => $block_id])->one();

		if (empty($event)) {
			throw new NotFoundHttpException('Kon optie/blokkering niet vinden');
		}

		$optionState  = RecreationEventsState::find()->where(['state_type' => 'offerte'])->one();
		if (empty($optionState)) {
			throw new BadRequestHttpException('Optie/blokkering niet beschikbaar');
		}

		$cancelState  = RecreationEventsState::find()->where(['state_type' => 'vervallen'])->one();
		if (empty($cancelState)) {
			throw new BadRequestHttpException('Optie/blokkering niet beschikbaar');
		}

		if ($event->state_id === $cancelState->state_id) {
			throw new BadRequestHttpException('Optie/blokkering was al geannuleerd');
		}

		if ($event->state_id === $optionState->state_id) {
			$event->state_id = $cancelState->state_id;
		}

		if (!$event->save()) {
			throw new ServerErrorHttpException('Cant save option'.print_r($event->getErrors(), true));
		}

		return true;
	}


	/**
	 * Retreive booking products
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
	public function actionProducts($type_id) {
		throw new NotAcceptableHttpException('TODO');
	}

};