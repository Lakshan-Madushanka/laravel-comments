<?php

use LakM\Comments\Livewire\ReactionsManager;
use function Pest\Livewire\livewire;

it('remove already existing dislike for auth mode', function () {
    $user = actAsAuth();
    $comment = createCommentsForAuthUser($user, video());
    createReaction($comment->getKey(), 'dislike', $user->id);

    expect($comment->reactions)->toHaveCount(1);

    livewire(ReactionsManager::class, ['comment' => $comment, 'relatedModel' => video()])
        ->call('handle', type: 'dislike')
        ->assertOk();

    $comment->refresh();

    expect($comment->reactions)->toHaveCount(0);
});

it('can create dislike for auth mode', function () {
    $user = actAsAuth();
    $comment = createCommentsForAuthUser($user, video());

    livewire(ReactionsManager::class, ['comment' => $comment, 'relatedModel' => video()])
        ->call('handle', type: 'dislike')
        ->assertOk();

    $comment->refresh();

    expect($comment->reactions)
        ->toHaveCount(1)
        ->first()->type->toBe('dislike')
        ->first()->user_id->toBe($user->getKey());
});

it('can create dislike when already has liked for auth mode', function () {
    $user = actAsAuth();
    $comment = createCommentsForAuthUser($user, video());
    createReaction($comment->getKey(), 'like', $user->id);

    expect($comment->reactions)
        ->toHaveCount(1)
        ->first()->type->toBe('like');

    livewire(ReactionsManager::class, ['comment' => $comment, 'relatedModel' => video()])
        ->call('handle', type: 'dislike')
        ->assertOk();

    $comment->refresh();

    expect($comment->reactions)
        ->toHaveCount(1)
        ->first()->type->toBe('dislike')
        ->first()->user_id->toBe($user->getKey());
});

it('remove already existing dislike for guest mode', function () {
    $comment = createCommentsForGuest(video());
    createReaction($comment->getKey(), 'dislike');

    expect($comment->reactions)->toHaveCount(1);

    livewire(ReactionsManager::class, ['comment' => $comment, 'guestMode' => true, 'relatedModel' => video()])
        ->call('handle', type: 'dislike')
        ->assertOk();

    $comment->refresh();

    expect($comment->reactions)->toHaveCount(0);
});

it('can create dislike for guest mode', function () {
    $comment = createCommentsForGuest(video());

    livewire(ReactionsManager::class, ['comment' => $comment, 'guestMode' => true, 'relatedModel' => video()])
        ->call('handle', type: 'dislike')
        ->assertOk();

    $comment->refresh();

    expect($comment->reactions)
        ->toHaveCount(1)
        ->first()->type->toBe('dislike')
        ->first()->user_id->toBe(null);
});

it('can create dislike when already has liked for guest mode', function () {
    $comment = createCommentsForGuest(video());
    createReaction($comment->getKey(), 'like');

    expect($comment->reactions)
        ->toHaveCount(1)
        ->first()->type->toBe('like');

    livewire(ReactionsManager::class, ['comment' => $comment, 'guestMode' => true, 'relatedModel' => video()])
        ->call('handle', type: 'dislike')
        ->assertOk();

    $comment->refresh();

    expect($comment->reactions)
        ->toHaveCount(1)
        ->first()->type->toBe('dislike')
        ->first()->user_id->toBe(null);
});

