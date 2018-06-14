<?php

use app\models\Chat;
use app\models\Server;

/**
 * @var $this yii\web\View
 * @var int $roomId
 * @var Server $myServer
 */

$this->title = 'Присоединиться к трансляции';

$this->registerJsFile('/js/janus.js', [
    'depends' => 'yii\web\JqueryAsset'
]);

$this->registerCssFile('/css/video-js.css');
$this->registerJsFile('/js/lib/video.js', ['position' => \yii\web\View::POS_HEAD]);

$this->registerJsFile('/js/chatController.js', [
    'depends' => 'yii\web\JqueryAsset'
]);

$this->registerJsFile('/js/clientCamera.js', [
    'depends' => 'yii\web\JqueryAsset'
]);

$this->registerJsFile('/js/clientScreen.js', [
    'depends' => 'yii\web\JqueryAsset'
]);

$this->registerJsFile('/js/wm.js', [
    'depends' => 'yii\web\JqueryAsset'
]);

?>

<script>
    const userId = $('#userBlock').data('user');

    $(window).on('keydown', function (event) {
        if ((event.ctrlKey && event.keyCode == 116) || event.keyCode == 116) {
            $.ajax({
                method: 'POST',
                dataType: 'json',
                url: '/ajax/refresh-user-situation',
                data: {userId: userId}
            });
        }
    });

    $(document).ready(function () {

        function sendAjaxSetUserSituation() {

            $.ajax({
                method: 'POST',
                dataType: 'json',
                url: '/ajax/set-user-situation',
                data: {userId: userId}
            });
        }

        sendAjaxSetUserSituation();

        setInterval(
            function () {
                sendAjaxSetUserSituation();
            }, 3000
        );

        $(window).on('beforeunload', function () {

            $.ajax({
                method: 'POST',
                dataType: 'json',
                url: '/ajax/refresh-user-situation',
                data: {userId: userBlockChat.data('user')},
                success: function (data) {
                    return false;
                }
            });

            return true;
        });
    });
</script>

<div>
    <div>
        <input id="currentRoomCamera" type="hidden" value="<?= $myServer->currentRoomCamera; ?>">
        <input id="currentRoomScreen" type="hidden" value="<?= $myServer->currentRoomScreen; ?>">
        <input id="opaqueIdCamera" type="hidden" value="<?= $myServer->opaqueIdCamera; ?>">
        <input id="opaqueIdScreen" type="hidden" value="<?= $myServer->opaqueIdScreen; ?>">
        <input id="chatParticipants" type="hidden" data-participants="">

        <input type="hidden" id="courseId" value="1">
    </div>
</div>

<div class="row video-chat-wrapper main-page">

    <div class="b-row">

        <div class="left-side col-sm-8 col-lg-8 col-md-8">

            <div style="width: 200px; margin: 120px auto;" id="loadingClient">
                <img src="/img/test-load.gif"/>
            </div>

            <div id="errorConnection"></div>

            <div id="notStart" style="display: none">Подождите начала трансляции.</div>

            <div class="row top-video">
                <div id="videos-container">
                    <div hidden class="panel-body myVideoBlock" id="videos1">
                        <video class="rounded centered video-js vjs-big-play-centered" id="screenvideo1" width="100%"
                               height="100%" autoplay></video>
                        <div id="bit1"></div>
                    </div>
                </div>
            </div>

            <div class="row bottom-video" style="margin-top: 30px">
                <div class="panel-body myVideoBlock" id="videos2" hidden>
                    <video class="rounded centered video-js vjs-big-play-centered" id="screenvideo2" width="100%"
                           height="100%" autoplay></video>
                    <div id="bit2"></div>
                </div>
            </div>

        </div>

        <div class="right-side col-sm-4 col-lg-4 col-md-4">
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

                                                            echo $strMesUser;
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

    </div>

</div>

