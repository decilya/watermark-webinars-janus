<?php

/**
 * @var int $startScreen
 * @var bool $startProject
 * @var bool $isFireFox
 * @var int $roomNumber
 * @var int $roomId
 * @var \app\models\Server $myServer
 */

use app\models\Chat;
use yii\web\View;

$this->registerJsFile('/js/chatController.js?' . time(), [
    'depends' => 'yii\web\JqueryAsset'
]);

$this->registerJsFile('/js/teacherScreenObserver.js?' . time(), [
    'depends' => 'yii\web\JqueryAsset'
]);

$this->registerJsFile('/js/teacherScreenStarter.js?' . time(), [
    'depends' => 'yii\web\JqueryAsset'
]);

$this->registerJsFile('/js/ArtemCrutches.js', [
    'depends' => 'yii\web\JqueryAsset'
]);

$this->registerJsFile('/js/moment.min.js?' . time(), [
    'depends' => 'yii\web\JqueryAsset'
]);

/** Для работы модального окна  */
$this->registerJsFile('/js/recordArchive.js?' . time(), [
    'depends' => 'yii\web\JqueryAsset'
]);
?>

<style>
    .countdown {
        list-style: none;
        margin: 6px 0 0 0;
        padding: 0;
        display: block;
    }

    .countdown li {
        display: inline-block;
    }

    /* здесь дни, часы, минуты, секунды */
    .countdown li span {
        width: 100%;
        color: #000;
        font: 18px Verdana;
        display: inline-block;
    }

    /* разделители . и : */
    .countdown li.seperator {
        color: #000;
        font: 16px Verdana;
        vertical-align: top;
    }

    .countdown li div {
        margin: 0;
        color: #a7abb1;
        font: 8pt Verdana;
    }
</style>

