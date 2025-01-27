<?php

namespace frontend\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\DbHandler;


class UsoAguaSiguienteCicloController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        DbHandler::obtenerCicloYFechas();
        return $this->render('index', []);
    }

    public function actionFiltrar()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $tipoCultivo = Yii::$app->request->post('tipoCultivo');
        $tipoRiego = Yii::$app->request->post('tipoRiego');

        if ($tipoCultivo && $tipoRiego) {
            // Simula una búsqueda de datos (reemplaza con tu lógica real)
            $datosHistoricos = DbHandler::obtenerConsumoTotalAgua($tipoCultivo, $tipoRiego);
            $predicciones = DbHandler::predecirConsumoAgua($tipoCultivo, $tipoRiego);
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'success' => true,
                'datos_historicos' => $datosHistoricos,
                'predicciones' => $predicciones,
            ];
        }

        return [
            'success' => false,
            'message' => 'Faltan parámetros o no se encontraron datos.'
        ];
    }
}
