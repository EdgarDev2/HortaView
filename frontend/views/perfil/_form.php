<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use  yii\jui\DatePicker;

/* @var $this yii\web\View */
/* @var $model frontend\models\Perfil */
/* @var $form yii\widgets\ActiveForm */

$this->registerCssFile('@web/css/custom-datepicker.css', ['depends' => [\yii\jui\JuiAsset::class]]);
?>

<div class="perfil-form container mt-4">

    <?php $form = ActiveForm::begin(['options' => ['class' => 'needs-validation', 'novalidate' => true]]); ?>

    <?= $form->field($model, 'nombre')->textInput(['maxlength' => 45, 'class' => 'form-control'])->label('Nombre') ?>
    <?= $form->field($model, 'apellido')->textInput(['maxlength' => 45, 'class' => 'form-control'])->label('Apellido') ?>
    <?php
    /* Cambia los temas del calendario segun tus necesidades:
    *  Ruta de temas: vendor/bower-asset/jquery-ui/themes/. podrías usar-> base, flick, redmond.
    *  Modifica juiAsset.php para aplicar los themes. En el vector: public $css[]; Ruta del archivo -> yisoft/yii2-jui/juiAsset.php
    */
    echo $form->field($model, 'fecha_nacimiento')->widget(DatePicker::class, [
        'dateFormat' => 'yyyy-MM-dd',
        'clientOptions' => [
            'yearRange' => '-115:+0',
            'changeYear' => true,
            'beforeShow' => new \yii\web\JsExpression('function(input, inst) {
                    $(inst.dpDiv).addClass("custom-datepicker");  //CSS al calendario
                }'),
        ],
        'options' => ['class' => 'form-control', 'placeholder' => 'Selecciona la fecha']
    ]);
    ?>
    <div class="mb-3">
        <?= $form->field($model, 'genero_id')->dropDownList($model->generoLista, ['prompt' => 'Seleccione el genero', 'class' => 'form-control'])->label('Género') ?>
    </div>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Crear' : 'Actualizar', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>