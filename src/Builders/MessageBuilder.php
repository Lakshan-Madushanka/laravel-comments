<?php

namespace LakM\Comments\Builders;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use LakM\Comments\Contracts\CommentableContract;
use LakM\Comments\Facades\SecureGuestMode;
use LakM\Comments\ModelResolver as M;
use LakM\Comments\Models\Guest;
use LakM\Comments\Models\Message;

/**
 * @template TModelClass of Message
 * @extends Builder<Message>
 * @method MessageBuilder whereApproved(bool $value)
 * @method MessageBuilder whereType(string $value)
 */
class MessageBuilder extends Builder
{
    /** @return MessageBuilder<Message> */
    public function approved(): self
    {
        return $this->whereApproved(true);
    }

    /**
     * @param Model&CommentableContract $relatedModel
     * @return MessageBuilder<Message>
     */
    public function checkApproval(Model $relatedModel): self
    {
        return $this->when($relatedModel->approvalRequired(), fn (MessageBuilder $query) => $query->approved());
    }

    /**
     * @param Model&CommentableContract $relatedModel
     * @return MessageBuilder<Message>
     *
     */
    public function withOwnerReactions(Model $relatedModel): self
    {
        return $this->with([
            'ownerReactions' => fn (/** @var ReactionBuilder $query */ $query) =>
                $query->checkMode(!$relatedModel->guestModeEnabled())
        ]);
    }

    /**
     * @param Model&CommentableContract $relatedModel
     * @param string $filter
     * @return MessageBuilder<Message>
     */
    public function currentUserFilter(Model $relatedModel, string $filter): self
    {
        return $this->when(
            $filter === 'own' && $relatedModel->guestModeEnabled(),
            fn (MessageBuilder $query) => $query->currentGuest()
        )
            ->when(
                $filter === 'own' && !$relatedModel->guestModeEnabled(),
                function (MessageBuilder $query) use ($relatedModel) {
                    if ($user = $relatedModel->getAuthUser()) {
                        return $query->currentUser($user);
                    }
                }
            );
    }

    public function guest(): self
    {
        return $this
            ->whereHasMorph('commenter', Guest::class);
    }

    /**
     * @return MessageBuilder<Message>
     */
    public function currentGuest(): self
    {
        if (SecureGuestMode::enabled()) {
            return $this->whereMorphedTo('commenter', SecureGuestMode::user());
        }

        return $this->whereHasMorph(
            'commenter',
            M::guestModel()->getMorphClass(),
            fn (Builder $query) => $query->where('ip_address', request()->ip())
        );
    }

    /**
     * @param Authenticatable|User $user
     * @return MessageBuilder
     */
    public function currentUser(Authenticatable|User $user): self
    {
        return $this->whereHasMorph(
            'commenter',
            M::userModel()->getMorphClass(),
            fn (Builder $query) => $query->where('commenter_id', $user->getAuthIdentifier())
        );
    }
}
