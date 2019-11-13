<?php

namespace app\models;

use Yii;
use \yii\db\ActiveRecord;

/**
 * This is the model class for table "request_video_user".
 *
 * @property int $user_id
 * @property string $filename
 * @property int $status_id
 *
 * @property User user
 *
 * @property string $new_name
 * @property int $hours
 * @property int $minutes
 * @property string $duration
 * @property int $total_minutes
 */
class RequestVideoUser extends ActiveRecord
{

    const STATUS_NEW = 0;
    const STATUS_OK = 1;
    const STATUS_ERROR = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'request_video_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'filename'], 'required'],
            [['user_id', 'status_id', 'hours', 'minutes', 'total_minutes'], 'integer'],
            [['filename'], 'string', 'max' => 50],
            [['new_name', 'duration'], 'string', 'max' => 250],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'filename' => 'Filename',
            'status_id' => 'Status ID',
            'new_name' => 'New Name',
            'hours' => 'Hours',
            'minutes' => 'Minutes',
            'duration' => 'Duration',
            'total_minutes' => 'Total Minutes',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}