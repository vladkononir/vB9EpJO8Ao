<?php

use yii\helpers\Html;
use yii\widgets\ListView;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\Post $model */

$this->title = 'Создание сообщений - StoryValut';

$pagination = $dataProvider->getPagination();
$totalCount = $dataProvider->getTotalCount();
$currentPage = $pagination->getPage();
$pageSize = $pagination->getPageSize();
$start = ($currentPage * $pageSize) + 1;
$end = min(($currentPage + 1) * $pageSize, $totalCount);
?>
<div class="site-index">
    <div class="row">
        <div class="col-md-8">
            <h2>Показаны записи <?= $start ?>-<?= $end ?> из <?= $totalCount ?></h2>

            <?= ListView::widget([
                'dataProvider' => $dataProvider,
                'itemView' => '_post',
                'layout' => "{items}\n{pager}",
                'emptyText' => 'Пока нет сообщений.',
                'emptyTextOptions' => ['class' => 'alert alert-info'],
                'pager' => [
                    'options' => ['class' => 'pagination'],
                ],
            ]) ?>
        </div>

        <div class="col-md-4">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Новое сообщение</h3>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'id' => 'post-form',
                    ]); ?>

                    <?= $form->field($model, 'author_name')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'Ваше имя (2-15 символов)'
                    ]) ?>

                    <?= $form->field($model, 'email')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'your@email.com'
                    ]) ?>

                    <?= $form->field($model, 'message')->textarea([
                        'rows' => 6,
                        'placeholder' => 'Ваше сообщение... (5-1000 символов)',
                        'maxlength' => 1000
                    ]) ?>

                    <?= $form->field($model, 'captcha')->widget(\yii\captcha\Captcha::class, [
                        'template' => '{image}{input}',
                    ]) ?>

                    <div class="form-group">
                        <?= Html::submitButton('Опубликовать', ['class' => 'btn btn-primary btn-block']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <div class="mt-3">
                <div class="alert alert-info">
                    <small>
                        <strong>Правила публикации:</strong><br>
                        • Разрешены теги: &lt;b&gt;, &lt;i&gt;, &lt;s&gt;<br>
                        • Сообщения проверяются<br>
                        • 1 сообщение в 3 минуты<br>
                        • Редактирование в течение 12 часов<br>
                        • Удаление в течение 14 дней
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>