<?php

namespace LakM\Commenter\Reactions;

use Illuminate\Support\Facades\DB;
use LakM\Commenter\Models\Reaction as ReactionModel;

class Reaction extends ReactionContract
{
    public function handle(): bool
    {
        return DB::transaction(function () {
            if ($this->removeExistingReaction()) {
                return true;
            }

            $this->create();

            return true;
        });
    }

    protected function removeExistingReaction(): null|bool
    {

        return $this->reactionBuilder()
            ->where('type', $this->type)
            ->checkMode($this->authMode)
            ->first()
            ?->delete();
    }

    protected function create(): ReactionModel
    {
        return $this->createReaction();
    }
}
