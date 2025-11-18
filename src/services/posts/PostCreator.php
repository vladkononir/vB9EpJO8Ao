<?php

namespace app\services\posts;

use app\models\Post;
use app\services\notifications\EmailSender;
use app\services\system\RateLimitChecker;
use Yii;

class PostCreator
{
    private EmailSender $emailSender;
    private RateLimitChecker $rateLimitChecker;

    public function __construct(
        EmailSender      $emailSender,
        RateLimitChecker $rateLimitChecker
    ) {
        $this->emailSender = $emailSender;
        $this->rateLimitChecker = $rateLimitChecker;
    }

    public function create(Post $post): bool
    {
        $cooldownError = $this->rateLimitChecker->check(Yii::$app->request->userIP);

        if ($cooldownError) {
            $post->addError(Post::ATTR_MESSAGE, $cooldownError);

            return false;
        }

        $post->ip_address = Yii::$app->request->userIP;

        if ($post->save()) {
            $this->emailSender->send($post);

            return true;
        }

        return false;
    }
}
