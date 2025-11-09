<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Post $model */

$this->title = 'Удаление сообщения - StoryValut';
?>
<div class="post-delete">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="card card-default">
                <div class="card-header">
                    <h2 class="card-title text-danger"><?= Html::encode($this->title) ?></h2>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <strong>Внимание!</strong> Вы собираетесь удалить сообщение. Это действие необратимо.
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><?= Html::encode($model->author_name) ?></h5>
                            <div class="card-text post-message">
                                <?= nl2br(Html::encode($model->message)) ?>
                            </div>
                            <p class="card-text">
                                <small class="text-muted">
                                    Опубликовано: <?= Yii::$app->formatter->asDatetime($model->created_at) ?>
                                </small>
                            </p>
                        </div>
                    </div>

                    <?php $form = ActiveForm::begin(); ?>

                    <div class="form-group">
                        <p>Вы уверены, что хотите удалить это сообщение?</p>
                        <?= Html::submitButton('Да, удалить', ['class' => 'btn btn-danger']) ?>
                        <?= Html::a('Отмена', ['site/index'], ['class' => 'btn btn-default']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>