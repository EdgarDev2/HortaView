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
        // Pasar la fecha y el ciclo a la vista
        return $this->render('index', [
            'cicloSeleccionado' => $cicloSeleccionado,
            'fechaInicio' => $fechaInicio,  // Pasar la fecha de inicio a la vista
            'fechaFin' => $fechaFinal,      // Asegurar consistencia aquí
        ]);
    }

    public function actionObtenerTemperaturas()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Obtener los parámetros de la solicitud
        $fechainicio = Yii::$app->request->post('fechainicio');
        $fechafin = Yii::$app->request->post('fechafin');

        // Validar que ambos parámetros estén presentes
        if (empty($fechainicio) || empty($fechafin)) {
            return ['success' => false, 'message' => 'Faltan parámetros requeridos.'];
        }

        // Convertir las fechas al formato compatible con MySQL (Y-m-d)
        $fechainicio = date('Y-m-d', strtotime(str_replace('/', '-', $fechainicio)));
        $fechafin = date('Y-m-d', strtotime(str_replace('/', '-', $fechafin)));

        // Consultar las temperaturas entre las fechas proporcionadas
        $datos = Temperatura::find()
            ->select(['fecha', 'temperatura'])
            ->where(['between', 'fecha', $fechainicio, $fechafin])
            ->orderBy(['fecha' => SORT_ASC])
            ->asArray()
            ->all();

        // Verificar si hay datos
        if (empty($datos)) {
            return ['success' => false, 'message' => 'No se encontraron datos para el rango de fechas especificado.'];
        }

        return ['success' => true, 'data' => $datos];
    }
}
