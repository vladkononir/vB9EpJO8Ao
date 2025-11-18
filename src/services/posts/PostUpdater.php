<?php

namespace app\services\posts;

use app\models\Post;

class PostUpdater
{
    public function update(Post $post): bool
    {
        $post->scenario = Post::SCENARIO_UPDATE;

        if ($post->save()) {
            return true;
        }

        return false;
    }
}
