<?php

namespace LakM\Comments\Reactions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        return $this->comment
            ->reactions()
            ->checkMode()
            ->where('type', 'dislike')
            ->first()
            ?->delete();
    }

    protected function removeExistingLike(): null|bool
    {
        return $this->comment
            ->reactions()
            ->where('type', 'like')
            ->checkMode()
            ->first()
            ?->delete();
    }

    protected function createLike(): \LakM\Comments\Models\Reaction
    {
        return $this->comment->reactions()->create([
            'type' => 'like',
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
        ]);
    }
}
