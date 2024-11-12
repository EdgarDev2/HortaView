<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Inicio';
$this->registerCssFile('@web/css/custom_styles.css', ['depends' => [\yii\web\YiiAsset::class]]);
?>

<div class="site-index">
    <div class="body-content d-flex flex-column justify-content-center" style="min-height: 80vh;">
        <section class="row">
            <div class="col-12 col-md-6 d-flex flex-column justify-content-center">
                <div class="text text-center text-md-start">
                    <h1 class="display-4 text-dark">MONITOREO WEB EN TIEMPO REAL DE HORTALIZAS</h1>
                    <p class="lead text-dark">En el Instituto Tecnológico Superior de Valladolid se elaboró este proyecto con el fin de monitorear
                        las variables ambientales cruciales...</p>
                    <div class="buttons mt-4">
                        <?= Html::a('SEGUIR LEYENDO', ['/site/about'], ['class' => 'btn btn-outline-success btn-lg', 'title' => 'Más información sobre el proyecto']) ?>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 d-flex justify-content-center align-items-center mt-4 mt-md-0">
                <div class="image position-relative">
                    <?= Html::img('@web/images/cilantro1.png', ['class' => 'img-fluid rounded', 'alt' => 'Imagen de cilantro, utilizado en el monitoreo de hortalizas.']) ?>
                </div>
            </div>
        </section>
    </div>
</div>