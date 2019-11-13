<?php

namespace app\controllers;

use app\models\Records;
use app\models\RequestVideoUser;
use app\models\Situation;
use app\models\User;
use Yii;
use yii\imagine;
use app\models\Message;
use yii\web\HttpException;
use yii\web\Request;

class ApiController extends SiteController
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
                        'actions' => ['build-result', 'get-list-for-build', 'see-file', 'new-record', 'get-files-list'],
                        'allow' => true,
                    ],
                    [
                        'allow' => true,
                        'actions' => ['build-result', 'get-list-for-build', 'see-file', 'new-record', 'get-files-list'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     *
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

    /**
     * https://watermark.wrk/api/get-files-list Список файлов на сборку, сборка осуществляется путем вызова actionNewRecord со status=1
     *
     * @return false|string
     */
    public function actionGetFilesList()
    {
        $records = Records::find()->where(['status_build' => 0])->asArray()->all();

        return json_encode($records);
    }

    // https://watermark.wrk/api/get-list-for-build
    public function actionGetListForBuild()
    {
        $requestVideoUser = RequestVideoUser::find()->where(['status_id' => RequestVideoUser::STATUS_NEW])->all();

        $arr = [];

        /** @var RequestVideoUser $item */
        foreach ($requestVideoUser as $item) {

            /** @var User $user */
            $user = User::findOne($item->user_id);

            $arr[] = [
                'filename' => $item->filename,
                'dest_folder' => $user->hash,
                'wm' => [
                    'fio' => $user->surname . " " . $user->name . " " . $user->patronymic,
                    'email' => $user->email,
                    'phone' => $user->phone
                ],

            ];
        }

        return json_encode($arr);
    }

    // https://watermark.wrk/api/build-result?user_hash=f0f305b40e5fcee1be5b1ed7de7e5de5&filename=screen_2018-10-01_12-18-54-456.webm&result=1
    public function actionBuildResult()
    {
        if (!Yii::$app->request->isGet) return new HttpException(418, "Ууууупс");

        $filename = Yii::$app->request->get('filename');
        $userHash = Yii::$app->request->get('user_hash');
        $result = (bool)Yii::$app->request->get('result');

        /** @var RequestVideoUser $requestVideoUser */
        $requestVideoUser = RequestVideoUser::find()
            ->joinWith('user')
            ->where(['user.hash' => $userHash])
            ->andWhere(['filename' => $filename])
            ->one();

        if (!empty($requestVideoUser)) {

            if ($result == 1) {
                $requestVideoUser->status_id = RequestVideoUser::STATUS_OK;
            } else {
                $requestVideoUser->status_id = RequestVideoUser::STATUS_ERROR;
            }

            if ($requestVideoUser->save()) {
                return $requestVideoUser->status_id;
            }
        }

        return false;
    }

    /**
     * Завести в базе данных запись про новую запись, возвращает или айди записи или 0
     *
     * @param string|null $filename
     * @param int|null $id
     * @param string|null $duration
     * @param int|null $status
     * @param int|null $userId
     * @param int|null $roomId
     * @param string $created_at
     * @param string $finished_at
     * @return int
     * @throws HttpException
     */
    public function actionNewRecord(string $filename = null,
                                    int $id = null,
                                    string $duration = null,
                                    int $status = null,
                                    int $userId = null,
                                    int $roomId = null,
                                    string $created_at = null,
                                    string $duration_tmp = null

    ): int
    {
        if ($filename === null) {
            $fileName = Yii::$app->request->get('filename');
        } else {
            $fileName = $filename;
        }

        if ($duration === null) {
            $duration = Yii::$app->request->get('duration');
        }

        if ($userId === null) {
            $userId = Yii::$app->user->identity->id;
        }

        if ($roomId === null) {
            $roomId = Yii::$app->request->get('roomId');
        }

        if ($created_at === null) {
            $created_at = Yii::$app->request->get('created_at');
        }

        if ($created_at === null) {
            $dTime = \DateTime::createFromFormat("Y.m.d H:i:s", $created_at);
            $created_at = $dTime->getTimestamp();
        }

        if ($duration_tmp === null) {
            $duration_tmp = Yii::$app->request->get('duration_tmp');
        }

        $finished_at = null;
        if ($duration_tmp === null) {
            $dTime = \DateTime::createFromFormat("H:i:s", $created_at);
            $duration_tmp = $dTime->getTimestamp();
            $finished_at = $created_at + $duration_tmp;
        }

        if (($fileName === null) && ($id === null)) {
            throw new HttpException(418, "Ууууупс");
        }

        if ($id !== null) {
            $newRecord = Records::find()->where(['id' => $id])->one();
        } else {
            $fileNameForSearch = ($status == 1) ? substr($fileName, 0, -5) : $fileName;
            /** @var Records $newRecord */
            $newRecord = Records::find()->where(['filename' => $fileNameForSearch])->one();
        }

        if (empty($newRecord)) {
            $newRecord = new Records();
        }

        $newRecord->filename = $fileName;
        $newRecord->duration = $duration;

        if ($status) {
            $newRecord->status_build = $status;

            if ($duration !== null) {
                $newRecord->duration = $duration;
            }
        }

        if ($userId !== null) {
            $newRecord->user_id = $userId;
        }

        if ($roomId !== null) {
            $newRecord->room_id = $roomId;
        }

        $newRecord->created_at = $created_at;
        $newRecord->finished_at = $finished_at;

        if ($newRecord->save()) {
            $newRecord->refresh();
            return $newRecord->id;
        }

        // Если status 1 то убрать нахуй все обрывки и оставить только 1 запись, ту которая пришла сейчас

        return 0;
    }

    public function actionStatusProject()
    {
        if (Yii::$app->user->isGuest) return false;

        $userSituation = Situation::find()->where(['user_id' => Yii::$app->user->identity->id])->asArray()->one();
        if ((time() <= ($userSituation['last_time']) + 10)) {
            return false;
        }

        return true;
    }

    /**
     * @return bool|HttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteRecord()
    {
        if (!Yii::$app->request->isGet) return new HttpException(418, "Ууууупс");

        $fileName = Yii::$app->request->get('filename');

        /** @var Records $record */
        $record = Records::find()->where(['filename' => $fileName])->one();

        if ($record->delete()) {

        }
    }


}