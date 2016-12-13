<?php
namespace app\controllers;
use app\models\User;
use eo\base\EO;
use eo\models\database\Cmsusers;
use eo\models\database\Countries;
use eo\models\database\CountryLanguages;
use eo\models\database\Relation;
use eo\models\database\RelationAdresses;
use eo\models\database\RelationType;
use eo\models\database\RelationTypeConnection;
use eo\models\database\StaffCompanies;
use eo\models\database\StaffCompaniesOptions;
use eo\models\database\StaffCompaniesOptionsTypes;
use yii\helpers\VarDumper;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;
use \DateTime;

/**
 * Relations
 *
 * Relations
 *
 * @definition Relation
 */
class RelationController extends Rest {
	public function init() {
		$this->modelClass =	Relation::className();
		$this->modelsNamespace 	=	'\eo\models\database';

		parent::init();
	}

	/**
	 * Retreive all relations
	 *
	 * @path /relations
	 * @method get
	 * @security default
	 * @tag relations
	 * @optparameter string[] $expand
	 * @enum $expand country language
	 * @return Relation[] successful operation
	 * @errors 405 Invalid input
	 */
	public function actionAll($expand = []) {}

	/**
	 * Retreive specific relation
	 *
	 * @path /relations/{id}
	 * @method get
	 * @tag relations
	 * @security default
	 * @param integer $id
	 * @parameter int64 $id Relation id to retreive
	 * @constraint minimum $id 1
	 * @optparameter string[] $expand
	 * @enum $expand country language
	 * @return Relation successful operation
	 * @errors 404 Relation not found
	 */
	public function actionOne($expand = []) {}


