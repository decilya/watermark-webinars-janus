<?php

/* @var $this \yii\web\View */

/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\web\View;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
$this->title = Yii::$app->params['nameProject'];
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
        <?php
            $this->registerJsFile('/js/adminUsers_layout.js?' . time(), [
                'depends' => 'yii\web\JqueryAsset',
                'position' => yii\web\View::POS_HEAD,
            ]);

            $this->registerCssFile("/css/material.css");
        ?>


        <?php $this->head(); ?>

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
        <script
        src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js"
        integrity="sha256-eGE6blurk5sHj+rmkfsGYeKyZx3M4bG+ZlFyA7Kns7E="
        crossorigin="anonymous"></script>
    </head>
    <body>
    <?php $this->beginBody() ?>

    <div class="wrapper">
    <!-- Always shows a header, even in smaller screens. -->
        <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">
        <header class="mdl-layout__header">
            <div class="mdl-layout__header-row">
            <!-- Title -->
            <span class="mdl-layout-title"><?= Yii::$app->params['nameProject'] ?></span>
            <!-- Add spacer, to align navigation to the right -->
            <div class="mdl-layout-spacer"></div>
            <!-- Navigation. We hide it in small screens. -->
            <nav class="mdl-navigation mdl-layout--large-screen-only">
                <?php if( (Yii::$app->user->identity->type == \app\models\User::TYPE_USER_ADMIN) ||
                        (Yii::$app->user->identity->type == \app\models\User::TYPE_USER_MANAGER) ||
                        (Yii::$app->user->identity->type == \app\models\User::TYPE_USER_MASTER) ): ?>
                    <a class="mdl-navigation__link" href="/site/translate-room">Транслировать</a>
                <?php endif; ?>
                <?php if( (Yii::$app->user->identity->type == \app\models\User::TYPE_USER_MANAGER) ||
                        (Yii::$app->user->identity->type == \app\models\User::TYPE_USER_ADMIN) ): ?>
                    <a class="mdl-navigation__link" href="/admin/default/users">Пользователи</a>
                <?php endif; ?>
                <?php if( (!Yii::$app->user->isGuest) ): ?>
                    <a class="mdl-navigation__link" href="<?= Yii::$app->homeUrl ?>">Трансляция</a>
                <?php endif; ?>
                <?php if( (Yii::$app->user->identity->type == \app\models\User::TYPE_USER_ADMIN) ||
                        (Yii::$app->user->identity->type == \app\models\User::TYPE_USER_MANAGER) ||
                        (Yii::$app->user->identity->type == \app\models\User::TYPE_USER_MASTER)  ||
                        (Yii::$app->user->identity->type == \app\models\User::TYPE_USER_STUDENT)): ?>
                    <a class="mdl-navigation__link" href="/record/archive">Архив</a>
                <?php endif; ?>

                <?php if( (Yii::$app->user->isGuest) ): ?>
                    <a class="mdl-navigation__link" href="<?= Yii::$app->homeUrl ?>">Войти</a>
                <?php else: ?>
                    <a class="mdl-navigation__link" href="/site/logout"> <i class="material-icons">exit_to_app</i> Выйти (<?= Yii::$app->user->identity->username ?>)</a>
                <?php endif; ?>
            </nav>
            </div>
        </header>
        <div class="mdl-layout__drawer">
            <span class="mdl-layout-title"><?= Yii::$app->params['nameProject'] ?></span>
            <nav class="mdl-navigation">
                <?php if( (Yii::$app->user->identity->type == \app\models\User::TYPE_USER_ADMIN) ||
                        (Yii::$app->user->identity->type == \app\models\User::TYPE_USER_MANAGER) ||
                        (Yii::$app->user->identity->type == \app\models\User::TYPE_USER_MASTER) ): ?>
                    <a class="mdl-navigation__link" href="/site/translate-room">Транслировать</a>
                <?php endif; ?>
                <?php if( (Yii::$app->user->identity->type == \app\models\User::TYPE_USER_MANAGER) ||
                        (Yii::$app->user->identity->type == \app\models\User::TYPE_USER_ADMIN) ): ?>
                    <a class="mdl-navigation__link" href="/admin/default/users">Пользователи</a>
                <?php endif; ?>
                <?php if( (!Yii::$app->user->isGuest) ): ?>
                    <a class="mdl-navigation__link" href="<?= Yii::$app->homeUrl ?>">Трансляция</a>
                <?php endif; ?>
                <?php if( (Yii::$app->user->identity->type == \app\models\User::TYPE_USER_ADMIN) ||
                        (Yii::$app->user->identity->type == \app\models\User::TYPE_USER_MANAGER) ||
                        (Yii::$app->user->identity->type == \app\models\User::TYPE_USER_MASTER)  ||
                        (Yii::$app->user->identity->type == \app\models\User::TYPE_USER_STUDENT)): ?>
                    <a class="mdl-navigation__link" href="/record/archive">Архив</a>
                <?php endif; ?>

                <?php if( (Yii::$app->user->isGuest) ): ?>
                    <a class="mdl-navigation__link" href="<?= Yii::$app->homeUrl ?>">Войти</a>
                <?php else: ?>
                    <a class="mdl-navigation__link" href="/site/logout"> <i class="material-icons">exit_to_app</i> Выйти (<?= Yii::$app->user->identity->username ?>)</a>
                <?php endif; ?>
            </nav>
        </div>
            <main class="mdl-layout__content" style="flex-grow: 0;">
                <div class="page-content" style="min-height: 84.1vh !important;">

                        <?= Alert::widget() ?>
                        <?= $content ?>
                </div>
                <footer class="mdl-mini-footer">
                    <div class="mdl-mini-footer__left-section">
                        <div class="mdl-logo">
                            <p class="pull-left footer__copyright">
                                &copy; 2006—<?= date('Y') ?>. ЧОУ ДПО «Институт прикладной автоматизации и программирования»
                            </p>
                        </div>
                    </div>
                    <div class="mdl-mini-footer__right-section">
                            <p class="pull-right footer__address">
                                Адрес: 198097, Санкт-Петербург, пр. Стачек, д.47, БЦ «Шереметев». <br>
                                Телефон/факс: +7 (812) 655-63-21, E-mail: info@ipap.ru
                            </p>
                    </div>
                </footer>
            </main>
        </div>

    </div>

    <?php $this->endBody() ?>

    <script>


    </script>
    </body>
    </html>
<?php $this->endPage() ?>
