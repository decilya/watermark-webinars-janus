<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use app\assets\BootboxAsset;

AppAsset::register($this);
BootboxAsset::overrideSystemConfirm();
$this->title = 'Система присутствия на мероприятиях';
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>

        <?php

        $this->registerJsFile('/js/lib/adapter.min.js', [
            'depends' => 'yii\web\JqueryAsset',
            'position' => yii\web\View::POS_END,
        ]);

        $this->registerJsFile('/js/lib/bootbox.min.js', [
            'depends' => 'yii\web\JqueryAsset',
            'position' => yii\web\View::POS_END,
        ]);

        $this->registerJsFile('/js/lib/bootstrap.min.js', [
            'depends' => 'yii\web\JqueryAsset',
            'position' => yii\web\View::POS_END,
        ]);

        $this->registerJsFile('/js/lib/jquery.blockUI.min.js', [
            'depends' => 'yii\web\JqueryAsset',
            'position' => yii\web\View::POS_END,
        ]);

        $this->registerJsFile('/js/lib/spin.min.js', [
            'depends' => 'yii\web\JqueryAsset',
            'position' => yii\web\View::POS_END,
        ]);

        $this->registerJsFile('/js/lib/toastr.min.js', [
            'depends' => 'yii\web\JqueryAsset',
            'position' => yii\web\View::POS_END,
        ]);

        $this->registerJsFile('/js/chat.js', [
            'depends' => 'yii\web\JqueryAsset',
            'position' => yii\web\View::POS_END,
        ]);

        ?>

        <script>
            $(document).ready(function () {

                const myUserId = $("#userBlock").data('user');

                /** Статус присутсвия пользователя в системе */
                function sendStatus(myUserId) {
                    if (myUserId != undefined && myUserId != null && myUserId != '') {

                        $.ajax({
                            method: 'POST',
                            dataType: 'json',
                            data: {userId: myUserId},
                            url: '/ajax/login-user-info',
                            success: function (data) {
                                // ок, юзер в системе
                            }
                        });
                    }
                }

                sendStatus(myUserId);

                setInterval(
                    function () {
                        sendStatus(myUserId);
                    },
                    15000
                );
            });
        </script>

    </head>
    <body>
    <?php $this->beginBody() ?>
    <input type="hidden" id="urlHost" value="<?= $_SERVER['HTTP_HOST']; ?>">

    <div class="wrapper">
        <header class="header">
            <div class="container-static">

                <?php
                NavBar::begin([
                    'brandLabel' => Yii::$app->name,
                    'brandUrl' => Yii::$app->homeUrl,
                    'innerContainerOptions' => ['class' => 'container-static'],
                    'options' => [
                        'class' => 'navbar-inverse navbar-fixed-top header-nav',
                    ],
                ]);
                echo Nav::widget([
                    'options' => ['class' => 'navbar-nav pull-right'],
                    'items' => [
                        (Yii::$app->user->identity->type == \app\models\User::TYPE_USER_ADMIN) ||
                        (Yii::$app->user->identity->type == \app\models\User::TYPE_USER_MANAGER) ||
                        (Yii::$app->user->identity->type == \app\models\User::TYPE_USER_MASTER) ?
                            ['label' => 'Транслировать', 'url' => ['/site/translate-room']] : '',

                        (Yii::$app->user->identity->type == \app\models\User::TYPE_USER_ADMIN) ?
                            ['label' => 'Пользователи', 'url' => ['/admin/default/users']] : '',

                        Yii::$app->user->isGuest ? (
                        ['label' => 'Войти', 'url' => ['/site/login']]
                        ) : (
                            '<li>'
                            . Html::beginForm(['/site/logout'], 'post')
                            . Html::submitButton(
                                'Выйти (' . Yii::$app->user->identity->username . ')',
                                [
                                    'class' => 'btn btn-link logout',
                                    'id' => 'userBlock',
                                    'data-user' => Yii::$app->user->identity->id,
                                    'data-name' => Yii::$app->user->identity->name,
                                    'data-type' => Yii::$app->user->identity->type,
                                    'data-surname' => Yii::$app->user->identity->surname,
                                    'data-patronymic' => Yii::$app->user->identity->patronymic,
                                ]
                            )
                            . Html::endForm()
                            . '</li>'
                        )
                    ],
                ]);
                NavBar::end();
                ?>
            </div>
        </header>

        <main class="main">
            <div class="container-content">
                <?= Breadcrumbs::widget([
                    'homeLink' => ['label' => 'Главная', 'url' => '/'],
                    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                ]) ?>
                <?= Alert::widget() ?>
                <?= $content ?>
            </div>
        </main>

        <footer class="footer">
            <div class="container-static">
                <div class="col-xs-6 col-sm-6 col-md-6">
                    <p class="pull-left footer__copyright">
                        &copy; 2006—<?= date('Y') ?>. ЧОУ ДПО «Институт прикладной автоматизации и программирования»
                    </p>
                </div>
                <div class="col-xs-6 col-sm-6 col-md-6">
                    <p class="pull-right footer__address">
                        Адрес: 198097, Санкт-Петербург, пр. Стачек, д.47, БЦ «Шереметев». <br>
                        Телефон/факс: +7 (812) 655-63-21, E-mail: info@ipap.ru
                    </p>
                </div>
            </div>
        </footer>

    </div>
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>