<?php
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

<h1><?= $this->title; ?></h1>

<div class="row">
    <div class="blog-header">
        <a href="<?= \yii\helpers\Url::to(['/admin/default/create-user']) ?>" class="btn btn-info">Создать</a>
    </div>
</div>

<?php if (!empty($items)) { ?>
    <div class="row">

        <div class="paginator-air2">
            <?php
            echo LinkPager::widget([
                'pagination' => $dataProvider->pagination,
            ]);
            ?>
        </div>

        <?php
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'id' => 'ordersTbl',
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
                    'label' => 'Действие',
                    'content' => function ($data) {

                        if ($data->status_id == \app\models\User::STATUS_ACTIVE) {
                            return "<a href='/admin/default/delete-user/?id=$data->id' class='detachUser' data-confirm='Вы уверены?'>Удалить</a>";
                        } else {
                            return false;
                        }
                    },
                ],
                [
                    'label' => '#',
                    'content' => function ($data) {
                        if ($data->type == \app\models\User::TYPE_USER_ADMIN) return false;
                        return "<a href='/admin/default/update-user/?id=$data->id' class='editUser'>Редактировать</a>";
                    },
                ]
            ],
            'emptyText' => 'Здесь будут отображаться все активные пользователи зарегесированные в системе',
            'summary' => "",

        ]);
        ?>

<!--        <div class="paginator-air2">-->
<!--            --><?php
//            echo LinkPager::widget([
//                'pagination' => $dataProvider->pagination,
//            ]);
//            ?>
<!--        </div>-->
    </div>
<?php } ?>