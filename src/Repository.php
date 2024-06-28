<?php

namespace LakM\Comments;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LakM\Comments\Builders\CommentBuilder;
use LakM\Comments\Builders\ReactionBuilder;
use LakM\Comments\Builders\ReplyBuilder;
use LakM\Comments\Contracts\CommentableContract;
use LakM\Comments\Contracts\CommenterContract;
use LakM\Comments\Data\UserData;
use LakM\Comments\Models\Comment;
use LakM\Comments\Models\Reaction;
use LakM\Comments\Models\Reply;
use LakM\Comments\Model as M;

class Repository
{
    public static ?UserData $guest = null;

    public static function guestCommentCount(Model $relatedModel): int
    {
        $alias = $relatedModel->getMorphClass();

        return M::commentQuery()->where('commentable_type', $alias)
            ->where('commentable_id', $relatedModel->getKey())
            ->where('ip_address', request()->ip())
            ->count();
    }

    /**
     * @param  Authenticatable&CommenterContract  $user
     * @param  Model&CommentableContract  $relatedModel
     * @return int
     */
    public static function userCommentCount(Authenticatable $user, Model $relatedModel): int
    {
        $alias = $relatedModel->getMorphClass();

        return $user
            ->comments()
            ->where('commentable_type', $alias)
            ->where('commentable_id', $relatedModel->getKey())
            ->count();
    }

