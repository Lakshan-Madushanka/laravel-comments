<?php

use Illuminate\Pagination\LengthAwarePaginator;
use LakM\Comments\Livewire\CommentList;
use LakM\Comments\Models\Comment;
use LakM\Comments\Tests\Fixtures\Post;
use LakM\Comments\Tests\Fixtures\Video;
use function Pest\Livewire\livewire;

it('can render comment list', function () {
    livewire(CommentList::class, ['modelClass' => Post::class, 'modelId' => \post()->getKey()])
        ->assertOk();
});

it('can render paginated comment list for auth user', function ($count) {
    config(['comments.guest_mode.enabled' => false]);
    config(['comments.approval_required' => false]);
    config(['comments.pagination.per_page' => $count]);

    $user = actAsAuth();
    $video = \video();

    createCommentsForAuthUser($user, $video, 5);

    livewire(CommentList::class, ['modelClass' => Video::class, 'modelId' => $video->getKey()])
        ->assertViewHas('comments', function (LengthAwarePaginator $comments) use ($count) {
            expect($comments)
                ->toHaveCount($count)
                ->first()
                ->toBeInstanceOf(Comment::class);
            return true;
        })
        ->assertOk();
})->with([
    1,
    2,
]);

it('can render paginated comment list for guest', function ($count) {
    config(['comments.guest_mode.enabled' => true]);
    config(['comments.approval_required' => false]);
    config(['comments.pagination.per_page' => $count]);

    $video = \video();

    createCommentsForGuest($video, 5);

    livewire(CommentList::class, ['modelClass' => Video::class, 'modelId' => $video->getKey()])
        ->assertViewHas('comments', function (LengthAwarePaginator $comments) use ($count) {
            expect($comments)
                ->toHaveCount($count)
                ->first()
                ->toBeInstanceOf(Comment::class);
            return true;
        })
        ->assertOk();
})->with([
    1,
    2,
]);

it('only shows approved comments when enabled in config', function ($approval) {
    config(['comments.guest_mode.enabled' => true]);
    config(['comments.approval_required' => $approval]);

    $video = \video();

    createCommentsForGuest($video, 2);
    createCommentsForGuest($video, 1, ['approved' => true]);

    livewire(CommentList::class, ['modelClass' => Video::class, 'modelId' => $video->getKey()])
        ->assertViewHas('comments', function (LengthAwarePaginator $comments) use ($approval) {
            $e = expect($comments);

            if ($approval) {
                $e->toHaveCount(1);
            } else {
                $e->toHaveCount(3);
            }
            return true;
        })
        ->assertOk();
})->with([
    true,
    false,
]);
