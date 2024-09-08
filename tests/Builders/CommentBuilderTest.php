<?php


use Illuminate\Database\Eloquent\Collection;
use LakM\Comments\Models\Comment;
use LakM\Comments\Tests\Fixtures\Post;

it('can get approved comment', function () {
    $post = Post::query()->create(['name' => 'post']);

    createCommentsForGuest($post, 5);
    $approvedCmt = createCommentsForGuest(relatedModel: $post, data: ['approved' => true]);

    expect(Comment::query()->approved()->get())
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(1)
        ->first()
        ->id
        ->toBe($approvedCmt->getKey());
});

it('can check comment approval', function () {
    $post = Post::query()->create(['name' => 'post']);

    createCommentsForGuest($post, 5);
    $approvedCmt = createCommentsForGuest(relatedModel: $post, data: ['approved' => true]);

    expect(Comment::query()->approved()->get())
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(1)
        ->first()
        ->id
        ->toBe($approvedCmt->getKey());
});

it('can identify current user comments in guest mode', function () {
    onGuestMode();

    $video = video();

    createCommentsForAuthUser(user(), relatedModel: $video);

    createCommentsForGuest(relatedModel: $video);
    $comment = createCommentsForGuest(relatedModel: $video, forCurrentUser: true);

    $comments = Comment::query()
        ->currentGuest($video)
        ->get();

    expect($comments)
        ->toHaveCount(1)
        ->first()
        ->id
        ->toBe($comment->getKey());
});

it('can identify current user comments in auth mode', function () {
    onGuestMode(false);

    $video = video();
    $user = actAsAuth();

    $comment = createCommentsForAuthUser($user, relatedModel: $video);
    createCommentsForAuthUser(user(), relatedModel: $video);

    createCommentsForGuest(relatedModel: $video);

    $comments = Comment::query()
        ->currentUser($user)
        ->get();

    expect($comments)
        ->toHaveCount(1)
        ->first()
        ->id
        ->toBe($comment->getKey());
});

it('can filter current user comments in guest mode', function () {
    onGuestMode();

    $video = video();

    createCommentsForAuthUser(user(), relatedModel: $video);

    createCommentsForGuest(relatedModel: $video);
    $comment = createCommentsForGuest(relatedModel: $video, forCurrentUser: true);

    $comments = Comment::query()
        ->currentUserFilter($video, 'own')
        ->get();

    expect($comments)
        ->toHaveCount(1)
        ->first()
        ->id
        ->toBe($comment->getKey());

    $comments = Comment::query()
        ->get();

    expect($comments)
        ->toHaveCount(3);
});

it('can filter current user comments in auth mode', function () {
    onGuestMode(false);

    $video = video();
    $user = actAsAuth();

    $comment = createCommentsForAuthUser($user, relatedModel: $video);
    createCommentsForAuthUser(user(), relatedModel: $video);

    createCommentsForGuest(relatedModel: $video);

    $comments = Comment::query()
        ->currentUserFilter($video, 'own')
        ->get();

    expect($comments)
        ->toHaveCount(1)
        ->first()
        ->id
        ->toBe($comment->getKey());

    $comments = Comment::query()
        ->get();

    expect($comments)
        ->toHaveCount(3);
});
