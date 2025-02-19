<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Event;
use LakM\Comments\Events\CommentReplyCreated;
use LakM\Comments\Exceptions\ReplyLimitExceededException;
use LakM\Comments\Livewire\CreateCommentReplyForm;
use LakM\Comments\Models\Comment;

use function Pest\Livewire\livewire;

it('render comment form', function () {
    $video = \video();
    $comment = createCommentsForGuest($video);

    livewire(CreateCommentReplyForm::class, ['comment' => $comment, 'relatedModel' => $video, 'guestMode' => true])
        ->assertOk();
});

it('does not show guest name input field when guest mode is disabled', function () {
    $video = \video();
    $comment = createCommentsForGuest($video);

    livewire(CreateCommentReplyForm::class, ['comment' => $comment, 'relatedModel' => $video, 'guestMode' => true])
        ->assertDontSee('comment as')
        ->assertOk();
});

it('show guest name input field when guest mode is enabled', function () {
    onGuestMode();

    $video = \video();
    $comment = createCommentsForGuest($video, data: ['ip_address' => fake()->ipv4()]);

    livewire(CreateCommentReplyForm::class, ['comment' => $comment, 'relatedModel' => $video, 'guestMode' => true])
        ->set('show', true)
        ->assertSee(__('Reply as'))
        ->assertOk();
});

it('can validate guest name', function () {
    onGuestMode();

    $video = \video();
    $comment = createCommentsForGuest($video);

    livewire(CreateCommentReplyForm::class, ['comment' => $comment, 'relatedModel' => $video, 'guestMode' => true])
        ->set('name', '')
        ->call('create')
        ->assertHasErrors(['name' => 'required'])
        ->assertOk();
});

it('can validate guest email', function () {
    onGuestMode();

    $video = \video();
    $comment = createCommentsForGuest($video);

    livewire(CreateCommentReplyForm::class, ['comment' => $comment, 'relatedModel' => $video, 'guestMode' => true])
        ->set('email', 'email')
        ->call('create')
        ->assertHasErrors(['email' => 'email'])
        ->assertOk();
});

it('can validate text field', function () {
    onGuestMode();

    $video = \video();
    $comment = createCommentsForGuest($video);

    livewire(CreateCommentReplyForm::class, ['comment' => $comment, 'relatedModel' => $video, 'guestMode' => true])
        ->set('text', '')
        ->call('create')
        ->assertHasErrors(['text' => 'required'])
        ->assertOk();
});


it('shows email field when guest mode enabled', function ($emailEnabled, $guestMode) {
    config(['comments.reply.email_enabled' => $emailEnabled]);

    if (!$guestMode) {
        actAsAuth();
    }

    $video = \video();
    $comment = createCommentsForGuest($video);

    $component = livewire(
        CreateCommentReplyForm::class,
        ['comment' => $comment, 'relatedModel' => $video, 'guestMode' => $guestMode]
    )
        ->set('show', true)
        ->assertOk();

    if (!$guestMode || !$emailEnabled) {
        $component->assertDontSee(__('Email'));
    }

    if ($guestMode && $emailEnabled) {
        $component->assertSee(__('Email'));
    }
})->with([
    ['emailEnabled' => true, 'guestMode' => true],
    ['emailEnabled' => false, 'guestMode' => true],
    ['emailEnabled' => true, 'guestMode' => false],
    ['emailEnabled' => false, 'guestMode' => false],
]);

it('can create comment for guest mode', function () {
    onGuestMode();

    $video = \video();
    $comment = createCommentsForGuest($video);

    livewire(
        CreateCommentReplyForm::class,
        ['comment' => $comment, 'relatedModel' => $video, 'guestMode' => true]
    )
        ->call('showForm')
        ->set('name', 'test user')
        ->set('email', 'testuser@gmail.com')
        ->set('text', 'test comment')
        ->call('create')
        ->assertHasNoErrors()
        ->assertOk();

    expect(Comment::all())
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(2)
        ->last()->toBeInstanceOf(Comment::class)
        ->last()->commentable_id->toBeNull()
        ->last()->commentable_type->toBeNull()
        ->last()->commenter->name->toBe('test user')
        ->last()->commenter->email->toBe('testuser@gmail.com')
        ->last()->text->toContain('test comment')
        ->last()->reply_id->toBe($comment->getKey())
        ->last()->commenter->ip_address->toBe(request()->ip());
});

