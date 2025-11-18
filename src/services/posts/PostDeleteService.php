<?php

namespace app\services\posts;

use app\models\Post;

class PostDeleteService
{
    public function softDelete(Post $post): bool
    {
        $post->deleted_at = time();

        return $post->save(false, ['deleted_at']);
    }
}
