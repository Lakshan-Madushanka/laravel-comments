<?php

namespace LakM\Comments\Concerns;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use LakM\Comments\Exceptions\CommentLimitExceeded;
use LakM\Comments\Models\Comment;
use LakM\Comments\Repository;

/**
 * @mixin Model
 */
trait Commentable
{
    public function comments(): MorphMany
    {
        return $this->morphMany(config('comments.model'), 'commentable');
    }

    public function authCheck(): bool
    {
        return Auth::guard($this->getAuthGuard())->check();
    }

    public function getAuthGuard()
    {
        if (config('comments.auth_guard') === 'default') {
            return Auth::getDefaultDriver();
        }

        return config('comments.auth_guard');
    }

    /**
     * @throws \Throwable
     */
    public function canCreateComment(Authenticatable $user = null): bool
    {
        if (method_exists($this, 'commentCanCreate')) {
            return $this->commentCanCreate($user);
        }

        throw_if(
            $this->limitExceeded($user),
            CommentLimitExceeded::make($this, $this->getCommentLimit())
        );

        if ($this->guestModeEnabled()) {
            return true;
        }

        throw_unless(
            $this->authCheck(),
            new AuthenticationException(guards: [$this->getAuthGuard()], redirectTo: config('login_route'))
        );

        return Gate::allows('create-comment');
    }

    public function guestModeEnabled(): bool
    {
        if (property_exists($this, 'guestMode')) {
            return $this->guestMode;
        }

        if (config('comments.guest_mode.enabled')) {

            return true;
        }

        return false;
    }

    public function limitExceeded(Model $user = null): bool
    {
        $limit = $this->getCommentLimit();

        if (is_null($limit)) {
            return false;
        }

        if (is_null($user)) {
            return $this->checkLimitForGuest($limit);
        }

        return $this->checkLimitForAuthUser($user, $limit);
    }

    public function paginationEnabled(): bool
    {
        return config('comments.pagination.enabled');
    }

    public function checkLimitForGuest(int $limit): bool
    {
        return Repository::guestCommentCount($this) >= $limit;
    }

    public function checkLimitForAuthUser(Model $user, int $limit): bool
    {
        return Repository::userCommentCount($user, $this) >= $limit;
    }

    public function getCommentLimit(): ?int
    {
        $limit = config('comments.limit');

        if (property_exists($this, 'commentLimit')) {
            $limit =  $this->commentLimit;
        }

        return $limit;
    }

    public function approvalRequired(): bool
    {
        if (property_exists($this, 'approvalRequired')) {
            return $this->approvalRequired;
        }

        return config('comments.approval_required');
    }

    public function getAuthUser(): ?Authenticatable
    {
        return Auth::guard($this->getAuthGuard())->user();
    }

    public function canEditComment(Comment $comment): bool
    {
        if (method_exists($this, 'commentCanEdit')) {
            return $this->commentCanEdit($comment);
        }

        return Gate::allows('update-comment',  [$comment,  $this->guestModeEnabled()]);
    }

    public function canDeleteComment(Comment $comment): bool
    {
        if (method_exists($this, 'commentCanEdit')) {
            return $this->commentCanEdit($comment);
        }

        return Gate::allows('delete-comment', [$comment, $this->guestModeEnabled()]);
    }
}
