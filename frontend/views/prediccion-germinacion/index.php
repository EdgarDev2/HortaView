<?php
$this->title = 'Predicción porcentage de germinación';

use yii\helpers\Html;
?>
<div class="prediccion-germinacion-index">
    <h1 class="display-5 text-secondary text-center"><?= Html::encode($this->title) ?></h1>

    <?php foreach ($cultivos as $cultivo): ?>
        <div class="cultivo mb-5">
            <h2 class="text-secondary mb-3"><?= Html::encode($cultivo['nombreCultivo']) ?></h2>

            <!-- Tabla de Predicciones -->
            <table class="table table-bordered table-striped table-hover shadow-sm rounded">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">Línea</th>
                        <th class="text-center">Predicción % de Germinación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($prediccionesPorCultivo[$cultivo['cultivoId']])): ?>
                        <?php foreach ($prediccionesPorCultivo[$cultivo['cultivoId']] as $prediccion): ?>
                            <tr>
                                <td class="text-center"><?= Html::encode($prediccion['linea']) ?></td>
                                <td class="text-center"><?= Html::encode(number_format($prediccion['prediccion'], 2)) ?> %</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" class="text-center text-muted">No hay predicciones disponibles.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
</div>


</div>