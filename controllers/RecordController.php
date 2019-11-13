<?php

namespace app\controllers;

use app\models\Records;
use app\models\RequestVideoUser;
use app\models\search\RecordsSearch;
use phpDocumentor\Reflection\Types\Integer;
use Yii;
use yii\db\Exception;
use yii\web\Controller;
use app\models\User;
use app\models\Message;

/**
 * @author
 */
class RecordController extends Controller
{

    public $startProject = 1;

    /**
     * @inheritdoc$startProject
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => 'yii\filters\AccessControl',
                'rules' => [
                    [
                        'actions' => ['record-control', 'archive', 'record-control-modal-form', 'get-last-file'],
                        'allow' => true,
                    ],
                    [
                        'allow' => true,
                        'actions' => [],
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['translate-room'],
                        'roles' => ['@'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            return ((Yii::$app->user->identity->type === User::TYPE_USER_ADMIN) ||
                                (Yii::$app->user->identity->type === User::TYPE_USER_MANAGER) ||
                                (Yii::$app->user->identity->type === User::TYPE_USER_MASTER));
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
        if (!isset(Yii::$app->user->identity->id)) {
            $this->layout = '@app/views/layouts/forLogin';
        } else {
            $this->layout = '@app/views/layouts/main';
        }

        if (User::checkUserAgent()) {
            return $this->renderPartial('dummy');
        }

        $this->enableCsrfValidation = false;

        return parent::beforeAction($action);
    }

    /**
     * Страница /record/archive, управление архивом записей
     *
     * @return string
     * @throws Exception
     */
    public function actionArchive()
    {
        if (!isset(Yii::$app->user->identity->id)) throw new Exception('Отказано в доступе');

        $this->enableCsrfValidation = false;

        $searchModel = new RecordsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $preFiles = $dataProvider->getModels();

        /** @var RecordsSearch $preFile */
        foreach ($preFiles as &$preFile) {
            /** @var RequestVideoUser $requestVideoUser */
            $requestVideoUser = RequestVideoUser::find()
                ->where(['filename' => $preFile->filename])
                ->andWhere(['user_id' => Yii::$app->user->identity->id])
                ->one();

            $preFile->setStatus(($requestVideoUser) ? $requestVideoUser->status_id : -1);
        }

        return $this->render('archive', [
            'files' => $preFiles,
            'paginator' => $dataProvider->pagination,
            'startProject' => $this->startProject
        ]);
    }

    /**
     * record/record-control-modal-form?recordId=69
     *
     * @param int $recordId
     * @return string
     * @throws Exception
     */
    public function actionRecordControlModalForm(int $recordId): string
    {
        $userId = Yii::$app->user->identity->id;

        /** @var Records $record */
        $record = Records::find()->where(['id' => $recordId])->one();
        if (empty($record)) {
            throw new Exception('Запись с данным id отсутствует в БД!');
            return false;
        }

        return $this->renderPartial('_record-control-modal-form', [
            'record' => $record
        ]);

    }

    /**
     * Выводит название последнего записаного файла или null, если все файлы уже собраны
     *
     * @return false|string|null
     */
    public function actionGetLastFile()
    {
        $dir = Yii::$app->basePath . '/records-tmp/';
        $files = scandir($dir);

        return (isset($files[4])) ? (substr($files[count($files) - 1], 0, -10)) : null;
    }
}
