<?php
/**
 * @var \app\models\search\RecordsSearch $files
 * @var int $startProject
 * @var \yii\data\Pagination $paginator
 */

use yii\db\Exception;

$this->registerCssFile('/css/video-js.css');
$this->registerJsFile('/js/lib/video.js', ['position' => \yii\web\View::POS_HEAD]);

/** @var \app\models\User $user */
$user = \app\models\User::find()->where(['id' => Yii::$app->user->identity->id])->one();

$userHash = $user->hash;

$dir = (Yii::$app->user->identity->type === \app\models\User::TYPE_USER_ADMIN) ?
    "https://" . $_SERVER['HTTP_HOST'] . '/' . Yii::$app->params['recordsFolder'] :
    "https://" . $_SERVER['HTTP_HOST'] . '/' . Yii::$app->params['recordsFolderUser'] . '/' . $userHash;

$this->registerJsFile('/js/recordArchive.js?' . time(), [
    'depends' => 'yii\web\JqueryAsset'
]);
?>

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
        <div class="mdl-cell mdl-cell--2-col"></div>
        <div id="archive-page" class="mdl-cell mdl-cell--8-col">
            <table id="demo" class="mdl-data-table mdl-js-data-table mdl-shadow--2dp">
                <tr>
                    <th>Вам доступно для просмотра</th>
                    <th>Продолжительность</th>
                    <th>Действие</th>
                </tr>
                <?php
                /** @var \app\models\search\RecordsSearch $file */
                foreach ($files as $file) { ?>

                    <tr class="myItem <?php if ($file->status_build === 2) {
                        echo "notBuild";
                    } ?>" <?php if ($file->status_build === 2) { ?>title="Запись собрана с ошибкой" <?php } ?>
                        data-id="<?= $file->id; ?>">
                        <td data-id="<?= $file->id; ?>" class="nameRecord"><?= $file->new_name; ?></td>
                        <td align="center"><?php
                            echo $file->hours;
                            echo " ч. " . $file->minutes . " мин." ?>
                        </td>
                        <td>
                            <?php if ($file->status_build === 1) { ?>

                                <a class="request" data-name="<?= $file->filename; ?>"
                                   data-user="<?= Yii::$app->user->identity->id; ?>"
                                    <?php if (($file['status'] == 0) ||
                                        ($file['status'] == 1) ||
                                        (Yii::$app->user->identity->type === \app\models\User::TYPE_USER_ADMIN)) { ?>
                                        hidden="hidden"
                                    <?php } ?>
                                >Запросить
                                </a>

                                <?php
                                if (Yii::$app->user->identity->type === \app\models\User::TYPE_USER_MASTER) { ?>
                                    <a class="rename show-dialog"
                                       data-id=<?= $file->id; ?> data-name="<?= $file->filename; ?>"
                                       data-user="<?= Yii::$app->user->identity->id; ?>">Переименовать</a>
                                <?php } ?>

                                <?php if (Yii::$app->user->identity->type !== \app\models\User::TYPE_USER_ADMIN) { ?>
                                    <span class="coming" data-name="<?= $file->filename; ?>"
                                          data-user="<?= Yii::$app->user->identity->id; ?>"
                                  <?php if (($file->status == 2) || ($file->status == 1) || ($file->status == -1)) { ?>
                                      hidden="hidden"
                                  <?php } ?>
                                >Идёт подготовка...</span>
                                <?php } ?>

                                <span <?php if ((($file->status == -1) || ($file->status == 2) || ($file->status == 0)) && (Yii::$app->user->identity->type !== \app\models\User::TYPE_USER_ADMIN)) { ?>
                                    hidden="hidden" <?php } ?>>

                                <a download class="download" data-name="<?= $file->filename; ?>"
                                   data-user="<?= Yii::$app->user->identity->id; ?>"
                                   href="<?= $dir . '/' . $file->filename ?>">Скачать / </a>

                                <a class="see" data-name="<?= $file->filename; ?>"
                                   data-user="<?= Yii::$app->user->identity->id; ?>">Смотреть </a>

                                <?php
                                if ((Yii::$app->user->identity->type === \app\models\User::TYPE_USER_ADMIN) ||
                                    (Yii::$app->user->identity->type === \app\models\User::TYPE_USER_MASTER)) { ?>
                                    <a class="rename show-dialog"
                                       data-id=<?= $file->id; ?> data-name="<?= $file->filename; ?>"
                                       data-user="<?= Yii::$app->user->identity->id; ?>"> / Переименовать</a>

                                    <a class="delete"
                                       data-altname="<?= $file->new_name; ?>"
                                       data-id=<?= $file->id; ?> data-name="<?= $file->filename; ?>"
                                       data-user="<?= Yii::$app->user->identity->id; ?>"> / Удалить</a>
                                <?php } ?>

                            </span>

                            <?php } elseif ($file->status_build === 2) { ?>
                                <a class="delete"
                                   data-altname="<?= $file->new_name; ?>"
                                   data-id=<?= $file->id; ?> data-name="<?= $file->filename; ?>"
                                   data-user="<?= Yii::$app->user->identity->id; ?>">Удалить</a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
            <div class="paginator-material">
                <?=
                \yii\widgets\LinkPager::widget([
                    'options' => [
                        'class' => 'mdl-list'
                    ],
                    'pageCssClass' => ['class' => 'mdl-list__item'],
                    'nextPageLabel' => false, // наличие порядкового переключателя вперед
                    'prevPageLabel' => false, // наличие порядкового переключателя назад
                    'linkOptions' => ['class' => 'mdl-button mdl-js-button mdl-js-ripple-effect'],
                    'pagination' => $paginator,
                    'lastPageLabel' => true,
                    'firstPageLabel' => true
                ]);
                ?>
            </div>

            <h3 class="video-title"></h3>
            <div id="videoBlock" style='display: none;'>
                <video controls autoplay width="100%" height="100%" style="max-height: 500px" id="myVideo"></video>
            </div>
        </div>
        <div class="mdl-cell mdl-cell--2-col"></div>
    </div>

    <!-- Modal -->
    <dialog id="rename-modal" class="mdl-dialog mdl-typography--text-center">
        <span class="close">&times;</span>
        <div id="modal-content">
            <div id="content"></div>
        </div>
    </dialog>

    <script>
        $('#demo .see').on('click', function () {
            var archTitle = $(this).parent().parent().parent().children('td:first-child').text();
            $('.video-title').text(archTitle);
        });
    </script>

<?php } ?>
