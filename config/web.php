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
			],

			'errorHandler' => [
//				'errorAction' => 'api/error',
			],

			'user' => [
				'loginUrl'			=> null,
				'enableSession'		=> false,
				'identityClass'		=> Cmsusers::className(),
			],

			'urlManager'	=> [
				'enablePrettyUrl' => true,
				'enableStrictParsing' => true,
				'showScriptName' => false,

				'rules'	=> [
					[
						'class' => 'yii\rest\UrlRule',
						'controller' => [
							'relation',
							'invoice',
						],
					],
					[
						'class' => 'yii\rest\UrlRule',
						'controller' => 'order',
						'except' => ['delete', 'create', 'update']
					],
					[
						'class' => 'yii\rest\UrlRule',
						'controller' => 'product',
						'except' => ['delete', 'create', 'update']
					],
					[
						'class' => 'yii\rest\UrlRule',
						'controller' => ['recreation/objects' => 'recreation-object'],
						'except' => ['delete', 'create', 'update'],
						'extraPatterns' => [
							'GET swagger.json' => 'documentation',
						],
					],
					[
						'class' => 'yii\rest\UrlRule',
						'controller' => ['objecttypes' => 'recreation-object-type'],
						'prefix'	=> 'recreation/',
						'except' => ['delete', 'create', 'update'],
						'extraPatterns' => [
							'GET swagger.json' => 'documentation',
						],
					],
					[
						'class' => 'tunecino\nestedrest\UrlRule',
//						'controller' => ['facilities' => 'recreation-object-facilities'],
						'modelClass'  => \eo\models\database\RecreationObjectType::className(),
        				'relations' => [
        					'facilities' => ['facilities' => 'recreation-object-facilities'],
        					'composition' => ['composition' => 'recreation-composition']
						],
						'resourceName' => 'recreation\/objecttypes',
//						'patterns' => [
//							'GET,HEAD {IDs}' => 'nested-view',
//						],
//						'tokens' => [ /* optional */
//							'{IDs}' => '<IDs:\\d[\\d,]*>',
//						],
					],
					[
						'class' => 'yii\rest\UrlRule',
						'controller' => ['facilities' => 'recreation-object-facilities'],
						'prefix'	=> 'recreation/',
						'except' => ['delete', 'create', 'update'],
						'extraPatterns' => [
							'GET swagger.json' => 'documentation',
						],
					],
					[
						'class' => 'yii\rest\UrlRule',
						'controller' => ['rentaltypes' => 'recreation-rental-type'],
						'prefix'	=> 'recreation/',
						'except' => ['delete', 'create', 'update'],
						'extraPatterns' => [
							'GET swagger.json' => 'documentation',
						],
					],
					[
						'class' => 'yii\rest\UrlRule',
						'controller' => ['packages' => 'recreation-package'],
						'prefix'	=> 'recreation/',
						'except' => ['delete', 'create', 'update'],
						'extraPatterns' => [
							'GET swagger.json' => 'documentation',
						],
					],
					[
						'class' => 'yii\rest\UrlRule',
						'controller' => ['rentalperiods' => 'recreation-rental-period'],
						'prefix'	=> 'recreation/',
						'except' => ['delete', 'create', 'update'],
						'extraPatterns' => [
							'GET swagger.json' => 'documentation',
						],
					],
					[
						'class' => 'yii\rest\UrlRule',
						'controller' => ['periods' => 'recreation-period'],
						'prefix'	=> 'recreation/',
						'except' => ['delete', 'create', 'update'],
						'extraPatterns' => [
							'GET swagger.json' => 'documentation',
						],
					],
					[
						'class' => 'yii\rest\UrlRule',
						'controller' => ['compositions' => 'recreation-composition'],
						'prefix'	=> 'recreation/',
						'except' => ['delete', 'create', 'update'],
						'extraPatterns' => [
							'GET swagger.json' => 'documentation',
						],
					],
					[
						'class' => 'yii\rest\UrlRule',
						'controller' => ['floormaps' => 'recreation-floormap'],
						'prefix'	=> 'recreation/',
						'except' => ['delete', 'create', 'update'],
						'extraPatterns' => [
							'GET swagger.json' => 'documentation',
						],
					],
					[
						'class' => 'yii\rest\UrlRule',
						'controller' => ['products' => 'product'],
						'prefix'	=> '/',
						'except' => ['delete', 'create', 'update'],
						'extraPatterns' => [
							'GET swagger.json' => 'documentation',
						],
					],

					[
						'class' => 'yii\rest\UrlRule',
						'controller' => ['booking' => 'recreation-booking'],
						'prefix'	=> 'recreation/',
						'except' => ['delete', 'create', 'update'],
						'extraPatterns' => [
							'GET swagger.json' => 'documentation',
						],
					],[
						'class' => 'yii\rest\UrlRule',
						'controller' => ['events' => 'recreation-event'],
						'prefix'	=> 'recreation/',
						'except' => ['delete', 'create', 'update'],
						'extraPatterns' => [
							'GET swagger.json' => 'documentation',
						],
					],
					'recreation/booking/nights' 			=> 'recreation-booking/nights', // TODO samenvoegen met bovestaande regel
					'recreation/booking/availability' 		=> 'recreation-booking/availability', // TODO samenvoegen met bovestaande regel
					'recreation/booking/first-available' 	=> 'recreation-booking/first-available', // TODO samenvoegen met bovestaande regel
					'recreation/booking/pricing' 			=> 'recreation-booking/pricing', // TODO samenvoegen met bovestaande regel

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
