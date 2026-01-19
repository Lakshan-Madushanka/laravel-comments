<?php

namespace LakM\Commenter;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use LakM\Commenter\Abstracts\AbstractQueries;
use LakM\Commenter\Builders\MessageBuilder;
use LakM\Commenter\Builders\ReactionBuilder;
use LakM\Commenter\Contracts\CommentableContract;
use LakM\Commenter\Contracts\CommenterContract;
use LakM\Commenter\Data\UserData;
use LakM\Commenter\Enums\Sort;
use LakM\Commenter\ModelResolver as M;
use LakM\Commenter\Models\Comment;
use LakM\Commenter\Models\Guest;
use LakM\Commenter\Models\Message;
use LakM\Commenter\Models\Reaction;
use LakM\Commenter\Models\Reply;

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
     * @param MessageBuilder $query
     * @param Model&CommentableContract $relatedModel
     * @param Sort $sortBy
     * @param string $filter
     * @return MessageBuilder<Comment>
     */
    public static function applyFilters(
        MessageBuilder $query,
        Model $relatedModel,
        Sort $sortBy,
        string $filter
    ): MessageBuilder {
        return $query->currentUserFilter($relatedModel, $filter)
            ->withOwnerReactions($relatedModel)
            ->with('commenter')
            ->withCount(self::addCount())
            ->repliesCount()
            ->checkApproval($relatedModel)
            ->when(Helpers::isModernTheme(), fn ( $query) => $query->addScore())
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
                    ->orderByDesc("score");
            });
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
        $commentQuery = $relatedModel->comments()->getQuery();

        return static::applyFilters($commentQuery, $relatedModel, $sortBy, $filter)
            ->when(
                $relatedModel->paginationEnabled(),
                fn (Builder $query) => $query->paginate($limit),
                fn (Builder $query) => $query->get()
            );
    }

    /**
     * @param Model&CommentableContract $relatedModel
     * @param mixed $commentId
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
        $commentQuery = ModelResolver::commentQuery()->where('id', $commentId);

        return self::applyFilters($commentQuery, $relatedModel, $sortBy, $filter)
            ->get();
    }

    /**
     * @param Model&CommentableContract $relatedModel
     * @return Message|null
     */
    public static function pinnedMsg(
        Model $relatedModel,
    ): ?Message {
        /** @var MessageBuilder<Comment> $commentQuery */
        $commentQuery = $relatedModel->comments()->getQuery();
        $commentQuery  = $commentQuery->isPinned();

        $pinnedComment = self::applyFilters($commentQuery, $relatedModel, Sort::TOP, '')
            ->first();

        if ($pinnedComment) {
            return $pinnedComment;
        }

        /** @var MessageBuilder<Comment> $commentQuery */
        $pinnedComment = $relatedModel->comments()->with('replies', function (MorphMany $query) {
            $query->where('is_pinned', true);
        })->first();

        if ($pinnedComment) {
            $reply = $pinnedComment['replies']->first();

            if ($reply) {
                $replyQuery = ModelResolver::replyQuery()->where('id', $reply->getKey())->with('comment');

                return self::applyFilters($replyQuery, $relatedModel, Sort::LATEST, '')
                    ->first();
            }
        }

        return null;
    }

    public static function addCount(): array
    {
        $count = [];

        foreach (array_keys(config('commenter.reactions')) as $reaction) {
            $name = Str::plural($reaction);
            $key = "reactions as {$name}_count";
            $count[$key] = function (Builder $query) use ($reaction) {
                return $query->whereType($reaction);
            };
        }
        return $count;
    }

    /**
     * @param Message $message
     * @param string $reactionType
     * @param int $limit
     * @param bool $authMode
     * @return \Illuminate\Support\Collection<int, UserData>
     */
    public static function reactedUsers(
        Message $message,
        string $reactionType,
        int $limit,
        bool $authMode
    ): \Illuminate\Support\Collection {
        /** @var ReactionBuilder<Reaction> $reactionQuery */
        $reactionQuery = $message->reactions();

        $reactions = $reactionQuery
            ->whereType($reactionType)
            ->with('owner')
            ->limit($limit)
            ->get();

        return $reactions->map(function (Reaction $reaction) use ($authMode) {
            return new UserData(name: $reaction->ownerName($authMode), photo: $reaction->ownerPhotoUrl());
        });
    }

    public static function lastReactedUser(Message $message, string $reactionType, bool $authMode): ?UserData
    {
        /** @var ReactionBuilder<Reaction> $reactionQuery */
        $reactionQuery = $message->reactions();

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

    public static function userReplyCountForMessage(Message $message, bool $guestMode, ?Authenticatable $user): int
    {
        /** @var MessageBuilder<Reply> $replyQuery */
        $replyQuery = $message->replies();

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
     * @param Message $message
     * @param Model&CommentableContract $relatedModel
     * @param bool $approvalRequired
     * @param int|null $limit
     * @param Sort $sortBy
     * @param string $filter
     * @return LengthAwarePaginator|Collection
     */
    public static function commentReplies(
        Message $message,
        Model $relatedModel,
        bool $approvalRequired,
        ?int $limit,
        Sort $sortBy,
        string $filter = ''
    ): LengthAwarePaginator|Collection {
        /** @var MessageBuilder<Reply> $replyQuery */
        $replyQuery = $message->replies();

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
            ->repliesCount()
            ->latest()
            ->when(
                config('commenter.reply.pagination.enabled'),
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
        return config('commenter.user_model')::query()->count();
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
