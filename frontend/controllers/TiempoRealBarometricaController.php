<?php

namespace frontend\controllers;

use common\components\DbHandler;
//use frontend\models\CicloSiembra;
use frontend\models\PresionBarometrica;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * frontend/views/tiempo-real/
 * **/

class TiempoRealBarometricaController extends Controller
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
        $resultados = DbHandler::obtenerCicloYFechas();
        $cicloSeleccionado = $resultados['cicloSeleccionado'];
        $fechaInicio = $resultados['fechaInicio'];
        $fechaFinal = $resultados['fechaFinal'];

        return $this->render('index', [
            'cicloSeleccionado' => $cicloSeleccionado,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFinal,
        ]);
    }

    public function actionAjax()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $fecha = Yii::$app->request->post('fecha'); // Recibe el parámetro de fecha desde el frontend

        try {
            // Obtener los datos de presión, temperatura, y altitud, filtrando por fecha si está presente
            $resultados = $this->obtenerDatosPresion($fecha);
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

    protected function obtenerDatosPresion($fecha = null)
    {
        $query = PresionBarometrica::find()
            ->select(['idPresionBarometrica', 'presion', 'temperatura', 'altitud', 'fecha', 'hora'])
            ->orderBy(['fecha' => SORT_ASC, 'hora' => SORT_ASC]);

        // Si se proporciona una fecha, se filtran los datos
        if ($fecha) {
            $query->where(['fecha' => $fecha]);
        }

        return $query->asArray()->all();
    }
}
