<?php

namespace app\services\system;

use app\services\posts\PostFinder;
use Yii;

class RateLimitChecker
{
    const ERROR_COOLDOWN = 'Вы можете отправить следующее сообщение только после %s';
    const POST_COOLDOWN = 3 * 60;

    private PostFinder $postFinder;

    public function __construct(PostFinder $postFinder)
    {
        $this->postFinder = $postFinder;
    }

    public function check(string $ipAddress): ?string
    {
        $lastPost = $this->postFinder->getLastPost($ipAddress);

        if ($lastPost && (time() - $lastPost->created_at) < self::POST_COOLDOWN) {
            $nextTime = $lastPost->created_at + self::POST_COOLDOWN;
            $formattedTime = Yii::$app->formatter->asDatetime($nextTime, 'php:d.m.Y H:i');

            return sprintf(self::ERROR_COOLDOWN, $formattedTime);
        }

        return null;
    }
}
