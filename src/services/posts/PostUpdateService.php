<?php

namespace app\services\posts;

use app\models\Post;

class PostUpdateService
{
    private PostAccessService $postAccessService;

    public function __construct(PostAccessService $postAccessService)
    {
        $this->postAccessService = $postAccessService;
    }

    public function updatePost(Post $post): bool
    {
        if (!$this->postAccessService->canEdit($post)) {
            return false;
        }

        $post->scenario = Post::SCENARIO_UPDATE;

        if ($post->save()) {
            return true;
        }

        return false;
    }
}