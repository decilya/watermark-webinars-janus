<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "records".
 *
 * @property int $id
 * @property string $filename
 * @property string $new_name
 * @property int $duration
 * @property int $total_minutes
 * @property int $hours
 * @property int $minutes
 * @property int $status_build
 * @property int $build_id
 * @property int $user_id
 * @property int $room_id
 * @property int $created_at
 * @property int $finished_at
 * @property int $lesson_id
 *
 */
class Records extends \yii\db\ActiveRecord
{
    public $status;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'records';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['filename', 'duration'], 'required'],
            [['total_minutes', 'hours', 'minutes', 'status_build', 'room_id', 'user_id', 'created_at', 'finished_at', 'lesson_id'], 'integer'],
            [['filename', 'new_name'], 'string', 'max' => 250],
            [['duration'], 'string', 'max' => 88],
            ['build_id', 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'filename' => 'Filename',
            'new_name' => 'New Name',
            'duration' => 'Duration',
            'total_minutes' => 'Total Minutes',
            'hours' => 'Hours',
            'minutes' => 'Minutes',
            'room_id' => 'Room',
            'lesson_id' => 'ID урока'
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (($this->new_name == null) || ($this->new_name == '')) {
            $this->new_name = 'Трансляция от ' . str_replace('.webm', '', str_replace('screen_', '', $this->filename));
        }

        $this->total_minutes = $this->duration / 60;
        $this->hours = round(floor($this->total_minutes / 60));
        $this->minutes = round($this->total_minutes - ($this->hours * 60)); // Получаем оставшиеся минуты

        return true;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Вернуть максимальное значение build_id на текущий момент
     *
     * @return int
     *
     * @author Ilya <ilya.v87v@gmail.com>
     * @data 18.10.2019
     */
    public static function getMaxBuildId(): int
    {
        /** @var Records $record */
        $record = Records::find()->orderBy(['build_id' => SORT_DESC])->one();

        return (int)$record->build_id;
    }


}