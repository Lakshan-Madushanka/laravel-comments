<?php

use LakM\Comments\Livewire\ReactionsManager;
use function Pest\Livewire\livewire;

it('remove already existing like for auth mode', function () {
    onGuestMode(false);

    $user = actAsAuth();
    $comment = createCommentsForAuthUser($user, video());
    createReaction($comment->getKey(), 'like', $user->id);

    expect($comment->reactions)->toHaveCount(1);

    livewire(ReactionsManager::class, ['comment' => $comment, 'relatedModel' => video()])
        ->call('handle', type: 'like')
        ->assertOk();

    $comment->refresh();

    expect($comment->reactions)->toHaveCount(0);
});

it('can create like for auth mode', function () {
    $user = actAsAuth();
    $comment = createCommentsForAuthUser($user, video());

    livewire(ReactionsManager::class, ['comment' => $comment, 'relatedModel' => video()])
        ->call('handle', type: 'like')
        ->assertOk();

    $comment->refresh();

    expect($comment->reactions)
        ->toHaveCount(1)
        ->first()->type->toBe('like')
        ->first()->user_id->toBe($user->getKey());
});

it('can create like when already has disliked for auth mode', function () {
    onGuestMode(false);

    $user = actAsAuth();
    $comment = createCommentsForAuthUser($user, video());
    createReaction($comment->getKey(), 'dislike', $user->id);

    expect($comment->reactions)
        ->toHaveCount(1)
        ->first()->type->toBe('dislike');

    livewire(ReactionsManager::class, ['comment' => $comment, 'relatedModel' => video()])
        ->call('handle', type: 'like')
        ->assertOk();

    $comment->refresh();

    expect($comment->reactions)
        ->toHaveCount(1)
        ->first()->type->toBe('like')
        ->first()->user_id->toBe($user->getKey());
});

it('remove already existing like for guest mode', function () {
    onGuestMode();

    $comment = createCommentsForGuest(video());
    createReaction($comment->getKey(), 'like');

    expect($comment->reactions)->toHaveCount(1);

    livewire(ReactionsManager::class, ['comment' => $comment, 'relatedModel' => video()])
        ->call('handle', type: 'like')
        ->assertOk();

    $comment->refresh();

    expect($comment->reactions)->toHaveCount(0);
});

it('can create like for guest mode', function () {
    onGuestMode();

    $comment = createCommentsForGuest(video());

    livewire(ReactionsManager::class, ['comment' => $comment, 'relatedModel' => video()])
        ->call('handle', type: 'like')
        ->assertOk();

    $comment->refresh();

    expect($comment->reactions)
        ->toHaveCount(1)
        ->first()->type->toBe('like')
        ->first()->user_id->toBe(null);
});

it('can create like when already has disliked for guest mode', function () {
    onGuestMode();

    $comment = createCommentsForGuest(video());
    createReaction($comment->getKey(), 'dislike');

    expect($comment->reactions)
        ->toHaveCount(1)
        ->first()->type->toBe('dislike');

    livewire(ReactionsManager::class, ['comment' => $comment, 'relatedModel' => video()])
        ->call('handle', type: 'like')
        ->assertOk();

    $comment->refresh();

    expect($comment->reactions)
        ->toHaveCount(1)
        ->first()->type->toBe('like')
        ->first()->user_id->toBe(null);
});

