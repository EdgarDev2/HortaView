<?php

namespace frontend\controllers;

use frontend\models\CicloSiembra;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;
use Phpml\Regression\LeastSquares;
use Phpml\Regression\SVR;
use Phpml\SupportVectorMachine\Kernel;

class PrediccionPesoProduccionFinalController extends Controller
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
        $ciclos = CicloSiembra::find()
            ->select(['cicloId', 'descripcion', 'ciclo'])
            ->orderBy(['ciclo' => SORT_ASC])
            ->asArray()
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
        $ciclo = CicloSiembra::findOne($cicloSeleccionado);
        date_default_timezone_set('America/Mexico_City');
        $fechaActual = date('Y-m-d');
        if ($ciclo) {
            $fechaInicio = $ciclo->fechaInicio;
            $fechaFinal = $ciclo->fechaFin;
        } else {
            $fechaInicio = $fechaActual;
            $fechaFinal = $fechaActual;
        }

        $fechaInicio = date('Y-m-d', strtotime($fechaInicio));
        $fechaFinal = date('Y-m-d', strtotime($fechaFinal));

        return $this->render('index', [
            'cicloSeleccionado' => $cicloSeleccionado,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFinal,
        ]);
    }
}
