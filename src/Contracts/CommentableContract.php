<?php

namespace LakM\Comments\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use LakM\Comments\Enums\Sort;
use LakM\Comments\Models\Comment;

interface CommentableContract
{
    /**
     * @return MorphMany<Comment>
     */
    public function comments(): MorphMany;

    public function authCheck(): bool;

    public function getAuthGuard(): string;

    public function canCreateComment(?Authenticatable $user = null): bool;

    public function guestModeEnabled(): bool;

    public function limitExceeded(?Authenticatable $user = null): bool;

    public function paginationEnabled(): bool;

    public function checkLimitForGuest(int $limit): bool;

    public function checkLimitForAuthUser(Authenticatable $user, int $limit): bool;

    public function getCommentLimit(): ?int;

    public function approvalRequired(): bool;

    public function getAuthUser(): ?Authenticatable;

    public function getCommentsSortOrder(): Sort;

    public function getRepliesSortOrder(): Sort;

    public function canEditComment(Comment $comment): bool;

    public function canDeleteComment(Comment $comment): bool;
}
