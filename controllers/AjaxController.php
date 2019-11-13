<?php

namespace app\controllers;

use app\models\Records;
use Yii;
use app\models\Chat;
use app\models\RequestVideoUser;
use app\models\User;
use yii\imagine;
use yii\helpers\Json;
use app\models\Situation;
use app\models\Server;
use app\models\Message;
use function GuzzleHttp\json_encode;

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
                            'server-screen-room-save', 'get-server-room', 'set-request-file', 'see-file', 'set-server-time',
                            'get-server-time-now', 'set-screen-time', 'check-relevance-stream-time-for-student',
                            'get-server-time-now-screen', 'refresh-admin-translate', 'logout-user', 'status-project',
                            'set-alt-name-record', 'delete-file', 'stick-together'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            // только для залогиненых и только для аякса
                            return ((isset(Yii::$app->user->identity)) && (Yii::$app->request->isAjax));
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
        // Если это не аякс, то просто дальше не будем обрабатывать скрипт
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        return parent::beforeAction($action);
    }

    /** Подсчет пользователей находящихся в системе */
    public function actionCalcActiveUser()
    {
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
        $userId = (int)Yii::$app->user->identity->id;

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
        if (!Yii::$app->request->post()) {
            return false;
        }

        /** @var Server $myServer */
        $myServer = Server::find()->where(['id' => 1])->one();
        $myServer->on = time();
        $myServer->now = time();
        if ($myServer->save(false)) return true;
        return false;
    }

    public function actionSetServerTime()
    {
        /** @var Server $myServer */
        $myServer = Server::find()->where(['id' => 1])->one();
        $oldTime = $myServer->screen;
        $myServer->screen = time();
        $myServer->now = $myServer->screen;

        if ($myServer->save(false)) return json_encode($oldTime);
        return false;
    }

    public function actionSetServerOff()
    {
        /** @var Server $myServer */
        $myServer = Server::find()->where(['id' => 1])->one();
        $myServer->on = 0;
        $myServer->save(false);
        if ($myServer->save(false)) return true;
        return false;
    }

    public function actionSetServerRestart()
    {
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

    public function actionSetRequestFile()
    {
        if (!Yii::$app->request->post()) {
            return false;
        }

        $userId = Yii::$app->request->post('userId');

        if (!$userId) return false;
        $fileName = Yii::$app->request->post('fileName');

        if ((is_null($userId)) || (is_null($fileName))) return false;

        /** @var RequestVideoUser $requestVideoUser */
        $requestVideoUser = RequestVideoUser::find()
            ->where(['filename' => $fileName])
            ->andWhere(['user_id' => $userId])
            ->one();

        if (empty($requestVideoUser)) {
            $requestVideoUser = new RequestVideoUser();
            $requestVideoUser->user_id = $userId;
            $requestVideoUser->filename = $fileName;
            $requestVideoUser->status_id = RequestVideoUser::STATUS_NEW;
        } elseif ($requestVideoUser->status_id == RequestVideoUser::STATUS_NEW) {
            return true;
        }

        if ($requestVideoUser->save()) return true;

        return false;
    }

    public function actionSeeFile()
    {
        //$userId = (int)Yii::$app->request->post('userId');
        $userId = Yii::$app->user->identity->id;

        if (!$userId) return false;
        $fileName = Yii::$app->request->post('fileName');

        /** @var RequestVideoUser $requestVideoUser */
        $requestVideoUser = (Yii::$app->user->identity->type === \app\models\User::TYPE_USER_ADMIN) ?
            true :
            RequestVideoUser::find()
                ->where(['filename' => $fileName])
                ->andWhere(['user_id' => $userId])
                ->one();

        if ($requestVideoUser) {

            /** @var User $user */
            $user = User::find()->where(['id' => $userId])->one();

            $dir = (Yii::$app->user->identity->type === \app\models\User::TYPE_USER_ADMIN) ?
                "https://" . $_SERVER['HTTP_HOST'] . '/' . Yii::$app->params['recordsFolder'] :
                "https://" . $_SERVER['HTTP_HOST'] . '/' . Yii::$app->params['recordsFolderUser'] . '/' . $user->hash;

            $myFileName = $dir . '/' . $fileName;

            return json_encode($myFileName);
        }

        return false;
    }

    /**
     * Время сервера
     *
     * @return json
     */
    public function actionGetServerTimeNow()
    {
        return json_encode(time());
    }

    /**
     *
     * @return bool
     */
    public function actionSetScreenTime()
    {
        /** @var Server $server */
        $server = Server::find()->where(['id' => 1])->one();
        $oldTimeServer = $server->screen;

        $server->screen = time();

        if ($server->save()) {
            return json_encode($oldTimeServer);
        } else {
            var_dump($server->errors);
        }
    }

    public function actionCheckRelevanceStreamTimeForStudent()
    {
        /** @var Server $server */
        $server = Server::find()->where(['id' => 1])->one();

        if ($server->screen == null) return false;

        return json_encode(time() - $server->screen);
    }

    public function actionRefreshAdminTranslate()
    {
        /** @var Server $server */
        $server = Server::find()->where(['id' => 1])->one();

        $server->screen = 0;

        if ($server->save()) return json_encode(true);

        return false;
    }

    public function actionLogoutUser()
    {
        if (isset(Yii::$app->user->identity->id)) {

            /** * @var User $user */
            $user = User::find()->where(['id' => Yii::$app->user->identity->id])->one();
            if ($user->isLogin != null) {
                $user->isLogin = $user->isLogin - round($user->isLogin / 2);
                $user->save(false);
            }

            /** @var Situation $userSituation */
            $userSituation = Situation::find()->where(['user_id' => Yii::$app->user->identity->id])->one();
            $userSituation->last_time = $user->isLogin;
            $userSituation->save(false);

        }

        Yii::$app->user->logout();

        return true;
    }

    public function actionStatusProject()
    {
        if (Yii::$app->user->isGuest) return json_encode(false);

        /** @var Server $myServer */
        $myServer = Server::find()->one();

        if ((time() <= ($myServer->screen + 3))) {
            return json_encode(true);
        }

        return json_encode(false);
    }

    public function actionSetAltNameRecord()
    {
        if (Yii::$app->user->isGuest) return json_encode(false);

        $recordId = Yii::$app->request->post('recordId');
        $altName = Yii::$app->request->post('altName');

        if (($altName == null) || ($recordId == null)) return json_encode(false);

        /** @var Records $record */
        $record = Records::find()->where(['id' => $recordId])->one();
        $record->new_name = trim($altName);

        if ($record->save()) {
            return json_encode(true);
        }

        return json_encode(false);
    }

    public function actionDeleteFile()
    {
        if (Yii::$app->user->isGuest) return json_encode(false);

        // Если не админ или ведущий, то пошел вон от сюда
        if (!(Yii::$app->user->identity->type === User::TYPE_USER_ADMIN ||
            (Yii::$app->user->identity->type === User::TYPE_USER_MASTER))) return json_encode(false);

        $fileName = Yii::$app->request->post('fileName');
        $record = Records::find()->where(['filename' => $fileName])->one();
        if ($record->delete()) {

            $dir = \Yii::$app->basePath . '/records/';
            if ($this->deleteFile($dir, $fileName)) return json_encode(true);
        }

        return json_encode(false);
    }

    function deleteFile($directory, $filename)
    {
        // открываем директорию (получаем дескриптор директории)
        $dir = opendir($directory);

        // считываем содержание директории
        while (($file = readdir($dir))) {
            // Если это файл и он равен удаляемому ...
            if ((is_file("$directory/$file")) && ("$directory/$file" == "$directory/$filename")) {
                // ...удаляем его.
                unlink("$directory/$file");

                // Если файла нет по запрошенному пути, возвращаем TRUE - значит файл удалён.
                if (!file_exists($directory . "/" . $filename)) return true;
            }
        }
        // Закрываем дескриптор директории.
        closedir($dir);
        return false;
    }

    /**
     * Склеить записи
     *
     * @param $roomId
     * @param $userId
     */
    function actionStickTogether($roomId, $userId)
    {
        // возьмем самый большой id записи
        /** @var Records $recordMaxBuildId */
        $recordMaxBuildId = Records::find()
            ->where(['room_id' => $roomId])
            ->andWhere(['user_id' => $userId])
            ->orderBy(['build_id' => SORT_DESC])
            ->one();

        $maxBuildId = (int)$recordMaxBuildId->build_id;
        $maxBuildId++;

        // теперь возьмем все записи у которых нет buildId и присвоим им следующее значение $maxBuildId
        /** @var Records[] $recordsNullBuildId */
        $recordsNullBuildId = Records::find()->where(['build_id' => null])->all();
        foreach ($recordsNullBuildId as $record) {
            $record->build_id = $maxBuildId;
            if (!$record->save()) {
                echo "<pre>";
                print_r($record->errors);
                echo "</pre>";
            }
        }


    }

}
