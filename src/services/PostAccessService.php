<?php

namespace app\services;

use app\models\Post;

class PostAccessService
{
    public function canEdit(Post $post): bool
    {
        if ($post->isDeleted()) {
            return false;
        }

        $timeSinceCreation = time() - $post->created_at;

        return $timeSinceCreation <= Post::EDIT_TIME_LIMIT;
    }

    public function canDelete(Post $post): bool
    {
        if ($post->isDeleted()) {
            return false;
        }

        $timeSinceCreation = time() - $post->created_at;

        return $timeSinceCreation <= Post::DELETE_TIME_LIMIT;
    }
}
