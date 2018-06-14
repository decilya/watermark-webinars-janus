<?php

namespace app\controllers;

use app\models\Chat;
use Yii;
use app\models\User;
use yii\imagine;
use app\models\Message;
use yii\helpers\Json;
use app\models\Situation;
use app\models\Server;

class AjaxController extends SiteController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => 'yii\filters\AccessControl',
                'rules' => [
                    [
                        'actions' => ['calc-active-user', 'get-alt-for-watermark', 'get-chat', 'send-message',
                            'user-info', 'login-user-info', 'refresh-user-situation', 'set-user-situation', 'set-server-off',
                            'set-server-on', 'set-server-restart', 'all-users', 'server-camera-room-save',
                            'server-screen-room-save', 'get-server-room'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            // только для залогиненых и только для аякса
                            return (
                                (isset(Yii::$app->user->identity))
                                && (Yii::$app->request->isAjax)
                            );
                        }
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }

    /** Подсчет пользователей находящихся в системе */
    public function actionCalcActiveUser()
    {
        // Если это не аякс, то просто дальше не будем обрабатывать скрипт
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        $timeNow = time() - 10;
        $users = User::find()->where(['>', 'isLogin', $timeNow])->asArray()->all();

        return json_encode($users);
    }

    /**
     * Полученините текста ALT для вотермарки
     *
     * ajax/get-alt-for-watermark
     *
     * @return JSON
     */
    public function actionGetAltForWatermark()
    {
        // Если это не аякс, то просто дальше не будем обрабатывать скрипт
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        $id = Yii::$app->user->identity->id;
        /** @var User $user */
        $user = User::find()->where(['id' => $id])->one();

        if (!empty($user)) {
            $user->name = htmlspecialchars($user->name);
            mb_regex_encoding('UTF-8');
            mb_internal_encoding("UTF-8");
            $user->name = preg_split('/(?<!^)(?!$)/u', $user->name);

            $user->patronymic = htmlspecialchars($user->patronymic);
            mb_regex_encoding('UTF-8');
            mb_internal_encoding("UTF-8");
            $user->patronymic = preg_split('/(?<!^)(?!$)/u', $user->patronymic);

            $text1 = $user->surname . ' ' . $user->name[0] . '.' . $user->patronymic[0] . '. ';
            $text2 = $user->email;
            $text3 = $user->phone;
        } else {
            $text1 = 'пр. Стачек, д.47, БЦ «Шереметев»';
            $text2 = 'info@ipap.ru';
            $text3 = '+7(812)655-63-21';
        }

        $text2 = trim($text2);
        $text2 = htmlspecialchars($text2);
        $text3 = trim($text3);
        $text3 = htmlspecialchars($text3);

        $text = $text1 . ' ' . $text2 . ' ' . $text3;

        return json_encode($text);
    }

    /**
     * Получение чата
     */
    public function actionGetChat()
    {
        // Если это не аякс, то просто дальше не будем обрабатывать скрипт
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        if (!Yii::$app->request->post()) {
            return false;
        }

        // $courseId = Yii::$app->request->post('courseId');
        /** @todo когда будут разные курсы */
        $courseId = 1;

        $chat = [];
        // Берем чат за прошедшие 20 часов
        $chatTmp = Chat::getChatByDate((time() - (8400 * 20)), $courseId);

        /** @var Chat $message */
        foreach ($chatTmp as $message) {
            $chat[] = [
                'created_at' => $message->created_at,
                'user_id' => $message->user_id,
                'text' => $message->text
            ];
        }

        return json_encode($chat);
    }

    /**
     * Запись сообщения чата
     */
    public function actionSendMessage()
    {
        // Если это не аякс, то просто дальше не будем обрабатывать скрипт
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        if (!Yii::$app->request->post()) {
            return false;
        }

        /** @var Chat $message */
        $message = new Chat();
        $message->user_id = (int)Yii::$app->request->post('userId');
        $message->course_id = (int)Yii::$app->request->post('courseId');
        $message->text = Yii::$app->request->post('message');
        $message->room_id = Yii::$app->request->post('roomId');

        if ($message->isNotSystem($message->text)) {
            if ($message->save()) {
                return true;
            }
        } else {
            return true;
        }

        return false;
    }

    /** Информальция о пользователе */
    public function actionUserInfo()
    {
        // Если это не аякс, то просто дальше не будем обрабатывать скрипт
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        if (!Yii::$app->request->post()) {
            return false;
        }

        $userId = (int)Yii::$app->request->post('userId');

        if (empty($userId)) return false;

        /** @var User $userTmp */
        $userTmp = User::find()->where(['id' => $userId])->one();

        if (empty($userTmp)) return false;

        $user = [
            'id' => $userTmp->id,
            'name' => $userTmp->name,
            'surname' => $userTmp->surname,
            'patronymic' => $userTmp->patronymic,
            'type' => $userTmp->type
        ];

        if (!empty($user)) {
            return json_encode($user);
        } else {
            return false;
        }
    }


    /**
     * Сохранение времени активности пользователя
     */
    public function actionLoginUserInfo()
    {
        // Если это не аякс, то просто дальше не будем обрабатывать скрипт
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        if (!Yii::$app->request->post()) {
            return false;
        }

        $userId = Yii::$app->request->post('userId');

        /** @var User $user */
        $user = User::find()->where(['id' => $userId])->one();

        if (!empty($user)) {
            $user->isLogin = time();

            if ($user->save(false)) return true;
        }

        return false;
    }

    public function actionRefreshUserSituation()
    {
        // Если это не аякс, то просто дальше не будем обрабатывать скрипт
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        if (!Yii::$app->request->post()) {
            return false;
        }

        $userId = (int)Yii::$app->request->post('userId');

        $userSituation = Situation::find()->where(['user_id' => $userId])->one();
        if (empty($userSituation)) return false;

        /** @var Situation $userSituation */

        $userSituation->user_id = $userId;
        $userSituation->last_time = 0;

        if ($userSituation->save()) return true;

        return false;
    }

    public function actionSetUserSituation()
    {
        // Если это не аякс, то просто дальше не будем обрабатывать скрипт
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        if (!Yii::$app->request->post()) {
            return false;
        }

        $userId = (int)Yii::$app->request->post('userId');

        $userSituation = Situation::find()->where(['user_id' => $userId])->one();
        if (empty($userSituation)) $userSituation = new Situation();

        $userSituation->user_id = $userId;
        $userSituation->last_time = time();

        if ($userSituation->save()) return true;

        return false;
    }

    public function actionSetServerOn()
    {
        // Если это не аякс, то просто дальше не будем обрабатывать скрипт
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        if (!Yii::$app->request->post()) {
            return false;
        }

        /** @var Server $myServer */
        $myServer = Server::find()->where(['id' => 1])->one();
        $myServer->on = time();;
        if ($myServer->save(false)) return true;
        return false;
    }

    public function actionSetServerOff()
    {
        // Если это не аякс, то просто дальше не будем обрабатывать скрипт
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        /** @var Server $myServer */
        $myServer = Server::find()->where(['id' => 1])->one();
        $myServer->on = 0;
        $myServer->save(false);
        if ($myServer->save(false)) return true;
        return false;
    }

    public function actionSetServerRestart()
    {
        // Если это не аякс, то просто дальше не будем обрабатывать скрипт
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        /** @var Server $myServer */
        $myServer = Server::find()->where(['id' => 1])->one();
        $myServer->on = 0;
        $myServer->save(false);
        if ($myServer->save(false)) return true;
        return false;
    }

    /** Получение всех пользователей кроме запрашивающего  */
    public function actionAllUsers()
    {
        // Если это не аякс, то просто дальше не будем обрабатывать скрипт
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        $userId = Yii::$app->user->identity->id;
        $users = User::find()
            ->where(['status_id' => User::STATUS_ACTIVE])
            ->andWhere(['type' => User::TYPE_USER_STUDENT])
            ->all();

        $result = [];

        /**
         * @var User $user
         */
        foreach ($users as $user) {
            if ($user->id != $userId) {
                $result[] = [
                    'id' => $user->id,
                    'name' => $user->name, -
                    'surname' => $user->surname,
                    'patronymic' => $user->patronymic,
                    'type' => $user->type
                ];
            }
        }

        if (!empty($result)) {
            return json_encode($result);
        } else {
            return false;
        }
    }

    public function actionServerCameraRoomSave()
    {
        // Если это не аякс, то просто дальше не будем обрабатывать скрипт
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        if (!Yii::$app->request->post()) {
            return false;
        }

        $room = (int)Yii::$app->request->post('room');
        $opaqueId = Yii::$app->request->post('opaqueId');

        /** @var Server $server */
        /** @todo Если будет несколько одновременных занятий, то переписать участок (курсы заложены) */
        $server = Server::find()->where(['id' => 1])->one();
        if (empty($server)) $server = new Server();
        $server->currentRoomCamera = $room;
        $server->opaqueIdCamera = $opaqueId;
        $server->on = time();

        if ($server->save(false)) return true;

        return false;
    }

    public function actionServerScreenRoomSave()
    {
        // Если это не аякс, то просто дальше не будем обрабатывать скрипт
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        if (!Yii::$app->request->post()) {
            return false;
        }

        $room = (int)Yii::$app->request->post('room');
        $opaqueId = Yii::$app->request->post('opaqueId');

        /** @var Server $server */
        /** @todo Если будет несколько одновременных занятий, то переписать участок (курсы заложены) */
        $server = Server::find()->where(['id' => 1])->one();
        if (empty($server)) $server = new Server();
        $server->currentRoomScreen = $room;
        $server->opaqueIdScreen = $opaqueId;
        $server->on = time();

        if ($server->save(false)) return true;

        return false;
    }

    public function actionGetServerRoom()
    {
        // Если это не аякс, то просто дальше не будем обрабатывать скрипт
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        /** @var Server $server */
        $server = Server::find()->where(['id' => 1])->one();
        if (empty($server)) return false;

        return json_encode([
            'currentRoomCamera' => $server->currentRoomCamera,
            'opaqueIdCamera' => $server->opaqueIdCamera,
            'currentRoomScreen' => $server->currentRoomScreen,
            'opaqueIdScreen' => $server->opaqueIdScreen,
        ]);
    }

}
