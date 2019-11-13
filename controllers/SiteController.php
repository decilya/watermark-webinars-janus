<?php

namespace app\controllers;

use app\models\RequestVideoUser;
use app\models\search\RecordsSearch;
use SVG\Nodes\Shapes\SVGCircle;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Yii;
use yii\data\Pagination;
use yii\db\Exception;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Cookie;
use yii\web\Response;
use app\models\LoginForm;
use app\models\User;
use yii\web\UploadedFile;
use yii\imagine;
use app\models\Chat;
use app\models\File;
use app\models\Message;
use app\models\Organization;
use app\models\Server;
use app\models\Situation;

use SVG\SVG;
use SVG\Nodes\Shapes\SVGRect;

class SiteController extends Controller
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
                        'actions' => ['login', 'error', 'save', 'chat', 'watermark', 'save-file'],
                        'allow' => true,
                    ],
                    [
                        'allow' => true,
                        'actions' => ['logout', 'index', 'browser-outdated'],
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

                    [
                        'actions' => ['archive'],
                        'roles' => ['@'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            return ((Yii::$app->user->identity->type === User::TYPE_USER_ADMIN) ||
                                (Yii::$app->user->identity->type === User::TYPE_USER_MANAGER) ||
                                (Yii::$app->user->identity->type === User::TYPE_USER_MASTER) ||
                                (Yii::$app->user->identity->type === User::TYPE_USER_STUDENT));
                        }
                    ],
                    [
                        'actions' => ['organization'],
                        'roles' => ['@'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            return ((Yii::$app->user->identity->type === User::TYPE_USER_ADMIN) ||
                                (Yii::$app->user->identity->type === User::TYPE_USER_MANAGER));
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

//        if (isset(Yii::$app->user->identity->id)) {
//
//            if (($action->id === 'translate-room') || ($action->id === 'index')) {
//
//                $userSituation = Situation::find()->where(['user_id' => Yii::$app->user->identity->id])->asArray()->one();
//
//                //  if (((Yii::$app->request->referrer !== 'https://watermark.wrk/site/translate-room') && ($action->id !== 'index')) &&
//                if ((time() <= ($userSituation['last_time']) + 10)) {
//                    $this->startProject = 0;
//                }
//            }
//        }

        return parent::beforeAction($action);
    }

    /**
     * Страница просмотра вебинара.
     *
     * @@param string $id
     * @return Response|string
     */
    public function actionIndex($id = null)
    {
        // Если это гость, то отправим его авторизироваться
        $this->guestGoLogin();

//        $userSituation = Situation::find()->where(['user_id' => Yii::$app->user->identity->id])->asArray()->one();
//
//        if ((Yii::$app->request->referrer !== 'https://watermark.wrk/site/translate-room') &&
//            ((time() <= ($userSituation['last_time']) + 10))) {
//            $this->startProject = 0;
//        }

        /** @var Server $myServer */
        $myServer = Server::find()->one();

        if (empty($myServer)) {
            $myServer = new Server();
        }

        $chat = Chat::getChatByDate((time() - (3600 * 24)), 1);

        $roomNumber = Yii::$app->params['roomNumber'];

        return $this->render('index', [
            'roomId' => $id,
            'chat' => $chat,
            'myServer' => $myServer,
            'roomNumber' => $roomNumber,
            'startProject' => $this->startProject,
        ]);
    }

    /**
     * Если это гость, то отправим его авторизироваться
     * @return Response|string
     */
    private function guestGoLogin()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(Url::to(['site/login']));
        }

        return false;
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        $this->layout = '@app/views/layouts/forLogin';

        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        /** @var LoginForm $model */
        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post())) {

            if ($model->validate()) {
                /** @var User $userTmp */
                $userTmp = User::find()->where(['username' => $model->username])->one();

                // /** @ t o d o вернуть вместо 30 - 0 */
                // $timeFinal = $userTmp->isLogin + 30;
                ///if (time() > $timeFinal) {
                if (true) {
                    if ($model->login()) {

                        if (Yii::$app->user->identity->type == User::TYPE_USER_ADMIN)
                            return $this->redirect(Url::to(['/admin/default/users']));

                        if (Yii::$app->user->identity->type == User::TYPE_USER_MANAGER)
                            return $this->redirect(Url::to(['site/translate-room', 'id' => 777]));

                        if (Yii::$app->user->identity->type == User::TYPE_USER_MASTER)
                            return $this->redirect(Url::to(['site/translate-room', 'id' => 777]));

                        if (Yii::$app->user->identity->type == User::TYPE_USER_STUDENT)
                            return $this->redirect(Url::to(['site/index', 'id' => 777]));

                        return $this->goBack();
                    }
                } else {
                    Yii::$app->session->setFlash('error', "Если вы не авторизованы в другом браузере, подождите 10 секунд и повторите попытку.");
                }
            }
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
            'startProject' => $this->startProject
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        if (isset(Yii::$app->user->identity->id)) {

            /** * @var User $user */
            $user = User::find()->where(['id' => Yii::$app->user->identity->id])->one();
            if ($user->isLogin != null) {
                $user->isLogin = $user->isLogin - round($user->isLogin / 2);
                $user->save(false);
            }

            /** @var Situation $userSituation */
            // $userSituation = Situation::find()->where(['user_id' => Yii::$app->user->identity->id])->one();
            // $userSituation->last_time = $user->isLogin;
            // $userSituation->save(false);

        }

        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Страница ведения трансляции
     *
     * @@param int $id
     * @return Response|string
     */
    public function actionTranslateRoom($id = null)
    {
        $isFireFox = false;

        if (preg_match('(Firefox)', Yii::$app->request->userAgent)) {
            $isFireFox = true;
        }

        // потом надо будет как-то генерировать комнаты
        if ($id === null) $id = 2147483647;

        /** @var int $userCount Кол-во студентов (потом это будет кол-во студентов на конкретом курсе) */
        $userCount = count(User::find()
            ->where(['status_id' => User::STATUS_ACTIVE])
            ->andWhere(['type' => User::TYPE_USER_STUDENT])
            ->all());

        $chat = Chat::getChatByDate((time() - (8400 * 20)), 1);

        $startScreen = 1;
        /** @var Server $myServer */
        $myServer = Server::find()->one();

        if (isset($myServer->screen) && (time() <= ($myServer->screen + 10))) {
            sleep(3);
            if ((time() <= ($myServer->screen + 10))) {
                $startScreen = 0;
            }
        }

        $roomNumber = Yii::$app->params['roomNumber'];

        return $this->render('translateRoom', [
            'roomId' => $id,
            'userCount' => $userCount,
            'chat' => $chat,
            'myServer' => $myServer,
            'roomNumber' => $roomNumber,
            'startProject' => $this->startProject,
            'startScreen' => $startScreen,
            'isFireFox' => $isFireFox
        ]);
    }

    public function actionSave(): bool
    {
        $model = new File();
        $model->file = UploadedFile::getInstance($model, 'file');

        $path = realpath(dirname(dirname(__FILE__))) . '/uploads/';

        if ($model->file->saveAs($path . $model->file)) {
            return true;
        }

        return false;
    }

    public function actionOrganization()
    {
        if (empty($organization = Organization::find()->one())) {
            $organization = new Organization();
        }

        if (Yii::$app->request->post() && $organization->load(Yii::$app->request->post())) {
            $organization->save();
        }

        return $this->render('organizationForm', [
            'organization' => $organization,
            'startProject' => $this->startProject
        ]);
    }


    public function actionWatermark()
    {
        Yii::$app->response->format = Response::FORMAT_RAW;
        if (strstr($_SERVER["HTTP_USER_AGENT"], "MSIE") == false) {
            header("Cache-Control: no-cache");
            header("Pragma: no-cache");
        } else {
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: public");
        }
        header("Expires: Sat, 26 Jul 1979 05:00:00 GMT");
        header("Content-Encoding: utf-8");
        header("Content-Type: image/svg+xml");
        header("Cache-Control: max-age=0");
        header("Access-Control-Expose-Headers: Content-Length,Content-Range");

        $percent = 10;

        $width = 1980;
        $height = 1020;

        $imgHeight = (int)(($height * $percent) / 100);
        $imgWidth = (int)(($width * $percent) / 100);

        $imgHeight = 80;
        $imgWidth = 400;

        $svg = '<svg width="' . $imgWidth . '" height="' . $imgHeight . '">';

        $user = User::find()->where(['id' => Yii::$app->user->identity->id])->one();

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

        $svg .= '<text x="0" y="25" font-size="25" fill="red">' . $text1 . '</text>';
        $svg .= '<text x="0" y="50" font-size="25"  fill="red">' . $text2 . '</text>';
        $svg .= '<text x="0" y="75" font-size="25"  fill="red">' . $text3 . '</text>';

        $svg .= '</svg>';

        $image1 = SVG::fromString($svg);
        $doc = $image1->getDocument();

        //header('Content-Type: image/svg+xml; charset=utf-8');
        //Yii::$app->response->format = 'svg';

        return $image1;
    }

    /**
     * Старый архив, на всякий случай просто редирект
     *
     * @throws Exception
     */
    public function actionArchive()
    {
        $this->redirect('/record/archive');
    }

    public function actionSaveFile()
    {
        ini_set('max_execution_time', 5 * 60000);
        set_time_limit(5 * 60000);

        $file = Yii::$app->request->get('file');

        $userHash = Yii::$app->user->identity['hash'];

        $href = Yii::$app->basePath . '/web/records-user/' . trim($userHash) . "/" . $file;

        if (file_exists($href)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary");
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Expires: 0');
            header("Cache-Control: private");
            header('Pragma: public');
            flush();
            readfile($href);
            exit;
        }

        return false;
    }

    public function actionBrowserOutdated()
    {
        $this->layout = 'browserOutdated';
        return $this->render('blank');
    }
}
