<?php

namespace app\services;

use Yii;

class RateLimitService
{
    const ERROR_COOLDOWN = 'Вы можете отправить следующее сообщение только после %s';
    const POST_COOLDOWN = 3 * 60;

    private PostQueryService $postQueryService;

    public function __construct(PostQueryService $postQueryService)
    {
        $this->postQueryService = $postQueryService;
    }

    public function checkRateLimit(string $ipAddress): ?string
    {
        $lastPost = $this->postQueryService->getLastPost($ipAddress);

        if ($lastPost && (time() - $lastPost->created_at) < self::POST_COOLDOWN) {
            $nextTime = $lastPost->created_at + self::POST_COOLDOWN;
            $formattedTime = Yii::$app->formatter->asDatetime($nextTime, 'php:d.m.Y H:i');

            return sprintf(self::ERROR_COOLDOWN, $formattedTime);
        }

        return null;
    }
}
