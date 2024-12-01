<?php
$this->title = 'Filtrar humedad por rango';

use yii\helpers\Html;

$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@1.2.1/dist/chartjs-plugin-zoom.min.js');
?>

<div class="filtrar-humedad-por-rango-index">
    <div class="row mt-2">
        <!-- Botones de gráfico y filtros -->
        <div class="col-md-12 d-flex flex-wrap align-items-center gap-2">
            <!-- Botones de tipo de gráfico -->
            <div class="btn-group" role="group" aria-label="Gráficos">
                <button class="btn btn-outline-success btn-sm border-0 shadow-none" type="button" title="Gráfico de tipo Lineal" onclick="cambiarTipoGrafico('line', 'graficoCama')">
                    <i class="fas fa-chart-line"></i> Lineal
                </button>
                <button class="btn btn-outline-success btn-sm border-0 shadow-none" type="button" title="Gráfico de tipo Barra" onclick="cambiarTipoGrafico('bar', 'graficoCama')">
                    <i class="fas fa-chart-bar"></i> Barra
                </button>
                <button class="btn btn-outline-success btn-sm border-0 shadow-none" type="button" title="Gráfico de tipo Radar" onclick="cambiarTipoGrafico('radar', 'graficoCama')">
                    <i class="fas fa-chart-pie"></i> Radar
                </button>
                <button class="btn btn-outline-primary btn-sm border-0 shadow-none" type="button" title="Descargar gráfico como imagen" onclick="descargarImagen('graficoCama', 'grafico_cama.png')">
                    <i class="fas fa-download"></i> Descargar
                </button>
            </div>
            <!-- Filtros de fecha -->
            <div class="input-group input-group-sm" style="max-width: 180px;">
                <input type="date" id="fechaInicio" class="form-control placeholder-wave bg-transparent text-secondary" style="width: 140px; border: none;" title="Seleccione la fecha de inicio">
            </div>
            <div class="input-group input-group-sm" style="max-width: 180px;">
                <input type="date" id="fechaFin" class="form-control placeholder-wave bg-transparent text-secondary" style="width: 140px; border: none;" title="Seleccione la fecha final">
            </div>
            <!-- Selector de cama -->
            <div class="input-group input-group-sm" style="max-width: 162px;">
                <select id="camaId" class="form-select border-0 border-secondary text-secondary" title="Selecciona cama">
                    <option value="" disabled selected>Seleccionar cama</option>
                    <option value="1">Cama 1</option>
                    <option value="2">Cama 2</option>
                    <option value="3">Cama 3</option>
                    <option value="4">Cama 4</option>
                </select>
            </div>
            <!-- Botón Filtrar -->
            <div>
                <button id="btnFiltrar" class="btn btn-primary btn-sm border-0 shadow-none">Filtrar</button>
            </div>
        </div>
        <!-- Gráfico -->
        <div class="col-md-12 mt-3">
            <canvas id="graficoCama" style="width: 100%; height: 315px;"></canvas>
        </div>
    </div>
</div>

<script>
    // Este es el código JS que realiza la lógica de filtrado y muestra el gráfico
    document.getElementById('btnFiltrar').addEventListener('click', function() {
        let fechaInicio = document.getElementById('fechaInicio').value;
        let fechaFin = document.getElementById('fechaFin').value;
        let camaId = document.getElementById('camaId').value;

        if (!fechaInicio || !fechaFin || !camaId) {
            alert('Por favor, completa todos los filtros');
            return;
        }

        // Enviar los datos al controlador usando AJAX
        $.ajax({
            type: 'POST',
            url: 'index.php?r=filtrar-humedad-por-rango/ajax', // Asegúrate de que la URL esté correcta
            data: {
                fechaInicio: fechaInicio,
                fechaFin: fechaFin,
                camaId: camaId
            },
            success: function(response) {
                if (response.success) {
                    // Datos para la gráfica
                    let datosGrafico = {
                        labels: Array.from({
                            length: 24
                        }, (_, i) => i + "h"), // Horas del día
                        datasets: [{
                            label: 'Promedio de humedad',
                            data: response.promedios, // Datos de los promedios
                            borderColor: '#36A2EB', // Color de la línea
                            fill: false
                        }]
                    };

                    // Configuración de la gráfica
                    let config = {
                        type: 'radar', // Aquí se puede cambiar el tipo de gráfico
                        data: datosGrafico,
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    };

                    // Dibujar la gráfica
                    let ctx = document.getElementById('graficoCama').getContext('2d');
                    if (window.chart) {
                        window.chart.destroy(); // Elimina la gráfica anterior si existe
                    }
                    window.chart = new Chart(ctx, config);
                } else {
                    alert(response.message || 'Error al cargar los datos.');
                }
            }
        });
    });
</script>