<?php

namespace frontend\controllers;

use frontend\models\CicloSiembra;
use frontend\models\Cultivo;
use frontend\models\LineaCultivo;
use frontend\models\RegistroGerminacion;
use frontend\models\Temperatura;
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
        // Obtener los ciclos disponibles
        $ciclos = CicloSiembra::find()
            ->select(['cicloId', 'descripcion', 'ciclo'])
            ->orderBy(['ciclo' => SORT_ASC])
            ->asArray()
            ->all();

        if (empty($ciclos)) {
            Yii::$app->session->setFlash('error', 'No hay ciclos disponibles en este momento.');
        }

        Yii::$app->view->params['ciclos'] = $ciclos;
        $cicloSeleccionado = Yii::$app->session->get('cicloSeleccionado');
        $ciclo = CicloSiembra::findOne($cicloSeleccionado);

        date_default_timezone_set('America/Mexico_City');
        $fechaActual = date('Y-m-d');

        // Establecer las fechas de inicio y fin del ciclo
        if ($ciclo) {
            $fechaInicio = $ciclo->fechaInicio;
            $fechaFinal = $ciclo->fechaFin;
        } else {
            $fechaInicio = $fechaActual;
            $fechaFinal = $fechaActual;
        }

        // Obtener cultivos asociados al ciclo seleccionado
        $cultivos = Cultivo::find()
            ->select(['cultivoId', 'nombreCultivo', 'germinacion'])
            ->where(['cicloId' => $cicloSeleccionado])
            ->orderBy(['nombreCultivo' => SORT_ASC])
            ->asArray()
            ->all();

        // Crear un índice para mapear cultivoId a nombreCultivo
        $cultivoMap = array_column($cultivos, 'nombreCultivo', 'cultivoId');

        // Obtener los datos de lineacultivo
        $lineasCultivo = LineaCultivo::find()
            ->select(['numeroLinea', 'gramaje', 'cultivoId'])
            ->where(['cultivoId' => array_column($cultivos, 'cultivoId')])
            ->orderBy([
                'cultivoId' => SORT_ASC, // Primero ordena por cultivoId
                'numeroLinea' => SORT_ASC // Luego ordena por numeroLinea dentro de cada cultivoId
            ])
            ->asArray()
            ->all();


        // Asociar el nombre del cultivo a cada línea de cultivo
        foreach ($lineasCultivo as &$linea) {
            $linea['nombreCultivo'] = isset($cultivoMap[$linea['cultivoId']]) ? $cultivoMap[$linea['cultivoId']] : 'Desconocido';
        }

        // Obtener registros de germinación
        $registros = RegistroGerminacion::find()
            ->select(['numeroZurcosGerminados', 'broteAlturaMaxima', 'linea', 'cultivoId', 'fechaRegistro'])
            ->where(['cultivoId' => array_column($cultivos, 'cultivoId')])
            ->orderBy(['fechaRegistro' => SORT_ASC])
            ->asArray()
            ->all();

        // Obtener el promedio diario de temperatura y humedad dentro del rango del ciclo seleccionado
        $promediosDiarios = Temperatura::find()
            ->select([
                'fecha',
                'promedioTemperatura' => 'AVG(temperatura)',
                'promedioHumedad' => 'AVG(humedad)',
            ])
            ->where(['between', 'fecha', $fechaInicio, $fechaFinal])
            ->groupBy(['fecha'])
            ->orderBy(['fecha' => SORT_ASC])
            ->asArray()
            ->all();

        // Preparar las muestras para la predicción (ejemplo: gramaje, número de surcos germinados, etc.)
        $samples = [];
        $targets = [];

        // Recopilación de datos para cada línea de cultivo (por cada cama y ciclo)
        foreach ($lineasCultivo as $linea) {
            $gramaje = floatval($linea['gramaje']);  // Asegúrate de que el gramaje sea un número
            $promedioSurcosGerminados = 0;
            $promedioAlturaBrote = 0;

            // Filtrar los registros de germinación para la línea de cultivo actual
            $registrosLinea = array_filter($registros, function ($registro) use ($linea) {
                return $registro['cultivoId'] == $linea['cultivoId'];
            });

            // Calcular promedios para surcos germinados y altura de brote
            $totalSurcos = 0;
            $totalAltura = 0;
            $totalRegistros = count($registrosLinea);

            foreach ($registrosLinea as $registro) {
                $totalSurcos += $registro['numeroZurcosGerminados'];
                $totalAltura += $registro['broteAlturaMaxima'];
            }

            if ($totalRegistros > 0) {
                $promedioSurcosGerminados = $totalSurcos / $totalRegistros;
                $promedioAlturaBrote = $totalAltura / $totalRegistros;
            }

            // Almacenar las muestras y objetivos
            $samples[] = [
                $promedioSurcosGerminados,
                $promedioAlturaBrote,
                $gramaje
            ];

            // Usamos el gramaje como objetivo (peso de producción)
            $targets[] = $gramaje;
        }

        // Entrenar el modelo de predicción (SVR)
        $regression = new SVR(Kernel::LINEAR);  // Usamos Kernel lineal
        $regression->train($samples, $targets);

        // Realizar la predicción para un nuevo conjunto de datos (por ejemplo, para un nuevo ciclo)
        $predictions = [];
        foreach ($samples as $sample) {
            $predictions[] = $regression->predict($sample);
        }

        // Pasar los resultados a la vista
        return $this->render('index', [
            'cicloSeleccionado' => $cicloSeleccionado,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFinal,
            'lineasCultivo' => $lineasCultivo,
            'promediosDiarios' => $promediosDiarios,
            'predictions' => $predictions,

        ]);
    }
}
