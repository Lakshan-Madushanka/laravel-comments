<?php

namespace LakM\Comments;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\RequiredIf;
use function Laravel\Prompts\confirm;

class ValidationRules
{
    public static array $rules = [];
    public static $validateUsing;

    public static function set(Model $model)
    {

        self::$rules = [
            'guest_email' => [
                new RequiredIf($model->guestModeEnabled() && config('comments.guest_mode.email_enabled')),
                'nullable',
                'email'
            ],
            'guest_name' => [new RequiredIf($model->guestModeEnabled())],
            'text' => ['required'],
        ];
    }

    public static function setRulesUsing(callable $setRules)
    {
        self::$validateUsing = $setRules;
    }

    public static function get(Model $model): array
    {
        if (! isset(self::$validateUsing)) {
            self::set($model);
            return self::$rules;
        }

        return self::$rules = call_user_func(self::$validateUsing, $model);
    }
}
