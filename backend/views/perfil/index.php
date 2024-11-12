<?php

use frontend\models\Perfil;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

use \yii\bootstrap5\Accordion;

/** @var yii\web\View $this */
/** @var backend\models\search\PerfilSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Perfiles';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="perfil-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <!-- <p>
        <?= Html::a('Create Perfil', ['create'], ['class' => 'btn btn-success']) ?>
    </p> -->

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php  echo Accordion::widget([
                            'items' => [
                                // equivalent to the above
                                [
                                    'label' => 'Usuario a Buscar ',
                                    'content' => $this->render('_search', ['model' => $searchModel]) ,
                                    // open its content by default
                                    'contentOptions' => ['class' => 'in']
                                ],
                                   // if you want to swap out .card-block with .list-group, you may use the following
                            ]
                        ]);
    ?> 
    <p></p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
            ['attribute'=>'perfilIdLink', 'format'=>'raw'],
            ['attribute'=>'userLink', 'format'=>'raw'],
            //'user_id',
            'nombre:ntext',
            'apellido:ntext',
            'fecha_nacimiento',
            'generoNombre',
            //'genero_id',
            //'created_at',
            //'updated_at',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Perfil $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
