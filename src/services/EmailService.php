<?php

namespace app\services;

use app\models\Post;
use Yii;

class EmailService
{
    const SENDER_EMAIL = 'noreply@storyvalut.com';
    const SENDER_NAME = 'StoryValut';
    const EMAIL_SUBJECT = 'Ссылки для управления вашим сообщением в StoryValut';

    const LOG_INFO_TEMPLATE = 'Email сохранен в файл для поста ID: %d на email: %s';
    const LOG_ERROR_SEND = 'Не удалось сохранить email для поста ID: %d';
    const LOG_ERROR_EXCEPTION = 'Ошибка при создании email: %s';

    public function sendManagementEmail(Post $post): bool
    {
        try {
            $emailSent = Yii::$app->mailer->compose('postManagement', ['post' => $post])
                ->setFrom([Yii::$app->params['senderEmail'] ?? self::SENDER_EMAIL => self::SENDER_NAME])
                ->setTo($post->email)
                ->setSubject(self::EMAIL_SUBJECT)
                ->send();

            if ($emailSent) {
                Yii::info(sprintf(self::LOG_INFO_TEMPLATE, $post->id, $post->email));
            } else {
                Yii::error(sprintf(self::LOG_ERROR_SEND, $post->id));
            }

            return $emailSent;

        } catch (\Exception $e) {
            Yii::error(sprintf(self::LOG_ERROR_EXCEPTION, $e->getMessage()));
            return false;
        }
    }
}
