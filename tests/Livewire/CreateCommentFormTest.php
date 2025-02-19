<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use LakM\Comments\Events\CommentCreated;
use LakM\Comments\Exceptions\CommentLimitExceededException;
use LakM\Comments\Livewire\CreateCommentForm;
use LakM\Comments\Models\Comment;
use LakM\Comments\Tests\Fixtures\User;
use LakM\Comments\Tests\Fixtures\Video;

use function Pest\Livewire\livewire;

it('render comment form', function () {
    livewire(CreateCommentForm::class, ['model' => \post()])
        ->assertOk();
});

it('does not show guest name input field when guest mode is disabled', function () {
    livewire(CreateCommentForm::class, ['model' => \post()])
        ->assertDontSee('comment as')
        ->assertOk();
});

it('show guest name input field when guest mode is enabled', function () {
    onGuestMode();

    livewire(CreateCommentForm::class, ['model' => \video()])
        ->assertSee('Comment as')
        ->assertOk();
});

it('doesn\'t show name and email when guest mode is secured', function () {
    onGuestMode(secured: true);

    livewire(CreateCommentForm::class, ['model' => \video()])
        ->assertDontSeeText(__('Comment as'))
        ->assertDontSeeText(__('Email'))
        ->assertOk();
});

it('requires name and email when sending verify link in safe guest mode', function () {
    onGuestMode(secured: true);

    livewire(CreateCommentForm::class, ['model' => \video()])
        ->call('sendVerifyLink', 'url')
        ->assertHasErrors([
            'name' => 'required',
            'email' => 'required',
        ])
        ->assertOk();
});

test('name must be unique except existing email when sending verify link in safe guest mode', function () {
    Notification::fake();

    onGuestMode(secured: true);

    $guest = guest();

    livewire(CreateCommentForm::class, ['model' => \video()])
        ->set('email', fake()->email)
        ->set('name', $guest->name)
        ->call('sendVerifyLink', 'url')
        ->assertHasErrors(['name' => 'unique'])
        ->assertOk();

    livewire(CreateCommentForm::class, ['model' => \video()])
        ->set('email', $guest->email)
        ->set('name', $guest->name)
        ->call('sendVerifyLink', 'url')
        ->assertHasNoErrors()
        ->assertOk();
});

it('can validate guest name', function () {
    onGuestMode();

    $video = \video();

    livewire(CreateCommentForm::class, ['model' => $video])
        ->set('name', '')
        ->call('create')
        ->assertHasErrors(['name' => 'required'])
        ->assertOk();
});

it('can validate guest email', function () {
    onGuestMode();

    $video = \video();

    livewire(CreateCommentForm::class, ['model' => $video])
        ->set('email', 'email')
        ->call('create')
        ->assertHasErrors(['email' => 'email'])
        ->assertOk();
});

it('can validate text field', function () {
    onGuestMode();

    $video = \video();

    livewire(CreateCommentForm::class, ['model' => $video])
        ->set('text', '')
        ->call('create')
        ->assertHasErrors(['text' => 'required'])
        ->assertOk();
});

test('guest name must be unique for different ip address', function () {
    onGuestMode();

    $video = \video();

    $comment = createCommentsForGuest($video, 1, ['ip_address' => fake()->ipv4()]);

    livewire(CreateCommentForm::class, ['model' => $video])
        ->set('text', 'test')
        ->set('name', $comment->commenter->name)
        ->set('email', fake()->email())
        ->call('create')
        ->assertHasErrors(['name' => 'unique'])
        ->assertOk();
});

test('guest name must not be unique for the same ip address', function () {
    onGuestMode();

    $video = \video();

    $comment = createCommentsForGuest($video, 1, forCurrentUser: true);

    livewire(CreateCommentForm::class, ['model' => $video])
        ->set('text', 'test')
        ->set('name', $comment->commenter->name)
        ->set('email', fake()->email())
        ->call('create')
        ->assertHasNoErrors()
        ->assertOk();
});

test('email must be unique for different ip address', function () {
    onGuestMode();

    $video = \video();

    $comment = createCommentsForGuest($video, 1, ['ip_address' => fake()->ipv4()]);

    livewire(CreateCommentForm::class, ['model' => $video])
        ->set('text', 'test')
        ->set('name', fake()->name())
        ->set('email', $comment->commenter->email)
        ->call('create')
        ->assertHasErrors(['email' => 'unique'])
        ->assertOk();
});

test('email must not be unique for the same ip address', function () {
    onGuestMode();

    $video = \video();

    $comment = createCommentsForGuest($video, 1, ['ip_address' => request()->ip()]);

    livewire(CreateCommentForm::class, ['model' => $video])
        ->set('text', 'test')
        ->set('name', fake()->name)
        ->set('email', fake()->email())
        ->call('create')
        ->assertHasNoErrors()
        ->assertOk();
});

it('shows login link when guest mode disabled', function () {
    config(['comments.guest_mode.enabled' => false]);
    $video = \video();

    livewire(CreateCommentForm::class, ['model' => $video])
        ->assertSee('login')
        ->assertOk();
});

it('shows email field when guest mode enabled', function ($emailEnabled, $guestMode) {
    config(['comments.guest_mode.enabled' => $guestMode]);
    config(['comments.guest_mode.email_enabled' => $emailEnabled]);

    $video = \video();

    $component = livewire(CreateCommentForm::class, ['model' => $video])
        ->assertOk();

    if (!$guestMode || !$emailEnabled) {
        $component->assertDontSee('Email');
    }

    if ($guestMode && $emailEnabled) {
        $component->assertSee('Email');
    }
})->with([
    ['emailEnabled' => true, 'guestMode' => true],
    ['emailEnabled' => false, 'guestMode' => true],
    ['emailEnabled' => true, 'guestMode' => false],
    ['emailEnabled' => false, 'guestMode' => false],
]);

