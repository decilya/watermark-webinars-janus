<?php

namespace app\models;

use app\components\validators\CustomEmailValidator;
use app\controllers\SiteController;
use app\models\traits\CheckForm;
use Yii;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "clients".
 *
 * @property int $id
 * @property string $username
 *
 * @property string $name
 * @property string $patronymic
 * @property string $surname
 *
 * @property string $phone
 * @property string $email
 * @property string $description
 *
 * @property int $status_id
 *
 * @property int $type
 * @property int $record_time_min
 * @property int $hours
 * @property int $minutes
 *
 * @property string $created_at
 * @property string $updated_at
 * @property int last_updated_user_id
 *
 * @property string $oldPassword;
 * @property string $password;
 * @property string $rePassword;
 *
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property int $isLogin
 *
 * @property string $hash
 *
 */
class User extends ActiveRecord implements IdentityInterface
{
    use CheckForm;

    const STATUS_NEW = 1;
    const STATUS_ACTIVE = 2;
    const STATUS_CLOSE = 3;

    const TYPE_USER_ADMIN = 1;
    const TYPE_USER_MANAGER = 2;
    const TYPE_USER_MASTER = 3;
    const TYPE_USER_STUDENT = 4;

    public $oldPassword;
    public $password;
    public $rePassword;

