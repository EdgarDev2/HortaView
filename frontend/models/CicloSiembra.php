<?php

namespace frontend\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "ciclosiembra".
 *
 * @property string $cicloId
 * @property string $descripcion
 * @property string $fechaInicio
 * @property string $fechaFin
 * @property int $ciclo
 */
class CicloSiembra extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ciclosiembra';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cicloId', 'descripcion', 'fechaInicio', 'fechaFin', 'ciclo'], 'required'], // Todos los campos son obligatorios
            [['fechaInicio', 'fechaFin'], 'datetime', 'format' => 'php:Y-m-d H:i:s'], // Validación para formato datetime
            [['ciclo'], 'integer'], // Validación para ciclo como entero
            [['cicloId'], 'string', 'max' => 50], // Longitud máxima para cicloId
            [['descripcion'], 'string', 'max' => 255], // Longitud máxima para descripción
            [['cicloId'], 'unique'], // Asegura que cicloId sea único
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'cicloId' => 'ID del Ciclo',
            'descripcion' => 'Descripción',
            'fechaInicio' => 'Fecha de Inicio',
            'fechaFin' => 'Fecha de Fin',
            'ciclo' => 'Ciclo',
        ];
    }
}
