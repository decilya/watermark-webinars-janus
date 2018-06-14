<?php

namespace app\models;

use Yii;
use \yii\db\ActiveRecord;

/**
 * This is the model class for table "chat".
 *
 * @property int $id
 * @property int $created_at
 * @property int $user_id
 * @property int $course_id
 * @property string $text
 * @property string $room_id
 *
 * @property User user
 */
class Chat extends ActiveRecord
{
    /** Сигнал для перезагрузки страницы пользователя */
    public $systemMessage = [
        'SYSTEM_RELOAD_CLIENT_PAGE' => "SYSTEM_reload_SYSTEM",
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'chat';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'text', 'course_id'], 'required'],
            [['created_at', 'user_id', 'course_id'], 'integer'],
            [['text'], 'string', 'max' => 500],
            [['room_id'], 'string', 'max' => 88],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'user_id' => Yii::t('app', 'User ID'),
            'course_id' => Yii::t('app', 'Course ID'),
            'text' => Yii::t('app', 'Text'),
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->created_at = time();

        return true;
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Взять все сообщения комнаты, начиная с определенной даты
     *
     * @param int $date
     * @param int $courseId
     *
     * @return Chat[]
     */
    public static function getChatByDate($date, $courseId)
    {
        return Chat::find()
            ->where('created_at > :date', ['date' => $date])
            ->andWhere(['course_id' => $courseId])
            ->orderBy(['created_at' => SORT_ASC])
            ->all();
    }

    public function isNotSystem($text)
    {
        foreach ($this->systemMessage as $oneSysMessage) {
            if ($oneSysMessage == $text) return false;
        }

        return true;
    }

}