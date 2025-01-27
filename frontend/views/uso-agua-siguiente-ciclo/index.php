<?php

use yii\helpers\Html;

$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@1.2.1/dist/chartjs-plugin-zoom.min.js');

// Clases comunes bootstrap
$btnClass = 'btn btn-outline-success btn-sm border-0 shadow-none';
$btnDownloadClass = 'btn btn-outline-primary btn-sm border-0 shadow-none';
$cardInputDate = 'input-group input-group-sm d-flex align-items-center gap-2 bg-light rounded px-2';
$inputDate = 'form-control placeholder-wave bg-transparent text-secondary';
$selectPlace = 'form-select placeholder-wave border-0 text-secondary bg-transparent rounded';

$this->title = 'Predicción total agua requerida para el siguiente ciclo';

?>
<div class="prediccion-uso-agua-index bg-light">
    <div class="card mt-0">
        <div class="card-header bg-light text-secondary text-start">
            <h4 class="mb-0 display-6">Predicción agua requerida (siguiente ciclo).</h4>
        </div>
        <!-- Filtros y opciones de gráficos -->
        <div class="row p-2 border-bottom">
            <div class="col-md-12 d-flex flex-wrap align-items-center gap-2">
                <!-- Botones para tipo de gráfico -->
                <div class="btn-group" role="group" aria-label="Opciones de gráficos">
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
                <!-- Selector de sistema de riego -->
                <div class="input-group input-group-sm" style="max-width: 231px;">
                    <select id="tipoRiego" class="<?= $selectPlace ?>" title="Selecciona sistema de riego">
                        <option value="" disabled selected>Seleccionar tipo de riego</option>
                        <option value="valvula">Riego automático</option>
                        <option value="riegomanual">Riego manual</option>
                    </select>
                </div>

                <!-- Selector de tipo de cultivo -->
                <div class="input-group input-group-sm" style="max-width: 231px;">
                    <select id="tipoCultivo" class="<?= $selectPlace ?>" title="Selecciona cultivo">
                        <option value="" disabled selected>Seleccionar cultivo</option>
                    </select>
                </div>
                <!-- Botón Filtrar -->
                <div>
                    <button id="btnFiltrar" class="btn btn-outline-primary btn-sm border-0 shadow-none">Filtrar datos</button>
                </div>
            </div>
        </div>
        <!-- Gráfico -->
        <div class="card-body bg-light"> <!--container border mb-3-->
            <div class="">
                <div class="chart-container" style="position: relative; height: 70vh; width: 100%;">
                    <canvas id="grafico-consolidado"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<script>
    // Opciones de cultivo según el sistema de riego
    const opcionesCultivo = {
        valvula: [{
                value: "Cama 1 cilantro automático",
                text: "Cama 1 cilantro automático"
            },
            {
                value: "Cama 2 rábano automático",
                text: "Cama 2 rábano automático"
            },
        ],
        riegomanual: [{
                value: "Cama 3 cilantro manual",
                text: "Cama 3 cilantro manual"
            },
            {
                value: "Cama 4 rábano manual",
                text: "Cama 4 rábano manual"
            },
        ],
    };

    // Elementos del DOM
    const sistemaRiegoSelect = document.getElementById("tipoRiego");
    const tipoCultivoSelect = document.getElementById("tipoCultivo");

    // Evento para cambiar las opciones del selector de cultivo
    sistemaRiegoSelect.addEventListener("change", function() {
        const seleccion = sistemaRiegoSelect.value;

        // Limpiar las opciones actuales del selector de cultivo
        tipoCultivoSelect.innerHTML = `<option value="" disabled selected>Seleccionar cultivo</option>`;

        // Agregar las nuevas opciones según la selección del sistema de riego
        if (seleccion && opcionesCultivo[seleccion]) {
            opcionesCultivo[seleccion].forEach(cultivo => {
                const option = document.createElement("option");
                option.value = cultivo.value;
                option.textContent = cultivo.text;
                tipoCultivoSelect.appendChild(option);
            });
        }
    });

    // Elementos del DOM
    const btnFiltrar = document.getElementById('btnFiltrar');
    let chartInstance = null;

    // Función para destruir el gráfico actual
    function destruirGrafico() {
        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }
    }

    //tipoCultivo: Cama 1 cilantro; tipoRiego: Riego automático o manual
    function cargarDatos(tipoCultivo, tipoRiego) {
        console.log('Enviando datos:', {
            tipoCultivo,
            tipoRiego
        });

        $.ajax({
            type: 'POST',
            url: 'index.php?r=uso-agua-siguiente-ciclo/filtrar',
            data: {
                tipoCultivo: tipoCultivo,
                tipoRiego: tipoRiego
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const datosHistoricos = response.datos_historicos; // Datos históricos por línea
                    const predicciones = response.predicciones; // Predicciones por línea
                    console.log('datos historicos: ', datosHistoricos)
                    console.log('predicciones: ', predicciones);
                } else {
                    alert('No se encontraron resultados.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud:', {
                    xhr,
                    status,
                    error
                });
                alert('Hubo un error al obtener los datos.');
            }
        });
    }

    btnFiltrar.addEventListener('click', function() {
        const tipoCultivoo = tipoCultivoSelect.value;
        const riegoSeleccionada = sistemaRiegoSelect.value;

        if (!tipoCultivoo || !riegoSeleccionada) {
            alert('Por favor, selecciona un tipo de riego y un cultivo.');
            return;
        }

        cargarDatos(tipoCultivoo, riegoSeleccionada);
    });
</script>