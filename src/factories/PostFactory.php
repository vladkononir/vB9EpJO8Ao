<?php

namespace app\factories;

use app\models\Post;

class PostFactory
{
    public function createFromRequest(): Post
    {
        return new Post(['scenario' => Post::SCENARIO_CREATE]);
    }
}
