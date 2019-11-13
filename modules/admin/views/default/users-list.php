<?php

/**
 * @var bool $startProject
 * @var \app\models\User $data
 * @var \yii\debug\models\timeline\DataProvider $dataProvider
 */

use yii\widgets\LinkPager;
use yii\grid\GridView;

//use kartik\alert\Alert;

$this->title = 'Пользователи';
$this->params['breadcrumbs'][] = $this->title;

$items = (!empty($dataProvider)) ? $dataProvider->getModels() : null;

/*
$errorFlashMessage = Yii::$app->session->getFlash('error');
if ($errorFlashMessage) {
    echo Alert::widget([
        'type' => Alert::TYPE_DANGER,
        'icon' => 'glyphicon glyphicon-remove-sign',
        'body' => $errorFlashMessage,
        'showSeparator' => true,
        'delay' => 8000
    ]);
}*/
?>
<div class="mdl-grid">
    <div class="mdl-cell mdl-cell--1-col"></div>
    <div class="mdl-cell mdl-cell--10-col">
        <!-- <h1><?= $this->title; ?></h1> -->

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

            <div class="blog-header">
                <a href="<?= \yii\helpers\Url::to(['/admin/default/create-user']) ?>" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent">Создать</a>
            </div>
    </div>
    <div class="mdl-cell mdl-cell--1-col"></div>
</div>
    <?php if (!empty($items)) { ?>
        <div class="mdl-grid">

            


            <div class="mdl-cell mdl-cell--1-col"></div>
            <?php
            $tableProvider = Clone $dataProvider;
            $tableProvider->pagination  = false;
            echo GridView::widget([
                'dataProvider' => $tableProvider,
                'id' => 'ordersTbl',
                'options' => [
                    'class' => 'mdl-cell mdl-cell--10-col'
                ],
                'tableOptions' => [
                    'class' => 'mdl-data-table mdl-js-data-table mdl-shadow--2dp'
                ],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'username',
                    'email',
                    'phone',
                    [
                        'attribute' => 'created_at',
                        'label' => 'Дата создания',
                        'content' => function ($data) {
                            return date('d.m.Y H:i', $data->created_at);
                        },
                    ],
                    [
                        'attribute' => 'created_at',
                        'label' => 'Дата редактирования',
                        'content' => function ($data) {
                            return date('d.m.Y H:i', $data->updated_at);
                        },
                    ],

                    [
                        'attribute' => 'type',
                        'label' => 'Тип учетной записи',
                        'content' => function ($data) {
                            /**  @var \app\models\User $data */
                            return $data->getUserTypeByString($data);
                        },
                    ],
                    [
                        'label' => 'Действие',
                        'content' => function ($data) {

                            /** @var \app\models\User $data */
                            if ($data->status_id == \app\models\User::STATUS_ACTIVE) {
                                if ($data->type !== \app\models\User::TYPE_USER_ADMIN) {
                                    return "<a href='/admin/default/update-user/?id=$data->id' class='editUser'><i class='material-icons'>edit</i></a>
                                            <a href='/admin/default/delete-user/?id=$data->id' 
                                            class='detachUser' data-confirm='Вы уверены?'><i class='material-icons'>delete_forever</i></a>";
                                }
                            }

                            return false;

                        },
                    ],
                    // [
                    //     'label' => 'Действие',
                    //     'content' => function ($data) {

                    //         /*
                    //             // можно редактировать только свою уч. запись админа
                    //             if (($data->type === \app\models\User::TYPE_USER_ADMIN) &&
                    //                 ($data->id !== Yii::$app->user->identity->id)) return false;
                    //                 return "<a href='/admin/default/update-user/?id=$data->id' class='editUser'>Редактировать</a>";
                    //         */

                    //         // админов дозволяется редактировать всем (выслать стандартный пароль из парамсов, остальное не дозволяем)
                    //         return "<a href='/admin/default/update-user/?id=$data->id' class='editUser'>Редактировать</a>";


                    //     },
                    // ]
                ],
                'emptyText' => 'Здесь будут отображаться все активные пользователи зарегесированные в системе',
                'summary' => "",
            ]);
            ?>
            <div class="mdl-cell mdl-cell--1-col"></div>
            <div class="mdl-cell mdl-cell--1-col"></div>
            <div class="paginator-material mdl-cell mdl-cell--10-col">
                <?= 
                    LinkPager::widget([
                        'options' => [
                            'class' => 'mdl-list'
                        ],
                        'pageCssClass' => ['class' => 'mdl-list__item'],
                        'nextPageLabel' => false,//наличие порядкового переключателя вперед
                        'prevPageLabel' => false,//наличие порядкового переключателя назад
                        'linkOptions' => ['class' => 'mdl-button mdl-js-button mdl-js-ripple-effect'],
                        'pagination' => $dataProvider->pagination,
                        'lastPageLabel' => true,
                        'firstPageLabel' => true
                    ]);
                ?>
            </div>
            <div class="mdl-cell mdl-cell--1-col"></div>
        </div>
    <?php }
} ?>