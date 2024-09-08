<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use LakM\Comments\Enums\Sort;
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

it('only shows approved comments in guest mode when enabled in config', function ($approval) {
    onGuestMode();
    approvalRequired(comment: $approval);

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

it('only shows approved comments in auth mode when enabled in config', function ($approval) {
    onGuestMode(false);
    approvalRequired(comment: $approval);

    $user = actAsAuth();

    $video = \video();

    createCommentsForAuthUser(user: $user, relatedModel: $video, count: 2);
    createCommentsForAuthUser(user: $user, relatedModel: $video, data: ['approved' => true]);

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
    onGuestMode();
    approvalRequired();
    setPaginateForComments();

    $video = \video();

    $text1 = 'a' . Str::random();
    $text2 = 'b' . Str::random();

    createCommentsForGuest(relatedModel: $video, data: ['text' => $text1]);

    travel(5)->minutes();

    createCommentsForGuest(relatedModel: $video, data: ['text' => $text2]);

    livewire(CommentList::class, ['model' => $video])
     ->set('sortBy', Sort::LATEST)
       ->assertViewHas('comments', function (Collection $comments) use ($text1, $text2) {
           $expect = [$text2, $text1];
           return $expect === $comments->pluck('text')->toArray();
       })
        ->assertOk();
});

it('can filter current user comments', function () {
    onGuestMode();
    approvalRequired();
    setPaginateForComments();

    $video = video();

    createCommentsForGuest(relatedModel: $video, count: 5);

    $comment = createCommentsForGuest(relatedModel: $video, forCurrentUser: true);

    livewire(CommentList::class, ['model' => $video])
        ->set('filter', 'own')
        ->assertViewHas('comments', function (Collection $comments) use ($comment) {
            expect($comments)
                ->toHaveCount(1)
                ->first()
                ->id
                ->toBe($comment->getKey());

            return true;
        })
        ->assertOk();
});
