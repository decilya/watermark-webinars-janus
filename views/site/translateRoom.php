<?php
use app\models\Chat;

$this->registerCssFile('/css/video-js.css');
$this->registerJsFile('/js/lib/video.js', ['position' => \yii\web\View::POS_HEAD]);
$this->registerCssFile('/css/style.css');
$this->registerJsFile('/js/janus.js', [
    'depends' => 'yii\web\JqueryAsset'
]);

$this->registerJsFile('/js/chatController.js', [
    'depends' => 'yii\web\JqueryAsset'
]);

$this->registerJsFile('/js/teacherCamera.js', [
    'depends' => 'yii\web\JqueryAsset'
]);

$this->registerJsFile('/js/teacherScreen.js', [
    'depends' => 'yii\web\JqueryAsset'
]);

?>

<div class="row video-chat-wrapper first-page">
    <div class="b-row">


        <div class="left-side col-sm-8 col-lg-8 col-md-8">

            <div class="">
                <section class="experiment">
                    <div class="make-center">
                        <input id="room-id" type="hidden" value="<?= $roomId; ?>" autocorrect=off autocapitalize=off>
                        <input type="hidden" id="statusRec" value="0">
                        <input type="hidden" id="courseId" value="1">
                    </div>
                </section>
            </div>

            <div class="not-left-side col-sm-12 col-lg-12 col-md-12">

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

                <div id="videos-container" class="media-container small-vid">

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

            <div class="col-md-12 col-lg-12">

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <span class="label label-primary hide" id="publisher1"></span>

                            <div class="btn-group btn-group-xs pull-right hide">

                                <div class="btn-group btn-group-xs">
                                    <button id="bitrateset1" autocomplete="off"
                                            class="btn btn-primary dropdown-toggle"
                                            data-toggle="dropdown">
                                        Bandwidth<span class="caret"></span>
                                    </button>

                                    <ul id="bitrate1" class="dropdown-menu" role="menu">
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
                        </h3>
                    </div>
                    <div class="panel-body" id="videolocal1"></div>
                </div>


            </div>
        </div>

        <div class="right-side col-sm-4 col-lg-4 col-md-4">
            <div class="right-table">

                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#panel1">Чат</a></li>
                    <?php /** @var int $userCount Кол-во пользователей в системе */ ?>
                    <li>&nbsp;</li>
                    <li>
                        <a data-toggle="tab" href="#panel2">
                            Участники
                            <div class="bg"><span id="nowOnline">1</span></div>
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-content-wrapper panel panel-default">
                        <div id="panel1" class="tab-pane fade in active">
                            <div id="chatBlock" class="row chat">
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
                        <div id="panel2" class="tab-pane fade">
                            <h5 class="text-center"><b>Присутствуют</b></h5>

                            <div id="present"></div>
                            <!--<h5 class="text-center"><b>Отсуствуют</b></h5>

                            <div id="notPresent"></div>-->
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


<!--<div class="b-row2">-->
<!---->
<!--  PAMMMMM!!!!!!!!!!!!!!!!!!!-->
<!--</div>-->

