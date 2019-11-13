<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Вход в систему';
$this->params['breadcrumbs'][] = $this->title;

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

<div class="site-login">

    <div class="mdl-grid" style="justify-content: center;">
        <div class="mdl-cell mdl-cell--3-col mdl-cell--3-col-tablet"></div>
        <div class="mdl-cell mdl-cell--6-col mdl-cell--12-col-phone mdl-cell--6-col-tablet">
            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'layout' => 'horizontal',
                'fieldConfig' => [
                    'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
                    'labelOptions' => ['class' => 'col-lg-1 control-label'],
                ],
            ]); ?>



            <?= $form->field($model, 'username', [
                'template' => '<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">{input}{label}{error}</div>',
                'labelOptions' => [ 'class' => 'mdl-textfield__label']
            ])->textInput(['maxlength' => true, 'class' => 'mdl-textfield__input']) ?>

            <?= $form->field($model, 'password', [
                'template' => '<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">{input}{label}{error}</div>',
                'labelOptions' => [ 'class' => 'mdl-textfield__label']
            ])->passwordInput(['maxlength' => true, 'class' => 'mdl-textfield__input']) ?>

            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="display: flex; justify-content: flex-end;">
                <?= Html::submitButton('Войти', ['class' => 'mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent']) ?>
            </div>


            <?php ActiveForm::end(); ?>
        </div>
        <div class="mdl-cell mdl-cell--3-col mdl-cell--3-col-tablet"></div>
    </div>


</div>

<?php } ?>
