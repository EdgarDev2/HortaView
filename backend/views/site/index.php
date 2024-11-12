<?php

use yii\helpers\Html;
use common\models\PermisosHelpers;

use yii\helpers\Url;


/** @var yii\web\View $this */

$this->title = 'Sistema XXX';
$es_admin = PermisosHelpers::requerirMinimoRol('Admin');

?>

<div class="site-index">
<div class="container">
    <center>
        <p></p>
        <IMG src=  <?php echo Url::to('@web/archivos/rbac.jpeg',true); ?> width="30%" height="30%"  BORDER=0 ALT="Imagen de Enzabezado" ALIGN="center">
        
        <hr>
            <h4 style=" background:"> Navegador Recomendado<a href=<?php echo Url::to('https://www.mozilla.org/es-MX/firefox/new/',true); ?> target="_blank" > Firefox </a> para un mejor desempeño del sistema

            <?php 
                //   $id_user = Yii::$app->user->identity->getId();
                //   $nombreRol = User::findOne(['id'=>$id_user])->rol->rol_nombre;
                //   echo "<p><p>ROL = " . $nombreRol . " VALOR ROL= " . User::findOne(['id'=>$id_user])->rol->rol_valor;
                //   echo "<p>User ID=" . $id_user;
            ?>

            </h4>
        </center>
    </div>
</div>