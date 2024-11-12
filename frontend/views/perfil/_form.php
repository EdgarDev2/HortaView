<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

use  yii\jui\DatePicker;

/* @var $this yii\web\View */
/* @var $model frontend\models\Perfil */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="perfil-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'nombre')->textInput(['maxlength' => 45]) ?>

    <?= $form->field($model, 'apellido')->textInput(['maxlength' => 45]) ?>

    <?php  echo  $form->field($model,'fecha_nacimiento')->widget(DatePicker::className(),[
                                                                        'dateFormat'  =>  'yyyy-MM-dd',
                                                                        'clientOptions'  =>  [
                                                                        'yearRange'  =>  '-115:+0',
                                                                        'changeYear'  =>  true]
                                                        ])  ?>

    <!-- <?= $form->field($model, 'fecha_nacimiento')->textInput() ?>
    * por favor use el formato YYYY-MM-DD -->

    <?= $form->field($model, 'genero_id')->dropDownList($model->generoLista, ['prompt' => 'Seleccione el genero' ]);?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
