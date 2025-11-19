<?php

namespace app\models\forms;

use app\models\Post;
use yii\base\Model;

class PostUpdateForm extends Model
{
    public $message;

    private Post $post;

    public function __construct(Post $post, $config = [])
    {
        $this->post = $post;
        $this->message = $post->message;

        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            ['message', 'required'],
            ['message', 'string', 'min' => 5, 'max' => 1000],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'message' => Post::LABEL_MESSAGE,
        ];
    }

    public function updatePost(): bool
    {
        $this->post->scenario = Post::SCENARIO_UPDATE;

        $this->post->message = $this->message;

        return $this->post->save();
    }

    public function getPost(): Post
    {
        return $this->post;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}