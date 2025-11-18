<?php

namespace app\services\posts;

use app\models\Post;

class PostUpdateService
{
    public function updatePost(Post $post): bool
    {
        $post->scenario = Post::SCENARIO_UPDATE;

        if ($post->save()) {
            return true;
        }

        return false;
    }
}
