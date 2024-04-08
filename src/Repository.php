<?php

namespace LakM\Comments;

use Illuminate\Database\Eloquent\Builder;
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

    public static function allRelatedCommentsForAuthUser(Model $relatedModel, int $limit)
    {
        return $relatedModel
            ->comments()
            ->when(! $relatedModel->guestModeEnabled(),  fn(Builder $query) => $query->with('commenter'))
            ->latest()
            ->when(
                config('comments.pagination.enabled'),
                fn(Builder $query) => $query->paginate($limit),
                fn(Builder $query) => $query->get()
            );
    }

    public static function getTotalCommentsCountForRelated(Model $relatedModel)
    {
        return $relatedModel->comments()->count();
    }
}
