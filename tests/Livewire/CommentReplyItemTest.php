<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use LakM\Commenter\Events\Comment\CommentDeleted;
use LakM\Commenter\Events\Reply\ReplyDeleted;
use LakM\Commenter\Livewire\Replies\ItemView;

use function Pest\Livewire\livewire;

beforeEach(function () {
    Event::fake();
});

it('can render a reply item in guest mode', function () {
    $video = video();

    $comment = createCommentsForGuest(relatedModel: $video, forCurrentUser: true);
    $reply = createCommentRepliesForGuestMode($comment);

    livewire(
        ItemView::class,
        [
            'message' => $comment,
            'reply' => $reply,
            'relatedModel' => $video,
            'guestMode' => true
        ]
    )
        ->assertSeeText(Str::limit($reply->ownerName(false), 25))
        ->assertOk();
});

it('can render a reply item in auth mode', function () {
    $user = actAsAuth();

    $video = video();

    $comment = createCommentsForAuthUser($user, $video);
    $reply = createCommentRepliesForAuthMode($comment, $user);

    livewire(
        ItemView::class,
        [
            'message' => $comment,
            'reply' => $reply,
            'relatedModel' => $video,
            'guestMode' => false
        ]
    )
        ->assertSeeText(Str::limit($reply->ownerName(true), 25))
        ->assertOk();
});

it('can delete a reply for authenticated user', function () {
    $user = actAsAuth();

    $video = video();

    $comment = createCommentsForAuthUser($user, $video);
    $reply = createCommentRepliesForAuthMode($comment, $user);

    livewire(
        ItemView::class,
        [
            'message' => $comment,
            'reply' => $reply,
            'relatedModel' => $video,
            'guestMode' => false
        ]
    )
        ->assertSeeText('Delete')
        ->call('delete', reply: $reply)
        ->assertDispatched('reply-deleted-' . $comment->getKey(), replyId: $reply->getKey(), messageId: $comment->getKey())
        ->assertOk();

    expect($reply->fresh())->toBeNull();

    Event::assertDispatched(ReplyDeleted::class);
});

it('cannot delete a reply for invalid authenticated user', function () {
    actAsAuth();

    $user = user();
    $video = video();

    $comment = createCommentsForAuthUser($user, $video);
    $reply = createCommentRepliesForAuthMode($comment, $user);

    livewire(
        ItemView::class,
        [
            'message' => $comment,
            'reply' => $reply,
            'relatedModel' => $video,
            'guestMode' => false
        ]
    )        ->assertDontSeeText('Delete')
        ->call('delete', reply: $reply)
        ->assertNotDispatched('reply-deleted', replyId: $reply->getKey())
        ->assertOk();

    expect($reply->fresh())->not->toBeNull();

    Event::assertNotDispatched(CommentDeleted::class);
});


it('can delete a reply for a guest', function () {
    $video = video();

    $comment = createCommentsForGuest(relatedModel: $video, forCurrentUser: true);
    $reply = createCommentRepliesForGuestMode($comment, forCurrentUser: true);

    livewire(
        ItemView::class,
        [
            'message' => $comment,
            'reply' => $reply,
            'relatedModel' => $video,
            'guestMode' => true
        ]
    )
        ->assertSeeText('Delete')
        ->call('delete', reply: $reply)
        ->assertDispatched('reply-deleted-' . $comment->getKey(), replyId: $reply->getKey(), messageId: $comment->getKey())
        ->assertOk();

    expect($reply->fresh())->toBeNull();

    Event::assertDispatched(ReplyDeleted::class);
});

it('cannot delete a reply for a invalid guest', function () {
    $video = video();

    $comment = createCommentsForGuest(relatedModel: $video);
    $reply = createCommentRepliesForGuestMode($comment);

    livewire(
        ItemView::class,
        [
            'message' => $comment,
            'reply' => $reply,
            'relatedModel' => $video,
            'guestMode' => true
        ]
    )
        ->assertDontSeeText('Delete')
        ->call('delete', reply: $reply)
        ->assertNotDispatched('reply-deleted-' . $comment->getKey(), replyId: $reply->getKey(), commentId: $comment->getKey())
        ->assertOk();

    expect($reply->fresh())->not->toBeNull();

    Event::assertNotDispatched(ReplyDeleted::class);
});
