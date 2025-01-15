<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Model;
use LakM\Comments\Concerns\Commentable;
use LakM\Comments\Enums\Sort;
use LakM\Comments\Tests\Fixtures\Post;

it('can create a comment', function () {
    $post = Post::create(['name' => 'post1']);

    $post->comments()->create(['text' => 'comment1']);

    expect($post->comments)
        ->toHaveCount(1)
        ->first()
        ->text->toBe('comment1');
});

it('can auth check', function () {
    $post = new Post();

    expect($post->authCheck())->toBeFalse();

    actAsAuth();

    expect($post->authCheck())->toBeTrue();
});

it('can authorize to create comment', function () {
    $post = new Post();

    expect($post->canCreateComment())->toThrow(AuthenticationException::class);

    $user = actAsAuth();

    expect($post->canCreateComment($user))->toBeTrue();
})->throws(AuthenticationException::class);

it('can authorize to create comment in guest mode', function () {
    $post = new Post();
    $post->guestMode = true;

    expect($post->canCreateComment())->toBeTrue();
});

it('takes priority guest mode of the model over guest mode in config', function () {
    config(['comments.guest_mode.enabled' => true]);

    $post = new Post();
    $post->guestMode = false;

    expect($post->canCreateComment())->toThrow(AuthenticationException::class);
})->throws(AuthenticationException::class);

test('commentCanCreate method takes highest priority', function () {
    $post = new class () extends Model {
        use Commentable;

        public bool $guestMode = false;

        public function commentCanCreate(): bool
        {
            return true;
        }
    };

    expect($post->canCreateComment())->toBeTrue();
});

it('can authorize to edit comment for auth mode', function () {
    onGuestMode(false);

    $user1 = actAsAuth();

    $user2 = user();

    $video = video();

    $comment1 = createCommentsForAuthUser($user2, $video);
    $comment2 = createCommentsForAuthUser($user1, $video);

    expect($video->canEditComment($comment1))->toBeFalse()
        ->and($video->canEditComment($comment2))->toBeTrue();
});

it('can authorize to edit comment for guest mode', function () {
    $video = video();

    $comment1 = createCommentsForGuest(relatedModel: $video, forCurrentUser: true);

    $comment2 = createCommentsForGuest($video);

    expect($video->canEditComment($comment2))->toBeFalse()
        ->and($video->canEditComment($comment1))->toBeTrue();
});


it('can get default comments sort order', function () {
    config(['comments.default_sort' => Sort::LATEST]);

    $video = video();

    expect($video->getCommentsSortOrder())->toBe(Sort::LATEST);
});

it('can get comments sort order defined in model', function () {
    config(['comments.default_sort' => Sort::LATEST]);

    $video = video();
    $video->commmentsSortOrder = Sort::OLDEST;

    expect($video->getCommentsSortOrder())->toBe(Sort::OLDEST);
});

it('can get default replies sort order', function () {
    config(['comments.reply.default_sort' => Sort::LATEST]);

    $video = video();

    expect($video->getRepliesSortOrder())->toBe(Sort::LATEST);
});

it('can get replies sort order defined in model', function () {
    config(['comments.default_sort' => Sort::LATEST]);

    $video = video();
    $video->commmentsSortOrder = Sort::OLDEST;

    expect($video->getCommentsSortOrder())->toBe(Sort::OLDEST);
});
