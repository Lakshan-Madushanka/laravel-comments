<?php

namespace LakM\Commenter\Abstracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use LakM\Commenter\Contracts\CommentableContract;
use LakM\Commenter\Contracts\CommenterContract;
use LakM\Commenter\Data\UserData;
use LakM\Commenter\Enums\Sort;
use LakM\Commenter\Models\Message;

abstract class AbstractQueries
{
    public static ?UserData $guest = null;

    abstract public static function guestCommentCount(Model $relatedModel): int;

    /**
     * @param  Authenticatable&CommenterContract  $user
     * @param  Model&CommentableContract  $relatedModel
     * @return int
     */
    abstract public static function userCommentCount(Authenticatable $user, Model $relatedModel): int;

    /**
     * @param Model&CommentableContract $relatedModel
     * @param mixed $commentId
     * @param  ?int $limit
     * @param Sort $sortBy
     * @param string $filter
     * @return Collection
     */
    abstract public static function relatedComment(
        Model $relatedModel,
        mixed $commentId,
        ?int $limit,
        Sort $sortBy,
        string $filter = ''
    ): Collection;

    /**
     * @param  Model&CommentableContract  $relatedModel
     * @param  ?int  $limit
     * @param  Sort  $sortBy
     * @param  string  $filter
     * @return LengthAwarePaginator|Collection
     */
    abstract public static function allRelatedComments(
        Model $relatedModel,
        ?int $limit,
        Sort $sortBy,
        string $filter = ''
    ): LengthAwarePaginator|Collection;

    abstract public static function pinnedMsg(
        Model $relatedModel,
    ): ?Message;

    abstract public static function addCount(): array;

    /**
     * @param Message $message
     * @param string $reactionType
     * @param int $limit
     * @param bool $authMode
     * @return \Illuminate\Support\Collection<int, UserData>
     */
    abstract public static function reactedUsers(
        Message $message,
        string $reactionType,
        int $limit,
        bool $authMode
    ): \Illuminate\Support\Collection;

    abstract public static function lastReactedUser(Message $message, string $reactionType, bool $authMode): ?UserData;

    abstract public static function userReplyCountForMessage(Message $message, bool $guestMode, ?Authenticatable $user): int;

    /**
     * @param Message $message
     * @param Model&CommentableContract $relatedModel
     * @param bool $approvalRequired
     * @param int $limit
     * @param Sort $sortBy
     * @param string $filter
     * @return LengthAwarePaginator|Collection
     */
    abstract public static function commentReplies(
        Message $message,
        Model $relatedModel,
        bool $approvalRequired,
        int $limit,
        Sort $sortBy,
        string $filter = ''
    ): LengthAwarePaginator|Collection;

    abstract public static function usersStartWithName(string $name, bool $guestMode, int $limit): \Illuminate\Support\Collection;

    abstract public static function usersCount(): int;


    abstract public static function guest(): UserData;
}
