<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "workflow".
 *
 * @property integer $workflow_id
 * @property integer $company_id
 * @property string $workflow_name
 */
class Workflow extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'workflow';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id'], 'required'],
            [['company_id'], 'integer'],
            [['workflow_name'], 'string', 'max' => 75],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'workflow_id' => Yii::t('app', 'Workflow ID'),
            'company_id' => Yii::t('app', 'Company ID'),
            'workflow_name' => Yii::t('app', 'Workflow Name'),
        ];
    }
}
