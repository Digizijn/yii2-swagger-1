<?php

use eo\base\EO;

return [
	'company_id'				=> 0,
	'digifinance_url'			=> defined(YII_DEBUG) && YII_DEBUG ? 'http://dev.digifinance.nl/betalen' : 'http://www.digifinance.nl/betalen',	//url van DigiFinace
	'eo_url'					=> 'https://www.everyoffice.nl/cmspanel/',	//url van EveryOffice omgeving
	'eo_ws_url'					=> 'http://webservice.everyoffice.nl/soap',	//url van EveryOffice API omgeving
	'adminEmail'				=> 'eojunkmail@everyoffice.nl',		//Yii_debug mailadres

	'languages'                 => array('nl'),

	// Yii Mailer variables
	'YiiMailer'				=> require('mail.php'),

	'recreation_event_create_invoice' 		=> true,
	'recreation_event_create_invoice_final' => false,
	'bookFormNumberOfMonths'				=> 12,

	'eo_image_callback' => function($url) {
		if (stripos($url, 'images/static/') !== false) {
			if (stripos($url, 'images/static/website') !== false) {
				$url = str_replace('images/static/website', 'images/companyfiles', $url);
			}
		}

		$url = trim($url, '/');

		return EO::param('eo_url').$url;
	}

];
