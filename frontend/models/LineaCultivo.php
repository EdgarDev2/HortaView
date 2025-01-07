<?php

namespace frontend\models;

use yii\db\ActiveRecord;
use frontend\models\Cultivo;


/**
 * This is the model class for table "lineacultivo".
 *
 * @property int $numeroLinea
 * @property double|null $gramaje
 * @property string $lineaCultivoId
 * @property string $cultivoId
 *
 * @property Cultivo $cultivo
 */
class LineaCultivo extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'lineacultivo';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['numeroLinea', 'lineaCultivoId', 'cultivoId'], 'required'], // Campos obligatorios
            [['numeroLinea'], 'integer'], // Validación de número entero
            [['gramaje'], 'number'], // Validación de número flotante
            [['lineaCultivoId', 'cultivoId'], 'string', 'max' => 255], // Longitud máxima para cadenas
            [['lineaCultivoId'], 'unique'], // Clave primaria debe ser única
            [['cultivoId'], 'exist', 'skipOnError' => true, 'targetClass' => Cultivo::class, 'targetAttribute' => ['cultivoId' => 'cultivoId']], // Relación con la tabla Cultivo
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'numeroLinea' => 'Número de Línea',
            'gramaje' => 'Gramaje',
            'lineaCultivoId' => 'ID de Línea de Cultivo',
            'cultivoId' => 'ID de Cultivo',
        ];
    }

    /**
     * Relación con el modelo Cultivo.
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getCultivo()
    {
        return $this->hasOne(Cultivo::class, ['cultivoId' => 'cultivoId']);
    }
}
