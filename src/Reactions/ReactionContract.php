<?php

namespace LakM\Commenter\Reactions;

use Illuminate\Database\Eloquent\Model;
use LakM\Commenter\Builders\ReactionBuilder;
use LakM\Commenter\Data\GuestData;
use LakM\Commenter\ModelResolver;
use LakM\Commenter\Models\Guest;
use LakM\Commenter\Models\Message;
use LakM\Commenter\Models\Reaction;

abstract class ReactionContract
{
    public function __construct(
        protected Message $message,
        protected bool $authMode,
        protected mixed $authId,
        protected ?string $type = null
    ) {
    }

    public function createReaction(): Reaction
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

        /** @var Reaction $reaction */
        $reaction =  $this->message->reactions()->create($data);

        return $reaction;
    }

    public function reactionBuilder(): ReactionBuilder
    {
        /** @var ReactionBuilder $reactionBuilder */
        $reactionBuilder = $this->message
            ->reactions()
            ->getQuery();

        return $reactionBuilder;
    }
}
