<?php

namespace app\services\errorManagement;

use yii\base\Model;

class ErrorMapper
{
    public static function mapErrors(Model $from, Model $to): void
    {
        foreach ($from->getErrors() as $attribute => $errors) {
            foreach ($errors as $error) {
                $to->addError($attribute, $error);
            }
        }
    }
}
