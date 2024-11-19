<?php
$this->title = 'Gráficas';

use yii\helpers\Html;

$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@1.2.1/dist/chartjs-plugin-zoom.min.js');

?>

<div class="graficas-index">
    <h1 class="display-6 text-success text-left">Filtrar datos de humedad del suelo</h1>
    <h5 class="text-success fw-normal text-end">Fecha actual: <span class="text-secondary"><?= Html::encode($fechaActual) ?></span></h5>
    <hr class="border border-success">
    <?= $this->render('_graficas', [
        'promedios' => $promedios,
        'maximos' => $maximos,
        'minimos' => $minimos,
        'fechaActual' => $fechaActual,
    ]) ?>
    <div class="row">
        <?= $this->render('_search', []) ?>
        <?php for ($i = 1; $i <= 4; $i++): ?>
            <div class="col-md-6">
                <h5 class="text-secondary">Humedad por día cama Ka'anche' <?= $i ?></h5>

                <div class="btn-toolbar mb-3" role="toolbar" aria-label="Toolbar with button groups">
                    <div class="btn-group me-2" role="group" aria-label="First group">
                        <button class="btn btn-outline-success border-0" type="button" onclick="cambiarTipoGrafico('line', 'graficoCama<?= $i ?>')">
                            <i class="fas fa-chart-line"></i>
                        </button>
                        <button class="btn btn-outline-success border-0" type="button" onclick="cambiarTipoGrafico('bar', 'graficoCama<?= $i ?>')">
                            <i class="fas fa-chart-bar"></i>
                        </button>
                        <button class="btn btn-outline-success border-0" type="button" onclick="cambiarTipoGrafico('radar', 'graficoCama<?= $i ?>')">
                            <i class="fas fa-chart-pie"></i>
                        </button>
                        <button class="btn btn-outline-success border-0" type="button" onclick="cambiarTipoGrafico('default', 'graficoCama<?= $i ?>')">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <!-- Botón para descargar el gráfico -->
                        <button class="btn btn-outline-success border-0" type="button" onclick="descargarGrafico('graficoCama<?= $i ?>')">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                    <div class="input-group">
                        <input type="date"
                            class="form-control placeholder-wave bg-transparent text-secondary"
                            id="fechaCama<?= $i ?>"
                            name="fechaCama<?= $i ?>"
                            style="width: 140px; border: none;"
                            title="Selecciona una fecha para filtrar los datos">
                    </div>
                </div>
                <canvas id="graficoCama<?= $i ?>"></canvas>
            </div>
        <?php endfor; ?>
    </div>

</div>