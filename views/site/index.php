<?php

use app\models\Chat;
use app\models\Server;

/**
 * @var $this yii\web\View
 * @var int $roomId
 * @var Server $myServer
 * @var int $roomNumber
 * @var int $startProject
 */

$this->title = 'Присоединиться к трансляции';


$filebase64 = 'K'.base64_encode(file_get_contents(Yii::$app->basePath . '/web/js/watermark.min.js'));

$this->registerJsFile('/js/chatController.js?' . time(), [
    'depends' => 'yii\web\JqueryAsset'
]);

$this->registerJsFile('/js/clientScreen.js?' . time(), [
    'depends' => 'yii\web\JqueryAsset'
]);

$this->registerJsFile('/js/ArtemCrutches.js', [
    'depends' => 'yii\web\JqueryAsset'
]);
?>
<script type="text/javascript">
    localStorage.openpages = Date.now();

    let onLocalStorageEvent = function (e) {
        if (e.key === "openpages") {
            localStorage.page_available = Date.now();
            window.document.location.href = "https://<?= $_SERVER['HTTP_HOST'] ?><?= \yii\helpers\Url::to(['/record/archive/']); ?>";
        }
    };

    window.addEventListener('storage', onLocalStorageEvent, false);

    fuckAdBlock = new FuckAdBlock;
    // and|or
    myFuckAdBlock = new FuckAdBlock({
        checkOnLoad: true,
        resetOnEnd: true
    });
</script>

<?php if ($startProject) { ?>

    <script>

        var siteUser = {
            id: "<?= isset(Yii::$app->user->identity->id) ? Yii::$app->user->identity->id : '' ?>",
            name: "<?= isset(Yii::$app->user->identity->name) ? Yii::$app->user->identity->name : '' ?>",
            type: "<?= isset(Yii::$app->user->identity->name) ? Yii::$app->user->identity->type : '' ?>",
            surname: "<?= isset(Yii::$app->user->identity->surname) ? Yii::$app->user->identity->surname : '' ?>",
            patronymic: "<?= isset(Yii::$app->user->identity->patronymic) ? Yii::$app->user->identity->patronymic : '' ?>",
        };
        var waterMarkInfo = {
            name: "<?= isset(Yii::$app->user->identity->name) ? Yii::$app->user->identity->name : '' ?> <?= isset(Yii::$app->user->identity->surname) ? Yii::$app->user->identity->surname : '' ?>",
            email: "<?= isset(Yii::$app->user->identity->email) ? Yii::$app->user->identity->email : '' ?>",
            phone: "<?= isset(Yii::$app->user->identity->phone) ? Yii::$app->user->identity->phone : '' ?>",
            address: "пр. Стачек, д.47, БЦ «Шереметев»",
            color: 'red',
        }
        const userId = siteUser.id;
    </script>

<?php } ?>

