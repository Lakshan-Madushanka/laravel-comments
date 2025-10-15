<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use LakM\Commenter\Events\Comment\CommentDeleted;
use LakM\Commenter\Livewire\Comments\ItemView;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Event::fake();
});

it('can render a comment item in guest mode', function () {
    onGuestMode();

    $video = video();
    $comment = createCommentsForGuest($video, 1, forCurrentUser: true);
    $comment->replies_count = 0;

    livewire(ItemView::class, ['comment' => $comment, 'guestMode' => true, 'model' => $video, 'showReplyList' => false])
        ->assertSeeText(Str::limit($comment->ownerName(false), 25))
        ->assertOk();
});

it('can render a comment item in auth mode', function () {
    onGuestMode(false);

    $user = actAsAuth();
    $video = video();
    $comment = createCommentsForAuthUser($user, $video);
    $comment->replies_count = 0;

    livewire(ItemView::class, ['comment' => $comment, 'guestMode' => false, 'model' => $video, 'showReplyList' => false])
        ->assertSeeText(Str::limit($comment->ownerName(true), 25))
        ->assertOk();
});

it('can delete a comment for authenticated user', function () {
    onGuestMode(false);

    $user = actAsAuth();
    $video = video();
    $comment = createCommentsForAuthUser($user, $video);
    $comment->replies_count = 0;

    livewire(ItemView::class, ['comment' => $comment, 'guestMode' => false, 'model' => $video, 'showReplyList' => false])
        ->assertSeeText('Delete')
        ->call('delete', comment: $comment)
        ->assertDispatched('comment-deleted', commentId: $comment->getKey())
        ->assertOk();

    assertDatabaseEmpty($comment->getTable());

    Event::assertDispatched(CommentDeleted::class);
});

it('cannot delete a comment for invalid authenticated user', function () {
    onGuestMode(false);

    actAsAuth();

    $user = user();
    $video = video();
    $comment = createCommentsForAuthUser($user, $video);
    $comment->replies_count = 0;

    livewire(ItemView::class, ['comment' => $comment, 'guestMode' => false, 'model' => $video, 'showReplyList' => false])
        ->assertDontSeeText('Delete')
        ->call('delete', comment: $comment)
        ->assertNotDispatched('comment-deleted', commentId: $comment->getKey())
        ->assertOk();

    assertDatabaseCount($comment->getTable(), 1);

    Event::assertNotDispatched(CommentDeleted::class);
});


it('can delete a comment for a guest', function () {
    onGuestMode();

    $video = video();
    $comment = createCommentsForGuest($video, 1, forCurrentUser: true);
    $comment->replies_count = 0;

    livewire(ItemView::class, ['comment' => $comment, 'guestMode' => true, 'model' => $video, 'showReplyList' => false])
        ->assertSeeText('Delete')
        ->call('delete', comment: $comment)
        ->assertDispatched('comment-deleted', commentId: $comment->getKey())
        ->assertOk();

    assertDatabaseEmpty($comment->getTable());

    Event::assertDispatched(CommentDeleted::class);
});

it('cannot delete a comment for a invalid guest', function () {
    onguestMode();

    $video = video();
    $comment = createCommentsForGuest($video, 1, ['ip_address' => fake()->ipv4()]);
    $comment->replies_count = 0;

    livewire(ItemView::class, ['comment' => $comment, 'guestMode' => true, 'model' => $video, 'showReplyList' => false])
        ->assertDontSeeText('Delete')
        ->call('delete', comment: $comment)
        ->assertNotDispatched('comment-deleted', commentId: $comment->getKey())
        ->assertOk();

    assertDatabaseCount($comment->getTable(), 1);

    Event::assertNotDispatched(CommentDeleted::class);
});

it('shows pin message option', function () {
    onGuestMode(false);

    authorizePinMessage();

    $user = actAsAuth();
    $video = video();
    $comment = createCommentsForAuthUser($user, $video);
    $comment->replies_count = 0;

    livewire(ItemView::class, ['comment' => $comment, 'guestMode' => true, 'model' => $video, 'showReplyList' => false])
        ->assertSeeText(__('Pin'))
        ->assertOk();
});

it('shows unpin message option for already pinned comment', function () {
    onGuestMode(false);

    authorizePinMessage();

    $user = actAsAuth();
    $video = video();
    $comment = createCommentsForAuthUser($user, $video);
    $comment->is_pinned = true;
    $comment->save();

    $comment->replies_count = 0;

    livewire(ItemView::class, ['comment' => $comment, 'guestMode' => true, 'model' => $video, 'showReplyList' => false])
        ->assertSeeText(__('Unpin'))
        ->assertOk();
});

it('doesn\'t show pin message option when disabled', function () {
    onGuestMode(false);

    canPinMessage(false);

    $user = actAsAuth();
    $video = video();
    $comment = createCommentsForAuthUser($user, $video);
    $comment->replies_count = 0;

    livewire(ItemView::class, ['comment' => $comment, 'guestMode' => true, 'model' => $video, 'showReplyList' => false])
        ->assertDontSeeText(__('Pin'))
        ->assertOk();

    canPinMessage();
    livewire(ItemView::class, ['comment' => $comment, 'guestMode' => true, 'model' => $video, 'showReplyList' => false])
        ->assertDontSeeText(__('Pin'))
        ->assertOk();

    canPinMessage();
    authorizePinMessage();
    livewire(ItemView::class, ['comment' => $comment, 'guestMode' => true, 'model' => $video, 'showReplyList' => false])
        ->assertSeeText(__('Pin'))
        ->assertOk();
});
