<?php

return [
	'components'	=> [
		'db' => [
			'dsn' 					=> 'mysql:host=devvps.digizijn.nl;dbname=count-it-test',
			'username' 				=> 'count-it-test',
			'password' 				=> 'bSic7!25',
			'enableSchemaCache' 	=> true,
		],

		'response'	=> [
			'formatters' => [
				'json' => '\eo\base\helper\PrettyJsonResponseFormatter',
			],
		],
	]
];