it('load user data in guest mode', function () {
    onGuestMode();

    $video = \video();

    $comment1 = createCommentsForGuest(relatedModel: $video, forCurrentUser: true);
    createCommentsForGuest($video);

    $component = livewire(CreateCommentForm::class, ['model' => $video])
        ->assertOk();

    expect($component->get('name'))
        ->toBe($comment1->commenter->name)
        ->and($component->get('email'))
        ->toBe($comment1->commenter->email);
});

it('can create comment for guest mode', function () {
    onGuestMode();

    $video = \video();

    livewire(CreateCommentForm::class, ['model' => $video])
        ->set('name', 'test user')
        ->set('email', 'testuser@gmail.com')
        ->set('text', 'test comment')
        ->call('create')
        ->assertHasNoErrors()
        ->assertOk();

    expect($video->comments)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(1)
        ->first()->toBeInstanceOf(Comment::class)
        ->first()->commentable_id->toBe($video->getKey())
        ->first()->commentable_type->toBe(Video::class)
        ->first()->commenter->name->toBe('test user')
        ->first()->commenter->email->toBe('testuser@gmail.com')
        ->first()->text->toContain('test comment')
        ->first()->commenter->ip_address->toBe(request()->ip());
});

it('can create comment for safe guest mode', function () {
    Notification::fake();
    onGuestMode(secured: true);

    $guest = actAsGuest();

    $video = \video();

    livewire(CreateCommentForm::class, ['model' => $video])
        ->set('text', 'test comment')
        ->call('create')
        ->assertHasNoErrors()
        ->assertOk();

    expect($video->comments)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(1)
        ->first()->toBeInstanceOf(Comment::class)
        ->first()->commentable_id->toBe($video->getKey())
        ->first()->commentable_type->toBe(Video::class)
        ->first()->commenter->name->toBe($guest->name)
        ->first()->commenter->email->toBe($guest->email)
        ->first()->text->toContain('test comment')
        ->first()->commenter->ip_address->toBe(request()->ip());
});

it('can create comment for auth mode', function () {
    onGuestMode(false);
    $user = actAsAuth();

    $video = \video();

    livewire(CreateCommentForm::class, ['model' => $video])
        ->set('text', 'test comment')
        ->call('create')
        ->assertHasNoErrors()
        ->assertOk();

    expect($video->comments)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(1)
        ->first()->toBeInstanceOf(Comment::class)
        ->first()->commentable_id->toBe($video->getKey())
        ->first()->commentable_type->toBe(Video::class)
        ->first()->text->toContain('test comment')
        ->and($user->comments)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(1)
        ->first()->toBeInstanceOf(Comment::class)
        ->first()->commenter_id->toBe($user->getKey())
        ->first()->commenter_type->toBe(User::class)
        ->first()->text->toContain('test comment');
});

it('dispatch a event after comment is created', function () {
    Event::fake();

    onGuestMode(false);
    actAsAuth();

    $video = \video();

    livewire(CreateCommentForm::class, ['model' => $video])
        ->set('text', 'test comment')
        ->call('create')
        ->assertHasNoErrors()
        ->assertOk();

    Event::assertDispatched(CommentCreated::class);
});

it('can limit comments creation for guest mode', function ($shouldLimit) {
    onGuestMode();

    if ($shouldLimit) {
        config(['comments.limit' => 1]);
    } else {
        config(['comments.limit' => null]);
    }

    $video = \video();
    createCommentsForGuest(relatedModel: $video, forCurrentUser: true);

    $c = livewire(CreateCommentForm::class, ['model' => $video])
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
            ->toThrow(CommentLimitExceededException::class)
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

    if (!Auth::check()) {
        $user = actAsAuth();
    }

    if ($shouldLimit) {
        config(['comments.limit' => 1]);
    } else {
        config(['comments.limit' => null]);
    }

    $video = \video();
    $comment = $video->comments()->create(['text' => 'comment']);
    $user->comments()->save($comment);


    $c = livewire(CreateCommentForm::class, ['model' => $video])
        ->set('text', 'test comment');

    if ($shouldLimit) {
        expect(
            fn () => $c
                ->call('create')
                ->assertHasNoErrors()
                ->assertOk()
        )
            ->toThrow(CommentLimitExceededException::class)
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

it('can change the guest name for all the same ip address', function () {
    onGuestMode();

    $video = \video();

    $comment = createCommentsForGuest($video, forCurrentUser: true);

    livewire(CreateCommentForm::class, ['model' => $video])
        ->set('text', 'test')
        ->set('name', 'lakm')
        ->set('email', fake()->email())
        ->call('create')
        ->assertHasNoErrors()
        ->assertOk();

    $guests = Comment::query()
        ->with('commenter')
        ->get()
        ->pluck('commenter')
        ->unique('name')
        ->pluck('name');

    expect($guests)
        ->toHaveCount(1)
        ->and($guests[0])
        ->toBe('lakm');
});

it('can change the guest email for all the same ip address', function () {
    onGuestMode();

    $video = \video();

    $comment = createCommentsForGuest($video, forCurrentUser: true);

    livewire(CreateCommentForm::class, ['model' => $video])
        ->set('text', 'test')
        ->set('name', 'lakm')
        ->set('email', 'lakm@gmail.com')
        ->call('create')
        ->assertHasNoErrors()
        ->assertOk();

    $guests = Comment::query()
        ->with('commenter')
        ->get()
        ->pluck('commenter')
        ->unique('email')
        ->pluck('email');

    expect($guests)
        ->toHaveCount(1)
        ->and($guests[0])
        ->toBe('lakm@gmail.com');
});
