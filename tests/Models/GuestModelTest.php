<?php

use Illuminate\Database\Eloquent\Relations\MorphMany;
use LakM\Comments\Data\GuestData;
use LakM\Comments\ModelResolver;
use LakM\Comments\Models\Comment;
use LakM\Comments\Models\Guest;

use LakM\Comments\Models\Reply;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\withoutExceptionHandling;

it('can create a guest', function () {
    $guest = new GuestData(name: fake()->name, email: fake()->email);

    Guest::createOrUpdate($guest);

    assertDatabaseHas('guests', $guest->toArray());
});

it('can update already existing guest', function () {
    onGuestMode();

    $guest = new GuestData(name: fake()->name, email: fake()->email);

    Guest::createOrUpdate($guest);

    $newGuest = new GuestData(name: 'lakm', email: $guest->email);

    Guest::createOrUpdate($newGuest);

    assertDatabaseCount('guests', 1);

    assertDatabaseHas('guests', [...$guest->toArray(), 'name' => 'lakm']);
});

it('can create nested replies', function () {
    $post = post();

    $comment = createCommentsForGuest($post);

    $reply = createCommentRepliesForGuestMode($comment);

    createNestedRepliesForGuestMode(reply: $reply, count: 4, forCurrentUser: true);

    $replies = $comment->replies;

    expect($replies)
        ->toHaveCount(1)
        ->toBeInstanceOf(Illuminate\Database\Eloquent\Collection::class);

    $nestedReplies = $reply->replies;

    expect($nestedReplies)
        ->toHaveCount(4)
        ->toBeInstanceOf(Illuminate\Database\Eloquent\Collection::class)
        ->each(function ($nestedReply) {
            $nestedReply->toBeInstanceOf(LakM\Comments\Models\Reply::class);
        });

    $nestedReplies->each(function (Reply $nestedReply) use ($reply) {
        expect($nestedReply->reply_id)->toBe($reply->id)
            ->and($nestedReply->reply_type)->toBe($reply->getMorphClass())
            ->and($nestedReply->commenter)->ip_address->toBe(request()->ip());
    });
});
