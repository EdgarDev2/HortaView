<?php

use yii\helpers\Html;

$this->title = 'Temperatura ambiente';
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
?>

<div class="filtrar-humedad-por-rango-index">
    <h1 class="display-6 text-secondary text-left mb-3"><?= Html::encode($this->title) ?></h1>
    <div class="row">
        <!-- Botones de gráfico y filtros -->
        <div class="col-md-12 d-flex flex-wrap align-items-center gap-2">
            <!-- Botones de tipo de gráfico -->
            <div class="btn-group" role="group" aria-label="Gráficos">
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
            <!-- Botón Filtrar -->
            <div>
                <button id="btnFiltrar" class="btn btn-outline-primary btn-sm border-0 shadow-none">Filtrar datos</button>
            </div>
        </div>
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

<script>
    // Función para realizar la solicitud AJAX
    function cargarDatos(fechaInicio, fechaFin) {
        console.log('Datos enviados:', {
            fechaInicio,
            fechaFin,
        });
        $.ajax({
            type: 'POST',
            url: 'index.php?r=tiempo-real-temperatura-ambiente/obtener-temperaturas',
            data: {
                fechaInicio,
                fechaFin,
            },
            success: function(response) {
                if (response.success) {
                    inicializarGrafico(response);
                } else {
                    alert(response.message || 'Error al cargar los datos.');
                }
            },
        });
    }

    // Configurar el botón de filtrar
    document.getElementById('btnFiltrar').addEventListener('click', function() {
        const fechaInicio = document.getElementById('fechaInicio').value;
        const fechaFin = document.getElementById('fechaFin').value;

        if (!fechaInicio || !fechaFin) {
            alert('Por favor, completa todos los filtros.');
            return;
        }
        cargarDatos(fechaInicio, fechaFin);
    });


    document.addEventListener('DOMContentLoaded', function() {
        let dataContainer = document.getElementById('data-container');
        let desde = dataContainer.dataset.fechaInicio; // Solo la fecha
        let hasta = dataContainer.dataset.fechaFin; // Solo la fecha
        document.getElementById('fechaInicio').value = desde;
        document.getElementById('fechaFin').value = hasta;

        $(document).ready(function() {
            setTimeout(clickbutton, 10);

            function clickbutton() {
                $("#btnFiltrar").click();
            }
        });
        //iniciarActualizacionAutomatica();
    });
</script>