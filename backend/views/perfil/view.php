<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

use  common\models\PermisosHelpers;

/** @var yii\web\View $this */
/** @var frontend\models\Perfil $model */

$this->title =  $model->user->username;

$mostrar_esta_nav  =  PermisosHelpers::requerirMinimoRol('SuperUsuario');

$this->params['breadcrumbs'][] = ['label' => 'Perfil', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="perfil-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
    <?php  if  (!Yii::$app->user->isGuest  &&  $mostrar_esta_nav)  {
               echo  Html::a('Update',  ['update',  'id'  =>  $model->id],['class'  =>  'btn  btn-primary']);
     }?>

    <?php  if  (!Yii::$app->user->isGuest  &&  $mostrar_esta_nav)  {
               echo  Html::a('Delete',  ['delete',  'id'  =>  $model->id],  ['class'  =>  'btn  btn-danger',
                'data'  =>  ['confirm'  =>  Yii::t('app',  'Are  you  sure  you  want  to  delete  this  item?'),
                             'method'  =>  'post',
                            ],
    ]);}?>

        <!-- <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?> -->


    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            ['attribute'=>'userLink',  'format'=>'raw'],
            'id',
            //'user_id',
            'nombre:ntext',
            'apellido:ntext',
            'fecha_nacimiento',
            'genero.genero_nombre',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
