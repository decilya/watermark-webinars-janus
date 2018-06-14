<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\User;

/**
 * @var $user User
 */
?>

<div class="manager-form">

    <?php
    $form = ActiveForm::begin([
        'enableAjaxValidation' => true,
        'options' => [
            'name' => 'managerForm'
        ],
        'id' => 'managerForm']);
    ?>

    <h1><?= ($user->isNewRecord) ? 'Создание пользователя' : 'Редактирование пользователя ' . $user->username; ?></h1>

    <div class="row form-group field-user-username required">
        <label for="typeUser">Тип учетной записи:</label>
        <select class="form-control" name="typeUser" id="typeUser" required="">
            <option <?php if ($user->type == User::TYPE_USER_MANAGER) {
                echo "selected";
            } ?> selected value="<?= User::TYPE_USER_MANAGER ?>">Менеджер
            </option>
            <option <?php if ($user->type == User::TYPE_USER_MASTER) {
                echo "selected";
            } ?>  value="<?= User::TYPE_USER_MASTER ?>">Ведущий
            </option>
            <option <?php if (($user->type == User::TYPE_USER_STUDENT) || ($user->isNewRecord)) {
                echo "selected";
            } ?>   value="<?= User::TYPE_USER_STUDENT ?>">Слушатель
            </option>
        </select>
    </div>

    <div id="blockNewUser" class="row">
        <?= $form->field($user, 'email')->textInput(['maxlength' => true]); ?>
        <?= $form->field($user, 'surname')->textInput(['maxlength' => true]); ?>
        <?= $form->field($user, 'name')->textInput(['maxlength' => true]); ?>
        <?= $form->field($user, 'patronymic')->textInput(['maxlength' => true]); ?>
        <?= $form->field($user, 'phone')->textInput(['maxlength' => true]); ?>
    </div>

    <div class="row">
        <div class="col-sm-6 col-lg-6 col-md-6">
            <?php if (!$user->isNewRecord) { ?>
                <div class="form-group text-right">
                    <a href="/admin/default/update-user-pass?id=<?= $user->id ?>" class="btn btn-danger">Отправить новый
                        пароль</a>
                </div>
            <?php } ?>
        </div>
        <div class="col-sm-6 col-lg-6 col-md-6">
            <div class="form-group text-right">
                <?= Html::submitButton($user->isNewRecord ? 'Создать' : 'Обновить', ['class' => $user->isNewRecord ? 'btn btn-success' : 'btn btn-info']) ?>
            </div>
        </div>

    </div>

    <?php ActiveForm::end(); ?>
</div>