<div class="video-chat-wrapper main-page">

    <div>
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
            <input id="roomNumber" type="hidden" value="<?= isset($roomNumber) ? $roomNumber : ''; ?>"/>
            <input type="hidden" id="courseId" value="1">

            <input type="hidden" id="startProject" value="<?= $startProject ?>"/>
        </div>
    </div>

    <article id="echoTestBlockBeforeStartUser">
        <header>
            <h3>Проверка оборудования для использования Системой.</h3>
        </header>

        <div class="row mt5">
            <div class="container">

                <div id="webRTCCheck" data-checkbox="true">
                    <div class="itemCheck" data-role="infoRTC">
                        <img src="/img/echo-test-success.png"/> <span>Поддержка WebRTC</span>&nbsp;&nbsp;&nbsp;
                    </div>
                </div>

                <div id="messageCheck" data-checkbox="true">
                    <div class="itemCheck" data-role="infoMessages">
                        <img src="/img/echo-test-success.png"/> <span>Обмен Сообщениями</span>&nbsp;&nbsp;&nbsp;
                    </div>
                </div>

                <div id="adBlockerInfo" data-checkbox="true">
                    <div id="successAdBlockerInfo" class="itemCheck" data-role="successAdBlockerInfo"
                         style='display: none;'>
                        <img src="/img/echo-test-success.png"/>
                        <span>Проверка наличия блокировщика рекламы (AdBlocker)</span>&nbsp;&nbsp;&nbsp;
                    </div>

                    <div id="errorAdBlockerInfo" class="itemCheck" data-role="errorAdBlockerInfo">
                        <img style="width: 19px; height: 19px; min-width: 19px; min-height: 19px;"
                             src="/img/warning.png"/> <span>Проверка наличия блокировщика рекламы. (Отключите блокировщик рекламы и перезагрузите страницу.)</span>
                    </div>
                </div>

                <div id="startProject" data-checkbox="true">
                    <div id="successInfoStartProject" class="itemCheck" data-role="successInfoStartProject"
                         style='display: none;'>
                        <img src="/img/echo-test-success.png"/> <span>Трансляция идет</span>&nbsp;&nbsp;&nbsp;
                    </div>

                    <div id="errorInfoStartProject" class="itemCheck" data-role="errorInfoStartProject">
                        <img style="width: 19px; height: 19px; min-width: 19px; min-height: 19px;"
                             src="/img/warning.png"/> <span>Трансляция не идет</span>
                    </div>
                </div>
            </div>

            <footer class="row mt5">
                <button id="joinJanus"
                        class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored"
                        disabled>
                    Подключиться
                </button>
            </footer>
        </div>
    </article>

    <article id="adBlock">
        <header>
            <h1>Для работы с Системой вам необходимо отключить AdBlock</h1>
        </header>

        <div class="row mt5">
            <div class="container">
                Скорей всего вы используете блокировщик рекламы, который препятствует транлcяции видео. Отлкючите его
                для
                работы с системой!
            </div>
        </div>
    </article>

    <div id="videoAndChatUserMainBlock" class="mdl-grid" style="display: none">

        <div class="left-side mdl-cell video-container">

            <?php if ($startProject) { ?>

                <div style="width: 200px; margin: 120px auto;" id="loadingClient">
                    <img id="myImgLoading" src="/img/test-load.gif"/>
                </div>

                <div id="errorConnection"></div>

                <div id="notStart" role="alert" hidden></div>

                <div class="bottom-video" id='videoWaterMark'>

                    <canvas id='videoWM'></canvas>

                    <div class="panel-body-controls" id="videos2">
                        <div class="vjs-control-bar">
                            <div class="bar-left">
                                <div title='Звук' class="volume video-control">
                                    <i class="fas fa-volume-up" id='volumeChange'></i>
                                </div>

                                <div class="volumeCount video-control">
                                    <div id="slider"></div> <!-- the Slider -->
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

            <?php } ?>

            <div id="errorJanusBlock">

                <?php if ($startProject) { ?>

                    <div id="noConnectionDiv" class="alert alert-danger myAlert" role="alert" hidden>
                        <p>Проблемы доступа к сервису, из-за низкого качества соединения.</p>
                    </div>
                    <div id="noTotalConnectionDiv" class="alert alert-danger myAlert" role="alert" hidden>
                        <p>Соединение с сервером разорвано. Обновите страницу. </p>
                    </div>
                    <div id="bigError" class="alert alert-danger myAlert" hidden>
                        <p>Ошибка сервера, вызванная разрывом соединения. Если проблема не исчезнет, то страница
                            будет перезагружена</p>
                    </div>
                    <div id="loadingJanus" class="alert alert-info myAlert" hidden>
                        <p>Загрузка трансляции... пожалуйста, подождите.</p>
                    </div>

                <?php } ?>

                <?php if (!$startProject) { ?>
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
            </div>
        </div>
        <script>glob('<?=$filebase64?>')</script>

        <?php if ($startProject) { ?>

            <div class="right-side mdl-cell chat-container mdl-cell--12-col-phone mdl-cell--12-col-tablet">
                <div class="right-table">
                    <ul class="nav nav-tabs">
                        <li><span>Чат</span></li>
                    </ul>
                    <div style="display: table-row;height: 10px"></div>
                    <div class="tab-content">
                        <div id="chatBlock" class="tab-content-wrapper panel panel-default">
                            <div class="">
                                <div id="historyChat">
                                    <div id="chatroom" class="chat-output">
                                        <?php
                                        /**
                                         * @var Chat $messageни
                                         * @var Chat[] $chat
                                         */
                                        foreach ($chat as $message) { ?>
                                            <?php if ($message->isNotSystem($message->text)) { ?>
                                                <div class="msg-item">
                                                    <span class="msg-user-date"><?= date('H:i', $message->created_at) ?></span>
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
                        </div>
                    </div>

                    <div style="display: table-row;height: 10px"></div>

                    <div class="enter-msg">
                        <textarea maxlength="255" id="inputMessageChat" name="inputMessageChat" rows='1'
                                  placeholder='Отправте Ваше сообщение...'></textarea>
                        <a id="sendMessage" style=""><span class="glyphicon glyphicon-send"></span></a>
                    </div>

                </div>
            </div>

        <?php } ?>

    </div>
</div>

<script>

    const video = document.querySelector('video');

    video.addEventListener('enterpictureinpicture', () => {

        if (document.pictureInPictureElement) {
            document.exitPictureInPicture()
                .then(() => { /**/
                })
                .catch(() => { /**/
                });
        }

    });
</script>
