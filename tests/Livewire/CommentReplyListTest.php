<?php

use LakM\Comments\Enums\Sort;
use LakM\Comments\Livewire\CommentReplyList;

use function Pest\Laravel\travel;
use function Pest\Livewire\livewire;

it('can sort comment\'s reply list by latest', function () {
    config(['comments.guest_mode.enabled' => true]);
    config(['comments.approval_required' => false]);

    $video = video();

    $comment = createCommentsForGuest($video);

    createCommentRepliesForGuestMode($comment, 1, ['text' => 'a']);

    travel(5)->minutes();

    createCommentRepliesForGuestMode($comment, 1, ['text' => 'b']);


    livewire(CommentReplyList::class, ['comment' => $comment, 'relatedModel' => $video, 'total' => 2])
        ->set('sortBy', Sort::LATEST)
        ->assertSeeTextInOrder(['b', 'a'])
        ->assertOk();
});

it('can sort comment\'s reply list by oldest', function () {
    config(['comments.guest_mode.enabled' => true]);
    config(['comments.approval_required' => false]);

    $video = video();

    $comment = createCommentsForGuest($video);

    createCommentRepliesForGuestMode($comment, 1, ['text' => 'a']);

    travel(5)->minutes();

    createCommentRepliesForGuestMode($comment, 1, ['text' => 'b']);


    livewire(CommentReplyList::class, ['comment' => $comment, 'relatedModel' => $video, 'total' => 2])
        ->set('sortBy', Sort::OLDEST)
        ->assertSeeTextInOrder(['a', 'b'])
        ->assertOk();
});
