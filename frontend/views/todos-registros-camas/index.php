<?php
$this->title = 'Todos los registros';
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@1.2.1/dist/chartjs-plugin-zoom.min.js');
$this->registerCssFile('@web/css/chart_card.css');
// Clases comunes bootstrap
$btnClass = 'btn btn-outline-success btn-sm border-0 shadow-none';
$btnDownloadClass = 'btn btn-outline-primary btn-sm border-0 shadow-none';
$cardInputDate = 'input-group input-group-sm d-flex align-items-center gap-2 bg-light rounded px-2';
$inputDate = 'form-control placeholder-wave bg-transparent text-secondary';
$selectPlace = 'form-select placeholder-wave border-0 text-secondary bg-light rounded';

use yii\helpers\Html;
?>

<div class="filtrar-humedad-por-rango-index">
    <h1 class="display-6 text-secondary text-left mb-3"><?= Html::encode($this->title) ?></h1>
    <div class="row">
        <!-- Botones de gráfico y filtros -->
        <div class="col-md-12 d-flex flex-wrap align-items-center gap-2">
            <!-- Botones de tipo de gráfico -->
            <div class="btn-group" role="group" aria-label="Gráficos">
                <button class="<?= $btnClass ?>" type="button" title="Gráfico de tipo Lineal" onclick="cambiarTipoGrafico('line')">
                    <i class="fas fa-chart-line"></i> Lineal
                </button>
                <button class="<?= $btnClass ?>" type="button" title="Gráfico de tipo Barra" onclick="cambiarTipoGrafico('bar')">
                    <i class="fas fa-chart-bar"></i> Barra
                </button>
                <button class="<?= $btnClass ?>" type="button" title="Gráfico de tipo Radar" onclick="cambiarTipoGrafico('radar')">
                    <i class="fas fa-chart-pie"></i> Radar
                </button>
                <button class="<?= $btnDownloadClass ?>" type="button" title="Descargar gráfico como imagen" onclick="descargarImagen('graficoCama', 'grafico_cama.png')">
                    <i class="fas fa-download"></i> Descargar
                </button>
            </div>
            <!-- Filtros de fecha -->
            <div class="<?= $cardInputDate ?>" style="max-width: 250px;">
                <label for="fechaInicio" class="form-label mb-0 text-secondary">Fecha Inicio:</label>
                <input type="date" id="fechaInicio" class="<?= $inputDate ?>" style="width: 140px; border: none;" min="<?= $fechaInicio ?>" max="<?= $fechaFin ?>">
            </div>
            <div class=" <?= $cardInputDate ?>" style="max-width: 250px;">
                <label for="fechaFin" class="form-label mb-0 text-secondary">Fecha Fin:</label>
                <input type="date" id="fechaFin" class="<?= $inputDate ?>" style="width: 140px; border: none;" min="<?= $fechaInicio ?>" max="<?= $fechaFin ?>">
            </div>
            <!-- Selector de cama -->
            <div class="input-group input-group-sm" style="max-width: 162px;">
                <select id="camaId" class="<?= $selectPlace ?>" title="Selecciona cama">
                    <option value="" disabled selected>Seleccionar cama</option>
                    <option value="1">Ka'anche' 1</option>
                    <option value="2">Ka'anche' 2</option>
                    <option value="3">Ka'anche' 3</option>
                    <option value="4">Ka'anche' 4</option>
                </select>
            </div>
            <!-- Botón Filtrar -->
            <div>
                <button id="btnFiltrar" class="btn btn-outline-primary btn-sm border-0 shadow-none">Filtrar datos</button>
            </div>
            <?= Html::a('Regresar', ['/filtrar-humedad-por-rango/index'], ['class' => 'btn btn-outline-primary btn-sm border-0']) ?>
        </div>
        <div class="chartCard">
            <div class="chartBox">
                <canvas id="graficoCama" class="mt-4"></canvas>
            </div>
        </div>
    </div>
    <!-- Pasamos los datos de la sesión a JS -->
    <div id="data-container"
        data-ciclo="<?= $cicloSeleccionado ?>"
        data-fecha-inicio="<?= $fechaInicio ?>"
        data-fecha-fin="<?= $fechaFin ?>">
    </div>

</div>