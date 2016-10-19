<?php

return [
	'company_id'				=> 0,
	'digifinance_url'			=> defined(YII_DEBUG) && YII_DEBUG ? 'http://dev.digifinance.nl/betalen' : 'http://www.digifinance.nl/betalen',	//url van DigiFinace
	'eo_url'					=> 'https://www.everyoffice.nl/cmspanel/',	//url van EveryOffice omgeving
	'eo_ws_url'					=> 'http://webservice.everyoffice.nl/soap',	//url van EveryOffice API omgeving
	'adminEmail'				=> 'eojunkmail@everyoffice.nl',		//Yii_debug mailadres

	'languages'                 => array('nl'),

	// Yii Mailer variables
	'YiiMailer'				=> require('mail.php'),


];
