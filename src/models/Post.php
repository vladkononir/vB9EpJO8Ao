<?php

namespace app\models;

use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class Post extends ActiveRecord
{
    const ALLOWED_HTML_TAGS = '<b><i><s>';
    const ERROR_ALLOWED_HTML_TAGS = 'Разрешены только HTML теги: <b>, <i>, <s>. Пожалуйста, удалите другие HTML теги из сообщения.';
    const ERROR_EMPTY_MESSAGE = 'Сообщение не может состоять только из пробелов или HTML тегов.';
    const ERROR_COOLDOWN = 'Вы можете отправить следующее сообщение только после %s';

    const EDIT_TIME_LIMIT = 12 * 3600;
    const DELETE_TIME_LIMIT = 14 * 24 * 3600;
    const POST_COOLDOWN = 3 * 60;

    const AUTHOR_NAME_MIN_LENGTH = 2;
    const AUTHOR_NAME_MAX_LENGTH = 15;
    const MESSAGE_MIN_LENGTH = 5;
    const MESSAGE_MAX_LENGTH = 1000;

    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    const ATTR_AUTHOR_NAME = 'author_name';
    const ATTR_EMAIL = 'email';
    const ATTR_MESSAGE = 'message';
    const ATTR_CAPTCHA = 'captcha';

    const LABEL_AUTHOR_NAME = 'Имя автора';
    const LABEL_EMAIL = 'Email';
    const LABEL_MESSAGE = 'Сообщение';
    const LABEL_CAPTCHA = 'Проверочный код';

    public $captcha;

    public static function tableName(): string
    {
        return 'posts';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => time(),
            ],
            [
                'class' => AttributeBehavior::class,
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => 'token',
                ],
                'value' => function () {
                    return Yii::$app->security->generateRandomString(32);
                },
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [[self::ATTR_AUTHOR_NAME, self::ATTR_EMAIL, self::ATTR_MESSAGE], 'required', 'on' => self::SCENARIO_CREATE],
            [self::ATTR_AUTHOR_NAME, 'string', 'min' => self::AUTHOR_NAME_MIN_LENGTH, 'max' => self::AUTHOR_NAME_MAX_LENGTH, 'on' => self::SCENARIO_CREATE],
            [self::ATTR_EMAIL, 'email', 'on' => self::SCENARIO_CREATE],
            [self::ATTR_MESSAGE, 'string', 'min' => self::MESSAGE_MIN_LENGTH, 'max' => self::MESSAGE_MAX_LENGTH, 'on' => self::SCENARIO_CREATE],
            [self::ATTR_MESSAGE, 'validateAllowedHtmlTags', 'on' => self::SCENARIO_CREATE],
            [self::ATTR_MESSAGE, 'filter', 'filter' => [self::class, 'stripTagsFilter'], 'on' => self::SCENARIO_CREATE],
            [self::ATTR_MESSAGE, 'filter', 'filter' => 'trim', 'on' => self::SCENARIO_CREATE],
            [self::ATTR_MESSAGE, 'validateNotEmpty', 'on' => self::SCENARIO_CREATE],
            [self::ATTR_CAPTCHA, 'required', 'on' => self::SCENARIO_CREATE],
            [self::ATTR_CAPTCHA, 'captcha', 'captchaAction' => 'site/captcha', 'on' => self::SCENARIO_CREATE],

            [[self::ATTR_MESSAGE], 'required', 'on' => self::SCENARIO_UPDATE],
            [self::ATTR_MESSAGE, 'string', 'min' => self::MESSAGE_MIN_LENGTH, 'max' => self::MESSAGE_MAX_LENGTH, 'on' => self::SCENARIO_UPDATE],
            [self::ATTR_MESSAGE, 'validateAllowedHtmlTags', 'on' => self::SCENARIO_UPDATE],
            [self::ATTR_MESSAGE, 'filter', 'filter' => [self::class, 'stripTagsFilter'], 'on' => self::SCENARIO_UPDATE],
            [self::ATTR_MESSAGE, 'filter', 'filter' => 'trim', 'on' => self::SCENARIO_UPDATE],
            [self::ATTR_MESSAGE, 'validateNotEmpty', 'on' => self::SCENARIO_UPDATE],
        ];
    }

    public function scenarios(): array
    {
        $scenarios = parent::scenarios();

        $scenarios[self::SCENARIO_CREATE] = [self::ATTR_AUTHOR_NAME, self::ATTR_EMAIL, self::ATTR_MESSAGE, self::ATTR_CAPTCHA];
        $scenarios[self::SCENARIO_UPDATE] = [self::ATTR_MESSAGE];

        return $scenarios;
    }

    public function validateAllowedHtmlTags(string $attribute): void
    {
        $value = $this->$attribute;

        $cleanedValue = strip_tags($value, self::ALLOWED_HTML_TAGS);

        if ($value !== $cleanedValue) {
            $this->addError($attribute, self::ERROR_ALLOWED_HTML_TAGS);
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
            $this->addError($attribute, self::ERROR_EMPTY_MESSAGE);
        }
    }

    public function attributeLabels(): array
    {
        return [
            self::ATTR_AUTHOR_NAME => self::LABEL_AUTHOR_NAME,
            self::ATTR_EMAIL => self::LABEL_EMAIL,
            self::ATTR_MESSAGE => self::LABEL_MESSAGE,
            self::ATTR_CAPTCHA => self::LABEL_CAPTCHA,
        ];
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

    public function getPostNumberByIp(): int
    {
        return (int) self::find()
            ->where(['ip_address' => $this->ip_address])
            ->andWhere(['OR',
                ['<', 'created_at', $this->created_at],
                ['AND',
                    ['=', 'created_at', $this->created_at],
                    ['<=', 'id', $this->id]
                ]
            ])
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
}
