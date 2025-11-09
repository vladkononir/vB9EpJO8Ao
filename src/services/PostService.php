<?php

namespace app\services;

use app\models\Post;
use yii\web\NotFoundHttpException;

class PostService
{
    public function findModel($id): Post
    {
        $model = Post::findOne($id);

        if ($model === null) {
            throw new NotFoundHttpException('Запрашиваемое сообщение не найдено.');
        }

        return $model;
    }
}
