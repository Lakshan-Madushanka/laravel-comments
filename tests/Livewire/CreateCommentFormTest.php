<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use LakM\Comments\Events\CommentCreated;
use LakM\Comments\Exceptions\CommentLimitExceeded;
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

it('can validate guest name', function () {
    onGuestMode();

    $video = \video();

    livewire(CreateCommentForm::class, ['model' => $video])
        ->set('guest_name', '')
        ->call('create')
        ->assertHasErrors(['guest_name' => 'required'])
        ->assertOk();
});

it('can validate guest email', function () {
    onGuestMode();

    $video = \video();

    livewire(CreateCommentForm::class, ['model' => $video])
        ->set('guest_email', 'email')
        ->call('create')
        ->assertHasErrors(['guest_email' => 'email'])
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

test('guest_name must be unique for different ip address', function () {
    onGuestMode();

    $video = \video();

    $comment = createCommentsForGuest($video, 1, ['ip_address' => fake()->ipv4()]);

    livewire(CreateCommentForm::class, ['model' => $video])
        ->set('text', 'test')
        ->set('guest_name', $comment->guest_name)
        ->set('guest_email', fake()->email())
        ->call('create')
        ->assertHasErrors(['guest_name' => 'unique'])
        ->assertOk();
});

test('guest_name must not be unique for the same ip address', function () {
    onGuestMode();

    $video = \video();

    $comment = createCommentsForGuest($video, 1, ['ip_address' => request()->ip()]);

    livewire(CreateCommentForm::class, ['model' => $video])
        ->set('text', 'test')
        ->set('guest_name', $comment->guest_name)
        ->set('guest_email', fake()->email())
        ->call('create')
        ->assertHasNoErrors()
        ->assertOk();
});

test('guest_email must be unique for different ip address', function () {
    onGuestMode();

    $video = \video();

    $comment = createCommentsForGuest($video, 1, ['ip_address' => fake()->ipv4()]);

    livewire(CreateCommentForm::class, ['model' => $video])
        ->set('text', 'test')
        ->set('guest_name', fake()->name())
        ->set('guest_email', $comment->guest_email)
        ->call('create')
        ->assertHasErrors(['guest_email' => 'unique'])
        ->assertOk();
});

test('guest_email must not be unique for the same ip address', function () {
    onGuestMode();

    $video = \video();

    $comment = createCommentsForGuest($video, 1, ['ip_address' => request()->ip()]);

    livewire(CreateCommentForm::class, ['model' => $video])
        ->set('text', 'test')
        ->set('guest_name', fake()->name)
        ->set('guest_email', fake()->email())
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

    $comment1 = createCommentsForGuest($video, 1, ['ip_address' => request()->ip()]);
    createCommentsForGuest($video, 1, ['ip_address' => fake()->ipv4()]);

    $component = livewire(CreateCommentForm::class, ['model' => $video])
        ->assertOk();

    expect($component->get('guest_name'))
        ->toBe($comment1->guest_name)
        ->and($component->get('guest_email'))
        ->toBe($comment1->guest_email);
});

it('can create comment for guest mode', function () {
    onGuestMode();

    $video = \video();

    livewire(CreateCommentForm::class, ['model' => $video])
        ->set('guest_name', 'test user')
        ->set('guest_email', 'testuser@gmail.com')
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
        ->first()->guest_name->toBe('test user')
        ->first()->guest_email->toBe('testuser@gmail.com')
        ->first()->text->toBe('test comment')
        ->first()->ip_address->toBe(request()->ip());
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
        ->first()->text->toBe('test comment')
        ->first()->ip_address->toBe(request()->ip())
        ->and($user->comments)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(1)
        ->first()->toBeInstanceOf(Comment::class)
        ->first()->commenter_id->toBe($user->getKey())
        ->first()->commenter_type->toBe(User::class)
        ->first()->text->toBe('test comment')
        ->first()->ip_address->toBe(request()->ip());
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
    $video->comments()->create([
        'text' => Str::random(),
        'ip_address' => request()->ip(),
    ]);

    $c = livewire(CreateCommentForm::class, ['model' => $video])
        ->set('text', 'test comment')
        ->set('guest_name', 'guest')
        ->set('guest_email', 'gues@mail.com');


    if ($shouldLimit) {
        expect(fn() => $c
            ->call('create')
            ->assertHasNoErrors()
            ->assertOk()
        )
            ->toThrow(CommentLimitExceeded::class)
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
        expect(fn() => $c
            ->call('create')
            ->assertHasNoErrors()
            ->assertOk()
        )
            ->toThrow(CommentLimitExceeded::class)
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

    $comment = createCommentsForGuest($video, 1, ['ip_address' => request()->ip()]);

    livewire(CreateCommentForm::class, ['model' => $video])
        ->set('text', 'test')
        ->set('guest_name', 'lakm')
        ->set('guest_email', fake()->email())
        ->call('create')
        ->assertHasNoErrors()
        ->assertOk();

    $comments = Comment::all()->unique('guest_name')->pluck('guest_name');

    expect($comments)
        ->toHaveCount(1)
        ->and($comments[0])
        ->toBe('lakm');
});

it('can change the guest email for all the same ip address', function () {
    onGuestMode();

    $video = \video();

    $comment = createCommentsForGuest($video, 1, ['ip_address' => request()->ip()]);

    livewire(CreateCommentForm::class, ['model' => $video])
        ->set('text', 'test')
        ->set('guest_name', 'lakm')
        ->set('guest_email', 'lakm@gmail.com')
        ->call('create')
        ->assertHasNoErrors()
        ->assertOk();

    $comments = Comment::all()->unique('guest_email')->pluck('guest_email');

    expect($comments)
        ->toHaveCount(1)
        ->and($comments[0])
        ->toBe('lakm@gmail.com');
});