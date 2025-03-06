<?php

namespace LakM\Comments;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use LakM\Comments\Abstracts\AbstractQueries;
use LakM\Comments\Builders\MessageBuilder;
use LakM\Comments\Builders\ReactionBuilder;
use LakM\Comments\Contracts\CommentableContract;
use LakM\Comments\Contracts\CommenterContract;
use LakM\Comments\Data\UserData;
use LakM\Comments\Enums\Sort;
use LakM\Comments\ModelResolver as M;
use LakM\Comments\Models\Comment;
use LakM\Comments\Models\Guest;
use LakM\Comments\Models\Reaction;
use LakM\Comments\Models\Reply;

class Queries extends AbstractQueries
{
    public static function guestCommentCount(Model $relatedModel): int
    {
        return M::commentQuery()
            ->currentGuest()
            ->count();
    }

    /**
     * @param Authenticatable&CommenterContract $user
     * @param Model&CommentableContract $relatedModel
     * @return int
     */
    public static function userCommentCount(Authenticatable $user, Model $relatedModel): int
    {
        $alias = $relatedModel->getMorphClass();

        return $user
            ->comments()
            ->currentUser($user)
            ->count();
    }

    /**
     * @param Model&CommentableContract $relatedModel
     * @param int|null $limit
     * @param Sort $sortBy
     * @param string $filter
     * @return Builder
     */
    public static function allRelatedCommentsQuery(
        Model $relatedModel,
        ?int $limit,
        Sort $sortBy,
        string $filter = ''
    ): Builder {
        /** @var MessageBuilder<Comment> $commentQuery */
        $commentQuery = $relatedModel->comments();

        return $commentQuery
            ->currentUserFilter($relatedModel, $filter)
            ->withOwnerReactions($relatedModel)
            ->with('commenter')
            ->withCount(self::addCount())
            ->withCount([
                'replies' => function (MessageBuilder $query) {
                    $query->when(
                        config('comments.reply.approval_required'),
                        fn (MessageBuilder $query) => $query->approved()
                    );
                },
            ])
            ->checkApproval($relatedModel)
            ->when(
                $sortBy === Sort::LATEST,
                fn (Builder $query) => $query->latest()
            )
            ->when($sortBy === Sort::OLDEST, function (Builder $query) {
                return $query->oldest();
            })
            ->when($sortBy === Sort::REPLIES, function (Builder $query) {
                return $query->orderByDesc('replies_count');
            })
            ->when($sortBy === Sort::TOP, function (Builder $query) {
                // @phpstan-ignore-next-line
                return $query
                    ->addScore()
                    ->orderByDesc("score");
            })
            ->clone();
    }

    /**
     * @param Model&CommentableContract $relatedModel
     * @param int|null $limit
     * @param Sort $sortBy
     * @param string $filter
     * @return LengthAwarePaginator|Collection
     */
    public static function allRelatedComments(
        Model $relatedModel,
        ?int $limit,
        Sort $sortBy,
        string $filter = ''
    ): LengthAwarePaginator|Collection {
        /** @var MessageBuilder<Comment> $commentQuery */
        $commentQuery = $relatedModel->comments();

        return static::allRelatedCommentsQuery($relatedModel, $limit, $sortBy, $filter)
            ->when(
                $relatedModel->paginationEnabled(),
                fn (Builder $query) => $query->paginate($limit),
                fn (Builder $query) => $query->get()
            );
    }

    /**
     * @param Model&CommentableContract $relatedModel
     * @param int|null $limit
     * @param Sort $sortBy
     * @param string $filter
     * @return Collection
     */
    public static function relatedComment(
        Model $relatedModel,
        mixed $commentId,
        ?int $limit,
        Sort $sortBy,
        string $filter = ''
    ): Collection {
        /** @var MessageBuilder<Comment> $commentQuery */
        $commentQuery = $relatedModel->comments();

        return static::allRelatedCommentsQuery($relatedModel, $limit, $sortBy, $filter)
            ->whereId($commentId)
            ->get();
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
     * @param Reply|Comment $comment
     * @param string $reactionType
     * @param int $limit
     * @param bool $authMode
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
            ->with('owner')
            ->limit($limit)
            ->get();

        return $reactions->map(function (Reaction $reaction) use ($authMode) {
            return new UserData(name: $reaction->ownerName($authMode), photo: $reaction->ownerPhotoUrl());
        });
    }

