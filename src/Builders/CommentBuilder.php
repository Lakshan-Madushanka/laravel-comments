<?php

namespace LakM\Comments\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LakM\Comments\Contracts\CommentableContract;
use LakM\Comments\Model as M;
use LakM\Comments\Models\Comment;

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
     * @param  Model&CommentableContract  $relatedModel
     * @return CommentBuilder<Comment>
     */
    public function checkApproval(Model $relatedModel): self
    {
        return $this->when($relatedModel->approvalRequired(), fn(CommentBuilder $query) => $query->approved());
    }

    /**
     * @param  Model&CommentableContract  $relatedModel
     * @return CommentBuilder<Comment>
     */
    public function withCommenter(Model $relatedModel): self
    {
        return $this->when(!$relatedModel->guestModeEnabled(), fn(CommentBuilder $query) => $query->with('commenter'));
    }

    /**
     * @param  Model&CommentableContract  $relatedModel
     * @return CommentBuilder<Comment>
     *
     */
    public function withOwnerReactions(Model $relatedModel): self
    {
        return $this->with(['ownerReactions' => fn($query) => $query->checkMode(!$relatedModel->guestModeEnabled())]);
    }

    /**
     * @param  Model&CommentableContract  $relatedModel
     * @param  string  $filter
     * @return CommentBuilder<Comment>
     */
    public function currentUser(Model $relatedModel, string $filter): self
    {
        $alias = M::userModel()->getMorphClass();

        return $this->when(
            $filter === 'my_comments' && $relatedModel->guestModeEnabled(),
            fn(Builder $query) => $query->where('ip_address', request()->ip())
        )
            ->when(
                $filter === 'my_comments' && !$relatedModel->guestModeEnabled(),
                fn(Builder $query) => $query
                    ->where('commenter_type', $alias)
                    ->where('commenter_id', $relatedModel->getAuthUser()->getAuthIdentifier())
            );
    }
}
