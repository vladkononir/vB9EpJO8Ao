<?php

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;

/** @var app\models\Post $model */
/** @var yii\web\View $this */

$postsCount = $model->getPostNumberByIp();
?>

<div class="card card-default mb-3" style="cursor: pointer;" onclick="window.location='<?= Yii::$app->urlManager->createUrl(['post/view', 'id' => $model->id]) ?>'">
    <div class="card-body">
        <h5 class="card-title"><?= Html::encode($model->author_name) ?></h5>

        <p>
            <?= HtmlPurifier::process($model->message, [
                'HTML.Allowed' => 'b,i,s',
                'AutoFormat.RemoveEmpty' => true,
            ]) ?>
        </p>

        <p>
            <small class="text-muted">
                <?= Yii::$app->formatter->asRelativeTime($model->created_at) ?> |
                <?= $model->getMaskedIp() ?> |
                <?= $postsCount . ' пост' ?>
            </small>
        </p>
    </div>
</div>