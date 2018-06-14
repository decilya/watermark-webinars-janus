<?php

namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use app\components\validators\CustomEmailValidator;

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
 * @property int type
 *
 * @property string  $created_at
 * @property string  $updated_at
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
 */
class User extends ActiveRecord implements IdentityInterface
{
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
            [['description'], 'string', 'max' => 250],

            ['email', 'unique', 'message' => 'Пользователь с таким email уже зарегистрирован'],
            ['email', CustomEmailValidator::className(), 'message' => 'Некорректный адрес'],

            [['phone'], 'string', 'max' => 20],

            [['username', 'email'], 'string', 'max' => 255],
            [['name', 'patronymic', 'surname'], 'string', 'max' => 125],

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
            'isLogin' => 'Последнее время активности в системе'
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
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     *
     */
    public static function findByUsername($username)
    {
        return static::find()->where(['username' => $username])->andWhere(['in', 'status_id', [self::STATUS_ACTIVE]])->one();
    }

    /**
     * Finds user by email
     *
     * @param $email
     * @return static
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
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    //ВОССТАНОВЛЕНИЕ ПАРОЛЯ
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
     * @param int $length Длинна пароля (по умолчанию - 6 символов)
     * @return string
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
    }

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

    public static function checkUserAgent()
    {

        // если Опера, то можно
        if (preg_match('/(OPR)/', Yii::$app->request->userAgent)) return false;

        return preg_match('/(MSIE [1-9]{1}\.)|(Chrome\/([1-5]{1}[0-9]{1})\.)|(Firefox\/([0-9]{1}|1[0-9]{1}|2[0-2]{1})\.)/', Yii::$app->request->userAgent);
    }


}
