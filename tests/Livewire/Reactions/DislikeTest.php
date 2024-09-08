<?php

use LakM\Comments\Livewire\ReactionsManager;

use function Pest\Livewire\livewire;

it('remove already existing dislike for auth mode', function () {
    onGuestMode(false);

    $user = actAsAuth();
    $comment = createCommentsForAuthUser($user, video());
    createReactionForAuthMode(comment: $comment, user: $user, type: 'dislike');

    expect($comment->reactions)->toHaveCount(1);

    livewire(ReactionsManager::class, ['comment' => $comment, 'relatedModel' => video()])
        ->call('handle', type: 'dislike')
        ->assertOk();

    $comment->refresh();

    expect($comment->reactions)->toHaveCount(0);
});

it('can create dislike for auth mode', function () {
    onGuestMode(false);

    $user = actAsAuth();
    $video = video();
    $comment = createCommentsForAuthUser($user, $video);

    livewire(ReactionsManager::class, ['comment' => $comment, 'relatedModel' => $video])
        ->call('handle', type: 'dislike')
        ->assertOk();

    $comment->refresh();

    expect($comment->reactions)
        ->toHaveCount(1)
        ->first()->type->toBe('dislike')
        ->first()->owner_id->toBe($user->getKey())
        ->first()->owner_type->toBe($user->getMorphClass());
});

it('can create dislike when already has liked for auth mode', function () {
    onGuestMode(false);

    $user = actAsAuth();
    $comment = createCommentsForAuthUser($user, video());
    createReactionForAuthMode(comment: $comment, user: $user, type: 'like');

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
        ->first()->owner_id->toBe($user->getKey())
        ->first()->owner_type->toBe($user->getMorphClass());
});

it('remove already existing dislike for guest mode', function () {
    onGuestMode();

    $comment = createCommentsForGuest(video());
    createReactionForGuestMode(comment: $comment, type:'dislike', forCurrentUser: true);

    expect($comment->reactions)->toHaveCount(1);

    livewire(ReactionsManager::class, ['comment' => $comment, 'guestMode' => true, 'relatedModel' => video()])
        ->call('handle', type: 'dislike')
        ->assertOk();

    $comment->refresh();

    expect($comment->reactions)->toHaveCount(0);
});

it('can create dislike for guest mode', function () {
    onGuestMode();

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
    onGuestMode();

    $comment = createCommentsForGuest(video());
    createReactionForGuestMode(comment: $comment, type:'like', forCurrentUser: true);

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
