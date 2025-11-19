<?php

namespace app\factories;

use app\models\forms\PostCreateForm;
use app\models\forms\PostUpdateForm;
use app\models\Post;

class FormFactory
{
    private PostFactory $postFactory;

    public function __construct(PostFactory $postFactory)
    {
        $this->postFactory = $postFactory;
    }

    public function postUpdateForm(Post $post): PostUpdateForm
    {
        return new PostUpdateForm($post);
    }

    public function postCreateForm(): PostCreateForm
    {
        return new PostCreateForm($this->postFactory);
    }
}
