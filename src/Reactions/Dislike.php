<?php

namespace LakM\Comments\Reactions;

use Illuminate\Support\Facades\DB;
use LakM\Comments\Models\Reaction;

class Dislike extends ReactionContract
{
    public function handle(): bool
    {
        return DB::transaction(function () {
            $this->removeLike();

            if ($this->removeExistingDislike()) {
                return true;
            }

            $this->createDislike();

            return true;
        });
    }

    protected function removeLike(): null|bool
    {
        return $this->comment
            ->reactions()
            ->checkMode($this->authMode)
            ->where('type', 'like')
            ->first()
            ?->delete();
    }

    protected function removeExistingDislike(): null|bool
    {
        return $this->comment
            ->reactions()
            ->checkMode($this->authMode)
            ->where('type', 'dislike')
            ->first()
            ?->delete();
    }

    protected function createDislike(): Reaction
    {
        return $this->createReaction();
    }
}