it('can create comment for auth mode', function () {
    onGuestMode(false);
    $user = \actAsAuth();

    $video = \video();
    $comment = createCommentsForAuthUser($user, $video);

    livewire(
        CreateCommentReplyForm::class,
        ['comment' => $comment, 'relatedModel' => $video, 'guestMode' => false]
    )
        ->call('showForm')
        ->set('text', 'reply')
        ->call('create')
        ->assertHasNoErrors()
        ->assertOk();

    expect(Comment::query()->get())
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(2)
        ->last()->toBeInstanceOf(Comment::class)
        ->last()->commentable_id->toBeNull()
        ->last()->commentable_type->toBeNull()
        ->last()->commenter_type->toBe($user->getMorphClass())
        ->last()->commenter_id->toBe($user->getAuthIdentifier())
        ->last()->reply_id->toBe($comment->getKey())
        ->last()->text->toContain('reply');
});

it('dispatch a event after reply is created', function () {
    Event::fake();

    onGuestMode(false);
    $user = actAsAuth();

    $video = \video();
    $comment = createCommentsForAuthUser($user, $video);

    livewire(
        CreateCommentReplyForm::class,
        ['comment' => $comment, 'relatedModel' => $video, 'guestMode' => false]
    )
        ->call('showForm')
        ->set('text', 'test comment')
        ->call('create')
        ->assertHasNoErrors()
        ->assertOk();

    Event::assertDispatched(CommentReplyCreated::class);
});

it('can limit comments creation for guest mode', function ($shouldLimit) {
    onGuestMode();

    if ($shouldLimit) {
        config(['comments.reply.limit' => 1]);
    } else {
        config(['comments.reply.limit' => null]);
    }

    $video = \video();

    $comment = createCommentsForGuest($video);
    $reply = createCommentRepliesForGuestMode(comment: $comment, forCurrentUser: true);

    $c = livewire(
        CreateCommentReplyForm::class,
        ['comment' => $comment, 'relatedModel' => $video, 'guestMode' => true]
    )
        ->call('showForm')
        ->set('text', 'test comment')
        ->set('name', 'guest')
        ->set('email', 'gues@mail.com');


    if ($shouldLimit) {
        expect(
            fn () => $c
                ->call('create')
                ->assertHasNoErrors()
                ->assertOk()
        )
            ->toThrow(ReplyLimitExceededException::class)
            ->and($c->get('limitExceeded'))->toBeTrue();
    } else {
        $c->call('create')
            ->assertHasNoErrors()
            ->assertOk();

        expect($c->get('limitExceeded'))->toBeFalse();
    }
})->with([
    true,
    false,
]);

it('can limit comments creation for auth mode', function ($shouldLimit) {
    onGuestMode(false);

    $user = actAsAuth();

    if ($shouldLimit) {
        config(['comments.reply.limit' => 1]);
    } else {
        config(['comments.reply.limit' => null]);
    }

    $video = \video();
    $comment = createCommentsForAuthUser($user, $video);
    createCommentRepliesForAuthMode($comment, $user);

    $video = \video();

    $c = livewire(
        CreateCommentReplyForm::class,
        ['comment' => $comment, 'relatedModel' => $video, 'guestMode' => false]
    )
        ->call('showForm')
        ->set('text', 'test comment');

    if ($shouldLimit) {
        expect(
            fn () => $c
                ->call('create')
                ->assertSeeText(__('Allowed reply limit'))
                ->assertHasNoErrors()
                ->assertOk()
        )
            ->toThrow(ReplyLimitExceededException::class)
            ->and($c->get('limitExceeded'))->toBeTrue();
    } else {
        $c->call('create')
            ->assertHasNoErrors()
            ->assertDontSeeText(__('Allowed reply limit'))
            ->assertOk();

        expect($c->get('limitExceeded'))->toBeFalse();
    }
})->with([
    true,
    false,
]);
