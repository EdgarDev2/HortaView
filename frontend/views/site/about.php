<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'About';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <div class="body-content d-flex flex-column justify-content-center" style="min-height: 80vh;">
        <section class="row">
            <!-- Imagen que estará arriba en dispositivos móviles -->
            <div class="col-12 col-md-6 d-flex justify-content-center align-items-center order-1 order-md-1">
                <div class="image position-relative">
                    <?= Html::img('@web/images/rabano1.png', ['class' => 'img-fluid rounded', 'alt' => 'Imagen de rabano, utilizado en acerca de.']) ?>
                </div>
            </div>
            <!-- Contenedor de texto que estará abajo de la imagen en móviles -->
            <div class="col-12 col-md-6 d-flex flex-column justify-content-center order-2 order-md-2">
                <div class="text text-center text-md-start">
                    <h1 class="display-4 text-dark">MONITOREO WEB EN TIEMPO REAL DE HORTALIZAS</h1>
                    <p class="lead text-dark" style="text-align: justify;">
                        En el Instituto Tecnológico Superior de Valladolid se llevó a cabo este proyecto con el objetivo de
                        monitorear las variables ambientales fundamentales para el cultivo de hortalizas. Además, se proporciona
                        al cliente un monitor estadístico, gráficos predictivos, gráficos de comparación y otros recursos analíticos.
                        Este sistema optimiza la producción agrícola mediante el análisis en tiempo real de datos relevantes. El proyecto web
                        fue desarrollado por el <strong>Ing. Edgar Manuel Poot Ku</strong> bajo la supervisión del <strong>Dr. Russell Renan Iuit Rios</strong>, colaborador del proyecto
                        <em>"Monitoreo y Optimización Inteligente de la Producción de Hortalizas en Cultivos Mayas en la Zona Oriente de
                            Yucatán. Fase II”</em>, dirigido por el <strong>Dr. Jesús Antonio Santos Tejero</strong>.
                    </p>
                    <div class="buttons mt-4">
                        <?= Html::a('Registrase', ['/site/signup'], ['class' => 'btn btn-outline-primary btn-lg float-end', 'title' => 'Más información sobre el proyecto']) ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>