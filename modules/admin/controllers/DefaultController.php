<?php

namespace app\modules\admin\controllers;

use app\controllers\SiteController;
use app\models\search\UserSearch;
use Yii;
use app\models\User;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\HttpException;
use yii\widgets\ActiveForm;
use yii\web\Response;
use app\models\Situation;

/**
 * Default controller for the `admin` module
 */
class DefaultController extends Controller
{

    public $startProject = 1;

    public $defaultAction = 'users';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],

            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @param $action
     * @return bool
     * @throws HttpException
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {

        $this->defaultAction = 'users';
        $this->layout = '@app/views/layouts/adminUsers';

        if (isset(Yii::$app->user->identity->type) && (Yii::$app->user->identity->type !== User::TYPE_USER_MANAGER) && (Yii::$app->user->identity->type !== User::TYPE_USER_ADMIN)) {
            throw new HttpException('403', 'Отказано в доступе!');
        }

//        if (isset(Yii::$app->user->identity->id)) {
//
//            $userSituation = Situation::find()->where(['user_id' => Yii::$app->user->identity->id])->asArray()->one();
//
//            if ((time() <= ($userSituation['last_time']) + 10)) {
//                $this->startProject = 0;
//            }
//        }

        return parent::beforeAction($action);
    }

    /**
     * @return string
     */
    public function actionUsers()
    {
        $searchModel = new UserSearch();
        // $searchModel->typeForSearch = User::TYPE_USER_MANAGER;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render(
            'users-list', [
                'dataProvider' => $dataProvider,
                'startProject' => $this->startProject
            ]
        );
    }

    /**
     * @return array|string|Response
     * @throws HttpException
     * @throws \yii\base\Exception
     */
    public function actionCreateUser()
    {
        $user = new User();
        $user->record_time_min = 10;

        if (Yii::$app->request->isAjax && $user->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($user);
        }

        if ($user->load((Yii::$app->request->post()))) {

            $user->type = Yii::$app->request->post('typeUser');
            $user->status_id = User::STATUS_ACTIVE;

            $pass = ($user->type === User::TYPE_USER_ADMIN) ? Yii::$app->params['adminPassword'] : $user->generatePassword(6);

            $user->setPassword($pass);
            $user->generateAuthKey();

            $user->isLogin = 1;
            $user->status_id = User::STATUS_ACTIVE;

            $user->email = trim($user->email);
            $user->username = trim($user->username);

            return $this->saveUserAndSandMail($user, $pass, ['/admin/default/']);
        }

        return $this->render(
            'user-form', [
                'user' => $user,
                'defaultAction' => $this->defaultAction
            ]
        );
    }

    /**
     * Редактирование пользователя
     *
     * @param $id
     * @return array|string|Response
     * @throws HttpException
     */
    public function actionUpdateUser($id)
    {
        /** @var User $user */
        $user = User::find()->where(['id' => $id])->one();
        $oldUser = clone $user;

        if (empty($user)) {
            throw new HttpException(404, 'Не найден пользователь с указаным id');
        }

        if (Yii::$app->request->isAjax && $user->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($user);
        }

        if ($user->record_time_min != null) {
            $user->hours = round(floor($user->record_time_min / 60)); // Получаем количество полных часов
            $user->minutes = round($user->record_time_min - ($user->hours * 60)); // Получаем оставшиеся минуты
        }

        if ($user->load((Yii::$app->request->post()))) {

            $user->type = Yii::$app->request->post('typeUser');
            $user->email = trim($user->email);
            $user->username = trim($user->username);

            if ($user->hours != 0 || $user->minutes != 0) {
                $user->record_time_min = (int)$user->hours * 60 + (int)$user->minutes;
            }

            if (!$user->save()) {
                // пусть ошибка уйдет во вьюшку, т.е. тут делать ничего  не будем
            } else {
                Yii::$app->session->setFlash('success', 'Пользователь успешно отредактирован!');
                $user->refresh();

                if (($oldUser->type !== $user->type) && $user->type === User::TYPE_USER_ADMIN) {
                    return $this->redirect(['/admin/default/update-user', 'id' => $user->id]);
                }
            }
        }

        if ($user->hours === null && $user->minutes === null && ($user->record_time_min != null)) {
            $user->hours = (int)round(floor($user->record_time_min / 60)); // Получаем количество полных часов
            $user->minutes = (int)round($user->record_time_min - ($user->hours * 60)); // Получаем оставшиеся минуты
        }

        return $this->render(
            'user-form', ['user' => $user,
                'defaultAction' => $this->defaultAction]
        );
    }

    /**
     * @param $id
     * @return array|Response
     * @throws HttpException
     * @throws \yii\base\Exception
     */
    public
    function actionUpdateUserPass($id)
    {
        /** @var User $user */
        $user = User::find()->where(['id' => $id])->one();

        if (empty($user)) {
            throw new HttpException(404, 'Не найден пользователь с указаным id');
        }

        $pass = $user->generatePassword(6);

        if ($user->type === User::TYPE_USER_ADMIN) {

            Yii::$app->session->setFlash(
                'error',
                'Для записи адинистрара всегда устанвливается стандартный пароль из файла config/params.php!'
            );

            $pass = Yii::$app->params['adminPassword'];

            Yii::$app->session->setFlash('success', 'Стандартный пароль установлен!');
        }

        if (Yii::$app->request->isAjax && $user->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($user);
        }

        $user->setPassword($pass);
        $user->generateAuthKey();

        return $this->saveUserAndSandMail($user, $pass);
    }

    /**
     * @param $id
     * @return Response
     */
    public
    function actionDeleteUser($id)
    {
        $error = false;

        if (isset(Yii::$app->user->identity)) {
            if (Yii::$app->user->identity->type == User::TYPE_USER_ADMIN) {
                /** @var User $user */
                $user = User::find()->where(['id' => $id])->one();

                if (!empty($user)) {

                    $user->status_id = User::STATUS_CLOSE;

                    if (!$user->save()) $error = true;

                } else {
                    $error = true;
                }
            } else {
                $error = true;
            }
        } else {
            $error = true;
        }

        if ($error) Yii::$app->session->setFlash('error', 'Ошибка при попытке удаления пользователя!');

        return $this->redirect(['/admin/default/users']);
    }

    /**
     * @param User $user
     * @param $pass
     * @param null $way
     * @return Response
     * @throws HttpException
     */
    private
    function saveUserAndSandMail(User $user, $pass, $way = null)
    {

//        if (!$user->checkParam('email')) {
//
//            if ($way != null) {
//                Yii::$app->session->setFlash('success', 'Пользователь успешно создан!');
//                return $this->redirect($way);
//            } else {
//                Yii::$app->session->setFlash('success', 'Пользователю был выслан новый пароль!');
//                return $this->redirect(['/admin/default/update-user', 'id' => $user->id]);
//            }
//        }


        if ($user->save()) {


            // письмо с логином/паролем для самого добавленного/отредактированого
            $messageToManager = $this->renderPartial('/default/messages/_message-create-or-update-manager.php', [
                'user' => $user,
                'pass' => $pass
            ]);

            if (!($tmp = User::sendMessage(
                $messageToManager,
                $user->email,
                Yii::$app->params['mailToManager_subjectAboutCreateOrUpdateUser']
            ))) {
                throw new HttpException(501, 'Не удалось отправить пиьсмо пользователю');
            }

            if ($way != null) {
                if ($user->type !== User::TYPE_USER_ADMIN) {
                    Yii::$app->session->setFlash('success', 'Пользователь успешно создан!');
                }
                return $this->redirect($way);
            } else {
                if ($user->type !== User::TYPE_USER_ADMIN) {
                    Yii::$app->session->setFlash('success', 'Пользователю был выслан новый пароль!');
                }
                return $this->redirect(['/admin/default/update-user', 'id' => $user->id]);
            }

        } else {
            throw new HttpException(501, 'Не удалось сохранить пользователя');
        }
    }

}
