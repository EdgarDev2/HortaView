<?php

namespace frontend\controllers;

use DateTime;
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

class GraficasController extends Controller
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
        // Agregación de datos y manipulación de colecciones.
        try {
            date_default_timezone_set('America/Mexico_City'); // Ajusta la zona horaria según tu ubicación
            $fechaActual = date('Y-m-d');

            // Inicializa un arreglo para las horas del día
            $horasDelDia = range(0, 23);

            // Crear un arreglo para almacenar los resultados finales
            $resultados = [];

            // Obtener promedios de humedad por hora para Cama 1
            $dataCama1 = Cama1::find()
                ->select(['HOUR(hora) as hora', 'AVG(humedad) as promedio_humedad'])
                ->where(['fecha' => $fechaActual])
                ->groupBy(['HOUR(hora)'])
                ->asArray()
                ->all();
            $promediosCama1 = array_column($dataCama1, 'promedio_humedad', 'hora');

            // Obtener promedios de humedad por hora para Cama 2
            $dataCama2 = Cama2::find()
                ->select(['HOUR(hora) as hora', 'AVG(humedad) as promedio_humedad'])
                ->where(['fecha' => $fechaActual])
                ->groupBy(['HOUR(hora)'])
                ->asArray()
                ->all();
            $promediosCama2 = array_column($dataCama2, 'promedio_humedad', 'hora');

            // Obtener promedios de humedad por hora para Cama 3
            $dataCama3 = Cama3::find()
                ->select(['HOUR(hora) as hora', 'AVG(humedad) as promedio_humedad'])
                ->where(['fecha' => $fechaActual])
                ->groupBy(['HOUR(hora)'])
                ->asArray()
                ->all();
            $promediosCama3 = array_column($dataCama3, 'promedio_humedad', 'hora');

            // Obtener promedios de humedad por hora para Cama 4
            $dataCama4 = Cama4::find()
                ->select(['HOUR(hora) as hora', 'AVG(humedad) as promedio_humedad'])
                ->where(['fecha' => $fechaActual])
                ->groupBy(['HOUR(hora)'])
                ->asArray()
                ->all();
            $promediosCama4 = array_column($dataCama4, 'promedio_humedad', 'hora');

            // Crear un arreglo para almacenar los resultados
            foreach ($horasDelDia as $hora) {
                $resultados[$hora] = [
                    'hora' => $hora,
                    'promedio_humedad_cama1' => $promediosCama1[$hora] ?? null,
                    'promedio_humedad_cama2' => $promediosCama2[$hora] ?? null,
                    'promedio_humedad_cama3' => $promediosCama3[$hora] ?? null,
                    'promedio_humedad_cama4' => $promediosCama4[$hora] ?? null,
                ];
            }

            // Pasar los datos a la vista
            return $this->render('index', [
                'resultados' => $resultados,
                'fechaActual' => $fechaActual, // Aquí pasas la fecha
            ]);
        } catch (\Exception $e) {
            // Registrar el error en el log
            Yii::error("Error al obtener los datos de humedad: " . $e->getMessage(), __METHOD__);

            // Mostrar un mensaje de error amigable a los usuarios
            Yii::$app->session->setFlash('error', 'Ocurrió un error al obtener los datos de humedad. Por favor, intenta de nuevo más tarde.');

            // Redirigir o renderizar una vista de error
            return $this->redirect(['site/error']);
        }
    }

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
            // Realiza la consulta para obtener los promedios por hora
            $data = $model
                ->select(['HOUR(hora) as hora', 'AVG(humedad) as promedio_humedad'])
                ->where(['fecha' => $fecha])
                ->groupBy(['HOUR(hora)'])
                ->asArray()
                ->all();

            // Inicializa el array de 24 horas con valores `null`
            $promediosPorHora = array_fill(0, 24, null);

            // Rellena el array con los datos de la consulta
            foreach ($data as $entry) {
                $promediosPorHora[(int)$entry['hora']] = (float)$entry['promedio_humedad'];
            }

            // Si no hay datos para la fecha, devuelve el array de `null` completo para limpiar el gráfico
            if (empty($data)) {
                return ['success' => true, 'promedios' => $promediosPorHora];
            }

            // Responde con los datos de promedio por hora
            return ['success' => true, 'promedios' => $promediosPorHora];
        } catch (\Exception $e) {
            Yii::error("Error al obtener los promedios: " . $e->getMessage(), __METHOD__);
            return ['success' => false, 'message' => 'Error al procesar la solicitud'];
        }
    }
}
