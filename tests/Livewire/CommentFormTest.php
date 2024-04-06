<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use LakM\Comments\Events\CommentCreated;
use LakM\Comments\Livewire\CommentForm;
use LakM\Comments\Models\Comment;
use LakM\Comments\Tests\Fixtures\Post;
use LakM\Comments\Tests\Fixtures\User;
use LakM\Comments\Tests\Fixtures\Video;
use function Pest\Livewire\livewire;

it('render comment form', function () {
    livewire(CommentForm::class, ['modelClass' => Post::class, 'modelId' => \post()->getKey()])
        ->assertOk();
});

it('does not show guest name input field when guest mode is disabled', function () {
    livewire(CommentForm::class, ['modelClass' => Post::class, 'modelId' => \post()->getKey()])
        ->assertDontSee('comment as')
        ->assertOk();
});

it('show guest name input field when guest mode is enabled', function () {
    onGuestMode();

    livewire(CommentForm::class, ['modelClass' => Video::class, 'modelId' => \video()->getKey()])
        ->assertSee('Comment as')
        ->assertOk();
});

it('can validate guest name', function () {
    onGuestMode();

    $video = \video();

    livewire(CommentForm::class, ['modelClass' => Video::class, 'modelId' => $video->getkey()])
        ->set('guest_name', '')
        ->call('create')
        ->assertHasErrors(['guest_name' => 'required'])
        ->assertOk();
});

it('can validate guest email', function () {
    onGuestMode();

    $video = \video();

    livewire(CommentForm::class, ['modelClass' => Video::class, 'modelId' => $video->getkey()])
        ->set('guest_email', 'email')
        ->call('create')
        ->assertHasErrors(['guest_email' => 'email'])
        ->assertOk();
});

it('can validate text field', function () {
    $video = \video();

    livewire(CommentForm::class, ['modelClass' => Video::class, 'modelId' => $video->getkey()])
        ->set('text', '')
        ->call('create')
        ->assertHasErrors(['text' => 'required'])
        ->assertOk();
});

it('shows login link when guest mode disabled', function () {
    $video = \video();

    livewire(CommentForm::class, ['modelClass' => Video::class, 'modelId' => $video->getkey()])
        ->assertSee('login')
        ->assertOk();
});

it('shows email field when guest mode enabled', function ($emailEnabled, $guestMode) {
    config(['comments.guest_mode.enabled' => $guestMode]);
    config(['comments.guest_mode.email_enabled' => $emailEnabled]);

    $video = \video();

    $component = livewire(CommentForm::class, ['modelClass' => Video::class, 'modelId' => $video->getkey()])
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

it('can create comment for guest mode', function () {
    onGuestMode();
    config(['comments.guest_mode.enabled' => true]);
    $video = \video();

    livewire(CommentForm::class, ['modelClass' => Video::class, 'modelId' => $video->getkey()])
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

    livewire(CommentForm::class, ['modelClass' => Video::class, 'modelId' => $video->getkey()])
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

    livewire(CommentForm::class, ['modelClass' => Video::class, 'modelId' => $video->getkey()])
        ->set('text', 'test comment')
        ->call('create')
        ->assertHasNoErrors()
        ->assertOk();

    Event::dispatch(CommentCreated::class);
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

    $c = livewire(CommentForm::class, ['modelClass' => Video::class, 'modelId' => $video->getkey()])
        ->set('text', 'test comment')
        ->set('guest_name', 'guest')
        ->set('guest_email', 'gues@mail.com')
        ->call('create')
        ->assertHasNoErrors()
        ->assertOk();

    if ($shouldLimit) {
        expect($c->get('limitExceeded'))->toBeTrue();
    } else {
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


    $c = livewire(CommentForm::class, ['modelClass' => Video::class, 'modelId' => $video->getkey()])
        ->set('text', 'test comment')
        ->assertHasNoErrors()
        ->assertOk();

    if ($shouldLimit) {
        expect($c->get('limitExceeded'))->toBeTrue();
    } else {
        expect($c->get('limitExceeded'))->toBeFalse();
    }
})->with([
    true,
    false,
]);
