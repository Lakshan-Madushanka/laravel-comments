<?php

use LakM\Commenter\Models\Reply;

it('has replies relationship', function () {
    $reply = new Reply();
    expect($reply->replies())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\MorphMany::class);
});

it('can retrieve replies correctly', function () {
    $post = post();

    $comment = createCommentsForGuest($post);

    createCommentRepliesForGuestMode($comment, 5);

    $replies = $comment->replies;

    expect($replies)
        ->toBeInstanceOf(Illuminate\Database\Eloquent\Collection::class)
        ->and($comment->replies->count())->toBe(5)
        ->and($replies)
        ->each(function ($reply) {
            $reply->toBeInstanceOf(Reply::class);
        });


    $replies->each(function (Reply $reply) use ($comment) {
        expect($reply->commentable_id)->toBeNull()
            ->and($reply->commentable_type)->toBeNull()
            ->and($comment->getKey())->toBe($reply->reply_id);
    });
});

it('can retrieve nested replies', function () {
    $post = post();

    $comment = createCommentsForGuest($post);

    $reply = createCommentRepliesForGuestMode($comment);

    $nestedReplies1 = createNestedRepliesForGuestMode($reply, 6);

    $firstNestedReply = $nestedReplies1->first();

    $nestedReplies2 = createNestedRepliesForGuestMode($firstNestedReply, 4);

    expect($reply->replies)
        ->toBeInstanceOf(Illuminate\Database\Eloquent\Collection::class)
        ->toHaveCount(6)
        ->each(function ($reply) {
            $reply->toBeInstanceOf(LakM\Commenter\Models\Reply::class);
        });

    $reply->replies->each(function (Reply $nestedReply) use ($reply) {
        expect($nestedReply->commentable_id)->toBeNull()
            ->and($nestedReply->commentable_type)->toBeNull()
            ->and($nestedReply->reply_id)->toBe($reply->getKey())
            ->and($nestedReply->reply_type)->toBe($reply->getMorphClass());
    });

    $firstNestedReply->replies->each(function (Reply $nestedReply) use ($firstNestedReply) {
        expect($nestedReply->commentable_id)->toBeNull()
            ->and($nestedReply->commentable_type)->toBeNull()
            ->and($nestedReply->reply_id)->toBe($firstNestedReply->getKey())
            ->and($nestedReply->reply_type)->toBe($firstNestedReply->getMorphClass());
    });
});