	/**
	 * Create relation
	 *
	 * @path /relations
	 * @method post
	 * @tag relations
	 * @security default
	 * @param integer $parent_id
	 * @optparameter int64 $parent_id Relation to create this relation under
	 * @constraint minimum $parent_id 1
	 * @param string $email
	 * @parameter string $email Email
	 * @param string $lastname
	 * @optparameter string $lastname Lastname
	 * @param string $prename
	 * @optparameter string $prename Prename
	 * @param string $firstname
	 * @optparameter string $firstname First name
	 * @param string $middlename
	 * @optparameter string $middlename Middle name
	 * @param string $lastname
	 * @optparameter string $lastname Last name
	 * @param string $street
	 * @optparameter string $street Street
	 * @param string $streetnumber
	 * @optparameter string $streetnumber Street house number
	 * @param string $postal
	 * @optparameter string $postal Postal or zip
	 * @param string $city
	 * @optparameter string $city City of residence
	 * @param string $country_code
	 * @optparameter string $country_code Country in ISO 3166-1-3
	 * @param string $language_code
	 * @optparameter string $language_code Language in ISO 639-3
	 * @param string $phone
	 * @optparameter string $phone Phone number
	 * @param string $mobile
	 * @optparameter string $mobile Mobile phone number
	 * @param string $dob
	 * @optparameter date-time $dob Date of birth
	 * @param string $gender
	 * @optparameter string $gender Gender
	 * @enum $gender man vrouw
	 * @optparameter boolean $makeUser Make User
	 * @param $makeUser boolean
	 * @return integer successful operation
	 * @errors 400 Could not create relation
	 * @errors 405 Invalid input
	 */
	public function actionSave($email, $parent_id=0, $lastname = '', $prename='de heer', $firstname='', $middlename='', $street = '', $streetnumber = '', $postal='', $city='', $country_code='NLD', $language_code='NLD', $phone='', $mobile='', $dob = '', $gender = '', $makeUser = true) {
		// Check parent relatie
		if ($parent_id > 0 && !Relation::find()->andWhere(['relation_id' => $parent_id])->exists()) {
			throw new BadRequestHttpException('Bovenliggende relatie bestaat niet');
		}

		// Check valide e-mail
		if (empty($email) || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
			throw new BadRequestHttpException('Ongeldige e-mail');
		}

		// Check geboortedatum
		$dobDate	= null;
		if (!empty($dob)) {
			$dobDate = DateTime::createFromFormat('Y-m-d', $dob);
			if (!$dobDate) {
				throw new BadRequestHttpException('Ongeldige geboortedatum');
			}
		}

		// Land opzoeken
		$country = Countries::find()->andWhere(['country_code_3166_1_3' => strtolower($country_code)])->one();
		if (empty($country)) {
			throw new BadRequestHttpException('Landcode onbekend');
		}

		// Taal opzoeken

		// Duits handmatig overschrijven, vanwege foute aanroep
		if ($language_code === 'deu') {
			$language_code = 'gem';
		}

		$language = CountryLanguages::find()->andWhere(['language_iso_639_3' => strtolower($language_code)])->one();
		if (empty($language)) {
			// TODO zoeken op basis van land
			throw new BadRequestHttpException('Taalcode onbekend');
		}

		switch($country->country_eumember){
			case 'ja':	$buitenlandseklant	= 'binnenEU';	break;
			case 'nee':	$buitenlandseklant	= 'buitenEU';	break;
			default:	$buitenlandseklant	= 'nee';		break;
		}

		// Aanspreekvorm bepalen
		$option		= StaffCompaniesOptions::find()->andWhere(['opt_name' => 'relation_salutation_type'])->one();
		$salutation_type = 'formeel';
		if(!empty($option->opt_text)){
			$salutation_type	= $option->opt_text;
		} else {
			$option				= StaffCompaniesOptionsTypes::find()->andWhere(['type_name' => 'relation_salutation_type'])->one();

			if (!empty($option)) {
				$salutation_type	= $option->type_default;
			}
		}

		// Debiteurnummer bepalen
		$debtor					= Relation::find()->select('MAX(CAST(debtor_nr AS UNSIGNED)) AS debtor_nr')->asArray()->one();
		if(!empty($debtor['debtor_nr'])){
			$debtor_nr 				= (int)$debtor['debtor_nr'] +1;
		} else {
			$debtor_nr				= 1001;
		}

		switch($gender){
			case 'man':		$titel	= Relation::TITEL_MALE;		break;
			case 'vrouw':	$titel	= Relation::TITEL_FEMALE;	break;
			default:		$titel	= Relation::TITEL_UNKNOWN;	break;
		}

		// Standaard betalingstermijn instellen
		$payment_id		= null;
		$option			= StaffCompaniesOptions::find()->andWhere(['opt_name' => 'payment_id'])->one();
		if(!empty($option->opt_text)){
			$payment_id		= (int)$option->opt_text;
		}

		$company_id				= (int)EO::param('company_id');

		$relation	= Relation::find()
			->andWhere(['relation_email' => $email])
			->orderBy('relation_id DESC')
			->one();
		$isNew	= false;
		if(empty($relation)){
			$relation 	= new Relation();
			$isNew		= true;
		}

		//velden die alleen bij toevoegen gezet moeten worden
		if($isNew){
			if($parent_id <= 0){
				$relation->debtor_nr				= (string)$debtor_nr;
			}

			if(!empty($payment_id)){
				$relation->payment_id			= $payment_id;
			}
			$relation->relation_soort			= 'particulier';
			$relation->relation_email 			= $email;
		}

		if(!empty($titel) || $isNew){
			$relation->relation_titel			= $titel;
		}
		if(!empty($prename) || $isNew){
			$relation->relation_aanhef_1		= $prename;
		}
		if(!empty($salutation_type) || $isNew){
			$relation->relation_aanhef_type		= $salutation_type;
		}
		if(!empty($firstname) || $isNew){
			$relation->relation_voornaam		= $firstname;
		}
		if(!empty($middlename) || $isNew){
			$relation->relation_tussenvoegsel	= $middlename;
		}
		if(!empty($lastname) || $isNew){
			$relation->relation_achternaam		= $lastname;
		}
		if(!empty($street) || $isNew){
			$relation->relation_straat			= $street;
		}
		if(!empty($streetnumber) || $isNew){
			$relation->relation_straatnummer	= (string)$streetnumber;
		}
		if(!empty($postal) || $isNew){
			$relation->relation_postcode		= $postal;
		}
		if(!empty($city) || $isNew){
			$relation->relation_plaats			= html_entity_decode($city);
		}
		if(!empty($country->country_name) || $isNew){
			$relation->relation_land			= utf8_encode($country->country_name);
			$relation->relation_land_id			= $country->country_id;
		}
		if(!empty($buitenlandseklant) || $isNew){
			$relation->relation_buitelandseklant= $buitenlandseklant;
		}
		if(!empty($language->language_id) || $isNew){
			$relation->language_id				= $language->language_id;
		}
		if(!empty($phone) || $isNew){
			$relation->relation_telprive		= $phone;
		}
		if(!empty($mobile) || $isNew){
			$relation->relation_telmob			= $mobile;
		}
		if($dobDate || $isNew){
			$relation->relation_geboorte		= $dobDate ? $dobDate->format('Y-m-d') : '0000-00-00';
		}

		if (!$relation->save()) {
			throw new ServerErrorHttpException('Kon relatie niet opslaan '.print_r($relation->getErrors(), true));
		}

		//relatie debiteur connecties aanmaken
		if($isNew){

			if($parent_id <= 0){
				$relationTypeConnection				= new RelationTypeConnection;
				$relationTypeConnection->type_id	= 2;
				$relationTypeConnection->relation_id= $relation->relation_id;
				$relationTypeConnection->save();

				//zoeken op standaard relatietypes
				$defaultTypes	= RelationType::find()
					->andWhere([
						'company_id'	=> $company_id,
						'type_checked'	=> 'ja',
					])
					->all();
				foreach($defaultTypes as $defaultType){
					$relationTypeConnection				= new RelationTypeConnection;
					$relationTypeConnection->type_id	= $defaultType->type_id;
					$relationTypeConnection->relation_id= $relation->relation_id;
					$relationTypeConnection->save();
				}
			}

			//contactpersoon aanmaken
			if($parent_id > 0){
				if($parent_id != $relation->relation_id){

					$relationTypeConnection				= new RelationTypeConnection;
					$relationTypeConnection->type_id	= 5;
					$relationTypeConnection->relation_id= $relation->relation_id;
					$relationTypeConnection->save();

					$relationAddress					= new RelationAdresses;
					$relationAddress->relation_id		= $parent_id;
					$relationAddress->parent_id			= $relation->relation_id;
					$relationAddress->type_id			= 8;
					$relationAddress->adress_isdefault	= 'nee';

					if (!$relationAddress->save()) {
						throw new ServerErrorHttpException('Kon contactpersoon connectie niet opslaan '.print_r($relationAddress->getErrors(), true));
					}
				} else {
					throw new ServerErrorHttpException('Parent_id kan niet gelijk zijn aan de relation_id');
				}
			}
		}
		$makeUser = false;
		if($makeUser === true){

			$option			= StaffCompaniesOptions::find()->andWhere(['opt_name' => 'mail_text_register_user'])->one();
			if(!empty($option->opt_text)){
				$mailtext	= $option->opt_text;
				if($relation->user_id <= 0){

					$company	= StaffCompanies::find()
						->andWhere(['company_id' => $company_id])
						->with(['user'])
						->one();
					$style_id				= 0;
					if (!empty($company->user)) {
						$style_id				= $company->user->style_id;
					}
					$password	= Cmsusers::generatePassword();

					$user					= new Cmsusers;
					$user->style_id			= $style_id;
					$user->company_id		= $company_id;
					$user->user_name		= $email;
					$user->user_fullname	= implode(' ', [
						$relation->relation_voornaam,
						$relation->relation_tussenvoegsel,
						$relation->relation_achternaam,
					]);
					$user->user_pass		= md5($password);
					$user->user_level		= 'relation';
					$user->user_hash		= md5(uniqid(rand(),true));

					if (!$user->save()) {
						throw new ServerErrorHttpException('Kon gebruiker niet opslaan '.print_r($user->getErrors(), true));
					}

					//gebruiker aan de relatie koppelen
					$relation->user_id	= $user->user_id;
					if (!$relation->save()) {
						throw new ServerErrorHttpException('Kon de gebruiker niet aan de relatie koppelen '.print_r($relation->getErrors(), true));
					}

					//relatietype webshop controleren
					$relationType	= RelationType::find()->andWhere([
						'type_type' 	=> 'webshop',
						'type_active'	=> 'actief',
					])->one();
					if(empty($relationType)){
						$relationType					= new RelationType;
						$relationType->company_id		= $company_id;
						$relationType->type_type		= 'webshop';
						$relationType->type_name		= 'Webshop';
						$relationType->save();
					}
					$relationTypeID	= $relationType->type_id;

					//relatie webshop connecties aanmaken
					$relationTypeConnection				= new RelationTypeConnection;
					$relationTypeConnection->type_id	= $relationTypeID;
					$relationTypeConnection->relation_id= $relation->relation_id;
					$relationTypeConnection->save();

					//relatie externe gebruiker connecties aanmaken
					$relationTypeConnection				= new RelationTypeConnection;
					$relationTypeConnection->type_id	= 4;
					$relationTypeConnection->relation_id= $relation->relation_id;
					$relationTypeConnection->save();

					if(strlen($option->opt_mail_fromname) > 0){
						$mailFromName		= $option->opt_mail_fromname;
					} else {
						$mailFromName		= $company->company_name;
					}

					if(strlen($option->opt_mail_fromaddress) > 5 && filter_var($option->opt_mail_fromaddress,FILTER_VALIDATE_EMAIL)){
						$mailFromAddress	= $option->opt_mail_fromaddress;
					} else {
						$mailFromAddress	= $company->company_email_1;
					}

					$mailtext		= preg_replace('#\[username\]#i', $user->user_name, $mailtext);
					$mailtext		= preg_replace('#\[password\]#i', $password, $mailtext);
					$mailtext		= preg_replace('#\[login_url\]#i', '', $mailtext);

					$toAddresses	= [];
					$ccAddresses	= [];
					$bccAddresses	= [];

					if(YII_DEBUG){
						$toAddresses[] 	= EO::param('adminEmail');
					} else {
						$toAddresses[] 	= $relation->relation_email;
						if($option->opt_mail_copy == 'ja'){
							if($option->opt_mail_type == 'to'){
								$toAddresses[] 	= $option->opt_mail;
							} else if($option->opt_mail_type == 'cc'){
								$ccAddresses[] 	= $option->opt_mail;
							} else if($option->opt_mail_type == 'bcc'){
								$bccAddresses[] = $option->opt_mail;
							}
						}
						$bccAddresses[] = EO::param('adminEmail');
					}

					$mail	= EO::$app->mailer->compose()
						->setTo($toAddresses)
						->setCc($ccAddresses)
						->setBcc($bccAddresses)
						->setFrom([$mailFromAddress => $mailFromName])
						->setSubject($option->opt_value)
						->setHtmlBody($mailtext);

					if (!$mail->send()){
						throw new ServerErrorHttpException('Fout tijdens het verzenden van de mail '.print_r($mail->getErrors(), true));
					}
				}
			}
		}


		// TODO gelijk trekken met reservering, status 201 created teruggeven?

		return $relation;
	}


