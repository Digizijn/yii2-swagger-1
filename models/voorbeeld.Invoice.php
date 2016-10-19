<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "invoices".
 *
 * @property integer $invoice_id
 * @property integer $user_id
 * @property integer $employee_id
 * @property integer $company_id
 * @property integer $parent_id
 * @property integer $project_id
 * @property integer $part_id
 * @property integer $sub_id
 * @property integer $type_id
 * @property integer $payment_id
 * @property integer $payment_item_id
 * @property integer $car_id
 * @property integer $order_id
 * @property integer $background_id
 * @property integer $language_id
 * @property integer $mailing_id
 * @property integer $relation_id
 * @property integer $relation_contact_id
 * @property integer $transaction_id
 * @property integer $invoice_relationsender
 * @property integer $debtor_id
 * @property string $debtor_company
 * @property string $debtor_department
 * @property string $debtor_title
 * @property string $debtor_initial
 * @property string $debtor_secname
 * @property string $debtor_lastname
 * @property string $debtor_street
 * @property string $debtor_streetnr
 * @property string $debtor_zip
 * @property string $debtor_place
 * @property string $debtor_country
 * @property string $debtor_street_home
 * @property string $debtor_streetnr_home
 * @property string $debtor_zip_home
 * @property string $debtor_place_home
 * @property string $debtor_country_home
 * @property string $sender_company
 * @property string $sender_title
 * @property string $sender_initial
 * @property string $sender_secname
 * @property string $sender_lastname
 * @property string $sender_street
 * @property string $sender_streetnr
 * @property string $sender_zip
 * @property string $sender_place
 * @property string $sender_country
 * @property integer $car_mileage
 * @property string $invoice_credit
 * @property integer $invoice_facnr
 * @property string $invoice_purchasingnr
 * @property string $invoice_reference
 * @property string $invoice_date
 * @property string $invoice_reportdate
 * @property integer $invoice_periode
 * @property string $invoice_payment
 * @property integer $invoice_paymentterm
 * @property string $invoice_paymentterm_text
 * @property string $invoice_paymentstate
 * @property integer $invoice_splitfacture
 * @property integer $invoice_splitfacture_days
 * @property string $invoice_splitfacture_type
 * @property string $invoice_feature
 * @property string $invoice_mailed
 * @property string $invoice_splitbtw
 * @property integer $invoice_booktime
 * @property string $invoice_layout_background
 * @property string $invoice_layout_blanco_background
 * @property string $invoice_layout_displaydebnr
 * @property string $invoice_layout_relationtext
 * @property string $invoice_display_specification
 * @property string $invoice_signature
 * @property string $invoice_state
 * @property string $invoice_yearclosed
 */
