<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Post $model */

$this->title = 'Редактирование сообщения - StoryValut';
?>
<div class="post-edit">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="card card-default">
                <div class="card-header">
                    <h2 class="card-title"><?= Html::encode($this->title) ?></h2>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <small>Вы можете редактировать сообщение в течение 12 часов после публикации.</small>
                    </div>

                    <?php $form = ActiveForm::begin(); ?>

                    <?= $form->field($model, 'message')->textarea([
                        'rows' => 8,
                        'maxlength' => 1000,
                        'placeholder' => 'Ваше сообщение...'
                    ])->label('Сообщение') ?>

                    <div class="form-group">
                        <?= Html::submitButton('Сохранить изменения', ['class' => 'btn btn-primary']) ?>
                        <?= Html::a('Отмена', ['site/index'], ['class' => 'btn btn-default']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>