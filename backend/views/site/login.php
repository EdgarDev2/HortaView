<?php
/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \common\models\LoginForm $model */
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Acceso al Sistema';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container">   
    <!-- <p>Please fill out the following fields to login:</p> -->
    <div class="row">      
      <div class="col-md-4 offset-md-4" >                                             <!--#f2f2f2  #33d6ff -->
        <div class="account-wall" align=center style="Background-color:#33d6ff;
                                                          border:1px solid #00a3cc;
                                                          border-radius:40px;
                                                          padding:15px;
                                                          box-shadow: 0 4px 16px 8px rgba(0, 0, 0, 0.2),
                                                          0 6px 20px 0 rgba(0, 0, 0, 0.19);">
                <h3 class="text-center login-title"><?= Html::encode($this->title) ?></h3>
                <img src= <?php echo Url::to('@web/archivos/avatar.png',true);?> width="40%"
                                                                                 height="25%"
                                                                                 BORDER=0
                                                                                 ALT="Imagen de Enzabezado">
                <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

                    <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>
                    <?= $form->field($model, 'password')->passwordInput() ?>
                    <?= $form->field($model, 'rememberMe')->checkbox() ?>
                    <div style="color:#999;margin:1em 0">
                    Si olvidó su contraseña, puede
                    <a href="http://transferencia.valladolid.tecnm.mx/frontend/web/index.php?r=site%2Frequest-password-reset" id="liga">reiniciarlo </a>
                    <!--<a href="http://localhost/transferencia/frontend/web/index.php?r=site%2Frequest-password-reset" id="liga">reiniciarlo </a> -->
                    <!-- <?= Html::a('reiniciarlo', ['site/request-password-reset']) ?>.    ESTE ES EL ORIGINAL EN FRONTEND -->
                    </div>
                    <div class="form-group">
                        <?= Html::submitButton('Login', ['class' => 'btn btn-lg btn-primary btn-block', 'name' => 'login-button']) ?>
                    </div>
                <?php ActiveForm::end(); ?>                  
        </div > <!-- account-wall -->      
      </div> <!-- col-md-4 offset-md- -->                
    </div> <!-- row -->
</div> <!-- container -->

