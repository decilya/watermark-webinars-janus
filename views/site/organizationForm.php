<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\alert\Alert;

/* @var $this yii\web\View */
/* @var $organization app\models\Organization */
/* @var $form yii\widgets\ActiveForm */

?>

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


