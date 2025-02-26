<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use LakM\Comments\Enums\Sort;
use LakM\Comments\Livewire\CommentReplyList;

use LakM\Comments\Models\Reply;
use Pest\Expectation;

use function Pest\Laravel\travel;
use function Pest\Livewire\livewire;

it('can render comment reply list in auth mode', function () {
    onGuestMode(false);
    approvalRequired();

    $user = actAsAuth();

    $video = video();

    $text = Str::random();

    $comment = createCommentsForAuthUser($user, $video);
    createCommentRepliesForAuthMode(comment: $comment, user: $user, data: ['text' => $text]);

    livewire(CommentReplyList::class, ['comment' => $comment, 'relatedModel' => $video, 'total' => 1])
        ->call('setShowStatus')
        ->assertSee($user->getAuthIdentifierName())
        ->assertSeeText($text)
        ->assertOk();
});

it('can render comment reply list in guest mode', function () {
    onGuestMode();
    approvalRequired();

    user();

    $video = video();

    $text = Str::random();

    $comment = createCommentsForGuest(relatedModel: $video);
    $reply = createCommentRepliesForGuestMode(comment: $comment, data: ['text' => $text]);

    livewire(CommentReplyList::class, ['comment' => $comment, 'relatedModel' => $video, 'total' => 1])
        ->call('setShowStatus')
        ->assertSeeText(Str::limit($reply->ownerName(false), 10))
        ->assertSeeText($text)
        ->assertOk();
});

it('can render paginated replies list for auth user', function ($count) {
    onGuestMode(false);
    approvalRequired();
    setPaginateForReplies($count);

    $user = actAsAuth();
    $video = \video();

    $comment = createCommentsForAuthUser($user, $video);
    createCommentRepliesForAuthMode(comment: $comment, user: $user, count: 5);

    livewire(CommentReplyList::class, ['comment' => $comment, 'relatedModel' => $video, 'total' => $count])
        ->call('setShowStatus')
        ->assertViewHas('replies', function (LengthAwarePaginator $comments) use ($count) {
            expect($comments)
                ->toHaveCount($count)
                ->first()
                ->toBeInstanceOf(Reply::class);
            return true;
        })
        ->assertOk();
})->with([
    1,
    2,
]);

it('can render paginated replies list for guest mode', function ($count) {
    onGuestMode();
    approvalRequired();
    setPaginateForReplies($count);

    $user = actAsAuth();
    $video = \video();

    $comment = createCommentsForGuest($video);
    createCommentRepliesForGuestMode(comment: $comment, count: 5);

    livewire(CommentReplyList::class, ['comment' => $comment, 'relatedModel' => $video, 'total' => $count])
        ->call('setShowStatus')
        ->assertViewHas('replies', function (LengthAwarePaginator $comments) use ($count) {
            expect($comments)
                ->toHaveCount($count)
                ->first()
                ->toBeInstanceOf(Reply::class);
            return true;
        })
        ->assertOk();
})->with([
    1,
    2,
]);

it('render only approved reply list in guest mode', function () {
    onGuestMode();
    approvalRequired(reply: true);

    $user = actAsAuth();

    $video = video();

    $text = Str::random();

    $comment = createCommentsForAuthUser(user: $user, relatedModel: $video);
    createCommentRepliesForAuthMode(comment: $comment, user: $user, count: 5);

    $reply = createCommentRepliesForGuestMode(comment: $comment, data: ['text' => $text, 'approved' => true]);

    livewire(CommentReplyList::class, ['comment' => $comment, 'relatedModel' => $video, 'total' => 1])
        ->call('setShowStatus')
        ->assertViewHas('replies', function (LengthAwarePaginator $replies) use ($reply, $text) {
            expect($replies)
                ->toHaveCount(1)
                ->each(function (Expectation $expect) use ($reply, $text) {
                    $expect->toBeInstanceOf(Reply::class)
                    ->text->toBe($reply->text);
                });

            return true;
        })
        ->assertOk();
});

it('render only approved reply list in auth mode', function () {
    onGuestMode(false);
    approvalRequired(reply: true);

    user();

    $video = video();

    $text = Str::random();

    $comment = createCommentsForGuest(relatedModel: $video);
    createCommentRepliesForGuestMode(comment: $comment, count: 5);

    $reply = createCommentRepliesForGuestMode(comment: $comment, data: ['text' => $text, 'approved' => true]);

    livewire(CommentReplyList::class, ['comment' => $comment, 'relatedModel' => $video, 'total' => 1])
        ->call('setShowStatus')
        ->assertViewHas('replies', function (LengthAwarePaginator $replies) use ($reply, $text) {
            expect($replies)
                ->toHaveCount(1)
                ->each(function (Expectation $expect) use ($reply, $text) {
                    $expect->toBeInstanceOf(Reply::class)
                        ->text->toBe($reply->text);
                });

            return true;
        })
        ->assertOk();
});

it('can sort comment\'s reply list by latest', function () {
    onGuestMode();
    approvalRequired();
    setPaginateForReplies();

    $video = video();

    $comment = createCommentsForGuest($video);

    $text1 = 'a' . Str::random();
    $text2 = 'b' . Str::random();

    createCommentRepliesForGuestMode($comment, 1, ['text' => $text1]);

    travel(5)->minutes();

    createCommentRepliesForGuestMode($comment, 1, ['text' => $text2]);

    livewire(CommentReplyList::class, ['comment' => $comment, 'relatedModel' => $video, 'total' => 2])
        ->set('sortBy', Sort::LATEST)
        ->assertSeeTextInOrder([$text2, $text1])
        ->assertOk();
});

it('can sort comment\'s reply list by oldest', function () {
    onGuestMode();
    approvalRequired();
    setPaginateForReplies();

    $video = video();

    $comment = createCommentsForGuest($video);

    $text1 = 'a' . Str::random();
    $text2 = 'b' . Str::random();

    createCommentRepliesForGuestMode($comment, 1, ['text' => $text1]);

    travel(5)->minutes();

    createCommentRepliesForGuestMode($comment, 1, ['text' => $text2]);

    livewire(CommentReplyList::class, ['comment' => $comment, 'relatedModel' => $video, 'total' => 2])
        ->set('sortBy', Sort::OLDEST)
        ->assertSeeTextInOrder([$text1, $text2])
        ->assertOk();
});

it('can filter current user replies', function () {
    onGuestMode();
    approvalRequired();
    setPaginateForReplies();

    $video = video();

    $comment = createCommentsForGuest($video);

    $reply = createCommentRepliesForGuestMode(comment: $comment, forCurrentUser: true);

    createCommentRepliesForGuestMode(comment: $comment, count: 5);

    createCommentRepliesForGuestMode(comment: $comment);

    livewire(CommentReplyList::class, ['comment' => $comment, 'relatedModel' => $video, 'total' => 2])
        ->set('filter', 'own')
        ->assertViewHas('replies', function (Collection $replies) use ($reply) {
            expect($replies)
                ->toHaveCount(1)
                ->first()
                ->id
                ->toBe($reply->getKey());

            return true;
        })
        ->assertOk();
});