<?php if (Yii::$app->user->identity->type !== \app\models\User::TYPE_USER_MANAGER) { ?>

    <script>
        $(document).ready(function () {
            $(window).on('keydown', function (event) {
                if ((event.ctrlKey && event.keyCode === 116) || event.keyCode === 116) {
                    $.ajax({
                        method: 'POST',
                        dataType: 'json',
                        url: '/ajax/refresh-admin-translate',
                        success: function (data) {
                            return true;
                        }
                    });
                }
            });
        });
    </script>

    <input id='deadLineTimerMin' type="hidden" value="<?= Yii::$app->user->identity->record_time_min ?>">
    <div id="noErrorDevice" class="mdl-grid">

        <?php if (!$isFireFox) { ?>
            <div style="margin-bottom: 60px; display: none" hidden>
                <div>
                    <input id="currentRoomCamera" type="hidden"
                           value="<?= isset($myServer->currentRoomCamera) ? $myServer->currentRoomCamera : ''; ?>">
                    <input id="currentRoomScreen" type="hidden"
                           value="<?= isset($myServer->currentRoomScreen) ? $myServer->currentRoomScreen : ''; ?>">
                    <input id="opaqueIdCamera" type="hidden"
                           value="<?= isset($myServer->opaqueIdCamera) ? $myServer->opaqueIdCamera : ''; ?>">
                    <input id="opaqueIdScreen" type="hidden"
                           value="<?= isset($myServer->opaqueIdScreen) ? $myServer->opaqueIdScreen : ''; ?>">
                    <input id="chatParticipants" type="hidden" data-participants="">

                    <input id="roomNumber" type="hidden" value="<?= $roomNumber ?>"/>

                    <input type="hidden" id="courseId" value="1">

                    <input type="hidden" id="startProject" value="<?= $startProject ?>"/>
                    <input type="hidden" id="startScreen" value="<?= $startScreen ?>"/>
                </div>
            </div>
        <?php } ?>

        <div class="left-side mdl-cell--8-col">

            <div>
                <section class="experiment">
                    <div class="make-center">
                        <input id="room-id" type="hidden" value="<?= $roomId; ?>" autocorrect=off
                               autocapitalize=off>
                        <input type="hidden" id="statusRec" value="0">
                        <input type="hidden" id="courseId" value="1">
                    </div>
                </section>
            </div>

            <div class="not-left-side col-sm-12 col-lg-12 col-md-12" style="display: none;">

                <div class="btn-group btn-group-xs pull-right hide">

                    <div class="btn-group btn-group-xs">
                        <button id="bitrateset" autocomplete="off"
                                class="btn btn-primary dropdown-toggle top-admin-bitrateset"
                                data-toggle="dropdown">
                            Bandwidth<span class="caret"></span>
                        </button>

                        <ul id="bitrate" class="dropdown-menu" role="menu">
                            <li><a href="#" id="0">No limit</a></li>
                            <li><a href="#" id="128">Cap to 128kbit</a></li>
                            <li><a href="#" id="256">Cap to 256kbit</a></li>
                            <li><a href="#" id="512">Cap to 512kbit</a></li>
                            <li><a href="#" id="1024">Cap to 1mbit</a></li>
                            <li><a href="#" id="1500">Cap to 1.5mbit</a></li>
                            <li><a href="#" id="2000">Cap to 2mbit</a></li>
                        </ul>
                    </div>

                </div>

                <div id="videos-container" class="media-container small-vid" style="display: none;">

                    <div class="panel panel-default">

                        <div class="panel-heading top-admin-panel-heading">
                            <h3 class="panel-title">
                                <span class="label label-primary hide" id="publisher"></span>
                            </h3>
                        </div>

                        <div class="panel-body" id="videolocal"></div>

                    </div>

                </div>
            </div>

            <div class="" id="teacherScreenBlock" style="display: none">

                <div class="panel panel-default" style="border: none !important;">
                    <div id="videoWaterMark" class="bottom-video">
                        <div class="panel-body-controls" id="videos2">
                            <div class="vjs-control-bar">
                                <div class="bar-left">
                                    <div title='Звук' class="volume video-control">
                                        <i class="fas fa-volume-up" id='volumeChange'></i>
                                    </div>

                                    <div class="volumeCount video-control">
                                        <div id="slider"></div>
                                    </div>

                                </div>
                                <div class="bar-right">
                                    <div title="TV Режим" class="tv-screen video-control">
                                        <i class="fas fa-tv"></i>
                                    </div>
                                    <div title="Расширить на весь экран" class="full-screen video-control">
                                        <i class="fas fa-expand" id='fullScreenIcon'></i>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div id="errorJanusBlock">

                    <?php if ($startProject && $startScreen && !$isFireFox) { ?>

                        <div id="errorDevice" class="alert alert-danger myAlert" style="display: none;">
                                <span id="myInfoBlockForErrorDevice">
                                    Невозможно начать трансляцию, если не подключен микрофон!
                                </span>
                        </div>

                        <div id="noConnectionDiv" class="alert alert-danger myAlert" role="alert"
                             style="display: none;">
                            <p>Проблемы доступа к сервису, из-за низкого качества соединения.</p>
                        </div>

                        <div id="noTotalConnectionDiv" class="alert alert-danger myAlert" role="alert"
                             style="display: none;">
                            <p>Соединение с сервером разорвано. Обновите страницу. </p>
                        </div>

                        <div id="bigError" class="alert alert-danger myAlert" style="display: none;">
                            <p>Ошибка сервера, вызванная разрывом соединения. Если проблема не исчезнет, то страница
                                будет перезагружена</p>
                        </div>

                        <div id="loadingJanus" class="alert alert-info myAlert" style="display: none;">
                            <p>Подготовка к началу трансляции...</p>
                        </div>

                    <?php } ?>

                    <?php if (!$startProject && !$isFireFox) { ?>
                        <div id="startError" class="alert alert-warning myAlert">
                            <p>Ошибка подключения. Обновите страницу через 10 секунд.<br>
                                Если проблема не устранилась:</p>
                            <ul style="padding-left: 10px">
                                <li>Проверьте подключение к интернет соединению и что оно не ниже 1 Мбит/сек;</li>
                                <li>Если у вас открыто много вкладок трансляции в браузере закройте их.</li>
                                <li>Свяжитесь с нами по телефону +7 (812) 655-63-21</li>
                            </ul>
                        </div>
                    <?php } ?>

                    <?php if ($isFireFox) { ?>
                        <div id="isFireFoxError" class="alert alert-danger myAlert">
                            <p>Трансляция возможна только с браузера <a
                                        href="https://www.google.com/intl/ru_ALL/chrome/">Chrome</a>.</p>
                        </div>
                    <?php } ?>

                    <?php if (!$startScreen) { ?>
                        <div id="startScreenError" class="alert alert-warning myAlert">
                            <p>Трансляция уже ведется другим ведущим. Вы можете <a
                                        href="<?= \yii\helpers\Url::to(['/site/index']) ?>">присоединиться к её
                                    просмотру.</a></p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <?php if ($startProject && !$isFireFox) { ?>

            <div class="right-side mdl-cell--4-col mdl-cell--12-col-phone mdl-cell--12-col-tablet">
                <div class='mdl-layout mdl-js-layout mdl-layout--fixed-header'>
                    <div class="mdl-layout__header">
                        <div class="mdl-layout__tab-bar mdl-js-ripple-effect">
                            <a href="#scroll-tab-1" class="mdl-layout__tab is-active" onclick="$('.enter-msg').show()">Чат</a>
                            <a href="#scroll-tab-2" class="mdl-layout__tab" onclick="$('.enter-msg').hide()">
                                Участники
                                <div class="bg">
                                        <span id="nowOnline">
                                            1
                                        </span>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div id="chatBlock" class="mdl-layout__content">
                        <section class="mdl-layout__tab-panel is-active" id="scroll-tab-1">
                            <div class="tab-content-wrapper panel panel-default page-content">
                                <div id="historyChat">
                                    <div id="chatroom" class="chat-output">
                                        <?php
                                        /**
                                         * @var Chat $message
                                         * @var Chat[] $chat
                                         */
                                        foreach ($chat as $message) { ?>
                                            <?php if ($message->isNotSystem($message->text)) { ?>
                                                <div class="msg-item">
                                        <span
                                                class="msg-user-date"><?= date('H:i', $message->created_at) ?></span>
                                                    <span class="msg-user-name">
                                                    <?php
                                                    if (isset($message->user->patronymic)) {
                                                        $nameUserPatronymic = htmlspecialchars($message->user->patronymic);
                                                        mb_regex_encoding('UTF-8');
                                                        mb_internal_encoding("UTF-8");
                                                        $nameUserPatronymic = preg_split('/(?<!^)(?!$)/u', $nameUserPatronymic);
                                                    } else {
                                                        $nameUserPatronymic = '';
                                                    }

                                                    if (isset($message->user->name)) {
                                                        $nameUserTmp = htmlspecialchars($message->user->name);
                                                        mb_regex_encoding('UTF-8');
                                                        mb_internal_encoding("UTF-8");
                                                        $nameUserTmp = preg_split('/(?<!^)(?!$)/u', $nameUserTmp);
                                                    } else {
                                                        $nameUserTmp = '';
                                                    }

                                                    if (isset($message->user->name)) {
                                                        $strMesUser = $message->user->surname . ' ' . $nameUserTmp[0] .
                                                            '.' . $nameUserPatronymic[0] . '.';
                                                    } else {
                                                        $strMesUser = '';
                                                    }

                                                    $tmpTextArr = explode("|", $strMesUser);
                                                    echo $tmpTextArr[0];

                                                    ?>
                                                </span>
                                                    <?= $message->text; ?>
                                                </div>
                                            <?php } ?>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </section>
                        <section class="mdl-layout__tab-panel" id="scroll-tab-2">
                            <div class="people" style="min-height: 100%; height: auto;">
                                <h5 class="text-center"><b>Присутствуют</b></h5>

                                <div id="present"></div>
                            </div>
                        </section>
                    </div>
                    <div class="enter-msg">
                        <textarea maxlength="255" id="inputMessageChat" name="inputMessageChat" rows='1'
                                  placeholder='Отправте Ваше сообщение...'>
                        </textarea>
                        <a id="sendMessage" style=""><span class="glyphicon glyphicon-send"></span></a>
                    </div>
                </div>
                <div class="buttonsControl">
                    <a id="startRecord" class="btn btn-success" style="display: none;">Старт</a>
                    <a id="stopRecord" class="btn btn-primary" style="display: none;">Стоп</a>
                    <a id="addTimeForRecord" class="btn btn-info" title="на 15 минут"
                       style="display: none;">Продлить</a>
                </div>
                <ul class="countdown" style="display: none;">
                    <li hidden>
                        <span class="days">00</span>
                        <div class="days_ref">дни</div>
                    </li>
                    <li hidden class="seperator">.</li>
                    <li>
                        <span class="hours">00</span>
                        <div class="hours_ref">часы</div>
                    </li>
                    <li class="seperator">:</li>
                    <li>
                        <span class="minutes">00</span>
                        <div class="minutes_ref">мин</div>
                    </li>
                    <li class="seperator">:</li>
                    <li>
                        <span class="seconds">00</span>
                        <div class="seconds_ref">сек</div>
                    </li>
                </ul>
                <script type="text/javascript">

                </script>
            </div>
            <script>
                $('#inputMessageChat').keypress(function (e) {
                    if (e.which == 13) {
                        $('#chatBlock').animate({scrollTop: $('#chatroom').height()}, "slow");
                    }
                });

                $('#sendMessage').click(() => {
                    $('#chatBlock').animate({scrollTop: $('#chatroom').height()}, "slow");
                });
            </script>
        <?php } ?>

        <!-- Modal -->
        <dialog id="rename-modal" class="mdl-dialog mdl-typography--text-center">
            <span class="close">&times;</span>
            <div id="modal-content">
                <div id="content"></div>
            </div>
        </dialog>

    </div>
<?php } else { ?>
    <div class="row container">
        <div class="alert alert-danger myAlert">
            <p>Невозможно осуществлять трансляцию под учетной записью менеджера! <a>Перейти на страницу управления
                    пользователями.</a></p>
        </div>
    </div>
<?php } ?>
