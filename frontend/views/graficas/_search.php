<?php

use yii\helpers\Url;
use yii\helpers\Html;

$this->registerJs(<<<JS
    // Función para cargar datos y actualizar la gráfica
    function cargarDatosPromedio(camaId, fechaSeleccionada) {
        // Mostrar en consola los valores de fecha y camaId
        console.log('Datos enviados:', { fecha: fechaSeleccionada, camaId: camaId });

        $.ajax({
            url: 'index.php?r=graficas/ajax',  // Asegúrate de que esta URL corresponde a la acción que maneja los datos
            type: 'POST',
            data: {
                fecha: fechaSeleccionada,
                camaId: camaId // Se pasa el nombre de los campos, como "fechaCama1", "fechaCama2", etc.
            },
            success: function(response) {
                if (response.success) {
                    actualizarGrafica(camaId, response.promedios);
                } else {
                    console.error(response.message || 'Error al obtener los datos');
                }
            },
            error: function() {
                console.error('Error en la solicitud AJAX');
            }
        });
    }

    // Listener para los cambios en los inputs de fecha
    $('#fechaCama1').on('change', function() {
        cargarDatosPromedio('fechaCama1', $(this).val()); // Pasa 'fechaCama1' como camaId
    });

    $('#fechaCama2').on('change', function() {
        cargarDatosPromedio('fechaCama2', $(this).val()); // Pasa 'fechaCama2' como camaId
    });

    $('#fechaCama3').on('change', function() {
        cargarDatosPromedio('fechaCama3', $(this).val()); // Pasa 'fechaCama3' como camaId
    });

    $('#fechaCama4').on('change', function() {
        cargarDatosPromedio('fechaCama4', $(this).val()); // Pasa 'fechaCama4' como camaId
    });

    // Función para actualizar la gráfica específica con los nuevos datos
    function actualizarGrafica(camaId, promedios) {
        let chartId = '';
        switch(camaId) {
            case 'fechaCama1':
                chartId = 'graficoCama1';
                break;
            case 'fechaCama2':
                chartId = 'graficoCama2';
                break;
            case 'fechaCama3':
                chartId = 'graficoCama3';
                break;
            case 'fechaCama4':
                chartId = 'graficoCama4';
                break;
        }
        
        // Accede a la gráfica de Chart.js y actualiza los datos
        const chart = Chart.getChart(chartId); // Asume que los gráficos ya están inicializados
        chart.data.datasets[0].data = promedios;
        chart.update();
    }
JS);
