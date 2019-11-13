<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\User;

/**
 * @var $user User
 */
?>
<style>
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        opacity: 1;
    }
</style>
<script>
    $(document).ready(function () {
        let recordTimeMinutesInput = $('#record_time_minutes');
        let recordTimeHoursInput = $('#record_time_hours');

        recordTimeMinutesInput.on('focus', function () {
            $('#timeControllerBlock').addClass('is-focused');
        });

        recordTimeMinutesInput.on('blur', function () {
            $('#timeControllerBlock').removeClass('is-focused');
        });

        recordTimeHoursInput.on('change', function () {
            if (this.value < 0) this.value = 0;
        });

        recordTimeMinutesInput.on('change', function () {
            if (this.value < 0) this.value = 0;
        });
    });
</script>
<div class="createUser">
    <?php
    $form = ActiveForm::begin([
        'enableAjaxValidation' => true,
        'options' => [
            'name' => 'managerForm',
            'class' => 'mdl-grid'
        ],
        'id' => 'managerForm']);
    ?>
    <div class='mdl-cell mdl-cell--1-col'></div>
    <div class='mdl-cell mdl-cell--10-col'>

        <?= ($user->isNewRecord) ? '<h3>Создание пользователя</h3>' : '<h3>Редактирование пользователя ' . $user->username . '</h3>'; ?>

    </div>
    <div class='mdl-cell mdl-cell--1-col'></div>
    <div class='mdl-cell mdl-cell--1-col'></div>
    <div class='mdl-cell mdl-cell--10-col'>
        <div id="blockNewUser">

            <?php if ($user->type !== User::TYPE_USER_ADMIN) { ?>
                <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                    <select class="mdl-textfield__input" name="typeUser" id="typeUser" required="">
                        <option <?php if ($user->type == User::TYPE_USER_MANAGER) {
                            echo "selected";
                        } ?> selected value="<?= User::TYPE_USER_MANAGER ?>">Менеджер
                        </option>
                        <option <?php if ($user->type == User::TYPE_USER_MASTER) {
                            echo "selected";
                        } ?> value="<?= User::TYPE_USER_MASTER ?>">Ведущий
                        </option>
                        <option <?php if (($user->type == User::TYPE_USER_STUDENT) || ($user->isNewRecord)) {
                            echo "selected";
                        } ?> value="<?= User::TYPE_USER_STUDENT ?>">Слушатель
                        </option>

                        <?php if ((Yii::$app->user->identity->type == User::TYPE_USER_MANAGER) ||
                            (Yii::$app->user->identity->type == User::TYPE_USER_ADMIN)) { ?>
                            <option <?php if ($user->type == User::TYPE_USER_ADMIN) {
                                echo "selected";
                            } ?> value="<?= User::TYPE_USER_ADMIN ?>">Администратор
                            </option>
                        <?php } ?>


                    </select>
                    <label for="typeUser" class="mdl-textfield__label">Тип учетной записи:</label>
                </div>
            <?php } ?>
            <?php if ($user->type !== User::TYPE_USER_ADMIN) { ?>
                <?= $form->field($user, 'email', [
                    'template' => '<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">{input}{label}{error}</div>',
                    'labelOptions' => ['class' => 'mdl-textfield__label']
                ])->textInput(['maxlength' => true, 'class' => 'mdl-textfield__input']); ?>
                <?= $form->field($user, 'surname', [
                    'template' => '<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">{input}{label}{error}</div>',
                    'labelOptions' => ['class' => 'mdl-textfield__label']
                ])->textInput(['maxlength' => true, 'class' => 'mdl-textfield__input']); ?>
                <?= $form->field($user, 'name', [
                    'template' => '<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">{input}{label}{error}</div>',
                    'labelOptions' => ['class' => 'mdl-textfield__label']
                ])->textInput(['maxlength' => true, 'class' => 'mdl-textfield__input']); ?>
                <?= $form->field($user, 'patronymic', [
                    'template' => '<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">{input}{label}{error}</div>',
                    'labelOptions' => ['class' => 'mdl-textfield__label']
                ])->textInput(['maxlength' => true, 'class' => 'mdl-textfield__input']); ?>
                <?= $form->field($user, 'phone', [
                    'template' => '<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">{input}{label}{error}</div>',
                    'labelOptions' => ['class' => 'mdl-textfield__label']
                ])->textInput(['maxlength' => true, 'class' => 'mdl-textfield__input']); ?>
            <?php } ?>
            <div class="time-control">
                <?php
                // для админов и самого ведущего
                if ((Yii::$app->user->identity->type === User::TYPE_USER_ADMIN) ||
                    ((Yii::$app->user->identity->type === User::TYPE_USER_MASTER) && (Yii::$app->user->identity->id === $user->id))) { ?>

                    <div id="timeControllerBlock"
                         class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label is-dirty is-upgraded">
                        <label class="mdl-textfield__label" for="record_time_hours">Продолжительности записи
                            трансляции по умолчанию</label>

                        <div class="row mdl-cell--12-col">
                            <div class="mdl-cell--2-col" style="float: left">
                                <input id="record_time_hours" class="mdl-textfield__input" placeholder="Часов"
                                       type="number"
                                       name="User[hours]"
                                       value="<?php if ($user->hours !== null) echo $user->hours; ?>">часов
                            </div>

                            <div class="mdl-cell--2-col"
                                 style="float: left; margin-left: 25px">
                                <input id="record_time_minutes" class="mdl-textfield__input" placeholder="Минут"
                                       type="number"
                                       name="User[minutes]"
                                       value="<?php if ($user->minutes !== null) echo $user->minutes; ?>">минут
                            </div>
                        </div>
                        <div class="help-block" style="clear: both;">
                            <?php
                            if (isset($user->errors['record_time_min'][0])) {
                                echo $user->errors['record_time_min'][0];
                            }
                            ?>

                        </div>
                    </div>

                <?php } ?>
            </div>

        </div>
    </div>
    <div class='mdl-cell mdl-cell--1-col'></div>
    <div class='mdl-cell mdl-cell--1-col'></div>
    <div class="mdl-cell mdl-cell--11-col">
        <div class="col-sm-6 col-lg-6 col-md-6">
            <?php if (!$user->isNewRecord) { ?>
                <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                    <a href="/admin/default/update-user-pass?id=<?= $user->id ?>"
                       class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent">Отправить
                        новый
                        пароль</a>
                </div>
            <?php } ?>
        </div>
        <div class="col-sm-6 col-lg-6 col-md-6">
            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                <?= Html::submitButton($user->isNewRecord ? 'Создать' : 'Обновить', ['class' => $user->isNewRecord ? 'mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent' : 'mdl-button mdl-js-button mdl-button--raised mdl-button--accent']) ?>
            </div>
        </div>

    </div>

    <?php ActiveForm::end(); ?>
</div>