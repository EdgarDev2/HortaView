<?php

/** @var \yii\web\View $this */
/** @var string $content */

use common\widgets\Alert;
use frontend\assets\AppAsset;
use yii\bootstrap5\Breadcrumbs;  //use  yii\widgets\Breadcrumbs;
use yii\bootstrap5\Html;         //use  yii\helpers\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

use frontend\assets\FontAwesomeAsset;

AppAsset::register($this);
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
        NavBar::begin([
            'brandLabel' => Html::img('@web/images/house-fill-light.svg', ['alt' => Yii::$app->name]) . ' ' . 'HortaView',
            'brandUrl' => Yii::$app->user->isGuest ? Yii::$app->homeUrl : ['/variables-ambientales/index'], // URL diferente según el estado de autenticación
            'options' => [
                'class' => 'navbar navbar-expand-md navbar-dark bg-success fixed-top',
            ],
        ]);
        // Array de elementos para nav.
        $menuItems = [];

        // Verificar si el usuario es un invitado (no autenticado)
        if (Yii::$app->user->isGuest) {
            // Si es un invitado, solo mostrar estas opciones
            $menuItems = [
                ['label' => 'Acerca de', 'url' => ['/site/about']],
                //['label' => 'Contacto', 'url' => ['/site/contact']],
            ];
        } else {
            // Aquí iría el código para los usuarios autenticados
            $menuItems = [
                ['label' => 'Tiempo Real', 'url' => ['/tiempo-real/index']],
                ['label' => 'Graficas', 'url' => ['/graficas/index']],
                ['label' => 'Predicciones', 'url' => ['/predicciones/index']],
                ['label' => 'Acerca de', 'url' => ['/site/about']],
                //['label' => 'Contacto', 'url' => ['/site/contact']],
            ];
        }
        // Renderizar el menu
        echo Nav::widget([
            'options' => ['class' => 'navbar-nav me-auto mb-2 mb-md-0'],
            'items' => $menuItems,
        ]);
        //bloque para iniciar sesión
        if (Yii::$app->user->isGuest) {
            //$menuItems[]  =  ['label'  =>  'Signup',  'url'  =>  ['/site/signup']];  //Se agrego
            //$menuItems[]  =  ['label'  =>  'Login',  'url'  =>  ['/site/login']];    //Se agrego

            echo Html::tag('div', Html::a('Registrarse', ['/site/signup'], ['class' => ['btn btn-success border-0 text-decoration-none']]), ['class' => ['d-flex']]);
            echo Html::tag('div', Html::a('Login', ['/site/login'], ['class' => ['btn btn-success border-0 text-decoration-none']]), ['class' => ['d-flex']]);
        } else {
            //echo Html::tag('div', Html::a('Perfil', ['/perfil/view'], ['class' => ['btn btn-link login text-decoration-none']]), ['class' => ['d-flex']]);

            echo Html::beginForm(['/site/logout'], 'post', ['class' => 'd-flex'])
                . Html::submitButton(
                    'Salir (' . Yii::$app->user->identity->username . ')',
                    ['class' => 'btn btn-link logout text-decoration-none']
                )
                . Html::endForm();
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
            <p class="float-start">&copy; Edgar Manuel Poot Ku <?= date('Y') ?></p>
            <p class="float-end">Instituto Tenológico Superior de Valladolid</p>
        </div>
    </footer>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage();
