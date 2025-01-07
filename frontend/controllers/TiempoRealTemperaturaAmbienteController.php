<?php

namespace frontend\controllers;

use frontend\models\CicloSiembra;
use frontend\models\Temperatura;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * frontend/views/tiempo-real/
 * **/

class TiempoRealTemperaturaAmbienteController extends Controller
{
    public function behaviors()
    {
        return [
            // Filtro de control de acceso
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index'], // Acciones a restringir
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // '@' solo usuarios autenticados tienen acceso
                    ],
                ],
            ],
            // Filtro de control de verbos HTTP
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'], // La acción 'delete' solo puede ser accedida mediante POST
                ],
            ],
        ];
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
        $fechaMinima = Temperatura::find()->min('fecha');
        $fechaMaxima = Temperatura::find()->max('fecha');

        // Pasar la fecha y el ciclo a la vista
        return $this->render('index', [
            'cicloSeleccionado' => $cicloSeleccionado,
            'fechaMinima' => $fechaInicio,
            'fechaMaxima' => $fechaFinal,
            //'fechaMinima' => $resultados['fecha_inicio'],  // Pasar la fecha de inicio a la vista
            //'fechaMaxima' => $resultados['fecha_final'],      // Asegurar consistencia aquí
        ]);
    }

    public function actionAjax()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $fecha = Yii::$app->request->post('fecha'); // Recibe el parámetro de fecha desde el frontend

        try {
            // Obtener los datos de temperatura y humedad, filtrando por fecha si está presente
            $resultados = $this->obtenerDatos($fecha);
            return [
                'success' => true,
                'data' => $resultados,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage(),
            ];
        }
    }

    protected function obtenerDatos($fecha = null)
    {
        $query = Temperatura::find()
            ->select(['temperatura', 'humedad', 'fecha', 'hora'])
            ->orderBy(['fecha' => SORT_ASC, 'hora' => SORT_ASC]);

        // Si se proporciona una fecha, se filtran los datos
        if ($fecha) {
            $query->where(['fecha' => $fecha]);
        }

        return $query->asArray()->all();
    }
}
