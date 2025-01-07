<?php

namespace frontend\controllers;

use frontend\models\CicloSiembra;
use frontend\models\Cama1;
use frontend\models\Cama2;
use frontend\models\Cama3;
use frontend\models\Cama4;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * frontend/views/tiempo-real/
 * **/

class TiempoRealHumedadSueloController extends Controller
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
            ->select(['cicloId', 'descripcion', 'ciclo'])
            ->orderBy(['ciclo' => SORT_ASC])
            ->asArray()
            ->all();

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
            'fechaFin' => $fechaFinal,
        ]);
    }
    public function actionAjax()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $fecha = Yii::$app->request->post('fecha'); // Recibe el parámetro de fecha desde el frontend

        try {
            // Obtener los datos de las cuatro camas, filtrando por fecha si está presente
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
        $tablas = [Cama1::class, Cama2::class, Cama3::class, Cama4::class];
        $resultados = [];

        foreach ($tablas as $modelo) {
            // Obtener el nombre de la clase sin el namespace
            $nombreTabla = basename(str_replace("\\", "/", $modelo));

            // Definir el nombre de la columna 'id' correspondiente a cada tabla
            $columnaId = 'id' . $nombreTabla;

            // Obtener los datos de cada tabla usando su modelo correspondiente
            if (class_exists($modelo)) {
                $datos = $modelo::find()
                    ->select([$columnaId, 'humedad', 'fecha', 'hora']) // Usar la columna de ID correspondiente
                    ->andFilterWhere(['fecha' => $fecha]) // Aplica el filtro por fecha si se proporciona
                    ->orderBy(['fecha' => SORT_ASC, 'hora' => SORT_ASC])
                    ->asArray()
                    ->all();

                // Agregar los resultados al arreglo, etiquetados por la tabla
                $resultados[$nombreTabla] = $datos;
            } else {
                throw new \Exception("Modelo no encontrado: " . $modelo);
            }
        }

        return $resultados;
    }
}
