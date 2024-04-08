<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use LakM\Comments\Events\CommentCreated;
use LakM\Comments\Exceptions\CommentLimitExceeded;
use LakM\Comments\Livewire\CommentForm;
use LakM\Comments\Livewire\CommentList;
use LakM\Comments\Models\Comment;
use LakM\Comments\Tests\Fixtures\Post;
use LakM\Comments\Tests\Fixtures\User;
use LakM\Comments\Tests\Fixtures\Video;
use Pest\Expectation;
use function Pest\Livewire\livewire;

it('can render comment list', function () {
    livewire(CommentList::class, ['modelClass' => Post::class, 'modelId' => \post()->getKey()])
        ->assertOk();
});

it('can render paginated comment list for auth user', function ($count) {
    config(['comments.pagination.per_page' => $count]);

    $user = actAsAuth();
    $video = \video();

    $comments = createCommentsForAuthUser($user, $video, 5);

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
    config(['comments.pagination.per_page' => $count]);

    $video = \video();

    $comments = createCommentsForGuest($video, 5);

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
