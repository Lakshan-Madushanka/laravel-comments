<?php

namespace LakM\Comments\Reactions;

use Illuminate\Database\Eloquent\Model;
use LakM\Comments\Data\GuestData;
use LakM\Comments\ModelResolver;
use LakM\Comments\Models\Comment;
use LakM\Comments\Models\Guest;
use LakM\Comments\Models\Reply;

abstract class ReactionContract
{
    public function __construct(
        protected Reply|Comment $comment,
        protected bool $authMode,
        protected mixed $authId,
        protected ?string $type = null
    ) {
    }

    public function createReaction(): \LakM\Comments\Models\Reaction
    {
        $data = [
            'type' => $this->type,
        ];

        if ($this->authMode) {
            $data['owner_id'] = $this->authId;
            $data['owner_type'] = ModelResolver::userModel()->getMorphClass();
        } else {
            $guestData = new GuestData(ip_address: request()->ip());
            /** @var Model $guest */
            $guest = Guest::createOrUpdate($guestData);

            $data['owner_id'] = $guest->getKey();
            $data['owner_type'] = $guest->getMorphClass();
        }

        return $this->comment->reactions()->create($data);
    }
}
