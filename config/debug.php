<?php

return [
    'bootstrap' => [
		'debug',
		'gii'
	],

    'modules' => [
		'debug' => [
        	'class' => \yii\debug\Module::className(),
			'allowedIPs' => ['127.0.0.1', '::1', '192.168.0.*', '217.100.45.50']
    	],
		'gii'	=> [
			'class' => yii\gii\Module::className(),
    		'allowedIPs' => ['127.0.0.1', '::1', '192.168.0.*', '217.100.45.50']
		],
	],
	
	'components'	=> [
		'db'	=> [
			'enableSchemaCache' 	=> YII_CACHE,
		],
	],
];
