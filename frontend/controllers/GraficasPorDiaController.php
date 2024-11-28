<?php

namespace frontend\controllers;

use frontend\models\Cama1;
use frontend\models\Cama2;
use frontend\models\Cama3;
use frontend\models\Cama4;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * frontend/views/graficas/
 * **/

class GraficasPorDiaController extends Controller
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
        return $this->render('index');
    }

    public function actionAjax()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $fecha = Yii::$app->request->post('fecha');
        $camaId = Yii::$app->request->post('camaId');

        $modelClass = match ($camaId) {
            'fechaCama1' => Cama1::class,
            'fechaCama2' => Cama2::class,
            'fechaCama3' => Cama3::class,
            'fechaCama4' => Cama4::class,
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
