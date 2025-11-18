<?php

namespace app\services\posts;

use app\models\Post;
use yii\web\NotFoundHttpException;

class PostQueryService
{
    const ERROR_POST_NOT_FOUND = 'Запрашиваемое сообщение не найдено.';

    public function findById(int $id): ?Post
    {
        return Post::findOne($id);
    }

    public function getPostNumberByIp(Post $post): int
    {
        return (int) Post::find()
            ->where(['ip_address' => $post->ip_address])
            ->andWhere(['IS', 'deleted_at', null])
            ->andWhere(['OR',
                ['<', 'created_at', $post->created_at],
                ['AND',
                    ['=', 'created_at', $post->created_at],
                    ['<=', 'id', $post->id]
                ]
            ])
            ->count();
    }

    public function getLastPost(string $ipAddress): ?Post
    {
        return Post::find()
            ->where(['ip_address' => $ipAddress])
            ->andWhere(['IS', 'deleted_at', null])
            ->orderBy(['created_at' => SORT_DESC])
            ->one();
    }

    public function findPost(int $id): ?Post
    {
        $post = $this->findById($id);

        if ($post === null) {
            throw new NotFoundHttpException(self::ERROR_POST_NOT_FOUND);
        }

        return $post;
    }

    public function findPostsByIp(array $ipAddresses): array
    {
        return Post::find()
            ->where(['ip_address' => $ipAddresses])
            ->andWhere(['IS', 'deleted_at', null])
            ->orderBy(['ip_address' => SORT_ASC, 'created_at' => SORT_ASC, 'id' => SORT_ASC])
            ->asArray()
            ->all();
    }
}
