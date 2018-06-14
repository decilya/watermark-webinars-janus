<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii;
use yii\console\Controller;
use app\models\User;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppController extends Controller
{

    /** Код красного цвета */
    const COLOR_ERROR = 31;

    /** Код зеленого цвета */
    const COLOR_SUCCESS = 32;

    private function getDsnAttribute($dsn, $name = "dbname")
    {
        if (preg_match('/' . $name . '=([^;]*)/', $dsn, $match)) {
            return $match[1];
        } else {
            return null;
        }
    }

    private function printColorStr($str, $code)
    {
        $str = $str . "\n";
        echo "\n";
        $code = array($code);
        echo "\033[" . implode(';', $code) . 'm' . $str . "\033[0m";
    }

    /**
     * Метод создает БД с названием указанным в файле конфига config/db.php
     *
     */
    public function actionCreateDb()
    {
        $nameDb = $this->getDsnAttribute(Yii::$app->getDb()->dsn);
        $username = Yii::$app->getDb()->username;
        $password = Yii::$app->getDb()->password;
        $host = $this->getDsnAttribute(Yii::$app->getDb()->dsn, 'host');
        $mysqli = mysqli_connect($host, $username, $password);

        if (mysqli_connect_errno($mysqli)) {
            $this->printColorStr("Не удалось подключиться к MySQL: " . mysqli_connect_error(), self::COLOR_ERROR);
        }

        $res = mysqli_query($mysqli, "CREATE database " . $nameDb . " CHARACTER SET utf8 COLLATE utf8_general_ci;");

        if ($res) {
            $this->printColorStr("База данных $nameDb успешно создана!", self::COLOR_SUCCESS);
        } else {
            $this->printColorStr('Неудалось создать базу данных ' . $nameDb . '. Возможно, что такая БД уже создана в системе или неправильно заполнен файл config/db.php', self::COLOR_ERROR);
        }

        echo "\n";
    }

    /**
     * Добавить в систему админа, если его нет. В качестве параметра метод принимает email суперпользователя; при вызове метода без парметра админ будет создан с почтой via@wizardforum.ru. Пример вызова: php yii app/add-admin test@test.com
     *
     * @param string|null $email
     */
    public function actionAddAdmin($email =  null)
    {
        $model = User::find()->where(['username' => 'admin'])->one();
        if (empty($model)) {

            /** @var User $user */

            $user = new User();
            $user->username = 'admin';

            ($email != null) ? ($user->email = $email) : ($user->email = 'via@wizardforum.ru');

            $user->setPassword('1qwe2qaz');
            $user->generateAuthKey();
            $user->phone = '89999999999';
            $user->type = User::TYPE_USER_ADMIN;
            $user->status_id = User::STATUS_ACTIVE;
            $user->created_at = time();
            $user->name = 'admin';
            $user->patronymic = 'admin';
            $user->surname = 'admin';
            $user->isLogin = 1;

            $user->description = 'Суперпользователь системы. Помимо возможностей менеджера может создавать менеджеров, а также удалять их из системы и редактировать';

            if ($user->save(false)) {
                $this->printColorStr("Поздравляем, суперпользователь успешно создан в системе! Теперь Вы можете авторизоваться используя логин/пароль: admin/1qwe2qaz\n", self::COLOR_SUCCESS);
            } else {
                echo "<pre>";
                print_r($user->errors);
                echo "</pre>";
            }
        } else {
            $this->printColorStr("Ошибка! Суперпользователь уже был создан в системе ранее! Невозможно создать 2х суперпользователей.\n", self::COLOR_ERROR);
        }
    }
}
