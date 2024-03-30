<?php


use LakM\Comments\Tests\Fixtures\Post;

it('can create a comment', function () {
    $post = Post::create(['name' => 'post1']);

    $post->comments()->create(['text' => 'comment1']);

    expect($post->comments)
        ->toHaveCount(1)
        ->first()
        ->text->toBe('comment1');
});