<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "server".
 *
 * @property int $id
 * @property string $course
 *
 * @property string $opaqueIdCamera
 * @property string $currentRoomCamera
 *
 * @property string $opaqueIdScreen
 * @property string $currentRoomScreen
 * @property int $on
 * @property int restart
 */
class Server extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'server';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['currentRoomCamera', 'currentRoomScreen'], 'required'],
            [['course', 'opaqueIdCamera', 'opaqueIdScreen'], 'string', 'max' => 250],
            [['currentRoomCamera', 'currentRoomScreen'], 'string', 'max' => 120],
            [['on', 'restart'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'course' => 'Course',
            'opaqueIdCamera' => 'Opaque Id Camera',
            'currentRoomCamera' => 'Current Room Camera',
            'opaqueIdScreen' => 'Opaque Id Screen',
            'currentRoomScreen' => 'Current Room Screen',
        ];
    }
}