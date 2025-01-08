<?php

namespace frontend\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "riegomanual".
 *
 * @property string $idRiegoManual
 * @property string|null $fechaEncendido
 * @property string|null $fechaApagado
 * @property float|null $volumen
 * @property string|null $cultivoId
 */
class RiegoManual extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'riegomanual';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idRiegoManual'], 'required'],
            [['fechaEncendido', 'fechaApagado'], 'safe'],
            [['volumen'], 'number'],
            [['idRiegoManual', 'cultivoId'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'idRiegoManual' => 'ID Riego Manual',
            'fechaEncendido' => 'Fecha Encendido',
            'fechaApagado' => 'Fecha Apagado',
            'volumen' => 'Volumen',
            'cultivoId' => 'Cultivo ID',
        ];
    }
}
