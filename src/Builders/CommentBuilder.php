<?php

namespace LakM\Comments\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LakM\Comments\Contracts\CommentableContract;
use LakM\Comments\ModelResolver as M;
use LakM\Comments\Models\Comment;
use LakM\Comments\Models\Guest;

/**
 * @template TModelClass of Comment
 * @extends Builder<Comment>
 * @method CommentBuilder whereApproved(bool $value)
 * @method CommentBuilder whereType(string $value)
 */
class CommentBuilder extends Builder
{
    /** @return CommentBuilder<Comment> */
    public function approved(): self
    {
        return $this->whereApproved(true);
    }

    /**
     * @param Model&CommentableContract $relatedModel
     * @return CommentBuilder<Comment>
     */
    public function checkApproval(Model $relatedModel): self
    {
        return $this->when($relatedModel->approvalRequired(), fn(CommentBuilder $query) => $query->approved());
    }

    /**
     * @param Model&CommentableContract $relatedModel
     * @return CommentBuilder<Comment>
     */
    public function withCommenter(Model $relatedModel): self
    {
        return $this->when(!$relatedModel->guestModeEnabled(), fn(CommentBuilder $query) => $query->with('commenter'));
    }

    /**
     * @param Model&CommentableContract $relatedModel
     * @return CommentBuilder<Comment>
     *
     */
    public function withOwnerReactions(Model $relatedModel): self
    {
        return $this->with(['ownerReactions' => fn($query) => $query->checkMode(!$relatedModel->guestModeEnabled())]);
    }

    /**
     * @param Model&CommentableContract $relatedModel
     * @param string $filter
     * @return CommentBuilder<Comment>
     */
    public function currentUserFilter(Model $relatedModel, string $filter): self
    {
        return $this->when(
            $filter === 'my_comments' && $relatedModel->guestModeEnabled(),
            fn(CommentBuilder $query) => $query->currentGuest()
        )
            ->when(
                $filter === 'my_comments' && !$relatedModel->guestModeEnabled(),
                fn(CommentBuilder $query) => $query->currentUser($relatedModel)
            );
    }

    public function guest(): self
    {
        return $this
            ->whereHasMorph('commenter', Guest::class);
    }

    /**
     * @return self
     */
    public function currentGuest(): self
    {
        return $this->whereHasMorph(
            'commenter',
            M::guestModel()->getMorphClass(),
            fn(Builder $query) => $query->where('ip_address', request()->ip())
        );
    }

    /**
     * @param Model&CommentableContract $relatedModel
     * @return CommentBuilder<Comment>
     */
    public function currentUser($relatedModel): self
    {
        return $this->whereHasMorph(
            'commenter',
            M::userModel()->getMorphClass(),
            fn(Builder $query) => $query->where('commenter_id', $relatedModel->getAuthUser()->getAuthIdentifier())
        );
    }
}
