<?php

namespace LakM\Comments;

use Illuminate\Database\Eloquent\Model;

class Repository
{
    public static function guestCommentCount(Model $relatedModel)
    {
        $alias = $relatedModel->getMorphClass();

        return config('comments.model')::where('commentable_type', $alias)
            ->where('commentable_id', $relatedModel->getKey())
            ->where('ip_address', request()->ip())
            ->count();
    }

    public static function userCommentCount(Model $user, Model $relatedModel)
    {
        $alias = $relatedModel->getMorphClass();

        return $user
            ->comments()
            ->where('commentable_type', $alias)
            ->where('commentable_id', $relatedModel->getKey())
            ->count();
    }
}
