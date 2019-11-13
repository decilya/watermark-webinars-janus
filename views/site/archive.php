<?php
/**
 * @var \app\models\search\RecordsSearch $files
 * @var int $startProject
 * @var \yii\data\Pagination $paginator
 */

$this->registerCssFile('/css/video-js.css');
$this->registerJsFile('/js/lib/video.js', ['position' => \yii\web\View::POS_HEAD]);

/** @var \app\models\User $user */
$user = \app\models\User::find()->where(['id' => Yii::$app->user->identity->id])->one();

$userHash = $user->hash;
$dir = "https://" . $_SERVER['HTTP_HOST'] . '/' . Yii::$app->params['recordsFolderUser'];
?>

<script>
    $(document).ready(function () {

        // Запросить
        $('.request').on('click', function () {

            var myLink = $(this);
            var fileName = myLink.data('name');
            var userId = myLink.data('user');

            $.ajax({
                url: '/ajax/set-request-file',
                method: 'POST',
                data: {fileName: fileName, userId: userId},
                dataType: 'json',
                success: function () {
                    myLink.hide();
                    myLink.parent().find('.coming').show();
                },
                error: function () {

                }
            });
        });

        // Смотреть
        $('.see').on('click', function () {

            var myLink = $(this);
            var fileName = myLink.data('name');
            var userId = myLink.data('user');

            $.ajax({
                url: '/ajax/see-file',
                method: 'POST',
                data: {fileName: fileName, userId: userId},
                dataType: 'json',
                success: function (dataFileLink) {
                    var videoBlock = $('#videoBlock');
                    videoBlock.show();
                    $("#videoBlock")[0].scrollIntoView();
                    var video = $('#myVideo');
                    var src = video.attr('src');
                    video.attr('src', dataFileLink, +'&autoplay=1');
                },
                error: function () {

                }
            });
        });
    });
</script>


<?php if (!$startProject) { ?>
    <div id="errorJanusBlock">
        <div id="startError" class="alert alert-warning myAlert">
            <p>Ошибка подключения. Обновите страницу через 10 секунд.<br>
                Если проблема не устранилась:</p>
            <ul style="padding-left: 10px">
                <li>Проверьте подключение к интернет соединению и что оно не ниже 1 Мбит/сек;</li>
                <li>Если у вас открыто много вкладок трансляции в браузере закройте их.</li>
                <li>Свяжитесь с нами по телефону +7 (812) 655-63-21</li>
            </ul>
        </div>
    </div>
<?php } else { ?>

    <div class="mdl-grid">
        <div class="mdl-cell mdl-cell--1-col"></div>
        <div id="archive-page" class="mdl-cell mdl-cell--10-col">
            <table id="demo" class="mdl-data-table mdl-js-data-table mdl-shadow--2dp">
                <tr>
                    <th>Вам доступно для просмотра</th>
                    <th>Продолжительность</th>
                    <th>Действие</th>
                </tr>
                <?php
                /** @var \app\models\search\RecordsSearch $file */
                foreach ($files as $file) { ?>

                    <tr class="myItem">
                        <td><?= $file->new_name; ?></td>
                        <td align="center"><?php
                            echo $file->hours;
                            echo " ч. " . $file->minutes . " мин." ?>
                        </td>
                        <td>
                            <a class="request" data-name="<?= $file->filename; ?>"
                               data-user="<?= Yii::$app->user->identity->id; ?>"

                                <?php if (($file['status'] == 0) || ($file['status'] == 1)) { ?>
                                    hidden="hidden"
                                <?php } ?>
                            >Запросить
                            </a>

                            <span class="coming" data-name="<?= $file->filename; ?>"
                                  data-user="<?= Yii::$app->user->identity->id; ?>"
                            <?php if (($file->status == 2) || ($file->status == 1) || ($file->status == -1)) { ?>
                                hidden="hidden"
                            <?php } ?>
                            >Идёт подготовка...</span>

                            <span <?php if (($file->status == -1) || ($file->status == 2) || ($file->status == 0)) { ?>
                                hidden="hidden" <?php } ?>>

                            <a download class="download" data-name="<?= $file->filename; ?>"
                               data-user="<?= Yii::$app->user->identity->id; ?>"
                               href="<?= $dir . '/' . $userHash . '/' . $file->filename ?>">Скачать</a>
                            /
                            <a class="see" data-name="<?= $file->filename; ?>"
                               data-user="<?= Yii::$app->user->identity->id; ?>">Смотреть</a>
                        </span>
                        </td>
                    </tr>
                <?php } ?>
            </table>
            <h3 class="video-title"></h3>
            <div id="videoBlock" style='display: none;'>
                <video controls autoplay width="100%" height="100%" style="max-height: 500px" id="myVideo"></video>
            </div>
        </div>
        <div class="mdl-cell mdl-cell--1-col"></div>
        <div class="mdl-cell mdl-cell--1-col"></div>
        <div id="paginatorArchive" class="paginator-material mdl-cell mdl-cell--10-col">
            <?=
            \yii\widgets\LinkPager::widget([
                'options' => [
                    'class' => 'mdl-list'
                ],
                'pageCssClass' => ['class' => 'mdl-list__item'],
                'nextPageLabel' => false,//наличие порядкового переключателя вперед
                'prevPageLabel' => false,//наличие порядкового переключателя назад
                'linkOptions' => ['class' => 'mdl-button mdl-js-button mdl-js-ripple-effect'],
                'pagination' => $paginator,
                'lastPageLabel' => true,
                'firstPageLabel' => true
            ]);
            ?>
        </div>
        <div class="mdl-cell mdl-cell--1-col"></div>
    </div>

    <script>
        $('#demo .see').on('click', function () {
           // //console.log($(this).parent().parent().parent().children('td:first-child'));
            var archTitle = $(this).parent().parent().parent().children('td:first-child').text();
            $('.video-title').text(archTitle);
        });
    </script>

<?php } ?>
