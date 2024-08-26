<?php

use Illuminate\Pagination\LengthAwarePaginator;
use LakM\Comments\Livewire\CommentList;
use LakM\Comments\Models\Comment;

use function Pest\Laravel\travel;
use function Pest\Livewire\livewire;

it('can render comment list in auth mode', function () {
    onGuestMode(false);

    $user = actAsAuth();

    $video = video();

    createCommentsForAuthUser($user, $video);

    livewire(CommentList::class, ['model' => $video])
        ->assertSee($user->getAuthIdentifierName())
        ->assertOk();
});

it('can render paginated comment list for auth user', function ($count) {
    onGuestMode(false);
    config(['comments.approval_required' => false]);
    config(['comments.pagination.per_page' => $count]);

    $user = actAsAuth();
    $video = \video();

    createCommentsForAuthUser($user, $video, 5);

    livewire(CommentList::class, ['model' => $video])
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

    $comments = createCommentsForGuest($video, 5);
    $comments->load('commenter');

    livewire(CommentList::class, ['model' => $video])
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

    livewire(CommentList::class, ['model' =>  $video])
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

it('can sort comments', function () {
    config(['comments.guest_mode.enabled' => true]);
    config(['comments.approval_required' => false]);

    $video = \video();

    createCommentsForGuest(relatedModel: $video, data: ['text' => 'a']);

    travel(5)->minutes();

    createCommentsForGuest(relatedModel: $video, data: ['text' => 'b']);

    livewire(CommentList::class, ['model' => $video])
        ->set('sortBy', 'latest')
        ->assertSeeTextInOrder(['b', 'a']);
});
