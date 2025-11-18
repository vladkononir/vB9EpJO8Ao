<?php

namespace app\services\posts;

use app\models\Post;

class PostDeletionService
{
    public function softDelete(Post $post): bool
    {
        $post->deleted_at = time();

        return $post->save(false, ['deleted_at']);
    }
}
