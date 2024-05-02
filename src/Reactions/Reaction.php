<?php

namespace LakM\Comments\Reactions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Reaction extends ReactionContract
{
    public function handle(): bool
    {
        return DB::transaction(function () {
            if ($this->removeExistingReaction()) {
                return true;
            }

            $this->createReaction();

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

    protected function createReaction(): \LakM\Comments\Models\Reaction
    {
        return $this->comment->reactions()->create([
            'type' => $this->type,
            'user_id' => $this->authId,
            'ip_address' => request()->ip(),
        ]);
    }
}