	/**
	 * Create contact under relation
	 *
	 * @path /relations/{id}/contacts
	 * @method post
	 * @tag relations
	 * @security default
	 * @param integer $id
	 * @optparameter int64 $id Relation to create this contact under
	 * @constraint minimum $id 1
	 * @param string $email
	 * @parameter string $email Email
	 * @param string $lastname
	 * @parameter string $lastname Lastname
	 * @param string $prename
	 * @optparameter string $prename Prename
	 * @param string $firstname
	 * @optparameter string $firstname First name
	 * @param string $middlename
	 * @optparameter string $middlename Middle name
	 * @param string $lastname
	 * @optparameter string $lastname Last name
	 * @param string $street
	 * @optparameter string $street Street
	 * @param string $streetnumber
	 * @optparameter string $streetnumber Street house number
	 * @param string $postal
	 * @optparameter string $postal Postal or zip
	 * @param string $city
	 * @optparameter string $city City of residence
	 * @param string $country_code
	 * @optparameter string $country_code Country in ISO 3166-1-3
	 * @param string $language_code
	 * @optparameter string $language_code Language in ISO 639-3
	 * @param string $phone
	 * @optparameter string $phone Phone number
	 * @param string $mobile
	 * @optparameter string $mobile Mobile phone number
	 * @param string $dob
	 * @optparameter date-time $dob Date of birth
	 * @param string $gender
	 * @optparameter string $gender Gender
	 * @enum $gender man vrouw
	 * @return integer successful operation
	 * @errors 400 Could not create contact
	 * @errors 405 Invalid input
	 */
	public function actionContactSave($id, $email, $lastname, $prename='de heer', $firstname='', $middlename='', $street = '', $streetnumber = '', $postal='', $city='', $country_code='NLD', $language_code='NLD', $phone='', $mobile='', $dob = '', $gender = '') {
		return $this->actionSave($email, $id, $lastname, $prename, $firstname, $middlename, $street, $streetnumber, $postal, $city, $country_code, $language_code, $phone, $mobile, $dob, $gender);
	}

	/**
	 * Update relation TODO
	 *
	 * @path /relations/{id}
	 * @method put
	 * @tag relations
	 * @security default
	 * @constraint minimum $id 1
	 * @param Relation $relation
	 * @parameter Relation $relation Relation to update
	 * @return integer successful operation
	 * @errors 400 Could not update relation
	 * @errors 404 Composition not found
	 * @errors 405 Invalid input
	 */
	public function actionUpdate() {}
}
