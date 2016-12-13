<?php

return [
	'components'	=> [
		'db' => [
			'dsn' 					=> 'mysql:host=localhost;dbname=count-it-backup',
			'username' 				=> 'count-it-backup',
			'password' 				=> 'co2un8',
			'charset'				=> 'latin1',
			'enableSchemaCache' 	=> YII_CACHE,
			'schemaCacheDuration' 	=> 3600,
//            'schemaCache' 			=> 'redis',
			'schemaCache'         	=> 'cache',
			// schemaCacheExclude
			'enableQueryCache'		=> YII_CACHE,
			'queryCache'          	=> 'cache',
			'queryCacheDuration'	=> 60,
		],
	],
	'params' => [
		'eo_url'	=> 'https://dev.everyoffice.nl/cmspanel/',
		'eo_ws_url'	=> 'http://dev.webservice.everyoffice.nl/soap',	//url van EveryOffice API omgeving
	]
];



//			'enableParamLogging'	=> true,
//			'enableProfiling'       => true,