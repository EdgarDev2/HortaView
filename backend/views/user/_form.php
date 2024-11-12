<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\User $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'estado_id')->dropDownList($model->estadoLista, [ 'prompt' => 'Por Favor Elija Uno' ]);?>
 
    <?= $form->field($model, 'rol_id')->dropDownList($model->rolLista, [ 'prompt' => 'Por Favor Elija Uno' ]);?>
            
    <?= $form->field($model, 'tipo_usuario_id')->dropDownList($model->tipoUsuarioLista, [ 'prompt' => 'Por Favor Elija Uno' ]);?>
    
    <?= $form->field($model, 'username')->textInput(['maxlength' => 255]) ?>
    
    <?= $form->field($model, 'email')->textInput(['maxlength' => 255]) ?>
    
    <div class="form-group">
    <?= Html::submitButton($model->isNewRecord ? 'Creare' : 'Actualizar', 
                        ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
</div>

    <?php ActiveForm::end(); ?>

</div>
