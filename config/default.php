<?php
$cookie_domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
if(substr($cookie_domain, 0, 4) == 'www.'){
	$cookie_domain	= substr($cookie_domain, 4);
}
$cookie_path   = '/';
if (YII_ENV_DEV) {
	$cookie_path   .= str_replace($_SERVER['DOCUMENT_ROOT'], '', getcwd());
}
$cookie_path	= '/'.trim($cookie_path, '/');

ini_set('session.cookie_domain', $cookie_domain);
ini_set('session.cookie_path', $cookie_path);


$params = require(__DIR__ . '/params.php');


return [
    'id' 		=> 'default',
    'basePath' 	=> dirname(__DIR__),
    'bootstrap' => ['log'],
    'language' => 'nl-NL',
    'sourceLanguage' => 'nl-NL',
//    'controllerNamespace' => '\\',
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'M0DIc86aXvsmJl8g3utCsGEkP-XR_YJ5',
        ],

        'filecache' => [
            'class' => \yii\caching\FileCache::className(),
        ],

        'user' => [
//			'class'				=> \eo\base\WebUser::classname,
            'identityClass' 	=> \eo\models\database\Cmsusers::className(),
			'loginUrl'			=> array('/user/default/inloggen'),
            'enableAutoLogin' 	=> false,
			'identityCookie'	=> array(
				'domain'			=> $cookie_domain,
				'autoRenewCookie'	=> false,
			),
        ],

        'errorHandler' => [
            'errorAction' => 'site/error',
        ],

        'mailer' => [
            'class' => \yii\swiftmailer\Mailer::className(),
            'useFileTransport' => YII_DEBUG,
        ],

		'log' => YII_ENV_PROD ?  [
            'traceLevel' => YII_DEBUG ? 3 : 0,
//			'flushInterval' => 1,
            'targets' => [
				[
					'class'			=> \yii\log\EmailTarget::className(),
					'exportInterval' => 1,
					'levels'		=> ['error','warning'],
					'except' => [
						'yii\web\HttpException:401',
						'yii\web\HttpException:404',
					],
					'prefix' => function ($message) {
						$user = Yii::$app->has('user', true) ? Yii::$app->get('user') : null;
						$userID = $user ? $user->getId(false) : '-';
						return "[$userID]";
					},
    				'logVars' => ['_SERVER', '_SESSION'],
					'message'		=> [
						'from'		=> 'error@digizijn.nl',
						'to'		=> 'error@digizijn.nl',
						'subject'	=> 'ERROR '.$_SERVER['SERVER_NAME'],
					]
				],
				[
					'class' 		=> \yii\log\FileTarget::className(),
					'levels' 		=> ['error', 'warning', 'info'],
					'categories'	=> ['response'],
					'logVars'		=> [],
					'logFile'		=> '@runtime/logs/response.log',
					'maxFileSize'	=> 1024 * 100,
					'maxLogFiles'	=> 10,
				],
				[
					'class' 		=> \yii\log\FileTarget::className(),
					'levels' 		=> ['error', 'warning'],
					'except' => [
						'yii\web\HttpException:404',
					],
				],
            ],
        ] : [],
        'db' => [
			'class' 				=> \yii\db\Connection::className(),
			'dsn' 					=> 'mysql:host=localhost;dbname=count-it',
			'username' 				=> 'count-it',
			'password' 				=> 'fm2804',
			'charset'				=> 'latin1',
			'enableSchemaCache' 	=> YII_CACHE,
            'schemaCacheDuration' 	=> 3600,
//            'schemaCache' 			=> 'redis',
            'schemaCache'         	=> 'cache',
			// schemaCacheExclude
			'enableQueryCache'		=> YII_CACHE,
            'queryCache'          	=> 'cache',
			'queryCacheDuration'	=> 60,
//			'enableParamLogging'	=> false,
//			'enableProfiling'       => false,
//			'emulatePrepare' 		=> true,
		],



		'cache' => [ // TODO Verplaatsen naar main config
			'class' => 'yii\redis\Cache',
		],

		'session' => [ // TODO Verplaatsen naar main config
			'class' => 'yii\redis\Session',
		],

		'redis' => [
			'class' 	=> 'yii\redis\Connection',
			'hostname' 	=> 'localhost',
			'port' 		=> 6379,
			'database' 	=> 0, // TODO serializer igbinary
		],


		'filecache'	=> [
			'class'		=> \yii\caching\FileCache::className()
		],


		'i18n' => [
			'translations' => [
				'*' 	=> [
					'class'		            => \eo\base\MessageSource::className(), // TODO TranslationEventHandler::handleMissingTranslation(MissingTranslationEvent $event)
					'on missingTranslation' => ['self', 'missing'],
					'sourceLanguage' 		=> 'nl-NL',
				]
			],
		],


        'urlManager' => [
			'class'				=> \eo\base\UrlManager::className(),
            'enablePrettyUrl' 	=> true,
    		'enableStrictParsing' => false,
            'showScriptName' 	=> false,
            'rules' 			=> [
            ],
        ],

//        'session' => [
//            'class' => 'yii\redis\Session',
//        ],

    ],
    'params' => $params,
];