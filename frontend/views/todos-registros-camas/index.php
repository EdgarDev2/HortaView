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
                </button>-->
                <button class="<?= $btnDownloadClass ?>" type="button" title="Descargar gráfico como imagen" onclick="descargarImagen('graficoCama', 'grafico_cama.png')">
                    <i class="fas fa-download"></i> Descargar img del gráfico
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>


<script>
    // Obtener fecha actual en formato YYYY-MM-DD
    function obtenerFechaActual() {
        const hoy = new Date();
        const year = hoy.getFullYear();
        const month = String(hoy.getMonth() + 1).padStart(2, '0');
        const day = String(hoy.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const fechaActual = obtenerFechaActual();
        let dataContainer = document.getElementById('data-container');
        let fechaInicioo = dataContainer.dataset.fechaInicio; // Solo la fecha
        let fechaFinn = dataContainer.dataset.fechaFin; // Solo la fecha
        document.getElementById('fechaInicio').value = fechaInicioo || fechaActual;
        document.getElementById('fechaFin').value = fechaFinn || fechaActual;

        const camaIdPredeterminada = '1';
        document.getElementById('camaId').value = camaIdPredeterminada;

        $(document).ready(function() {
            setTimeout(clickbutton, 10);

            function clickbutton() {
                $("#btnFiltrar").click();
            }
        });
    });

    $('#btnFiltrar').on('click', function() {
        const fechaInicio = $('#fechaInicio').val();
        const fechaFin = $('#fechaFin').val();
        const camaId = $('#camaId').val();

        // Validación de campos
        if (!camaId) {
            alert('Por favor, selecciona una cama.');
            return;
        }

        if (!fechaInicio || !fechaFin) {
            alert('Por favor, selecciona ambas fechas.');
            return;
        }

        if (new Date(fechaInicio) > new Date(fechaFin)) {
            alert('La fecha de inicio no puede ser mayor que la fecha de fin.');
            return;
        }

        // Realizar solicitud AJAX
        $.ajax({
            url: 'index.php?r=todos-registros-camas/solicitar',
            type: 'POST',
            data: {
                camaId: camaId,
                fechaInicio: fechaInicio,
                fechaFin: fechaFin
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    console.log('Datos filtrados:', response.data);

                    // Actualizar gráfico
                    actualizarGrafico(response.data);
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al filtrar los datos:', error);
                alert('Ocurrió un error al filtrar los datos. Por favor, intenta nuevamente.');
            }
        });
    });

    // Variable global para el tipo de gráfico
    let tipoGrafico = 'line';

    // Función para actualizar el gráfico
    function actualizarGrafico(datos) {
        const etiquetas = datos.map(item => `${item.fecha} ${item.hora}`);
        const humedades = datos.map(item => item.humedad);

        const ctx = document.getElementById('graficoCama').getContext('2d');

        // Verificar si el gráfico existe antes de intentar destruirlo
        if (window.graficoCama && typeof window.graficoCama.destroy === 'function') {
            window.graficoCama.destroy();
        }

        // Crear un nuevo gráfico con el tipo dinámico
        window.graficoCama = new Chart(ctx, {
            type: tipoGrafico, // Usa la variable global para el tipo de gráfico
            data: {
                labels: etiquetas,
                datasets: [{
                    label: 'Humedad del Suelo',
                    data: humedades,
                    borderColor: '#4BC0C0',
                    backgroundColor: 'rgba(75, 192, 192, 0.4)',
                    borderWidth: 2,
                    fill: false,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Horas',
                        },
                    },
                    y: {
                        min: 0,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Humedad (%)',
                        },
                    },
                },
                plugins: {
                    zoom: {
                        pan: {
                            enabled: true,
                            mode: 'xy',
                        },
                        zoom: {
                            wheel: {
                                enabled: true,
                            },
                        },
                    },
                },
            },
        });
    }

    // Función para cambiar el tipo de gráfico
    function cambiarTipoGrafico(nuevoTipo) {
        tipoGrafico = nuevoTipo; // Actualiza el tipo de gráfico
        $('#btnFiltrar').click(); // Vuelve a filtrar los datos y actualiza el gráfico
    }

    function descargarImagen(idCanvas, nombreArchivo) {
        const canvas = document.getElementById(idCanvas);
        const url = canvas.toDataURL('image/png'); // Convierte el gráfico a imagen en base64

        // Crear un enlace temporal para descargar la imagen
        const enlace = document.createElement('a');
        enlace.href = url;
        enlace.download = nombreArchivo; // Nombre del archivo que se descargará

        // Simular el clic en el enlace para iniciar la descarga
        enlace.click();
    }
</script>