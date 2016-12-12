<?php

use eo\models\database\Cmsusers;
use yii\helpers\ArrayHelper;

$params = require(__DIR__ . '/params.php');


$config = ArrayHelper::merge(
	require('default.php'), [
		'id' 		=> 'api',
		'components' => [
			'request' => [
            	'class' => '\yii\web\Request',
				// !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
				'cookieValidationKey' => 'M0DIc86aXvsmJl8g3utCsGEkP-XR_YJ5',
            	'enableCookieValidation' => false,
				 'parsers' => [
					'application/json' => 'yii\web\JsonParser',
				]

			],

			'response' => [
				'format' => yii\web\Response::FORMAT_JSON,
				'charset' => 'UTF-8',
				'acceptMimeType' => 'application/json',

				'formatters' => [
					\yii\web\Response::FORMAT_HTML => [
						'class' => \yii\web\HtmlResponseFormatter::class,
					],
					\yii\web\Response::FORMAT_JSON => [
						'class' => \yii\web\JsonResponseFormatter::class,
						'prettyPrint' => !YII_ENV_PROD,
						'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
					],
					\yii\web\Response::FORMAT_JSONP => [
						'class' => \yii\web\JsonResponseFormatter::class,
						'prettyPrint' => !YII_ENV_PROD,
						'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
						'useJsonp' => true
					],
				],
			],

			'errorHandler' => [
//				'errorAction' => 'api/error',
			],

			'user' => [
				'loginUrl'			=> null,
				'enableSession'		=> false,
				'identityClass'		=> Cmsusers::class,
			],

			'urlManager'	=> [
				'enablePrettyUrl' => true,
				'enableStrictParsing' => true,
				'showScriptName' => false,

				'rules'	=> [
//					[
//						'class' => \yii\rest\UrlRule::class,
//						'controller' => [
//							'invoice',
//						],
//					],

					[
						'class' => \yii\rest\UrlRule::class,
						'controller' => 'order',
						'except' => ['delete', 'create', 'update']
					],
					[
						'class' => \yii\rest\UrlRule::class,
						'controller' => 'product',
						'except' => ['delete', 'create', 'update']
					],
					[
						'class' => \yii\rest\UrlRule::class,
						'controller' => ['invoices' => 'invoice'],
						'except' => ['delete', 'create', 'update'],
						'extraPatterns' => [
							'GET swagger.json' => 'documentation',
						],
					],
					[
						'class' => \yii\rest\UrlRule::class,
						'controller' => ['relations' => 'relation'],
						'except' => ['delete', 'create'],
						'extraPatterns' => [
							'POST /' => 'save',
							'POST /{id}/contacts' => 'contact-save',
							'GET swagger.json' => 'documentation',
						],
					],
					[
						'class' => \yii\rest\UrlRule::class,
						'controller' => ['transactions' => 'journal-transaction'],
						'except' => ['delete', 'create'],
						'extraPatterns' => [
							'POST /' => 'save',
							'GET swagger.json' => 'documentation',
						],
					],
					[
						'class' => \yii\rest\UrlRule::class,
						'controller' => ['recreation/objects' => 'recreation-object'],
						'except' => ['delete', 'create', 'update'],
						'extraPatterns' => [
							'GET swagger.json' => 'documentation',
						],
					],
					[
						'class' => \tunecino\nestedrest\UrlRule::class,
//						'controller' => ['facilities' => 'recreation-object-facilities'],
						'modelClass'  => \eo\models\database\RecreationObject::class,
						'resourceName' => 'recreation/objects',
						'relations' => [
							'objectType' 	=> ['objecttype' 		=> 'recreation-object-type'],
							'facilities' 	=> ['facilities' 		=> 'recreation-object-facilities'],
							'rentalPeriod' 	=> ['rentalperiods' 	=> 'recreation-rental-period'],
							'rentalType' 	=> ['rentaltypes' 		=> 'recreation-rental-type'],
						],
					],
					[
						'class' => \yii\rest\UrlRule::class,
						'controller' => ['objecttypes' => 'recreation-object-type'],
						'prefix'	=> 'recreation/',
						'except' => ['delete', 'create', 'update'],
						'extraPatterns' => [
							'GET swagger.json' => 'documentation',
						],
					],
					[
						'class' 		=> \tunecino\nestedrest\UrlRule::class,
						'modelClass'  	=> \eo\models\database\RecreationObjectType::class,
						'resourceName' 	=> 'recreation/objecttypes',
        				'relations' 	=> [
							'facilities' 	=> ['facilities' 	=> 'recreation-object-facilities'],
							'rentalTypes' 	=> ['rentaltypes' 	=> 'recreation-rental-type'],
							'objects' 		=> ['objects' 		=> 'recreation-object'],
        					'packages' 		=> ['packages' 		=> 'recreation-package'],
        					'composition' 	=> ['composition' 	=> 'recreation-composition'],
						],
					],
					[
						'class' 		=> \yii\rest\UrlRule::class,
						'controller' 	=> ['facilities' => 'recreation-object-facilities'],
						'prefix'		=> 'recreation/',
						'except' 		=> ['delete', 'create', 'update'],
						'extraPatterns' => [
							'GET swagger.json' => 'documentation',
						],
					],
					[
						'class' 		=> \tunecino\nestedrest\UrlRule::class,
						'modelClass'  	=> \eo\models\database\RecreationObjectFacility::class,
						'resourceName' 	=> 'recreation/facilities',
						'relations' 	=> [
							'objects' 		=> ['objects' 		=> 'recreation-object'],
							'objecttypes' 	=> ['objecttypes' 	=> 'recreation-object-type'],
							'product' 		=> ['product' 		=> 'product'],
						],
					],
					[
						'class' 		=> \yii\rest\UrlRule::class,
						'controller' 	=> ['rentaltypes' => 'recreation-rental-type'],
						'prefix'		=> 'recreation/',
						'except' 		=> ['delete', 'create', 'update'],
						'extraPatterns' => [
							'GET swagger.json' => 'documentation',
						],
					],
					[
						'class' 		=> \yii\rest\UrlRule::class,
						'controller' 	=> ['packages' => 'recreation-package'],
						'prefix'		=> 'recreation/',
						'except' 		=> ['delete', 'create', 'update'],
						'extraPatterns' => [
							'GET swagger.json' => 'documentation',
						],
					],
					[
						'class' 		=> \tunecino\nestedrest\UrlRule::class,
						'modelClass'  	=> \eo\models\database\RecreationPackage::class,
						'resourceName' 	=> 'recreation/packages',
						'relations' 	=> [
							'periods' 		=> ['periods' 		=> 'recreation-period'],
							'objectType' 	=> ['objecttypes' 	=> 'recreation-object-type'],
							'rentalType' 	=> ['rentaltype' 	=> 'recreation-rental-type'],
							'product' 		=> ['product' 		=> 'product'],
						],
					],
					[
						'class' 		=> \yii\rest\UrlRule::class,
						'controller' 	=> ['rentalperiods' => 'recreation-rental-period'],
						'prefix'		=> 'recreation/',
						'except' 		=> ['delete', 'create', 'update'],
						'extraPatterns' => [
							'GET swagger.json' => 'documentation',
						],
					],
					[
						'class' 		=> \tunecino\nestedrest\UrlRule::class,
						'modelClass'  	=> \eo\models\database\RecreationRentalPeriod::class,
						'resourceName' 	=> 'recreation/rentalperiods',
						'relations' 	=> [
							'rentalType' 	=> ['rentaltype' 	=> 'recreation-rental-type'],
							'product' 		=> ['product' 		=> 'product'],
						],
					],
					[
						'class' 		=> \yii\rest\UrlRule::class,
						'controller' 	=> ['periods' => 'recreation-period'],
						'prefix'		=> 'recreation/',
						'except' 		=> ['delete', 'create', 'update'],
						'extraPatterns' => [
							'GET swagger.json' => 'documentation',
						],
					],
					[
						'class' 		=> \yii\rest\UrlRule::class,
						'controller' 	=> ['compositions' => 'recreation-composition'],
						'prefix'		=> 'recreation/',
						'except' 		=> ['delete', 'create', 'update'],
						'extraPatterns' => [
							'GET swagger.json' => 'documentation',
						],
					],
					[
						'class' 		=> \yii\rest\UrlRule::class,
						'controller' 	=> ['floormaps' => 'recreation-floormap'],
						'prefix'		=> 'recreation/',
						'except' 		=> ['delete', 'create', 'update'],
						'extraPatterns' => [
							'GET swagger.json' => 'documentation',
						],
					],
					[
						'class' 		=> \yii\rest\UrlRule::class,
						'controller' 	=> ['booking' => 'recreation-booking'],
						'prefix'		=> 'recreation/',
						'except' 		=> ['delete', 'create', 'update'],
						'extraPatterns' => [
							'GET swagger.json' => 'documentation',
						],
					],
					[
						'class' 		=> \yii\rest\UrlRule::class,
						'controller' 	=> ['events/compositions' => 'recreation-event-composition'],
						'prefix'		=> 'recreation/',
						'except' 		=> ['delete'],
						'extraPatterns' => [
							'GET swagger.json' => 'documentation',
						],
					],
					[
						'class' 		=> \tunecino\nestedrest\UrlRule::class,
						'modelClass'  	=> \eo\models\database\RecreationEventsComposition::class,
						'resourceName' 	=> 'recreation/events/compositions',
						'relations' 	=> [
							'event' 	=> ['event' 	=> 'recreation-event'],
						],
					],
					[
						'class' 		=> \yii\rest\UrlRule::class,
						'controller' 	=> ['rentaltypes' => 'recreation-rental-type'],
						'prefix'		=> 'recreation/',
						'except' 		=> ['delete', 'create', 'update'],
						'extraPatterns' => [
							'GET swagger.json' => 'documentation',
						],
					],
					[
						'class' 		=> \tunecino\nestedrest\UrlRule::class,
						'modelClass'  	=> \eo\models\database\RecreationRentalType::class,
						'resourceName' 	=> 'recreation/rentaltypes',
						'relations' 	=> [
							'rentalPeriod' 	=> ['rentalperiods' => 'recreation-rental-period'],
							'object' 		=> ['objects' 		=> 'recreation-object'],
							'product' 		=> ['product' 		=> 'product'],
							'objectType' 	=> ['objecttype' 	=> 'recreation-object-type'],
						],
					],
					[
						'class' 		=> \yii\rest\UrlRule::class,
						'controller' 	=> ['eventstates' => 'recreation-event-state'],
						'prefix'		=> 'recreation/',
						'except' 		=> ['delete', 'create', 'update'],
						'extraPatterns' => [
							'GET swagger.json' => 'documentation',
						],
					],
					[
						'class'		 	=> \yii\rest\UrlRule::class,
						'controller' 	=> ['events' => 'recreation-event'],
						'prefix'		=> 'recreation/',
						'except' 		=> ['delete', 'create'],
						'extraPatterns' => [
							'POST /' 			=> 'save',
							'GET swagger.json' 	=> 'documentation',
						],
					],
					[
						'class' 		=> \tunecino\nestedrest\UrlRule::class,
						'modelClass'  	=> \eo\models\database\RecreationEvents::class,
						'resourceName' 	=> 'recreation/events',
						'relations' 	=> [
							'object' 		=> ['objects' 		=> 'recreation-object'],
							'invoices' 		=> ['invoices' 		=> 'invoices'],
							'products' 		=> ['products' 		=> 'product'],
							'package' 		=> ['package' 		=> 'recreation-package'],
							'rentalType' 	=> ['rentaltype' 	=> 'recreation-rental-type'],
							'houseguests' 	=> ['guests' 		=> 'relation'],
							'eventRelation' => ['relation' 		=> 'relation'],
						],
					],
					[
						'class' 		=> \yii\rest\UrlRule::class,
						'controller' 	=> ['products' => 'product'],
						'prefix'		=> '/',
						'except' 		=> ['delete', 'create', 'update'],
						'extraPatterns' => [
							'GET swagger.json' => 'documentation',
						],
					],
					[
						'class'		 	=> \yii\rest\UrlRule::class,
						'controller' 	=> ['mailingtypes' => 'mailing-type'],
						'except' 		=> ['delete', 'create'],
						'extraPatterns' => [
							'GET swagger.json' 	=> 'documentation',
						],
					],

					'recreation/booking/nights' 			=> 'recreation-booking/nights',
					'recreation/booking/availability' 		=> 'recreation-booking/availability',
					'recreation/booking/first-available' 	=> 'recreation-booking/first-available',
					'recreation/booking/pricing' 			=> 'recreation-booking/pricing',
					'recreation/booking/block' 				=> 'recreation-booking/block',
					'recreation/events/calculate' 			=> 'recreation-event/calculate',
					'recreation/booking/block/<block_id:\d+>/cancel' 	=> 'recreation-booking/block-cancel',
					'mailingtypes/<id:\d+>/subscribe'			=> 'mailing-type/subscribe',
					'mailingtypes/<id:\d+>/unsubscribe'		=> 'mailing-type/unsubscribe',

					'documentation' => 'swagger',
                	'test' => 'test',
					'documentation.json' => 'documentation',
					//]

//                	'swagger.json' => 'api/documentation',
				]
			],
		],

		'params' => $params,
	]
);


