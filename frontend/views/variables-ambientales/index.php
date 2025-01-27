<?php
$this->title = 'Actividades realizadas';
$this->registerCssFile('@web/css/evidencias.css');

use yii\helpers\Html;
?>
<div class="variables-ambientales-index">
    <h1 class="display-5 text-dark text-center mb-3"><?= Html::encode($this->title) ?></h1>
    <div class="body">
        <div class="collage-container">
            <!-- Replace the src attributes with the URLs of your remote images -->
            <?= Html::img(Yii::getAlias('@web') . '/images/evidencia3.jpeg', ['class' => 'img-fluid rounded', 'alt' => 'Evidencia3']) ?>
            <?= Html::img(Yii::getAlias('@web') . '/images/evidencia4.jpeg', ['class' => 'img-fluid rounded', 'alt' => 'Evidencia4']) ?>
            <?= Html::img(Yii::getAlias('@web') . '/images/evidencia5.jpeg', ['class' => 'img-fluid rounded', 'alt' => 'Evidencia5']) ?>
            <?= Html::img(Yii::getAlias('@web') . '/images/evidencia6.jpeg', ['class' => 'img-fluid rounded', 'alt' => 'Evidencia6']) ?>
            <?= Html::img(Yii::getAlias('@web') . '/images/evidencia11.jpeg', ['class' => 'img-fluid rounded', 'alt' => 'Evidencia11']) ?>
            <?= Html::img(Yii::getAlias('@web') . '/images/evidencia13.jpeg', ['class' => 'img-fluid rounded', 'alt' => 'Evidencia13']) ?>
            <?= Html::img(Yii::getAlias('@web') . '/images/evidencia14.jpeg', ['class' => 'img-fluid rounded', 'alt' => 'Evidencia14']) ?>
            <?= Html::img(Yii::getAlias('@web') . '/images/evidencia10.jpeg', ['class' => 'img-fluid rounded', 'alt' => 'Evidencia10']) ?>
            <?= Html::img(Yii::getAlias('@web') . '/images/evidencia1.jpeg', ['class' => 'img-fluid rounded', 'alt' => 'Evidencia1']) ?>
            <?= Html::img(Yii::getAlias('@web') . '/images/evidencia2.jpeg', ['class' => 'img-fluid rounded', 'alt' => 'Evidencia2']) ?>
            <?= Html::img(Yii::getAlias('@web') . '/images/evidencia12.jpeg', ['class' => 'img-fluid rounded', 'alt' => 'Evidencia12']) ?>
            <?= Html::img(Yii::getAlias('@web') . '/images/evidencia15.jpeg', ['class' => 'img-fluid rounded', 'alt' => 'Evidencia15']) ?>
            <?= Html::img(Yii::getAlias('@web') . '/images/evidencia10.jpeg', ['class' => 'img-fluid rounded', 'alt' => 'Evidencia10']) ?>
        </div>
    </div>
    <!-- Modal for showing the enlarged image -->
    <div class="modal" id="imageModal">
        <button class="close-btn" id="closeModal">&times;</button>
        <img src="" alt="Ampliada" id="modalImage">
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const images = document.querySelectorAll('.collage-container img');
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');
        const closeModal = document.getElementById('closeModal');

        images.forEach(image => {
            image.addEventListener('click', function() {
                modalImage.src = this.src;
                modal.classList.add('active');
            });
        });

        closeModal.addEventListener('click', function() {
            modal.classList.remove('active');
        });

        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });
</script>