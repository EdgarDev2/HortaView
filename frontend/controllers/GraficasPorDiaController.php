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

    public function actionIndex()
    {
        // AGREGACIÓN DE DATOS Y MANIPULACIÓN DE COLECCIONES.
        try {
            date_default_timezone_set('America/Mexico_City'); // Ajusta la zona horaria según tu ubicación
            $fechaActual = date('Y-m-d');

            // Inicializamos los arreglos para almacenar los datos de las humedades por cama
            $promedios = [];
            $maximos = [];
            $minimos = [];

            // Listado de camas para iterar y obtener los promedios de humedad
            $camas = [
                'Cama1' => Cama1::class,
                'Cama2' => Cama2::class,
                'Cama3' => Cama3::class,
                'Cama4' => Cama4::class
            ];

            // Iterar sobre las camas para obtener los datos de humedad: promedio, máximo y mínimo por hora
            foreach ($camas as $key => $camaClass) {
                $data = $camaClass::find()
                    ->select([
                        'HOUR(hora) as hora',
                        'AVG(humedad) as promedio_humedad',
                        'MAX(humedad) as max_humedad',
                        'MIN(humedad) as min_humedad'
                    ])
                    ->where(['fecha' => $fechaActual])
                    ->groupBy(['HOUR(hora)'])
                    ->orderBy(['hora' => SORT_ASC]) // Asegura que los datos estén ordenados por hora
                    ->asArray()
                    ->all();

                // Inicializa el arreglo con null para cada hora
                $promedios[$key] = array_fill(0, 24, null);
                $maximos[$key] = array_fill(0, 24, null);
                $minimos[$key] = array_fill(0, 24, null);

                // Llenar los arreglos con los valores correspondientes
                foreach ($data as $row) {
                    $promedios[$key][$row['hora']] = $row['promedio_humedad'];
                    $maximos[$key][$row['hora']] = $row['max_humedad'];
                    $minimos[$key][$row['hora']] = $row['min_humedad'];
                }
            }

            // Pasar los datos a la vista
            return $this->render('index', [
                'promedios' => $promedios,
                'maximos' => $maximos,
                'minimos' => $minimos,
                'fechaActual' => $fechaActual,
            ]);
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Ocurrió un error al obtener los datos de humedad por: '
                . $e->getMessage(), __METHOD__);
            return $this->redirect(['site/error']);
        }
    }

    // Se procesa la solicitud Ajax y se realizan las consultas necesarias a la petición
    public function actionAjax()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // Obtiene la fecha y el ID de la cama de la solicitud POST
        $fecha = Yii::$app->request->post('fecha');
        $camaId = Yii::$app->request->post('camaId');

        // Depuración: Verifica los datos recibidos
        Yii::info('Datos recibidos: ' . json_encode(['fecha' => $fecha, 'camaId' => $camaId]), __METHOD__);

        // Selecciona el modelo correspondiente a la cama
        switch ($camaId) {
            case 'fechaCama1':
                $model = Cama1::find();
                break;
            case 'fechaCama2':
                $model = Cama2::find();
                break;
            case 'fechaCama3':
                $model = Cama3::find();
                break;
            case 'fechaCama4':
                $model = Cama4::find();
                break;
            default:
                return ['success' => false, 'message' => 'Cama no válida'];
        }

        // Verifica que la fecha sea válida
        if (!strtotime($fecha)) {
            return ['success' => false, 'message' => 'Fecha no válida'];
        }

        try {
            // Realiza la consulta para obtener promedios, máximos y mínimos por hora
            $data = $model
                ->select([
                    'HOUR(hora) as hora',
                    'AVG(humedad) as promedio_humedad',
                    'MAX(humedad) as maximo_humedad',
                    'MIN(humedad) as minimo_humedad',
                ])
                ->where(['fecha' => $fecha])
                ->groupBy(['HOUR(hora)'])
                ->orderBy(['hora' => SORT_ASC])
                ->asArray()
                ->all();

            // Inicializa los arrays de 24 horas con valores `null`
            $promediosPorHora = array_fill(0, 24, null);
            $maximosPorHora = array_fill(0, 24, null);
            $minimosPorHora = array_fill(0, 24, null);

            // Rellena los arrays con los datos de la consulta
            foreach ($data as $entry) {
                $hora = (int)$entry['hora'];
                $promediosPorHora[$hora] = (float)$entry['promedio_humedad'];
                $maximosPorHora[$hora] = (float)$entry['maximo_humedad'];
                $minimosPorHora[$hora] = (float)$entry['minimo_humedad'];
            }

            // Responde con los datos de promedios, máximos y mínimos por hora
            return [
                'success' => true,
                'promedios' => $promediosPorHora,
                'maximos' => $maximosPorHora,
                'minimos' => $minimosPorHora,
            ];
        } catch (\Exception $e) {
            Yii::error("Error al obtener los datos: " . $e->getMessage(), __METHOD__);
            return ['success' => false, 'message' => 'Error al procesar la solicitud'];
        }
    }
}
