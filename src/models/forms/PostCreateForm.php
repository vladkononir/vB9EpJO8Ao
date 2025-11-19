<?php

namespace app\models\forms;

use app\factories\PostFactory;
use app\models\Post;
use yii\base\Model;

class PostCreateForm extends Model
{
    public $author_name;
    public $email;
    public $message;
    public $captcha;
    private PostFactory $postFactory;

    public function __construct(PostFactory $postFactory, $config = [])
    {
        $this->postFactory = $postFactory;

        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            [['author_name', 'email', 'message', 'captcha'], 'required'],
            ['author_name', 'string', 'min' => Post::AUTHOR_NAME_MIN_LENGTH, 'max' => Post::AUTHOR_NAME_MAX_LENGTH],
            ['email', 'email'],
            ['message', 'string', 'min' => Post::MESSAGE_MIN_LENGTH, 'max' => Post::MESSAGE_MAX_LENGTH],
            ['captcha', 'captcha', 'captchaAction' => 'site/captcha'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'author_name' => Post::LABEL_AUTHOR_NAME,
            'email' => Post::LABEL_EMAIL,
            'message' => Post::LABEL_MESSAGE,
            'captcha' => Post::LABEL_CAPTCHA,
        ];
    }

    public function createPost(): Post
    {
        $post = $this->postFactory->createFromRequest();

        $post->author_name = $this->author_name;
        $post->email = $this->email;
        $post->message = $this->message;

        return $post;
    }
}
