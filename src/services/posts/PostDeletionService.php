<?php

namespace app\services\posts;

use app\models\Post;

class PostDeletionService
{
    private PostAccessService $postAccessService;

    public function __construct(PostAccessService $postAccessService)
    {
        $this->postAccessService = $postAccessService;
    }

    public function deletePost(Post $post): bool
    {
        if (!$this->postAccessService->canDelete($post)) {
            return false;
        }

        return $this->softDelete($post);
    }

    private function softDelete(Post $post): bool
    {
        $post->deleted_at = time();

        return $post->save(false, ['deleted_at']);
    }
}
