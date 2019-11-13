<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "lessons".
 *
 * @property int $id
 * @property int $user_id
 * @property string $record_name
 * @property int $room_id
 * @property int $created_at
 * @property int $finished_at
 *
 * @property Records[] $records
 */
class Lessons extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'lessons';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'record_name', 'room_id', 'created_at', 'finished_at'], 'required'],
            [['user_id', 'room_id', 'created_at', 'finished_at'], 'integer'],
            [['record_name'], 'string', 'max' => 125],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'record_name' => 'Record Name',
            'room_id' => 'Room ID',
            'created_at' => 'Created At',
            'finished_at' => 'Finished At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRecords()
    {
        return $this->hasMany(Records::className(), ['lesson_id' => 'id']);
    }
}