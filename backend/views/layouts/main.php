<?php

/** @var \yii\web\View $this */
/** @var string $content */

use backend\assets\AppAsset;
use common\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

use  common\models\PermisosHelpers;

use backend\assets\FontAwesomeAsset;
use common\models\User;

AppAsset::register($this);
FontAwesomeAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    
</head>

<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<header>
    <?php

    if (!Yii::$app->user->isGuest){
        $es_admin = PermisosHelpers::requerirMinimoRol('Admin');

        $id_user = Yii::$app->user->identity->getId();
        $nombreRol = User::findOne(['id'=>$id_user])->rol->rol_nombre;
        
        NavBar::begin([
            'brandLabel' => '<i class="fa fa-home fa-2x" style="padding: 0px 5px 5px 5px;
                                                                margin: 0px 5px 10px 0px;
                                                                background-color:#000099;
                                                                border-radius: 10px;"></i> Rol actual: '. $nombreRol,
            'brandUrl' => Yii::$app->homeUrl,
            'options' => [
                'class' => 'navbar navbar-expand-md navbar-dark bg-dark fixed-top',
                //navbar navbar-expand-md navbar-dark bg-dark fixed-top
                ],
            ]);
            $menuItems = [ ]; // Se manejo este arreglo vacio para poder alinear a la derecha el menu en Backend
    }else {
        NavBar::begin([
            'brandLabel' => 'Sistema XXX',            //Yii::$app->name,
            'brandUrl' => Yii::$app->homeUrl,
            'options' => [
                'class' => 'navbar navbar-expand-md navbar-dark bg-dark fixed-top',
                ],
            ]);
            $menuItems = [
                ['label' => 'Home', 'url' => ['/site/index']],
        ];
    }

    // echo Nav::widget([
    //         'options' => ['class' => 'navbar-nav me-auto mb-2 mb-md-0'],
    //         'items' => $menuItems,
    // ]);      
             
    if (!Yii::$app->user->isGuest && $es_admin) {

            $id_user = Yii::$app->user->identity->getId();
            $nombreRol = User::findOne(['id'=>$id_user])->rol->rol_nombre;
            $menuItems[] = [
            ['label'=>$nombreRol],
            'label' => 'Administración', 'url' => ['site/index'],
            'options' =>['class' =>'dropdown'],
            'template'=>'<a href="{url}" class="href_class">{label}</a>',
            'items' =>[ ['label' => 'Usuarios', 'url' => ['/user']],
                        //['label' => 'Perfiles', 'url' => ['/perfil']],
                        ['label' => 'Roles', 'url' => ['/rol']],   
                        ['label' => 'Tipos de Usuario', 'url' => ['/tipo-usuario']],  
                        ['label' => 'Estados Usuarios', 'url' => ['/estado']],                                               
                    ],
            ];
            $menuItems[] = ['label' => 'Catalogos', 'url' => ['/site/index'],
            'options' =>['class' =>'dropdown'],
            'template'=>'<a href="{url}" class="href_class">{label}</a>',
            'items' =>[ ['label' => 'Productos', 'url' => ['/asignatura']],
            
                        ],
            ]; 
            $menuItems[] = ['label' => 'Transacciones', 'url' => ['/site/index'],
            'options' =>['class' =>'dropdown'],
            'template'=>'<a href="{url}" class="href_class">{label}</a>',
            'items' =>[ ['label' => 'Ventas', 'url' => ['/asignatura']],
            
                        ],
            ];   
        // echo Html::tag('div',Html::a('Usuarios',['/user/index'],
        //                                 ['class' => ['btn btn-link login text-decoration-none']]),
        //                                 ['class' => ['d-flex']]);

        // echo Html::tag('div',Html::a('Perfiles',['/perfil/index'],
        //                                 ['class' => ['btn btn-link login text-decoration-none']]),
        //                                 ['class' => ['d-flex']]);
            
        // echo Html::tag('div',Html::a('Roles',['/rol/index'],
        //                                 ['class' => ['btn btn-link login text-decoration-none']]),
        //                                 ['class' => ['d-flex']]);

        // echo Html::tag('div',Html::a('Tipo de Usuario',['/tipo-usuario/index'],
        //                                 ['class' => ['btn btn-link login text-decoration-none']]),
        //                                 ['class' => ['d-flex']]);

        // echo Html::tag('div',Html::a('Estados',['/estado/index'],
        //                                 ['class' => ['btn btn-link login text-decoration-none']]),
        //                                 ['class' => ['d-flex']]);      
    }   

    echo Nav::widget([
            'options' => ['class' => 'navbar-nav me-auto mb-2 mb-md-0'],
            'items' => $menuItems,
    ]); 
    
    if (Yii::$app->user->isGuest) {

        $menuItems[] = ['label' => 'Login', 'url' => ['site/login']];
    }else{
        // $menuItems[] = ['label' => 'Logout (' . Yii::$app->user->identity->username . ')',
        //             'url' => ['/site/logout'],
        //             'linkOptions' => ['data-method' => 'post']
        //       ];
        echo Html::beginForm(['/site/logout'], 'post', ['class' => 'navbar'])
                . Html::submitButton(
                    'Salir (' . Yii::$app->user->identity->username . ')',
                    ['class' => 'btn btn-link logout text-decoration-none form-control me-2']
                )
                . Html::endForm();

        // $menuItems[] = ['label' => 'Login', 'url' => ['site/login']];       
    }
   
    NavBar::end();
    ?>
</header>

<main role="main" class="flex-shrink-0">
    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</main>

<footer class="footer mt-auto py-3 text-muted">
    <div class="container">
        <p class="float-start">&copy;
                                      <!-- <?= Html::encode('Sistema XXX') ?> -->
                                      <?= date('Y')?>
                                      <spam></spam>Dr. Rusell Renan Iuit Manzanero
        </p>
        <!-- <p class="float-end"><?= Yii::powered() ?></p> -->
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage();