    /**
     * @param  Model&CommentableContract  $relatedModel
     * @param  int  $limit
     * @param  string  $sortBy
     * @param  string  $filter
     * @return LengthAwarePaginator|Collection
     */
    public static function allRelatedComments(
        Model $relatedModel,
        int $limit,
        string $sortBy,
        string $filter = ''
    ): LengthAwarePaginator|Collection {
        /** @var CommentBuilder<Comment> $commentQuery */
        $commentQuery = $relatedModel->comments();

        return $commentQuery
            ->currentUser($relatedModel, $filter)
            ->withOwnerReactions($relatedModel)
            ->withCommenter($relatedModel)
            ->withCount(self::addCount())
            ->withCount([
                'replies' => function (ReplyBuilder $query) {
                    $query->when(
                        config('comments.reply.approval_required'),
                        fn(ReplyBuilder $query) => $query->approved()
                    );
                },
            ])
            ->checkApproval($relatedModel)
            ->when(
                $sortBy === 'latest',
                fn(Builder $query) => $query->latest()
            )
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
                    'replyReactions as reply_reactions_dislikes_count' => function (Builder $query) {
                        $query->where('type', 'dislike');
                    },
                ])
                    ->orderByDesc(DB::raw('reactions_count - (dislikes_count * 2) + (replies_count * 2) + reply_reactions_count - (reply_reactions_dislikes_count * 2)'));
            })
            ->when(
                $relatedModel->paginationEnabled(),
                fn(Builder $query) => $query->paginate($limit),
                fn(Builder $query) => $query->get()
            );
    }

    /**
     * @param  Model&CommentableContract  $relatedModel
     * @param  string  $filter
     * @return int
     */
    public static function getTotalCommentsCountForRelated(Model $relatedModel, string $filter = ''): int
    {
        return $relatedModel
            ->comments()
            ->currentUser($relatedModel, $filter)
            ->checkApproval($relatedModel)
            ->count();
    }

    public static function addCount(): array
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

    /**
     * @param  Reply|Comment  $comment
     * @param  string  $reactionType
     * @param  int  $limit
     * @param  bool  $authMode
     * @return \Illuminate\Support\Collection<int, UserData>
     */
    public static function reactedUsers(
        Reply|Comment $comment,
        string $reactionType,
        int $limit,
        bool $authMode
    ): \Illuminate\Support\Collection {
        /** @var ReactionBuilder<Reaction> $reactionQuery */
        $reactionQuery = $comment->reactions();

        $reactions = $reactionQuery
            ->whereType($reactionType)
            ->when(
                $authMode,
                function (ReactionBuilder $query) {
                    $query->with('user');
                }
            )
            ->limit($limit)
            ->get();

        return $reactions->map(function (Reaction $reaction) use ($authMode) {
            return new UserData(name: $reaction->user?->name ?? '', photo: $reaction->ownerPhotoUrl($authMode));
        });
    }

    public static function lastReactedUser(Reply|Comment $comment, string $reactionType, bool $authMode): ?UserData
    {
        /** @var ReactionBuilder<Reaction> $reactionQuery */
        $reactionQuery = $comment->reactions();

        $reaction = $reactionQuery
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
            return new UserData(name: $reaction->user?->name ?? '', photo: $reaction->ownerPhotoUrl($authMode));
        }

        return $reaction;
    }

    public static function userReplyCountForComment(Comment $comment, bool $guestMode, ?Authenticatable $user): int
    {
        /** @var ReplyBuilder<Reply> $replyQuery */
        $replyQuery = $comment->replies();

        return $replyQuery
                ->when(
                    !$guestMode,
                    function (ReplyBuilder $query) use ($user) {
                        $query->where('commenter_type', $user->getMorphClass())
                            ->where('commenter_id', $user->getAuthIdentifier());
                    },
                    function (ReplyBuilder $query) {
                        $query->where('ip_address', request()->ip());
                    })
                ->count();
    }

    /**
     * @param  Comment  $comment
     * @param  Model&CommentableContract  $relatedModel
     * @param  bool  $approvalRequired
     * @param  string  $filter
     * @return int
     */
    public static function getCommentReplyCount(
        Comment $comment,
        Model $relatedModel,
        bool $approvalRequired,
        string $filter = ''
    ): int {
        /** @var ReplyBuilder<Reply> $replyQuery */
        $replyQuery = $comment->replies();

        return $replyQuery
            ->currentUser($relatedModel, $filter)
            ->when($approvalRequired, fn(ReplyBuilder $query) => $query->approved())
            ->count();
    }

    /**
     * @param  Comment  $comment
     * @param  Model&CommentableContract  $relatedModel
     * @param  bool  $approvalRequired
     * @param  int  $limit
     * @param  string  $sortBy
     * @param  string  $filter
     * @return LengthAwarePaginator|Collection
     */
    public static function commentReplies(
        Comment $comment,
        Model $relatedModel,
        bool $approvalRequired,
        int $limit,
        string $sortBy = '',
        string $filter = ''
    ): LengthAwarePaginator|Collection {
        /** @var ReplyBuilder<Reply> $replyQuery */
        $replyQuery = $comment->replies();


        return $replyQuery
            ->currentUser($relatedModel, $filter)
            ->withOwnerReactions($relatedModel)
            ->when(!$relatedModel->guestModeEnabled(), fn(ReplyBuilder $query) => $query->with('commenter'))
            ->when($approvalRequired, fn(ReplyBuilder $query) => $query->approved())
            ->when($sortBy === 'latest', function (Builder $query) {
                return $query->latest();
            })
            ->when($sortBy === 'oldest', function (Builder $query) {
                return $query->oldest();
            })
            ->withCount(self::addCount())
            ->latest()
            ->when(
                config('comments.reply.pagination.enabled'),
                fn(Builder $query) => $query->paginate($limit),
                fn(Builder $query) => $query->get()
            );
    }

    public static function usersStartWithName(string $name, bool $guestMode, int $limit): \Illuminate\Support\Collection
    {
        if ($guestMode) {
            return M::commentQuery()
                ->where('guest_name', 'like', "{$name}%")
                ->limit($limit)
                ->get()
                // @phpstan-ignore-next-line
                ->transform(function (Comment $comment) {
                    return new UserData(name: $comment->guest_name, photo: $comment->ownerPhotoUrl(false));
                });
        }

        return M::userQuery()
            ->where('name', 'like', "{$name}%")
            ->limit($limit)
            ->get()
            ->transform(
                /**
                 * @param  User&CommenterContract  $user
                 * @return UserData
                 * @phpstan-ignore-next-line
                 */
                function ($user) {
                    // @phpstan-ignore-next-line
                    return new UserData(name: $user->name, photo: $user->photoUrl());
            });
    }

    public static function usersCount(): int
    {
        return config('comments.user_model')::query()->count();
    }


    public static function guest(): UserData
    {
        if (!is_null(self::$guest)) {
            return self::$guest;
        }

        $comment = M::commentQuery()
            ->whereNotNull('guest_name')
            ->where('ip_address', request()->ip())
            ->first();

        return self::$guest = new UserData(name: $comment->guest_name ?? '', email: $comment->guest_email ?? '');
    }
}
