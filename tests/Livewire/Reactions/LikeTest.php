<?php

use LakM\Comments\Livewire\ReactionsManager;

use function Pest\Livewire\livewire;

it('remove already existing like for auth mode', function () {
    onGuestMode(false);

    $user = actAsAuth();
    $comment = createCommentsForAuthUser($user, video());
    createReactionForAuthMode(comment: $comment, user: $user, type: 'like');

    expect($comment->reactions)->toHaveCount(1);

    livewire(ReactionsManager::class, ['comment' => $comment, 'relatedModel' => video()])
        ->call('handle', type: 'like')
        ->assertOk();

    $comment->refresh();

    expect($comment->reactions)->toHaveCount(0);
});

it('can create like for auth mode', function () {
    onGuestMode(false);

    $user = actAsAuth();

    $comment = createCommentsForAuthUser($user, video());

    livewire(ReactionsManager::class, ['comment' => $comment, 'relatedModel' => video()])
        ->call('handle', type: 'like')
        ->assertOk();

    $comment->refresh();

    expect($comment->reactions)
        ->toHaveCount(1)
        ->first()->type->toBe('like')
        ->first()->owner_id->toBe($user->getKey())
        ->first()->owner_type->toBe($user->getMorphClass());
});

it('can create like when already has disliked for auth mode', function () {
    onGuestMode(false);

    $user = actAsAuth();
    $comment = createCommentsForAuthUser($user, video());
    createReactionForAuthMode(comment: $comment, user: $user, type: 'dislike');

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
        ->first()->owner_type->toBe($user->getMorphClass())
        ->first()->owner_id->toBe($user->getKey());
});

it('remove already existing like for guest mode', function () {
    onGuestMode();

    $comment = createCommentsForGuest(video());
    createReactionForGuestMode(comment: $comment, type: 'like', forCurrentUser: true);

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
    createReactionForGuestMode(comment: $comment, type: 'dislike', forCurrentUser: true);

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
