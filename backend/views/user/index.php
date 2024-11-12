<?php
 
use yii\helpers\Html;
use yii\grid\GridView;
use \yii\bootstrap5\Accordion;
 
/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
 
$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">
 
    <h1><?= Html::encode($this->title) ?></h1>
 
     <?php  // echo Collapse::widget(['items' => [
                                            // equivalente a lo de arriba
                                           //  [
                                           //     'label' => 'Search',
                                            //    'content' => $this->render('_search', ['model' => $searchModel]) ,
                                                // open its content by default
                                                //'contentOptions' => ['class' => 'in']
                                           // ],
                                            
                                    //] 
                                   // ]);  
    ?> 

   
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
    
    <p> </p>
    
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
 
            //'id',
            ['attribute'=>'userIdLink', 'format'=>'raw'],
            ['attribute'=>'userLink', 'format'=>'raw'],
            ['attribute'=>'perfilLink', 'format'=>'raw'],
           
            'email:email',
            'rolNombre',
            'tipoUsuarioNombre',
            'estadoNombre',
            'created_at',
          
           ['class' => 'yii\grid\ActionColumn'],
           
            
            // 'updated_at',
 
            
        ],
    ]); ?>
 
</div>