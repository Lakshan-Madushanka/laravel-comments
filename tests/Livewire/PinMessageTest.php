<?php

use Illuminate\Database\Eloquent\Relations\MorphMany;
use LakM\Commenter\Livewire\PinMessageHandler;
use LakM\Commenter\Models\Comment;

use function Pest\Livewire\livewire;

it('can render the pin component', function () {
    $video = video();
    $comment = createCommentsForAuthUser(user(), $video);

    livewire(PinMessageHandler::class, ['commentable' => $video, 'msg' => $comment])
        ->assertSeeText(__('Pin'))
        ->assertOk();
});

it('wont throws unauthorize exception when unauthorized users trying to pin comment', function () {
    $video = video();
    $comment = createCommentsForAuthUser(user(), $video);

    livewire(PinMessageHandler::class, ['commentable' => $video, 'msg' => $comment])
        ->call('pin')
        ->assertForbidden();
});


it('can authorize user to pin message', function () {
    authorizePinMessage();

    $video = video();
    $comment = createCommentsForAuthUser(user(), $video);

    livewire(PinMessageHandler::class, ['commentable' => $video, 'msg' => $comment])
        ->call('pin')
        ->assertOk();
});

it('can pin comment', function () {
    authorizePinMessage();

    $user = user();
    $video = video();

    $comment = createCommentsForAuthUser($user, $video);

    livewire(PinMessageHandler::class, ['commentable' => $video, 'msg' => $comment])
        ->call('pin')
        ->assertDispatched('message-pinned')
        ->assertOk();

    $pinComment = $video->comments()->where(['is_pinned' => true])->first();


    expect($pinComment)->not()->toBeNull()
        ->and($pinComment->commentable_type)->toBe($video->getMorphClass())
        ->and($pinComment->commentable_id)->toBe($video->getKey());
});

it('remove previous pinned comment when new comment is pinned', function () {
    authorizePinMessage();

    $user = user();
    $video = video();

    $comment1 = createCommentsForAuthUser($user, $video);
    $comment1->is_pinned = true;
    $comment1->save();

    $comment2 = createCommentsForAuthUser($user, $video, 1, ['text' => 'pin comment']);

    livewire(PinMessageHandler::class, ['commentable' => $video, 'msg' => $comment2])
        ->call('pin')
        ->assertOk();

    $pinComments = $video->comments()->where(['is_pinned' => true])->get();

    expect($pinComments)->toHaveCount(1);

    $pinComment = $pinComments->where('text', 'pin comment')->first();

    expect($pinComment)->not()->toBeNull()
        ->and($pinComment->commentable_type)->toBe($video->getMorphClass())
        ->and($pinComment->is_pinned)->toBeTrue()
        ->and($pinComment->commentable_id)->toBe($video->getKey());
});

it('remove previous pinned reply when new comment is pinned', function () {
    authorizePinMessage();

    $user = user();
    $video = video();

    $comment = createCommentsForAuthUser($user, $video);

    $reply = createCommentRepliesForAuthMode($comment, $user, 1);
    $reply->is_pinned = true;
    $reply->save();

    livewire(PinMessageHandler::class, ['commentable' => $video, 'msg' => $comment])
        ->call('pin')
        ->assertOk();

    $pinReplies = $video->comments()->with(['replies' => function (MorphMany $query) {
        $query->where('is_pinned', true);
    }])->get();

    expect($pinReplies->pluck('replies')->collapse()->toArray())->toBeEmpty();

    $pinComments = $video->comments;

    expect($pinComments)->toHaveCount(1);

    $pinComment = $pinComments->first();

    expect($pinComment)->not()->toBeNull()
        ->and($pinComment->commentable_type)->toBe($video->getMorphClass())
        ->and($pinComment->is_pinned)->toBeTrue()
        ->and($pinComment->commentable_id)->toBe($video->getKey());
});

it('can pin reply', function () {
    authorizePinMessage();

    $user = user();
    $video = video();

    $comment = createCommentsForAuthUser($user, $video);

    $reply = createCommentRepliesForAuthMode($comment, $user);

    livewire(PinMessageHandler::class, ['commentable' => $video, 'msg' => $reply])
        ->call('pin')
        ->assertDispatched('message-pinned')
        ->assertOk();

    $pinReply = Comment::query()->where(['is_pinned' => true])->first();

    expect($pinReply)->not()->toBeNull()
        ->and($pinReply->commentable_type)->toBeNull()
        ->and($pinReply->reply_id)->tobe($comment->getKey())
        ->and($pinReply->commentable_id)->toBeNull()
        ->and($pinReply->is_pinned)->toBeTrue();
});

it('remove previous pinned reply when new reply is pinned', function () {
    authorizePinMessage();

    $user = user();
    $video = video();

    $comment = createCommentsForAuthUser($user, $video);

    $reply = createCommentRepliesForAuthMode($comment, $user);
    $reply->is_pinned = true;
    $reply->save();

    $reply2 = createCommentRepliesForAuthMode($comment, $user, 1, ['text' => 'pin reply']);

    livewire(PinMessageHandler::class, ['commentable' => $video, 'msg' => $reply2])
        ->call('pin')
        ->assertOk();

    $pinReplies = Comment::query()->where(['is_pinned' => true])->get();

    expect($pinReplies)->toHaveCount(1);

    $pinReply = $pinReplies->where('text', 'pin reply')->first();

    expect($pinReply)->not()->toBeNull()
        ->and($pinReply->commentable_type)->toBeNull()
        ->and($pinReply->reply_id)->tobe($comment->getKey())
        ->and($pinReply->commentable_id)->toBeNull()
        ->and($pinReply->is_pinned)->toBeTrue();
});

it('remove previous pinned comment when new reply is pinned', function () {
    authorizePinMessage();

    $user = user();
    $video = video();

    $comment = createCommentsForAuthUser($user, $video);
    $comment->is_pinned = true;
    $comment->save();

    $reply = createCommentRepliesForAuthMode($comment, $user);

    livewire(PinMessageHandler::class, ['commentable' => $video, 'msg' => $reply])
        ->call('pin')
        ->assertOk();

    $pinReplies = Comment::query()->where(['is_pinned' => true])->get();

    expect($pinReplies)->toHaveCount(1);

    $pinReply = $pinReplies->first();

    expect($pinReply)->not()->toBeNull()
        ->and($pinReply->commentable_type)->toBeNull()
        ->and($pinReply->commentable_id)->toBeNull()
        ->and($pinReply->reply_id)->toBe($comment->getKey())
        ->and($pinReply->is_pinned)->toBeTrue();
});

it('remove already pinned message', function () {
    authorizePinMessage();

    $user = user();
    $video = video();

    $comment = createCommentsForAuthUser($user, $video);

    $comment->is_pinned = true;
    $comment->save();

    livewire(PinMessageHandler::class, ['commentable' => $video, 'msg' => $comment])
        ->call('pin')
        ->assertOk();

    $pinReplies = Comment::query()->where(['is_pinned' => true])->get();

    expect($pinReplies)->toHaveCount(0);
});
