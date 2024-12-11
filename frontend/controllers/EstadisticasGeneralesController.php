<?php

namespace frontend\controllers;

use frontend\models\CicloSiembra;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * frontend/views/predicciones/
 * **/

class EstadisticasGeneralesController extends Controller
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
        return $this->render('index');
    }
}
