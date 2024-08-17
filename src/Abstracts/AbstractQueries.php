<?php

namespace LakM\Comments\Abstracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use LakM\Comments\Contracts\CommentableContract;
use LakM\Comments\Contracts\CommenterContract;
use LakM\Comments\Data\UserData;
use LakM\Comments\Models\Comment;
use LakM\Comments\Models\Reply;

abstract class AbstractQueries
{
    public static ?UserData $guest = null;

    public abstract static function guestCommentCount(Model $relatedModel): int;

    /**
     * @param  Authenticatable&CommenterContract  $user
     * @param  Model&CommentableContract  $relatedModel
     * @return int
     */
    public abstract static function userCommentCount(Authenticatable $user, Model $relatedModel): int;

    /**
     * @param  Model&CommentableContract  $relatedModel
     * @param  int  $limit
     * @param  string  $sortBy
     * @param  string  $filter
     * @return LengthAwarePaginator|Collection
     */
    public abstract static function allRelatedComments(
        Model $relatedModel,
        int $limit,
        string $sortBy,
        string $filter = ''
    ): LengthAwarePaginator|Collection;

    /**
     * @param  Model&CommentableContract  $relatedModel
     * @param  string  $filter
     * @return int
     */
    public abstract static function getTotalCommentsCountForRelated(Model $relatedModel, string $filter = ''): int;

    public abstract static function addCount(): array;

    /**
     * @param  Reply|Comment  $comment
     * @param  string  $reactionType
     * @param  int  $limit
     * @param  bool  $authMode
     * @return \Illuminate\Support\Collection<int, UserData>
     */
    public abstract static function reactedUsers(
        Reply|Comment $comment,
        string $reactionType,
        int $limit,
        bool $authMode
    ): \Illuminate\Support\Collection;

    public abstract static function lastReactedUser(Reply|Comment $comment, string $reactionType, bool $authMode): ?UserData;

    public abstract static function userReplyCountForComment(Comment $comment, bool $guestMode, ?Authenticatable $user): int;

    /**
     * @param  Comment  $comment
     * @param  Model&CommentableContract  $relatedModel
     * @param  bool  $approvalRequired
     * @param  string  $filter
     * @return int
     */
    public abstract static function getCommentReplyCount(
        Comment $comment,
        Model $relatedModel,
        bool $approvalRequired,
        string $filter = ''
    ): int;

    /**
     * @param  Comment  $comment
     * @param  Model&CommentableContract  $relatedModel
     * @param  bool  $approvalRequired
     * @param  int  $limit
     * @param  string  $sortBy
     * @param  string  $filter
     * @return LengthAwarePaginator|Collection
     */
    public abstract static function commentReplies(
        Comment $comment,
        Model $relatedModel,
        bool $approvalRequired,
        int $limit,
        string $sortBy = '',
        string $filter = ''
    ): LengthAwarePaginator|Collection;

    public abstract static function usersStartWithName(string $name, bool $guestMode, int $limit): \Illuminate\Support\Collection;

    public abstract static function usersCount(): int;


    public abstract static function guest(): UserData;

}
