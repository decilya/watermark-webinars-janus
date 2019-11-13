<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\alert\Alert;

/* @var $this yii\web\View */
/* @var $organization app\models\Organization */
/* @var $form yii\widgets\ActiveForm */

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

<div class="organization-form" id="organization-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($organization, 'name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($organization, 'email')->textInput(['maxlength' => true]) ?>
    <?= $form->field($organization, 'phone')->textInput(['maxlength' => true]) ?>
    <?= $form->field($organization, 'url')->textInput(['maxlength' => true]) ?>

    <div class="form-group">

        <div class="buttonsBlock">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
        </div>

    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php } ?>


