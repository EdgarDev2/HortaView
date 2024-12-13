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
use yii\web\Response;

/**
 * frontend/views/predicciones/
 * **/

class TodosRegistrosCamasController extends Controller
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

    public function actionSolicitar()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $camaId = Yii::$app->request->post('camaId');
        $fechaInicio = Yii::$app->request->post('fechaInicio');
        $fechaFin = Yii::$app->request->post('fechaFin');

        // Validar los parámetros
        if (!$camaId || !in_array($camaId, [1, 2, 3, 4])) {
            return [
                'success' => false,
                'message' => 'Por favor, seleccione una cama válida.',
            ];
        }

        if (!$fechaInicio || !$fechaFin) {
            return [
                'success' => false,
                'message' => 'Por favor, proporcione un rango de fechas válido.',
            ];
        }

        // Validar formato de fechas y convertir a formato compatible con la base de datos (YYYY-MM-DD)
        $fechaInicio = date('Y-m-d', strtotime($fechaInicio));
        $fechaFin = date('Y-m-d', strtotime($fechaFin));

        if ($fechaInicio > $fechaFin) {
            return [
                'success' => false,
                'message' => 'La fecha de inicio no puede ser mayor que la fecha de fin.',
            ];
        }

        // Obtener el modelo correspondiente
        $modelClass = $this->getModelByCamaId($camaId);

        if (!$modelClass) {
            return [
                'success' => false,
                'message' => 'No se encontró un modelo asociado a la cama seleccionada.',
            ];
        }

        // Consultar los datos de humedad dentro del rango de fechas
        $datos = $modelClass::find()
            ->select(['humedad', 'fecha', 'hora'])
            ->where(['between', 'fecha', $fechaInicio, $fechaFin])
            ->orderBy(['fecha' => SORT_ASC, 'hora' => SORT_ASC])
            ->asArray()
            ->all();

        if (empty($datos)) {
            return [
                'success' => false,
                'message' => 'No se encontraron datos para el rango de fechas proporcionado.',
            ];
        }

        return [
            'success' => true,
            'data' => $datos,
        ];
    }

    private function getModelByCamaId($camaId)
    {
        switch ($camaId) {
            case 1:
                return Cama1::class;
            case 2:
                return Cama2::class;
            case 3:
                return Cama3::class;
            case 4:
                return Cama4::class;
            default:
                return null;
        }
    }
}
