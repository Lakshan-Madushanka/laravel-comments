<?php

namespace LakM\Comments\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use LakM\Comments\Contracts\CommentableContract;
use LakM\Comments\ModelResolver as M;
use LakM\Comments\Models\Comment;
use LakM\Comments\Models\Guest;
use LakM\Comments\Models\Reply;

/**
 * @template TModelClass of Comment|Reply
 * @extends Builder<Comment|Reply>
 * @method MessageBuilder whereApproved(bool $value)
 * @method MessageBuilder whereType(string $value)
 */
class MessageBuilder extends Builder
{
    /** @return MessageBuilder<Comment|Reply> */
    public function approved(): self
    {
        return $this->whereApproved(true);
    }

    /**
     * @param Model&CommentableContract $relatedModel
     * @return MessageBuilder<Comment|Reply>
     */
    public function checkApproval(Model $relatedModel): self
    {
        return $this->when($relatedModel->approvalRequired(), fn (MessageBuilder $query) => $query->approved());
    }

    /**
     * @param Model&CommentableContract $relatedModel
     * @return MessageBuilder<Comment|Reply>
     *
     */
    public function withOwnerReactions(Model $relatedModel): self
    {
        return $this->with(['ownerReactions' => fn ($query) => $query->checkMode(!$relatedModel->guestModeEnabled())]);
    }

    /**
     * @param Model&CommentableContract $relatedModel
     * @param string $filter
     * @return MessageBuilder<Comment|Reply>
     */
    public function currentUserFilter(Model $relatedModel, string $filter): self
    {
        return $this->when(
            $filter === 'own' && $relatedModel->guestModeEnabled(),
            fn (MessageBuilder $query) => $query->currentGuest()
        )
            ->when(
                $filter === 'own' && !$relatedModel->guestModeEnabled(),
                fn (MessageBuilder $query) => $query->currentUser($relatedModel->getAuthUser())
            );
    }

    public function guest(): self
    {
        return $this
            ->whereHasMorph('commenter', Guest::class);
    }

    public function currentGuest(): self
    {
        return $this->whereHasMorph(
            'commenter',
            M::guestModel()->getMorphClass(),
            fn (Builder $query) => $query->where('ip_address', request()->ip())
        );
    }

    /**
     * @param User $user
     * @return MessageBuilder
     */
    public function currentUser(User $user): self
    {
        return $this->whereHasMorph(
            'commenter',
            M::userModel()->getMorphClass(),
            fn (Builder $query) => $query->where('commenter_id', $user->getAuthIdentifier())
        );
    }
}
