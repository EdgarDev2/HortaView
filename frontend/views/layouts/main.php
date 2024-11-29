<?php

/** @var \yii\web\View $this */
/** @var string $content */

use common\widgets\Alert;
use frontend\assets\AppAsset;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Url;

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

        $menuItems = [];

        if (Yii::$app->user->isGuest) {
            $menuItems = [
                ['label' => 'Acerca de', 'url' => ['/site/about']],
            ];
        } else {
            // Usuarios autenticados
            $menuItems = [
                [
                    'label' => 'Tiempo real',
                    'items' => [ // Submenús
                        ['label' => 'Humedad del suelo', 'url' => ['/tiempo-real-humedad-suelo/index']],
                        ['label' => 'Temperatura ambiente', 'url' => ['/tiempo-real-temperatura-ambiente/index']],
                        ['label' => 'Presión barométrica', 'url' => ['/tiempo-real-barometrica/index']],
                    ],
                ],
                [
                    'label' => 'Filtrar datos humedad',
                    'items' => [ // Submenús
                        ['label' => 'Humedad del suelo por día', 'url' => ['/filtrar-humedad-por-dia/index']],
                        ['label' => 'Humedad del suelo por rango', 'url' => ['/filtrar-humedad-por-rango/index']],
                        ['label' => 'Humedad del suelo por ciclo', 'url' => ['/filtrar-humedad-por-ciclo/index']],
                    ],
                ],
                ['label' => 'Estadísticas generales', 'url' => ['/estadisticas-generales/index']],
                [
                    'label' => 'Predicciones',
                    'items' => [ // Submenús
                        ['label' => 'Corto plazo', 'url' => ['/predicciones/index']],
                        ['label' => 'Mediano plazo', 'url' => ['/predicciones/mediano-plazo']],
                        ['label' => 'Largo plazo', 'url' => ['/predicciones/largo-plazo']],
                    ],
                ],
                ['label' => 'Acerca de', 'url' => ['/site/about']],
            ];
        }

        // Renderizar el menú
        echo Nav::widget([
            'options' => ['class' => 'navbar-nav me-auto mb-2 mb-md-0'],
            'items' => $menuItems,
        ]);

        $currentUrl = Url::current();
        $defaultColor = '#A8D5BA';
        $activeColor = '#ffffff';
        $this->registerCss("a.custom-link:hover {color: #C5E1D4 !important;}");

        // Bloque para iniciar sesión
        if (Yii::$app->user->isGuest) {
            $signupUrl = Url::to(['/site/signup']);
            $isSignupActive = $currentUrl === $signupUrl;
            echo Html::tag('div', Html::a('Registrarse', ['/site/signup'], ['class' => 'custom-link btn bg-transparent border-0 text-decoration-none', 'style' => 'color: ' . ($isSignupActive ? $activeColor : $defaultColor)]), ['class' => 'd-flex']);

            $loginUrl = Url::to(['/site/login']);
            $isLoginActive = $currentUrl === $loginUrl;
            echo Html::tag('div', Html::a('Login', ['/site/login'], ['class' => 'custom-link btn bg-transparent border-0 text-decoration-none', 'style' => 'color: ' . ($isLoginActive ? $activeColor : $defaultColor)]), ['class' => 'd-flex']);
        } else {
            $targetUrls = [Url::to(['/perfil/view']), Url::to(['/perfil/create'])];
            $isActive = in_array($currentUrl, $targetUrls);
            echo Html::tag('div', Html::a('Perfil', ['/perfil/view'], ['class' => 'custom-link btn bg-transparent border-0 text-decoration-none', 'style' => 'color: ' . ($isActive ? $activeColor : $defaultColor)]), ['class' => 'd-flex']);

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
