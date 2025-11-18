<?php

namespace app\services\posts;

use app\models\Post;
use app\services\notifications\EmailService;
use app\services\system\RateLimitService;
use Yii;

class PostCreationService
{
    private EmailService $emailService;
    private RateLimitService $rateLimitService;

    public function __construct(
        EmailService $emailService,
        RateLimitService $rateLimitService
    ) {
        $this->emailService = $emailService;
        $this->rateLimitService = $rateLimitService;
    }

    public function createPost(Post $post): bool
    {
        $cooldownError = $this->rateLimitService->checkRateLimit(Yii::$app->request->userIP);

        if ($cooldownError) {
            $post->addError(Post::ATTR_MESSAGE, $cooldownError);

            return false;
        }

        $post->ip_address = Yii::$app->request->userIP;

        if ($post->save()) {
            $this->emailService->sendManagementEmail($post);

            return true;
        }

        return false;
    }
}
