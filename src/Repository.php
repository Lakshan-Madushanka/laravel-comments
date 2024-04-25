<?php

namespace LakM\Comments;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use LakM\Comments\Data\UserData;
use LakM\Comments\Models\Comment;
use LakM\Comments\Models\Reaction;

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

    public static function allRelatedComments(Model $relatedModel, int $limit)
    {
        return $relatedModel
            ->comments()
            ->with(['reactions'])
            ->withCount(self::addCount())
            ->when(! $relatedModel->guestModeEnabled(),  fn(Builder $query) => $query->with('commenter'))
            ->latest()
            ->when($relatedModel->approvalRequired(), fn(Builder $query) => $query->approved())
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

    public static function addCount()
    {

        $count = [];

        foreach (array_keys(config('comments.reactions')) as $reaction) {
            $name = Str::plural($reaction);
            $key = "reactions as {$name}_count";
            $count[$key] = function(Builder $query) use ($reaction){
                return $query->whereType($reaction);
            };

        }
        return $count;
    }

    public static function reactedUsers(Comment $comment, string $reactionType, int $limit)
    {
        $reactions =  $comment
            ->reactions()
            ->whereType($reactionType)
            ->when(
                Auth::check(),
                function (Builder $query) {
                    $query->with('user');
                }
            )
            ->limit($limit)
            ->get();

        return $reactions->map(function (Reaction $reaction) {
            return new UserData(name: $reaction->user?->name, photo: $reaction->owner_photo_url);
        });
    }

    public static function lastReactedUser(Comment $comment, string $reactionType): ?UserData
    {
        $reaction = $comment
            ->reactions()
            ->whereType($reactionType)
            ->when(
                Auth::check(),
                function (Builder $query) {
                    $query->with('user');
                }
            )
            ->latest()
            ->first();

        if ($reaction) {
            return new UserData(name: $reaction->user?->name, photo: $reaction->owner_photo_url);
        }

        return $reaction;
    }
}
