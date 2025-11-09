<?php

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;

/** @var yii\web\View $this */
/** @var app\models\Post $model */

$this->title = 'Сообщение от ' . Html::encode($model->author_name) . ' - StoryValut';
$postsCount = $model->getPostsCountByIp();
?>
<div class="post-view">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="card card-default">
                <div class="card-header">
                    <h2 class="card-title">Сообщение</h2>
                </div>
                <div class="card-body">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><?= Html::encode($model->author_name) ?></h5>

                            <div class="card-text post-message">
                                <?= HtmlPurifier::process($model->message, [
                                    'HTML.Allowed' => 'b,i,s',
                                    'AutoFormat.RemoveEmpty' => true,
                                ]) ?>
                            </div>

                            <p class="card-text">
                                <small class="text-muted">
                                    <?= Yii::$app->formatter->asRelativeTime($model->created_at) ?> |
                                    <?= $model->getMaskedIp() ?> |
                                    <?= Yii::t('app', '{n, plural, =0{нет постов} one{# пост} few{# поста} many{# постов} other{# поста}}', ['n' => $postsCount]) ?>
                                </small>
                            </p>
                        </div>
                    </div>

                    <div class="text-center">
                        <?php if ($model->canEdit()): ?>
                            <?= Html::a('Редактировать', ['edit', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                        <?php endif; ?>

                        <?php if ($model->canDelete()): ?>
                            <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
                                'class' => 'btn btn-danger',
                                'data' => [
                                    'confirm' => 'Вы уверены, что хотите удалить это сообщение?',
                                    'method' => 'post',
                                ],
                            ]) ?>
                        <?php endif; ?>

                        <?= Html::a('На главную', ['site/index'], ['class' => 'btn btn-default']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>