class Invoice extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'invoices';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'employee_id', 'company_id', 'parent_id', 'project_id', 'part_id', 'sub_id', 'type_id', 'payment_id', 'payment_item_id', 'car_id', 'order_id', 'background_id', 'language_id', 'mailing_id', 'relation_id', 'relation_contact_id', 'transaction_id', 'invoice_relationsender', 'debtor_id', 'car_mileage', 'invoice_facnr', 'invoice_periode', 'invoice_paymentterm', 'invoice_splitfacture', 'invoice_splitfacture_days', 'invoice_booktime'], 'integer'],
            [['employee_id', 'parent_id', 'sub_id', 'payment_id', 'order_id', 'background_id', 'mailing_id', 'relation_id', 'relation_contact_id', 'transaction_id', 'invoice_relationsender', 'debtor_company', 'debtor_department', 'debtor_title', 'debtor_initial', 'debtor_secname', 'debtor_lastname', 'debtor_street', 'debtor_streetnr', 'debtor_zip', 'debtor_place', 'debtor_country', 'debtor_street_home', 'debtor_streetnr_home', 'debtor_zip_home', 'debtor_place_home', 'debtor_country_home', 'sender_company', 'sender_title', 'sender_initial', 'sender_secname', 'sender_lastname', 'sender_street', 'sender_streetnr', 'sender_zip', 'sender_place', 'sender_country', 'car_mileage', 'invoice_purchasingnr', 'invoice_reportdate', 'invoice_paymentterm', 'invoice_paymentterm_text', 'invoice_feature', 'invoice_booktime', 'invoice_signature', 'invoice_state'], 'required'],
            [['invoice_credit', 'invoice_payment', 'invoice_splitfacture_type', 'invoice_mailed', 'invoice_splitbtw', 'invoice_layout_displaydebnr', 'invoice_display_specification', 'invoice_signature', 'invoice_state', 'invoice_yearclosed'], 'string'],
            [['invoice_date', 'invoice_reportdate'], 'safe'],
            [['debtor_company', 'invoice_purchasingnr', 'invoice_reference'], 'string', 'max' => 100],
            [['debtor_department', 'debtor_country', 'debtor_country_home', 'sender_country', 'invoice_layout_blanco_background'], 'string', 'max' => 150],
            [['debtor_title', 'debtor_secname', 'debtor_streetnr', 'debtor_streetnr_home', 'sender_title', 'sender_secname', 'sender_streetnr'], 'string', 'max' => 15],
            [['debtor_initial', 'debtor_zip', 'debtor_zip_home', 'sender_initial', 'sender_zip'], 'string', 'max' => 10],
            [['debtor_lastname', 'debtor_street', 'debtor_place', 'debtor_street_home', 'debtor_place_home', 'sender_company', 'sender_lastname', 'sender_street', 'sender_place', 'invoice_layout_background', 'invoice_layout_relationtext'], 'string', 'max' => 50],
            [['invoice_paymentterm_text'], 'string', 'max' => 250],
            [['invoice_paymentstate'], 'string', 'max' => 45],
            [['invoice_feature'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'invoice_id' => Yii::t('app', 'Invoice ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'employee_id' => Yii::t('app', 'Employee ID'),
            'company_id' => Yii::t('app', 'Company ID'),
            'parent_id' => Yii::t('app', 'Parent ID'),
            'project_id' => Yii::t('app', 'Project ID'),
            'part_id' => Yii::t('app', 'Part ID'),
            'sub_id' => Yii::t('app', 'Sub ID'),
            'type_id' => Yii::t('app', 'Type ID'),
            'payment_id' => Yii::t('app', 'Payment ID'),
            'payment_item_id' => Yii::t('app', 'Payment Item ID'),
            'car_id' => Yii::t('app', 'Car ID'),
            'order_id' => Yii::t('app', 'Order ID'),
            'background_id' => Yii::t('app', 'Background ID'),
            'language_id' => Yii::t('app', 'Language ID'),
            'mailing_id' => Yii::t('app', 'Mailing ID'),
            'relation_id' => Yii::t('app', 'Relation ID'),
            'relation_contact_id' => Yii::t('app', 'Relation Contact ID'),
            'transaction_id' => Yii::t('app', 'Transaction ID'),
            'invoice_relationsender' => Yii::t('app', 'Invoice Relationsender'),
            'debtor_id' => Yii::t('app', 'Debtor ID'),
            'debtor_company' => Yii::t('app', 'Debtor Company'),
            'debtor_department' => Yii::t('app', 'Debtor Department'),
            'debtor_title' => Yii::t('app', 'Debtor Title'),
            'debtor_initial' => Yii::t('app', 'Debtor Initial'),
            'debtor_secname' => Yii::t('app', 'Debtor Secname'),
            'debtor_lastname' => Yii::t('app', 'Debtor Lastname'),
            'debtor_street' => Yii::t('app', 'Debtor Street'),
            'debtor_streetnr' => Yii::t('app', 'Debtor Streetnr'),
            'debtor_zip' => Yii::t('app', 'Debtor Zip'),
            'debtor_place' => Yii::t('app', 'Debtor Place'),
            'debtor_country' => Yii::t('app', 'Debtor Country'),
            'debtor_street_home' => Yii::t('app', 'Debtor Street Home'),
            'debtor_streetnr_home' => Yii::t('app', 'Debtor Streetnr Home'),
            'debtor_zip_home' => Yii::t('app', 'Debtor Zip Home'),
            'debtor_place_home' => Yii::t('app', 'Debtor Place Home'),
            'debtor_country_home' => Yii::t('app', 'Debtor Country Home'),
            'sender_company' => Yii::t('app', 'Sender Company'),
            'sender_title' => Yii::t('app', 'Sender Title'),
            'sender_initial' => Yii::t('app', 'Sender Initial'),
            'sender_secname' => Yii::t('app', 'Sender Secname'),
            'sender_lastname' => Yii::t('app', 'Sender Lastname'),
            'sender_street' => Yii::t('app', 'Sender Street'),
            'sender_streetnr' => Yii::t('app', 'Sender Streetnr'),
            'sender_zip' => Yii::t('app', 'Sender Zip'),
            'sender_place' => Yii::t('app', 'Sender Place'),
            'sender_country' => Yii::t('app', 'Sender Country'),
            'car_mileage' => Yii::t('app', 'Car Mileage'),
            'invoice_credit' => Yii::t('app', 'Invoice Credit'),
            'invoice_facnr' => Yii::t('app', 'Invoice Facnr'),
            'invoice_purchasingnr' => Yii::t('app', 'Invoice Purchasingnr'),
            'invoice_reference' => Yii::t('app', 'Invoice Reference'),
            'invoice_date' => Yii::t('app', 'Invoice Date'),
            'invoice_reportdate' => Yii::t('app', 'Invoice Reportdate'),
            'invoice_periode' => Yii::t('app', 'Invoice Periode'),
            'invoice_payment' => Yii::t('app', 'Invoice Payment'),
            'invoice_paymentterm' => Yii::t('app', 'Invoice Paymentterm'),
            'invoice_paymentterm_text' => Yii::t('app', 'Invoice Paymentterm Text'),
            'invoice_paymentstate' => Yii::t('app', 'Invoice Paymentstate'),
            'invoice_splitfacture' => Yii::t('app', 'Invoice Splitfacture'),
            'invoice_splitfacture_days' => Yii::t('app', 'Invoice Splitfacture Days'),
            'invoice_splitfacture_type' => Yii::t('app', 'Invoice Splitfacture Type'),
            'invoice_feature' => Yii::t('app', 'Invoice Feature'),
            'invoice_mailed' => Yii::t('app', 'Invoice Mailed'),
            'invoice_splitbtw' => Yii::t('app', 'Invoice Splitbtw'),
            'invoice_booktime' => Yii::t('app', 'Invoice Booktime'),
            'invoice_layout_background' => Yii::t('app', 'Invoice Layout Background'),
            'invoice_layout_blanco_background' => Yii::t('app', 'Invoice Layout Blanco Background'),
            'invoice_layout_displaydebnr' => Yii::t('app', 'Invoice Layout Displaydebnr'),
            'invoice_layout_relationtext' => Yii::t('app', 'Invoice Layout Relationtext'),
            'invoice_display_specification' => Yii::t('app', 'Invoice Display Specification'),
            'invoice_signature' => Yii::t('app', 'Invoice Signature'),
            'invoice_state' => Yii::t('app', 'Invoice State'),
            'invoice_yearclosed' => Yii::t('app', 'Invoice Yearclosed'),
        ];
    }

    /**
     * @inheritdoc
     * @return InvoicesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new InvoicesQuery(get_called_class());
    }
}
