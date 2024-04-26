<?php

use Illuminate\Support\Facades\Event;
use LakM\Comments\Events\CommentDeleted;
use LakM\Comments\Events\CommentUpdated;
use LakM\Comments\Livewire\CommentList;
use LakM\Comments\Livewire\UpdateCommentForm;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Livewire\livewire;

it('can delete a comment for authenticated user', function () {
    config(['comments.guest_mode.enabled' => false]);

    Event::fake();

    $user = actAsAuth();
    $video = video();
    $comment = createCommentsForAuthUser($user, $video);

    livewire(CommentList::class, ['model' => $video])
        ->assertSeeText('Delete')
        ->call('delete', comment: $comment)
        ->assertDispatched('comment-deleted', commentId: $comment->getKey())
        ->assertOk();

    assertDatabaseEmpty($comment->getTable());

    Event::assertDispatched(CommentDeleted::class);
});

it('cannot delete a comment for invalid authenticated user', function () {
    config(['comments.guest_mode.enabled' => false]);

    Event::fake();

    actAsAuth();

    $user = user();
    $video = video();
    $comment = createCommentsForAuthUser($user, $video);

    livewire(CommentList::class, ['model' => $video])
        ->assertDontSeeText('Delete')
        ->call('delete', comment: $comment)
        ->assertNotDispatched('comment-deleted', commentId: $comment->getKey())
        ->assertOk();

    assertDatabaseCount($comment->getTable(), 1);

    Event::assertNotDispatched(CommentDeleted::class);
});


it('can delete a comment for a guest', function () {
    config(['comments.guest_mode.enabled' => true]);

    Event::fake();

    $video = video();
    $comment = createCommentsForGuest($video, 1, ['ip_address' => request()->ip()]);

    livewire(CommentList::class, ['model' => $video])
        ->assertSeeText('Delete')
        ->call('delete', comment: $comment)
        ->assertDispatched('comment-deleted', commentId: $comment->getKey())
        ->assertOk();

    assertDatabaseEmpty($comment->getTable());

    Event::assertDispatched(CommentDeleted::class);
});

it('cannot delete a comment for a invalid guest', function () {
    config(['comments.guest_mode.enabled' => true]);

    Event::fake();

    $video = video();
    $comment = createCommentsForGuest($video, 1, ['ip_address' => fake()->ipv4()]);

    livewire(CommentList::class, ['model' => $video])
        ->assertDontSeeText('Delete')
        ->call('delete', comment: $comment)
        ->assertNotDispatched('comment-deleted', commentId: $comment->getKey())
        ->assertOk();

    assertDatabaseCount($comment->getTable(), 1);

    Event::assertNotDispatched(CommentDeleted::class);
});

