<?php

namespace LakM\Comments;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\RequiredIf;

class ValidationRules
{
    /** @var callable $createCommentUsing */
    public static $createCommentUsing;

    /** @var callable $updateCommentUsing */
    public static $updateCommentUsing;

    public static function get(Model $model, string $type): array
    {
        return match ($type) {
            'create' => self::createCommentRules($model),
            'update' => self::updateCommentRules($model),
        };
    }

    private static function createCommentRules(Model $model)
    {
        if (! isset(self::$createCommentUsing)) {
            return self::getCreateCommentRules($model);
        }

        return call_user_func(self::$createCommentUsing, $model);
    }

    private static function updateCommentRules(Model $model)
    {
        if (! isset(self::$updateCommentUsing)) {
            return self::getUpdateCommentRules();
        }

        return call_user_func(self::$updateCommentUsing, $model);
    }

    private static function getCreateCommentRules(Model $model): array
    {

        return [
            'guest_email' => [
                new RequiredIf($model->guestModeEnabled() && config('comments.guest_mode.email_enabled')),
                'nullable',
                'email'
            ],
            'guest_name' => [new RequiredIf($model->guestModeEnabled())],
            'text' => ['required'],
        ];
    }

    private static function getUpdateCommentRules(): array
    {
        return [
            'text' => ['required']
        ];
    }

    public static function createCommentUsing(callable $callable): void
    {
        self::$createCommentUsing = $callable;
    }

    public static function updateCommentUsing(callable $callable): void
    {
        self::$updateCommentUsing = $callable;
    }
}
