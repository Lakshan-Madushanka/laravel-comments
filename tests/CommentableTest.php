<?php


use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Model;
use LakM\Comments\Concerns\Commentable;
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

   expect($post->canCreateComment($post))->toThrow(AuthenticationException::class);

    actAsAuth();

    expect($post->canCreateComment($post))->toBeTrue();
})->throws(AuthenticationException::class);

it('can authorize to create comment in guest mode', function () {
    $post = new Post();
    $post->guestMode = true;

    expect($post->canCreateComment($post))->toBeTrue();
});

it('takes priority guest mode of the model over guest mode in config', function () {
    config(['comments.guest_mode.enabled' => true]);

    $post = new Post();
    $post->guestMode = false;

    expect($post->canCreateComment($post))->toThrow(AuthenticationException::class);
})->throws(AuthenticationException::class);

test('commentCanCreate method takes highest priority', function () {
    $post = new class extends Model {
        use Commentable;

        public bool $guestMode = false;

        public function commentCanCreate(): bool
        {
            return true;
        }
    };

    expect($post->canCreateComment($post))->toBeTrue();
});