    public $hours;
    public $minutes;

    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'name', 'patronymic', 'surname'], 'required', 'message' => 'Это обязательное поле.'],
            [['email', 'name', 'patronymic', 'surname', 'username'], 'trim'],
            [['description'], 'string', 'max' => 250],

            ['email', 'unique', 'message' => 'Пользователь с таким email уже зарегистрирован'],
            ['email', CustomEmailValidator::className(), 'message' => 'Некорректный адрес'],

            [['phone'], 'string', 'max' => 20],
            [['hash'], 'string', 'max' => 250],

            [['username', 'email'], 'string', 'max' => 255],
            [['name', 'patronymic', 'surname'], 'string', 'max' => 125],

            [['hours'], 'validHours'],
            [['minutes'], 'validMin'],

            [['oldPassword', 'password', 'rePassword'], 'string'],
            [['oldPassword', 'password'], 'validateNotEmptyOldPass'],

            ['status_id', 'default', 'value' => [self::STATUS_ACTIVE]],
            ['status_id', 'in', 'range' => [self::STATUS_NEW, self::STATUS_ACTIVE, self::STATUS_CLOSE]],

            ['password', 'match', 'pattern' => '/[A-ZА-Я]+/', 'message' => 'Пароль должен содержать хотя бы одну заглавную букву'],
            ['password', 'match', 'pattern' => '/\d+/', 'message' => 'Пароль должен содержать хотя бы одну цифру'],
            ['password', 'string', 'min' => 6, 'tooShort' => 'Пароль пользователя должен содержать не менее 6 символов, включая одну заглавную букву и одну цифру', 'max' => 255, 'tooLong' => 'Пароль должен быть не более 255 символов'],

            [['rePassword'], 'validateChangePassword'],
        ];
    }

    /**
     * Validates the hours.
     *
     * @param string $attribute
     * @param array $params
     */
    public function validHours($attribute, $params)
    {
        if (($this->hours > 8) || (($this->hours === 8) && ($this->minutes > 0))) {
            $this->addError('record_time_min', 'Максимальная продолжительность записи составляет 8 часов');
        }
    }

    /**
     * Validates the minutes.
     *
     * @param string $attribute
     * @param array $params
     */
    public function validMin($attribute, $params)
    {
        if ($this->minutes !== 0) {
            if ($this->hours == 0) {
                if ($this->minutes < 10) {
                    $this->addError('record_time_min', 'Минимальная продолжительность записи составляет 10 минут');
                }

                if ($this->minutes / 60 > 8) {
                    $this->addError('record_time_min', 'Максимальная продолжительность записи составляет 8 часов');
                }
            }
        } elseif ($this->minutes === 0){
            $this->addError('record_time_min', 'Минимальная продолжительность записи составляет 10 минут');
        }
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Логин',
            'name' => 'Имя',
            'patronymic' => 'Отчество',
            'surname' => 'Фамилия',
            'password' => 'Пароль',
            'rePassword' => 'Повторите новый пароль',
            'oldPassword' => 'Старый пароль',
            'phone' => 'Телефон',
            'email' => 'E-mail (логин)',
            'description' => 'Примечание',
            'status_id' => 'Status ID',
            'isLogin' => 'Последнее время активности в системе',
            'hash' => 'Hash',
            'record_time_min' => 'Продолжительности записи трансляции по умолчанию'
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute
     * @param array $params
     */
    public function validateChangePassword($attribute, $params)
    {
        if ($this->password !== $this->rePassword) {
            $this->addError($attribute, 'Пароли не совпадают');
        }
    }

    public function validateNotEmptyOldPass($attribute, $params)
    {
        if (empty($this->oldPassword)) {
            $this->addError($attribute, 'Введите старый пароль');
        }

        if (empty($this->password)) {
            $this->addError('password', 'Введите новый пароль');
        }

        if ((!empty($this->password)) && (empty($this->rePassword))) {
            $this->addError('rePassword', 'Повторите новый пароль');
        }

        if ((!empty($this->password)) || (!empty($this->rePassword))) {
            if (!($this->validatePassword($this->oldPassword))) {
                $this->addError('oldPassword', 'Старый пароль указан неверно');
            }
        }
    }

    public function getChats()
    {
        return $this->hasMany(Chat::className(), ['user_id' => 'id']);
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (empty($this->created_at)) {
            $this->created_at = time();
        }

        if ($this->type != User::TYPE_USER_ADMIN) {
            $this->username = $this->email;
        }

        if (empty($this->status_id)) {
            $this->status_id = User::STATUS_ACTIVE;
        }

        if (empty($this->isLogin)) {
            $this->isLogin = 0;
        }

        $this->updated_at = time();

        // На всякий слуйчай, если мы можем узнать какой юзер изменил запись,
        // то узнаем этот факт и сохраним
        if (isset(Yii::$app)) {
            if (isset(Yii::$app->user)) {
                if (isset(Yii::$app->user->identity)) {
                    if (isset(Yii::$app->user->identity->id)) {
                        $this->last_updated_user_id = Yii::$app->user->identity->id;
                    }
                }
            }
        }

        if (($this->getDirtyAttributes(['name'])) || ($this->getDirtyAttributes(['surname'])) ||
            ($this->getDirtyAttributes(['patronymic'])) || ($this->getDirtyAttributes(['phone'])) ||
            ($this->getDirtyAttributes(['email'])) || (is_null($this->hash))) {

            if (!is_null($this->hash)) {

                // Удалим старые записи
                $requestsVideoUsers = RequestVideoUser::find()
                    ->where(['user_id' => $this->id])
                    ->all();

                /** @var RequestVideoUser $requestVideoUser */
                foreach ($requestsVideoUsers as $requestVideoUser) {
                    $requestVideoUser->delete();
                }
            }

            $this->setHash();
        }

        if ($this->hours === null && $this->minutes === null) {
            if ($this->record_time_min < 10) $this->record_time_min = 10;
            if ($this->record_time_min > 480) $this->record_time_min = 480;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::find()->where(['id' => $id])->andWhere(['in', 'status_id', [self::STATUS_ACTIVE]])->one();
    }


    /**
     * @param mixed $token
     * @param null $type
     * @return void|IdentityInterface
     * @throws NotSupportedException
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     *  Finds user by username
     *
     * @param $username
     * @return array|ActiveRecord|null
     */
    public static function findByUsername($username)
    {
        return static::find()->where(['username' => $username])->andWhere(['in', 'status_id', [self::STATUS_ACTIVE]])->one();
    }

    /**
     * Finds user by email
     *
     * @param $email
     * @return array|ActiveRecord|null
     */
    public static function findByEmail($email)
    {
        return static::find()->where(['email' => $email])->andWhere(['in', 'status_id', [self::STATUS_ACTIVE]])->one();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param $password
     * @throws \yii\base\Exception
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     *
     * @throws \yii\base\Exception
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * ВОССТАНОВЛЕНИЕ ПАРОЛЯ
     *
     * @param $token
     * @return User|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status_id' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * @param $token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int)substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Метод генерирует пароль, содержащий хотя бы одну заглавную букву и одну цифру.
     *
     * @param int $length
     * @return bool|string
     * @throws \yii\base\Exception
     */
    public function generatePassword($length = 6)
    {
        // Бесконеный цикл пока не сгенерится строка содержащая хотя бы одну заглавную букву и хотя бы одну цифру
        while (true) {
            $tmpPassword = Yii::$app->security->generateRandomString($length);
            if (strlen(preg_replace('![^A-Z]+!', '', $tmpPassword)) > 0) {
                preg_match("/[\d]+/", $tmpPassword, $match);
                if (isset($match[0])) {
                    if ($match[0] > 0) {
                        return $tmpPassword;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param $message
     * @param $email
     * @param $subj
     * @return bool
     */
    public static function sendMessage($message, $email, $subj)
    {
        $email = idn_to_ascii($email, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

        return Yii::$app->mailer->compose('layouts/html', ['content' => $message])
            ->setFrom(Yii::$app->params['mailFrom'])
            ->setTo($email)
            ->setSubject($subj)
            ->setTextBody('Plain text content')
            ->setHtmlBody($message)
            ->send();
    }

    /**
     * @return bool|false|int
     */
    public static function checkUserAgent()
    {
        if (preg_match('/(OPR)/', Yii::$app->request->userAgent)) return true;

        if (preg_match('/(MSIE)/', Yii::$app->request->userAgent)) return true;

        if (preg_match('/(Edge)/', Yii::$app->request->userAgent)) return true;

        if (preg_match('/(Yowser)/', Yii::$app->request->userAgent)) return true;

        if (strpos(Yii::$app->request->userAgent, 'rv:11.0') !== false && strpos(Yii::$app->request->userAgent, 'Trident/7.0;') !== false) {
            return true;
        }

        if (strpos(Yii::$app->request->userAgent, 'MSIE') !== false ||
            strpos(Yii::$app->request->userAgent, 'rv:11.0') !== false) {
            return true;
        }

        if (preg_match('/(rv:11.0)/', Yii::$app->request->userAgent)) return true;

        return preg_match('/(MSIE [1-9]{1}\.)|(Chrome\/([1-5]{1}[0-9]{1})\.)|(Firefox\/([0-9]{1}|1[0-9]{1}|2[0-2]{1})\.)/', Yii::$app->request->userAgent);
    }

    public function setHash()
    {
        $this->hash = md5($this->email . $this->surname . $this->patronymic . $this->name);
    }

    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param $dir
     * @return array
     */
    public static function dirtyDirToArray($dir)
    {
        $result = [];

        $cDir = scandir($dir, 1);
        foreach ($cDir as $key => $value) {
            if (!in_array($value, [".", ".."])) {
                $myFile = $dir . DIRECTORY_SEPARATOR . $value;

                if (is_dir($myFile)) {
                    $result[$value] = self::dirtyDirToArray($myFile);
                } else {

                    $res = json_decode(shell_exec("ffprobe -i $myFile -v quiet -print_format json -show_format"));

                    if (empty($res)) continue;
                    if (!isset($res->format->duration)) continue;

                    die($res->format->duration);

                    $totalMinutes = ($res->format->duration / 60); // Переводим секунды в минуты (157 минут)
                    $hours = round(floor($totalMinutes / 60)); // Получаем количество полных часов
                    $minutes = round($totalMinutes - ($hours * 60)); // Получаем оставшиеся минуты

                    if (($hours == 0) && ($minutes <= 2)) continue;

                    $name = 'Трансляция от ' . str_replace('.webm', '', str_replace('screen_', '', $value));

                    $result[] = [
                        'value' => $value,
                        'hours' => $hours,
                        'minutes' => $minutes,
                        'duration' => $res->format->duration,
                        'newName' => $name,
                    ];
                }
            }
        }

        return $result;
    }

    public function getUserTypeByString()
    {
        $result = 'Неизвестный тип записи';

        if (empty($this->type)) return $result;;

        switch ($this->type) {
            case User::TYPE_USER_ADMIN:
                $result = "Администратор";
                break;
            case User::TYPE_USER_MANAGER:
                $result = "Менеджер";
                break;
            case User::TYPE_USER_MASTER:
                $result = "Ведущий";
                break;
            case User::TYPE_USER_STUDENT:
                $result = "Слушатель";
                break;
        }

        return $result;
    }

}
