<?php

namespace LakM\Commenter\Reactions;

use Illuminate\Support\Facades\DB;
use LakM\Commenter\Builders\ReactionBuilder;
use LakM\Commenter\Models\Reaction;

class Like extends ReactionContract
{
    public function handle(): bool
    {
        return DB::transaction(function () {
            $this->removeDislike();

            if ($this->removeExistingLike()) {
                return true;
            }

            $this->createLike();

            return true;
        });
    }

    protected function removeDislike(): null|bool
    {
        return $this->reactionBuilder()
            ->checkMode($this->authMode)
            ->where('type', 'dislike')
            ->first()
            ?->delete();
    }

    protected function removeExistingLike(): null|bool
    {
        return $this->reactionBuilder()
            ->where('type', 'like')
            ->checkMode($this->authMode)
            ->first()
            ?->delete();
    }

    protected function createLike(): Reaction
    {
        return $this->createReaction();
    }
}
