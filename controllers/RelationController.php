<?php
namespace app\controllers;
use eo\models\database\Countries;
use eo\models\database\CountryLanguages;
use eo\models\database\Relation;
use eo\models\database\StaffCompanies;
use eo\models\database\StaffCompaniesOptions;
use eo\models\database\StaffCompaniesOptionsTypes;
use Faker\Provider\cs_CZ\DateTime;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

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
	 * @return integer successful operation
	 * @errors 400 Could not create relation
	 * @errors 405 Invalid input
	 */
	public function actionSave($email, $parent_id=0, $lastname = '', $prename='de heer', $firstname='', $middlename='', $street = '', $streetnumber = '', $postal='', $city='', $country_code='NLD', $language_code='NLD', $phone='', $mobile='', $dob = '', $gender = '') {
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
		$language = CountryLanguages::find()->andWhere(['language_iso_639_3' => strtolower($language_code)])->one();
		if (empty($language)) {
			// TODO zoeken op basis van land
			throw new BadRequestHttpException('Taalcode onbekend');
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

		$relation = new Relation();
		$relation->parent_id 				= $parent_id;
		$relation->debtor_nr				= (string)$debtor_nr;
		$relation->relation_titel			= $titel;

		if(!empty($payment_id)){
			$relation->payment_id			= $payment_id;
		}

		switch($country->country_eumember){
			case 'ja':	$buitenlandseklant	= 'binnenEU';	break;
			case 'nee':	$buitenlandseklant	= 'buitenEU';	break;
			default:	$buitenlandseklant	= 'nee';		break;
		}

		$relation->relation_soort			= 'particulier';
		$relation->relation_email 			= $email;
		$relation->relation_aanhef_1		= $prename;
		$relation->relation_aanhef_type		= $salutation_type;
		$relation->relation_voornaam		= $firstname;
		$relation->relation_tussenvoegsel	= $middlename;
		$relation->relation_achternaam		= $lastname;
		$relation->relation_straat			= $street;
		$relation->relation_straatnummer	= $streetnumber;
		$relation->relation_postcode		= $postal;
		$relation->relation_plaats			= $city;
		$relation->relation_land			= utf8_encode($country->country_name);
		$relation->relation_land_id			= $country->country_id;

		$relation->relation_buitelandseklant= $buitenlandseklant;
		$relation->language_id				= $language->language_id;
		$relation->relation_telprive		= $phone;
		$relation->relation_telmob			= $mobile;
		$relation->relation_geboorte		= $dobDate ? $dobDate->format('Y-m-d') : '0000-00-00';

		if (!$relation->save()) {
			throw new ServerErrorHttpException('Kon relatie niet opslaan '.print_r($relation->getErrors(), true));
		}

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
