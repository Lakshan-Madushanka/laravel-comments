<?php

namespace LakM\Comments\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use LakM\Comments\Facades\SecureGuestMode;
use LakM\Comments\Models\Guest;
use LakM\Comments\Models\Reaction;

/**
 * @template TModelClass of Reaction
 * @extends Builder<Reaction>
 * @method ReactionBuilder whereApproved(bool $value)
 * @method ReactionBuilder whereType(string $value)
 * @method ReactionBuilder query()
 * @method ReactionBuilder newQuery()()
 */
class ReactionBuilder extends Builder
{
    /**
     * @param  bool  $authMode
     * @return ReactionBuilder<Reaction>
     */
    public function checkMode(bool $authMode): self
    {
        return $this->when(
            $authMode,
            function (ReactionBuilder $query) {
                return $query->authMode();
            },
            function (ReactionBuilder $query) {
                return $query->guestMode();
            }
        );
    }

    /** @return ReactionBuilder<Reaction> */
    public function guestMode(): self
    {
        if (SecureGuestMode::enabled()) {
            $guest = SecureGuestMode::user();
        } else {
            $guest = Guest::query()->where('ip_address', request()->ip())->first();
        }
        return $this->whereMorphedTo('owner', $guest);
    }

    /** @return ReactionBuilder<Reaction> */
    public function authMode(): self
    {
        return $this
            ->whereMorphedTo('owner', Auth::user());
    }
}
