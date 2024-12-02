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
                <button class="btn btn-outline-success btn-sm border-0 shadow-none" type="button" title="Gráfico de tipo Lineal" onclick="cambiarTipoGrafico('line')">
                    <i class="fas fa-chart-line"></i> Lineal
                </button>
                <button class="btn btn-outline-success btn-sm border-0 shadow-none" type="button" title="Gráfico de tipo Barra" onclick="cambiarTipoGrafico('bar')">
                    <i class="fas fa-chart-bar"></i> Barra
                </button>
                <button class="btn btn-outline-success btn-sm border-0 shadow-none" type="button" title="Gráfico de tipo Radar" onclick="cambiarTipoGrafico('radar')">
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
    let chart; // Variable global para el gráfico
    let tipoGrafico = 'line'; // Tipo de gráfico inicial

    // Función para inicializar el gráfico
    function inicializarGrafico(data) {
        const ctx = document.getElementById('graficoCama').getContext('2d');
        if (chart) {
            chart.destroy(); // Elimina el gráfico anterior si existe
        }
        chart = new Chart(ctx, {
            type: tipoGrafico,
            data: {
                labels: Array.from({
                    length: 24
                }, (_, i) => i + 'h'), // Horas
                datasets: [{
                        label: 'Promedio',
                        data: data.promedios,
                        borderColor: '#36A2EB',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        fill: false,
                    },
                    {
                        label: 'Máximo',
                        data: data.maximos,
                        borderColor: '#FF6384',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        fill: false,
                    },
                    {
                        label: 'Mínimo',
                        data: data.minimos,
                        borderColor: '#4BC0C0',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: false,
                    },
                ],
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                    },
                },
            },
        });
    }

    // Función para realizar la solicitud AJAX
    function cargarDatos(fechaInicio, fechaFin, camaId) {
        $.ajax({
            type: 'POST',
            url: 'index.php?r=filtrar-humedad-por-rango/ajax',
            data: {
                fechaInicio,
                fechaFin,
                camaId
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

    // Cambiar el tipo de gráfico
    function cambiarTipoGrafico(nuevoTipo) {
        tipoGrafico = nuevoTipo;
        const fechaInicio = document.getElementById('fechaInicio').value || obtenerFechaActual();
        const fechaFin = document.getElementById('fechaFin').value || obtenerFechaActual();
        const camaId = document.getElementById('camaId').value;
        cargarDatos(fechaInicio, fechaFin, camaId);
    }

    // Obtener fecha actual en formato YYYY-MM-DD
    function obtenerFechaActual() {
        const hoy = new Date();
        const year = hoy.getFullYear();
        const month = String(hoy.getMonth() + 1).padStart(2, '0');
        const day = String(hoy.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // Configurar el botón de filtrar
    document.getElementById('btnFiltrar').addEventListener('click', function() {
        const fechaInicio = document.getElementById('fechaInicio').value;
        const fechaFin = document.getElementById('fechaFin').value;
        const camaId = document.getElementById('camaId').value;

        if (!fechaInicio || !fechaFin || !camaId) {
            alert('Por favor, completa todos los filtros.');
            return;
        }
        cargarDatos(fechaInicio, fechaFin, camaId);
    });

    // Cargar datos iniciales al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        const fechaActual = obtenerFechaActual();
        document.getElementById('fechaInicio').value = fechaActual;
        document.getElementById('fechaFin').value = fechaActual;
        cargarDatos(fechaActual, fechaActual, '1'); // Cargar datos de la cama 1 por defecto
    });

    // Función para descargar el gráfico
    window.descargarImagen = function(canvasId, nombreArchivo) {
        let link = document.createElement('a');
        link.href = document.getElementById(canvasId).toDataURL();
        link.download = nombreArchivo;
        link.click();
    };
</script>