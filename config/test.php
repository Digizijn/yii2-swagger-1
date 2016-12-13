<?php

return [
	'components'	=> [
		'db' => [
			'dsn' 					=> 'mysql:host=devvps.digizijn.nl;dbname=count-it-test',
			'username' 				=> 'count-it-test',
			'password' 				=> 'bSic7!25',
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
		'eo_url'	=> 'https://test.everyoffice.nl/cmspanel/',
		'eo_ws_url'	=> 'http://test.webservice.everyoffice.nl/soap',
	]
];