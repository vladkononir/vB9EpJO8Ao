<?php

namespace app\services;

use app\models\Post;

class PostQueryService
{
    public function findById(int $id): ?Post
    {
        return Post::findOne($id);
    }

    public function findByToken(string $token): ?Post
    {
        return Post::findOne(['token' => $token]);
    }

    public function getPostsCountByIp(string $ipAddress): int
    {
        return Post::find()
            ->where(['ip_address' => $ipAddress])
            ->andWhere(['IS', 'deleted_at', null])
            ->count();
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
}
