<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "organization".
 *
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $url
 */
class Organization extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'organization';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'email', 'phone', 'url'], 'required'],
            [['name', 'email', 'phone', 'url'], 'string', 'max' => 250],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('app', 'Название'),
            'email' => Yii::t('app', 'E-mail'),
            'phone' => Yii::t('app', 'Телефон'),
            'url' => Yii::t('app', 'URL'),
        ];
    }

}
