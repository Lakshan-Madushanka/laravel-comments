<?php

namespace LakM\Comments\Reactions;

use Illuminate\Support\Facades\DB;

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
        return $this->comment
            ->reactions()
            ->where('type', $this->type)
            ->checkMode($this->authMode)
            ->first()
            ?->delete();
    }

    protected function create(): \LakM\Comments\Models\Reaction
    {
        return $this->createReaction();
    }
}
