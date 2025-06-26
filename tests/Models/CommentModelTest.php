<?php

use LakM\Commenter\ModelResolver;
use LakM\Commenter\Models\Comment;
use LakM\Commenter\Models\Reply;

test('comment model is a child of base comment model', function () {
    expect(ModelResolver::commentModel())->toBeInstanceOf(Comment::class);
});

it('has replies relationship', function () {
    $comment = ModelResolver::commentModel();
    expect($comment->replies())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\MorphMany::class);
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
            ->and($reply->reply_type)->toBe($comment->getMorphClass())
            ->and($comment->getKey())->toBe($reply->reply_id);
    });
});

it('can retrieve nested replies correctly', function () {
    $post = post();

    $comment = createCommentsForGuest($post);

    $reply = createCommentRepliesForGuestMode($comment);

    createNestedRepliesForGuestMode($reply, 6);

    $nestedReplies = $reply->replies;

    expect($comment->replies)
        ->toBeInstanceOf(Illuminate\Database\Eloquent\Collection::class)
        ->toHaveCount(1)
        ->each(function ($reply) {
            $reply->toBeInstanceOf(Reply::class);
        });


    $nestedReplies->each(function (Reply $nestedReply) use ($reply) {
        expect($nestedReply->commentable_id)->toBeNull()
            ->and($nestedReply->commentable_type)->toBeNull()
            ->and($nestedReply->reply_type)->toBe($reply->getMorphClass())
            ->and($nestedReply->reply_id)->toBe($reply->getKey());
    });
});
