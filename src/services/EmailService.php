<?php

namespace app\services;

use app\models\Post;
use Yii;

class EmailService
{
    public function sendManagementEmail(Post $post): bool
    {
        try {
            $emailSent = Yii::$app->mailer->compose('postManagement', ['post' => $post])
                ->setFrom([Yii::$app->params['senderEmail'] ?? 'noreply@storyvalut.com' => 'StoryValut'])
                ->setTo($post->email)
                ->setSubject('Ссылки для управления вашим сообщением в StoryValut')
                ->send();

            if ($emailSent) {
                Yii::info('Email сохранен в файл для поста ID: ' . $post->id . ' на email: ' . $post->email);
            } else {
                Yii::error('Не удалось сохранить email для поста ID: ' . $post->id);
            }

            return $emailSent;

        } catch (\Exception $e) {
            Yii::error('Ошибка при создании email: ' . $e->getMessage());
            return false;
        }
    }
}
