<?php

return [
	'components'	=> [
		'db' => [
			'dsn' 					=> 'mysql:host=localhost;dbname=count-it-backup',
			'username' 				=> 'count-it-backup',
			'password' 				=> 'co2un8',
			'charset'				=> 'utf8',
			'enableSchemaCache' 	=> true,
			'schemaCacheDuration' 	=> 3600,
//            'schemaCache' 			=> 'redis',
			'schemaCache'         	=> 'cache',
			// schemaCacheExclude
			'enableQueryCache'		=> true,
			'queryCache'          	=> 'cache',
			'queryCacheDuration'	=> 60,
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
			'database' 	=> 0,
		],

		'response'	=> [
			'formatters' => [
				'json' => '\eo\base\helper\PrettyJsonResponseFormatter',
			],
		],
	]
];



//			'enableParamLogging'	=> true,
//			'enableProfiling'       => true,