<?php

namespace frontend\controllers;

use common\components\DbHandler;
use frontend\models\Cama1;
use frontend\models\Cama2;
use frontend\models\Cama3;
use frontend\models\Cama4;
//use frontend\models\CicloSiembra;
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
