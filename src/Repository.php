<?php

namespace LakM\Comments;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LakM\Comments\Data\UserData;
use LakM\Comments\Models\Comment;
use LakM\Comments\Models\Reaction;
use LakM\Comments\Models\Reply;

class Repository
{
    public static ?UserData $guest = null;

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

    public static function allRelatedComments(Model $relatedModel, int $limit, string $sortBy, string $filter = '')
    {
        $userModelName = config('comments.user_model');
        $alias = (new $userModelName())->getMorphClass();

        return $relatedModel
            ->comments()
            ->when(
                $filter === 'my_comments' && $relatedModel->guestModeEnabled(),
                fn (Builder $query) => $query->where('ip_address', request()->ip())
            )
            ->when(
                $filter === 'my_comments' && !$relatedModel->guestModeEnabled(),
                fn (Builder $query) => $query
                    ->where('commenter_type', $alias)
                    ->where('commenter_type', $relatedModel->getAuthUser()->getAuthIdentifier())
            )
            ->withOwnerReactions($relatedModel)
            ->withCommenter($relatedModel)
            ->withCount(self::addCount())
            ->withCount([
                'replies' => function (Builder $query) {
                    $query->when(
                        config('comments.reply.approval_required'),
                        fn (Builder $query) => $query->approved()
                    );
                },
            ])
            ->checkApproval($relatedModel)
            ->when($sortBy === 'latest', function (Builder $query) {
                return $query->latest();
            })
            ->when($sortBy === 'oldest', function (Builder $query) {
                return $query->oldest();
            })
            ->when($sortBy === 'replies', function (Builder $query) {
                return $query->orderByDesc('replies_count');
            })
            ->when($sortBy === 'top', function (Builder $query) {
                return $query->withCount([
                    'reactions',
                    'replyReactions',
                    'replyReactions as reply_reactons_dislikes_count' => function (Builder $query) {
                        $query->where('type', 'dislike');
                    },
                ])
                    ->orderByDesc(DB::raw('reactions_count - (dislikes_count * 2) + (replies_count * 2) + reply_reactions_count - (reply_reactons_dislikes_count * 2)'));
            })
            ->when(
                $relatedModel->paginationEnabled(),
                fn (Builder $query) => $query->paginate($limit),
                fn (Builder $query) => $query->get()
            );
    }

    public static function getTotalCommentsCountForRelated(Model $relatedModel)
    {
        return $relatedModel
            ->comments()
            ->checkApproval($relatedModel)
            ->count();
    }

    public static function addCount()
    {
        $count = [];

        foreach (array_keys(config('comments.reactions')) as $reaction) {
            $name = Str::plural($reaction);
            $key = "reactions as {$name}_count";
            $count[$key] = function (Builder $query) use ($reaction) {
                return $query->whereType($reaction);
            };
        }
        return $count;
    }

    public static function reactedUsers(Reply|Comment $comment, string $reactionType, int $limit, bool $authMode)
    {
        $reactions = $comment
            ->reactions()
            ->whereType($reactionType)
            ->when(
                $authMode,
                function (Builder $query) {
                    $query->with('user');
                }
            )
            ->limit($limit)
            ->get();

        return $reactions->map(function (Reaction $reaction) use ($authMode) {
            return new UserData(name: $reaction->user?->name, photo: $reaction->ownerPhotoUrl($authMode));
        });
    }

    public static function lastReactedUser(Reply|Comment $comment, string $reactionType, bool $authMode): ?UserData
    {
        $reaction = $comment
            ->reactions()
            ->whereType($reactionType)
            ->when(
                $authMode,
                function (Builder $query) {
                    $query->with('user');
                }
            )
            ->latest()
            ->first();

        if ($reaction) {
            return new UserData(name: $reaction->user?->name, photo: $reaction->ownerPhotoUrl($authMode));
        }

        return $reaction;
    }

    public static function userReplyCountForComment(Comment $comment, bool $guestMode, ?Authenticatable $user): int
    {
        return $comment
            ->replies()
            ->when(!$guestMode, function (Builder $query) use ($user) {
                $query->where('commenter_type', $user->getMorphClass())
                    ->where('commenter_id', $user->getAuthIdentifier());
            }, function (Builder $query) {
                $query->where('ip_address', request()->ip());
            })
            ->count();
    }

    public static function getCommentReplyCount(Comment $comment)
    {
        return $comment->replies()->count();
    }

    public static function commentReplies(Comment $comment, Model $relatedModel, bool $approvalRequired, int $limit)
    {
        return $comment
            ->replies()
            ->with(['reactions'])
            ->withCount(self::addCount())
            ->when(!$relatedModel->guestModeEnabled(), fn (Builder $query) => $query->with('commenter'))
            ->latest()
            ->when($approvalRequired, fn (Builder $query) => $query->approved())
            ->when(
                config('comments.reply.pagination.enabled'),
                fn (Builder $query) => $query->paginate($limit),
                fn (Builder $query) => $query->get()
            );
    }

    public static function usersStartWithName(string $name, bool $guestMode, int $limit): \Illuminate\Support\Collection
    {
        if ($guestMode) {
            return Comment::query()
                ->where('guest_name', 'like', "{$name}%")
                ->limit($limit)
                ->get()
                ->map(function (Comment $comment) use ($guestMode) {
                    return new UserData(name: $comment->guest_name, photo: $comment->ownerPhotoUrl(!$guestMode));
                });
        }

        return config('comments.user_model')::query()
            ->where('name', 'like', "{$name}%")
            ->limit($limit)
            ->get()
            ->map(function ($user) {
                return new UserData(name: $user->name(true), photo: $user->photoUrl(true));
            });
    }

    public static function usersCount()
    {
        return config('comments.user_model')::query()->count();
    }

    public static function commentsCountOf(Model $model)
    {
        $alias = $model->getMorphClass();

        return Comment::query()->where('commentable_type', $alias)->count();
    }

    public static function commentsOfModelType(Model $model)
    {
        $alias = $model->getMorphClass();

        return Comment::query()
            ->selectRaw('*, count(1) as comments_count')
            ->where('commentable_type', $alias)
            ->groupBy('commentable_type');
    }

    public static function commentsOf(Model $model)
    {
        return $model
            ->comments()
            ->withCount([
                'replies',
                'reactions',
                'replyReactions',
                'replyReactions as reply_reactons_dislikes_count' => function (Builder $query) {
                    $query->where('type', 'dislike');
                },
            ])
            ->withCount(self::addCount());
    }

    public static function repliesOf(Comment $comment)
    {
        return $comment
            ->replies()
            ->withCount([
                'reactions',
            ])
            ->withCount(self::addCount());
    }

    public static function guest(): UserData
    {
        if (!is_null(self::$guest)) {
            return self::$guest;
        }

        $comment = Comment::query()
            ->whereNotNull('guest_name')
            ->where('ip_address', request()->ip())
            ->first();

        return self::$guest = new UserData(name: $comment->guest_name ?? '', email: $comment->guest_email ?? '');
    }
}
