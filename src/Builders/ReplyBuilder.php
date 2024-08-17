<?php

namespace LakM\Comments\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LakM\Comments\Contracts\CommentableContract;
use LakM\Comments\ModelResolver as M;
use LakM\Comments\Models\Reply;

/**
 * @template TModelClass of Reply
 * @extends Builder<Reply>
 * @method ReplyBuilder whereApproved(bool $value)
 * @method ReplyBuilder whereType(string $value)
 */
class ReplyBuilder extends Builder
{
    /** @return ReplyBuilder<Reply> */
    public function approved(): self
    {
        return $this->whereApproved(true);
    }

    /**
     * @param  Model&CommentableContract  $relatedModel
     * @return ReplyBuilder<Reply>
     */
    public function withOwnerReactions(Model $relatedModel): self
    {
        return $this->with(['ownerReactions' => fn ($query) => $query->checkMode(!$relatedModel->guestModeEnabled())]);
    }

    /**
     * @param  Model&CommentableContract  $relatedModel
     * @param  string  $filter
     * @return ReplyBuilder<Reply>
     */
    public function currentUser(Model $relatedModel, string $filter): self
    {
        $alias = M::userModel()->getMorphClass();

        return $this->when(
            $filter === 'my_replies' && $relatedModel->guestModeEnabled(),
            fn (Builder $query) => $query->where('ip_address', request()->ip())
        )
            ->when(
                $filter === 'my_replies' && !$relatedModel->guestModeEnabled(),
                fn (Builder $query) => $query
                    ->where('commenter_type', $alias)
                    ->where('commenter_id', $relatedModel->getAuthUser()->getAuthIdentifier())
            );
    }
}
