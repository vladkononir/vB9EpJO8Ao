<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $author_name
 * @property string $email
 * @property string $message
 * @property string $ip_address
 * @property int $created_at
 * @property int $updated_at
 * @property int|null $deleted_at
 */
class Post extends ActiveRecord
{
    const ALLOWED_HTML_TAGS = '<b><i><s>';
    const EDIT_TIME_LIMIT = 12 * 3600;
    const DELETE_TIME_LIMIT = 14 * 24 * 3600;
    const POST_COOLDOWN = 3 * 60;

    public $captcha;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'posts';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => time(),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['author_name', 'email', 'message'], 'required', 'on' => 'create'],
            ['author_name', 'string', 'min' => 2, 'max' => 15, 'on' => 'create'],
            ['email', 'email', 'on' => 'create'],
            ['message', 'string', 'min' => 5, 'max' => 1000, 'on' => 'create'],
            ['message', 'validateAllowedHtmlTags', 'on' => 'create'],
            ['message', 'filter', 'filter' => [self::class, 'stripTagsFilter'], 'on' => 'create'],
            ['message', 'filter', 'filter' => 'trim', 'on' => 'create'],
            ['message', 'validateNotEmpty', 'on' => 'create'],
            ['captcha', 'required', 'on' => 'create'],
            ['captcha', 'captcha', 'captchaAction' => 'site/captcha', 'on' => 'create'],

            [['message'], 'required', 'on' => 'update'],
            ['message', 'string', 'min' => 5, 'max' => 1000, 'on' => 'update'],
            ['message', 'validateAllowedHtmlTags', 'on' => 'update'],
            ['message', 'filter', 'filter' => [self::class, 'stripTagsFilter'], 'on' => 'update'],
            ['message', 'filter', 'filter' => 'trim', 'on' => 'update'],
            ['message', 'validateNotEmpty', 'on' => 'update'],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['create'] = ['author_name', 'email', 'message', 'captcha'];
        $scenarios['update'] = ['message'];
        return $scenarios;
    }

    public function validateAllowedHtmlTags(string $attribute): void
    {
        $value = $this->$attribute;

        $withoutAllowedTags = strip_tags($value, self::ALLOWED_HTML_TAGS);

        $withoutAllTags = strip_tags($withoutAllowedTags);

        if ($withoutAllowedTags !== $withoutAllTags) {
            $this->addError($attribute,
                'Разрешены только HTML теги: <b>, <i>, <s>. ' .
                'Пожалуйста, удалите другие HTML теги из сообщения.'
            );
        }
    }

    public static function stripTagsFilter(string $value): string
    {
        return strip_tags($value, self::ALLOWED_HTML_TAGS);
    }

    public function validateNotEmpty(string $attribute, mixed $params): void
    {
        $value = trim(strip_tags($this->$attribute));

        if (empty($value)) {
            $this->addError($attribute, 'Сообщение не может состоять только из пробелов или HTML тегов.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'author_name' => 'Имя автора',
            'email' => 'Email',
            'message' => 'Сообщение',
            'captcha' => 'Проверочный код',
        ];
    }

    public function canEdit(): bool
    {
        if ($this->isDeleted()) {
            return false;
        }

        $timeSinceCreation = time() - $this->created_at;

        if ($timeSinceCreation <= self::EDIT_TIME_LIMIT) {
            return true;
        }

        return false;
    }

    public function canDelete(): bool
    {
        if ($this->isDeleted()) {
            return false;
        }

        $timeSinceCreation = time() - $this->created_at;

        if ($timeSinceCreation <= self::DELETE_TIME_LIMIT) {
            return true;
        }

        return false;
    }

    public function isDeleted(): bool
    {
        return $this->deleted_at !== null;
    }

    public function softDelete(): bool
    {
        $this->deleted_at = time();

        return $this->save(false, ['deleted_at']);
    }

    public function getPostsCountByIp(): int
    {
        return self::find()
            ->where(['ip_address' => $this->ip_address])
            ->andWhere(['IS', 'deleted_at', null])
            ->count();
    }

    public function getMaskedIp(): string
    {
        if (filter_var($this->ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $this->ip_address);

            if (count($parts) === 4) {
                return $parts[0] . '.' . $parts[1] . '.**.**';
            }
        } elseif (filter_var($this->ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $this->ip_address);

            if (count($parts) >= 4) {
                $visible = array_slice($parts, 0, count($parts) - 4);
                $hidden = array_fill(0, 4, '****');

                return implode(':', array_merge($visible, $hidden));
            }
        }
        
        return $this->ip_address;
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert) {
            $this->ip_address = Yii::$app->request->userIP;

            $lastPost = self::find()
                ->where(['ip_address' => $this->ip_address])
                ->andWhere(['IS', 'deleted_at', null])
                ->orderBy(['created_at' => SORT_DESC])
                ->one();

            if ($lastPost && (time() - $lastPost->created_at) < self::POST_COOLDOWN) {
                $nextTime = $lastPost->created_at + self::POST_COOLDOWN;

                $this->addError('message',
                    'Вы можете отправить следующее сообщение только после ' .
                    Yii::$app->formatter->asDatetime($nextTime)
                );

                return false;
            }
        }

        return true;
    }
}