if (YII_ENV_DEV || YII_ENV_TEST || YII_ENV_PROD) {
	$configFile = __DIR__ .'/'. YII_ENV.'.php';
	if (file_exists($configFile) && is_readable($configFile)) {
		$config = ArrayHelper::merge(
			$config,
			require($configFile)
		);
	} else {
		throw new \yii\web\BadRequestHttpException($configFile.' bestaat niet of is niet leesbaar');
	}
} else {
	throw new \yii\web\BadRequestHttpException('Ongeldig omgeving DEV, TEST of PROD');
}


if (YII_DEBUG) {
	$configFile = __DIR__ .'/debug.php';
	if (file_exists($configFile) && is_readable($configFile)) {
		$config = ArrayHelper::merge(
			$config,
			require($configFile)
		);
	}
}

return $config;



//
//'<version:\d>/<controller:\w+>/<action:\w+>'
//
//While controller path was switched by the API module itself on “init()” method.
//
//public function init() {
//    if (isset($_GET['version'])) {
//        $apiVersion = $_GET['version'];
//        $apiControllerPaths = $this->getApiControllerPaths();
//        if (isset($apiControllerPaths[$apiVersion])) {
//            $controllerPath = Yii::getPathOfAlias($apiControllerPaths[$apiVersion]);
//            $this->setControllerPath($controllerPath);
//        }
//    }
//}
