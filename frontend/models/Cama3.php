<?php

namespace frontend\models;

use yii\db\ActiveRecord;

/**
 * Modelo para la tabla cama1 automatizado.
 */
class Cama3 extends ActiveRecord
{
    /**
     * Especifica el nombre de la tabla asociada con el modelo.
     *
     * @return string
     */
    public static function tableName()
    {
        return 'cama3'; // Nombre de la tabla en la base de datos
    }

    /**
     * Define las reglas de validación para los atributos del modelo.
     *
     * @return array
     */
    public function rules()
    {
        return [
            [['humedad'], 'number'],
            [['fecha'], 'date', 'format' => 'php:Y-m-d'], // Valida que la fecha sea del formato correcto
            [['hora'], 'time', 'format' => 'php:H:i:s'], // Valida que la hora sea del formato correcto
        ];
    }

    // Etiquetas amigables
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'humedad' => 'Humedad del Suelo',
            'fecha' => 'Fecha de Registro',
            'hora' => 'Hora de Registro',
        ];
    }
}