    public static function lastReactedUser(Reply|Comment $comment, string $reactionType, bool $authMode): ?UserData
    {
        /** @var ReactionBuilder<Reaction> $reactionQuery */
        $reactionQuery = $comment->reactions();

        $reaction = $reactionQuery
            ->whereType($reactionType)
            ->with('owner')
            ->latest()
            ->first();

        if ($reaction) {
            return new UserData(name: $reaction->ownerName($authMode), photo: $reaction->ownerPhotoUrl());
        }

        return $reaction;
    }

    public static function userReplyCountForComment(Comment $comment, bool $guestMode, ?Authenticatable $user): int
    {
        /** @var MessageBuilder<Reply> $replyQuery */
        $replyQuery = $comment->replies();

        return $replyQuery
            ->when(
                !$guestMode,
                function (MessageBuilder $query) use ($user) {
                    $query->currentUser($user);
                },
                function (MessageBuilder $query) {
                    $query->currentGuest();
                }
            )
            ->count();
    }

    /**
     * @param Comment $comment
     * @param Model&CommentableContract $relatedModel
     * @param bool $approvalRequired
     * @param int $limit
     * @param Sort $sortBy
     * @param string $filter
     * @return LengthAwarePaginator|Collection
     */
    public static function commentReplies(
        Comment $comment,
        Model $relatedModel,
        bool $approvalRequired,
        ?int $limit,
        Sort $sortBy,
        string $filter = ''
    ): LengthAwarePaginator|Collection {
        /** @var MessageBuilder<Reply> $replyQuery */
        $replyQuery = $comment->replies();

        return $replyQuery
            ->currentUserFilter($relatedModel, $filter)
            ->with('commenter')
            ->withOwnerReactions($relatedModel)
            ->when(!$relatedModel->guestModeEnabled(), fn (MessageBuilder $query) => $query->with('commenter'))
            ->when($approvalRequired, fn (MessageBuilder $query) => $query->approved())
            ->when($sortBy === Sort::LATEST, function (Builder $query) {
                return $query->latest();
            })
            ->when($sortBy === Sort::OLDEST, function (Builder $query) {
                return $query->oldest();
            })
            ->withCount(self::addCount())
            ->latest()
            ->when(
                config('comments.reply.pagination.enabled'),
                fn (Builder $query) => $query->paginate($limit),
                fn (Builder $query) => $query->get()
            );
    }

    public static function usersStartWithName(string $name, bool $guestMode, int $limit): \Illuminate\Support\Collection
    {
        if ($guestMode) {
            return M::guestQuery()
                ->where('name', 'like', "{$name}%")
                ->limit($limit)
                ->get()
                // @phpstan-ignore-next-line
                ->transform(function (Guest $guest) {
                    return new UserData(name: $guest->name, photo: $guest->ownerPhotoUrl());
                });
        }

        return M::userQuery()
            ->where('name', 'like', "{$name}%")
            ->limit($limit)
            ->get()
            ->transform(
                /**
                 * @param User&CommenterContract $user
                 * @return UserData
                 * @phpstan-ignore-next-line
                 */
                function (User $user) {
                    // @phpstan-ignore-next-line
                    return new UserData(name: $user->name(), photo: $user->photoUrl());
                }
            );
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

        $guest = M::guestQuery()
            ->whereNotNull('name')
            ->where('ip_address', request()->ip())
            ->first();

        return self::$guest = new UserData(name: $guest->name ?? '', email: $guest->email ?? '');
    }
}
