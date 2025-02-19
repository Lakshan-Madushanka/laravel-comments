<?php

use Illuminate\Support\Facades\Event;
use LakM\Comments\Events\CommentUpdated;
use LakM\Comments\Livewire\UpdateCommentForm;

use function Pest\Livewire\livewire;

it('can validate the form', function () {
    $user = actAsAuth();
    $video = video();
    $comment = createCommentsForAuthUser($user, $video);

    livewire(UpdateCommentForm::class, ['comment' => $comment, 'model' => $video])
        ->set('text', '')
        ->call('save')
        ->assertHasErrors(['text' => 'required']);
});

it('dispatch a event when commit update discarded', function () {
    $user = actAsAuth();
    $video = video();
    $comment = createCommentsForAuthUser($user, $video);

    livewire(UpdateCommentForm::class, ['comment' => $comment, 'model' => $video])
        ->set('text', '')
        ->call('discard')
        ->assertDispatched('comment-update-discarded');
});

it('can update a comment for authenticated user', function () {
    config(['comments.guest_mode.enabled' => false]);

    Event::fake();

    $user = actAsAuth();
    $video = video();
    $comment = createCommentsForAuthUser($user, $video);

    livewire(UpdateCommentForm::class, ['comment' => $comment, 'model' => $video])
        ->set('text', 'new comment')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('comment-updated', commentId: $comment->getKey(), text: '<p>new comment</p>')
        ->assertOk();

    $comment->refresh();

    expect($comment->text)->toContain('new comment');

    Event::assertDispatched(CommentUpdated::class);
});

it('cannot update a comment for invalid authenticated user', function () {
    config(['comments.guest_mode.enabled' => false]);

    Event::fake();

    actAsAuth();

    $user = user();
    $video = video();
    $comment = createCommentsForAuthUser($user, $video);

    livewire(UpdateCommentForm::class, ['comment' => $comment, 'model' => $video])
        ->set('text', 'new comment')
        ->call('save')
        ->assertHasNoErrors()
        ->assertNotDispatched('comment-updated', commentId: $comment->getKey(), text: 'new comment')
        ->assertOk();

    $updatedComment = $comment->fresh();

    expect($updatedComment->text)->toBe($comment->text);

    Event::assertNotDispatched(CommentUpdated::class);
});


it('can update a comment for a guest', function () {
    config(['comments.guest_mode.enabled' => true]);

    Event::fake();

    $video = video();
    $comment = createCommentsForGuest($video, 1, forCurrentUser: true);

    livewire(UpdateCommentForm::class, ['comment' => $comment, 'model' => $video])
        ->set('text', 'new comment')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('comment-updated', commentId: $comment->getKey(), text: '<p>new comment</p>')
        ->assertOk();

    $comment->refresh();

    expect($comment->text)->toContain('new comment');

    Event::assertDispatched(CommentUpdated::class);
});

it('cannot update a comment for a invalid guest', function () {
    config(['comments.guest_mode.enabled' => true]);

    Event::fake();

    $video = video();
    $comment = createCommentsForGuest($video, 1, ['ip_address' => fake()->ipv4()]);

    livewire(UpdateCommentForm::class, ['comment' => $comment, 'model' => $video])
        ->set('text', 'new comment')
        ->call('save')
        ->assertHasNoErrors()
        ->assertNotDispatched('comment-updated', commentId: $comment->getKey(), text: 'new comment')
        ->assertOk();

    $updatedComment = $comment->fresh();

    expect($updatedComment->text)->toBe($comment->text);

    Event::assertNotDispatched(CommentUpdated::class);
});

it('can update a comment in secured guest', function () {
    onGuestMode(secured: true);

    $guest = actAsGuest();

    Event::fake();

    $video = video();
    $comment = createCommentsForGuest($video, guest: $guest);

    livewire(UpdateCommentForm::class, ['comment' => $comment, 'model' => $video])
        ->set('text', 'new comment')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('comment-updated', commentId: $comment->getKey(), text: '<p>new comment</p>')
        ->assertOk();

    $comment->refresh();

    expect($comment->text)->toContain('new comment')
        ->and($comment->commenter)
        ->id->toBe($guest->getKey())
        ->name->toBe($guest->name)
        ->email->toBe($guest->email);

    Event::assertDispatched(CommentUpdated::class);
});

it('cannot update a comment in secured guest', function () {
    onGuestMode(secured: true);

    actAsGuest();

    $guest =  guest();

    Event::fake();

    $video = video();
    $comment = createCommentsForGuest($video, data: ['text' => 'comment'], guest: $guest);

    livewire(UpdateCommentForm::class, ['comment' => $comment, 'model' => $video])
        ->set('text', 'new comment')
        ->call('save')
        ->assertHasNoErrors()
        ->assertNotDispatched('comment-updated', commentId: $comment->getKey(), text: 'new comment')
        ->assertOk();

    $comment->refresh();

    expect($comment->text)->toBe('comment')
        ->and($comment->commenter)
        ->id->toBe($guest->getKey())
        ->name->toBe($guest->name)
        ->email->toBe($guest->email);

    Event::assertNotDispatched(CommentUpdated::class);
});

it('revoke the approval after updated', function () {
    onGuestMode(false);

    $user = actAsAuth();
    $video = video();
    $comment = createCommentsForAuthUser($user, $video, 1, ['approved' => true]);

    livewire(UpdateCommentForm::class, ['comment' => $comment, 'model' => $video])
        ->set('text', 'new comment')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('comment-updated', commentId: $comment->getKey(), text: '<p>new comment</p>')
        ->assertOk();

    $comment->refresh();

    expect($comment)
        ->text->toContain('new comment')
        ->approved->toBeFalse();
});
