<?php

namespace app\modules\admin\controllers;

use app\models\search\UserSearch;
use Yii;
use app\models\User;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\HttpException;
use yii\widgets\ActiveForm;
use yii\web\Response;

/**
 * Default controller for the `admin` module
 */
class DefaultController extends Controller
{

    public $defaultAction = 'users';

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
                        // На эти страницы будем пускать только суперпользователя
                        'actions' => ['users', 'create-user', 'update-user', 'update-user-pass'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if (!(isset(Yii::$app->user->identity->type))) return false;
                            return Yii::$app->user->identity->type === User::TYPE_USER_ADMIN;
                        }
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            return (Yii::$app->user->identity->type === User::TYPE_USER_ADMIN ||
                                Yii::$app->user->identity->type === User::TYPE_USER_MANAGER);
                        }
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
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->defaultAction = 'users';
        $this->layout = '@app/views/layouts/adminUsers';

        return parent::beforeAction($action);
    }

    public function actionUsers()
    {
        $searchModel = new UserSearch();
        // $searchModel->typeForSearch = User::TYPE_USER_MANAGER;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render(
            'users-list', [
                'dataProvider' => $dataProvider
            ]
        );
    }

    public function actionCreateUser()
    {
        $user = new User();

        if (Yii::$app->request->isAjax && $user->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($user);
        }

        if ($user->load((Yii::$app->request->post()))) {

            $user->type = Yii::$app->request->post('typeUser');
            $user->status_id = User::STATUS_ACTIVE;

            $pass = $user->generatePassword(6);
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
            ]
        );
    }

    public function actionUpdateUser($id)
    {
        /**
         * @var User $user
         */
        $user = User::find()->where(['id' => $id])->one();

        if (empty($user)) {
            throw new HttpException(404, 'Не найден пользователь с указаным id');
        }

        if ($user->type == User::TYPE_USER_ADMIN) {
            throw new HttpException(403, 'Запрещено редактировать администратора');
        }

        if ($user->type == User::TYPE_USER_ADMIN) return $this->redirect(['default/users']);

        if (Yii::$app->request->isAjax && $user->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($user);
        }

        if ($user->load((Yii::$app->request->post()))) {

            $user->type = Yii::$app->request->post('typeUser');

//            $pass = $user->generatePassword(6);
//            $user->setPassword($pass);
//            $user->generateAuthKey();
//            $this->saveUserAndSandMail($user, $pass);

            $user->email = trim($user->email);
            $user->username = trim($user->username);

            if (!$user->save()) {
                throw new HttpException(501, 'Не удалось сохранить пользователя');
            } else {
                Yii::$app->session->setFlash('success', 'Пользователь успешно отредактирован!');
            }
        }

        return $this->render(
            'user-form', [
                'user' => $user,
            ]
        );
    }

    public function actionUpdateUserPass($id)
    {
        /**
         * @var User $user
         */
        $user = User::find()->where(['id' => $id])->one();

        if (empty($user)) {
            throw new HttpException(404, 'Не найден пользователь с указаным id');
        }

        if ($user->type == User::TYPE_USER_ADMIN) return $this->redirect(['default/users']);

        if (Yii::$app->request->isAjax && $user->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($user);
        }

        $pass = $user->generatePassword(6);
        $user->setPassword($pass);
        $user->generateAuthKey();

        return $this->saveUserAndSandMail($user, $pass);
    }


    public function actionDeleteUser($id)
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

    private function saveUserAndSandMail(User $user, $pass, $way = null)
    {
        if ($user->save()) {

            // письмо с логином/паролем для самого добавленного/отредактированого
            $messageToManager = $this->renderPartial('messages/_message-create-or-update-manager.php', [
                'user' => $user,
                'pass' => $pass
            ]);

            if (!($tmp = User::sendMessage($messageToManager, $user->email, Yii::$app->params['mailToManager_subjectAboutCreateOrUpdateUser']))) {
                throw new HttpException(501, 'Не удалось отправить пиьсмо пользователю');
            }

            if ($way != null) {
                Yii::$app->session->setFlash('success', 'Пользователь успешно создан!');
                return $this->redirect($way);
            } else {
                Yii::$app->session->setFlash('success', 'Пользователю был выслан новый пароль!');
                return $this->redirect(['/admin/default/update-user', 'id' => $user->id]);
            }

        } else {
            throw new HttpException(501, 'Не удалось сохранить пользователя');
        }
    }

}