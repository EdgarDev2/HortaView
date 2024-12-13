<?php

namespace frontend\controllers;

use frontend\models\Cama1;
use frontend\models\Cama2;
use frontend\models\Cama3;
use frontend\models\Cama4;
use frontend\models\CicloSiembra;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * frontend/views/graficas/
 * **/

class FiltrarHumedadPorDiaController extends Controller
{
    public function behaviors()
    {
        return [
            // Filtro de control de acceso
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'solicitud'], // Incluye 'solicitud' para aplicar las reglas
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Solo usuarios autenticados pueden acceder
                    ],
                ],
            ],
            // Filtro de control de verbos HTTP
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'], // La acción 'delete' solo puede ser accedida mediante POST
                    'solicitud' => ['POST'], // Asegura que 'solicitud' solo acepte solicitudes POST
                ],
            ],
        ];
    }

    protected function obtenerDatosHumedad($modelClass, $fecha)
    {
        $data = $modelClass::find()
            ->select([
                'HOUR(hora) as hora', // HOUR(hora) -> extrae la hora y alias "hora" se asigna a la expresión para referenciar el resultado de la consulta.
                'AVG(humedad) as promedio_humedad',
                'MAX(humedad) as max_humedad',
                'MIN(humedad) as min_humedad',
            ])
            ->where(['fecha' => $fecha])
            ->groupBy(['HOUR(hora)'])
            ->orderBy(['hora' => SORT_ASC]) // hora es el alias que le dí a la columna HOUR(hora) dentro del select.
            ->asArray()
            ->all();

        $resultados = [
            'promedios' => array_fill(0, 24, null),
            'maximos' => array_fill(0, 24, null),
            'minimos' => array_fill(0, 24, null),
        ];

        foreach ($data as $entry) {
            $hora = (int)$entry['hora']; // hora es el alias dentro del select.
            $resultados['promedios'][$hora] = (float)$entry['promedio_humedad'];
            $resultados['maximos'][$hora] = (float)$entry['max_humedad'];
            $resultados['minimos'][$hora] = (float)$entry['min_humedad'];
        }

        return $resultados;
    }

    public function actionIndex()
    {
        // Obtener los ciclos desde la base de datos
        $ciclos = CicloSiembra::find()
            ->select(['cicloId', 'descripcion', 'ciclo']) // Seleccionar columnas específicas
            ->orderBy(['ciclo' => SORT_ASC]) // Ordenar por el campo 'ciclo'
            ->asArray() // Convertir el resultado a un array
            ->all();

        // Verificar si no hay ciclos disponibles
        if (empty($ciclos)) {
            Yii::$app->session->setFlash('error', 'No hay ciclos disponibles en este momento.');
        }

        // Pasar los ciclos al layout como variable global
        Yii::$app->view->params['ciclos'] = $ciclos;
        // Recuperar el ciclo seleccionado de la sesión
        $cicloSeleccionado = Yii::$app->session->get('cicloSeleccionado');

        // Obtener el ciclo correspondiente de la base de datos
        $ciclo = CicloSiembra::findOne($cicloSeleccionado);  // Buscar el ciclo usando el ID seleccionado
        date_default_timezone_set('America/Mexico_City');
        $fechaActual = date('Y-m-d');
        if ($ciclo) {
            // Asignar fechas si el ciclo es encontrado
            $fechaInicio = $ciclo->fechaInicio;
            $fechaFinal = $ciclo->fechaFin;
        } else {
            // Asignar valores nulos en caso contrario
            $fechaInicio = $fechaActual;
            $fechaFinal = $fechaActual; // Usar la misma variable aquí
        }
        // Convierte la cadena fecha y hora 2024-02-29 00:00:00 a una marca de tiempo unix y formatea a YYYY-MM-DD
        $fechaInicio = date('Y-m-d', strtotime($fechaInicio));
        $fechaFinal = date('Y-m-d', strtotime($fechaFinal));
        // Pasar la fecha y el ciclo a la vista
        return $this->render('index', [
            'cicloSeleccionado' => $cicloSeleccionado,
            'fechaInicio' => $fechaInicio,  // Pasar la fecha de inicio a la vista
            'fechaFin' => $fechaFinal,      // Asegurar consistencia aquí
        ]);
    }

    public function actionSolicitud()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $fecha = Yii::$app->request->post('fechaInicio');
        $camaId = Yii::$app->request->post('camaId');

        $modelClass = match ($camaId) {
            '1' => Cama1::class,
            '2' => Cama2::class,
            '3' => Cama3::class,
            '4' => Cama4::class,
            default => null,
        };

        if ($modelClass === null || !strtotime($fecha)) {
            return ['success' => false, 'message' => 'Datos inválidos'];
        }

        try {
            $resultados = $this->obtenerDatosHumedad($modelClass, $fecha);
            return [
                'success' => true,
                'promedios' => $resultados['promedios'],
                'maximos' => $resultados['maximos'],
                'minimos' => $resultados['minimos'],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error al procesar la solicitud'];
        }
    }
}
