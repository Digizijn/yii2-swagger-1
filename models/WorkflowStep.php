<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "workflow_step".
 *
 * @property integer $step_id
 * @property integer $step_left
 * @property integer $step_right
 */
class WorkflowStep extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'workflow_step';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['step_left', 'step_right'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'step_id' => Yii::t('app', 'Step ID'),
            'step_left' => Yii::t('app', 'Step Left'),
            'step_right' => Yii::t('app', 'Step Right'),
        ];
    }
}
