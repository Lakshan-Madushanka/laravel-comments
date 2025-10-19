<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use LakM\Commenter\Models\Comment;
use LakM\Commenter\Models\Guest;
use LakM\Commenter\Models\Reaction;
use LakM\Commenter\Models\Reply;
use LakM\Commenter\Tests\Fixtures\Post;
use LakM\Commenter\Tests\Fixtures\User;
use LakM\Commenter\Tests\Fixtures\Video;
use LakM\Commenter\Tests\TestCase;

use function Pest\Laravel\actingAs;

uses(TestCase::class, LazilyRefreshDatabase::class)->in('');

function actAsGuest(): Guest
{
    $guest = \guest(true);

    actingAs($guest, 'guest');

    return $guest;
}

function actAsAuth(): User
{
    $user = user();

    actingAs($user);

    return $user;
}

function user(): User
{
    return User::create(['name' => fake()->name(), 'email' => fake()->email()]);
}

function guest(bool $forCurrentUser = false): Guest
{
    return Guest::query()
        ->firstOrCreate(
            ['ip_address' => $forCurrentUser ? request()->ip() : fake()->ipv4()],
            [
                'name' => fake()->name(),
                'email' => fake()->email(),
                'ip_address' => $forCurrentUser ? request()->ip() : fake()->ipv4(),
            ]
        );
}

function post(): Post
{
    return Post::create(['name' => 'post']);
}

function video(): Video
{
    return Video::create(['name' => 'post']);
}

function onGuestMode($status = true, bool $secured = false): void
{
    config(['commenter.guest_mode.enabled' => $status]);
    config(['commenter.guest_mode.secured' => $secured]);
}

function approvalRequired($comment = false, $reply = false): void
{
    config(['commenter.approval_required' => $comment]);
    config(['commenter.reply.approval_required' => $reply]);
}

function setPaginateForComments(int $count = null): void
{
    if (is_null($count)) {
        config(['commenter.pagination.enabled' => false]);
        return;
    }

    config(['commenter.pagination.per_page' => $count]);
}

function setPaginateForReplies(int $count = null): void
{
    if (is_null($count)) {
        config(['commenter.reply.pagination.enabled' => false]);
        return;
    }

    config(['commenter.reply.pagination.per_page' => $count]);
}

function createCommentsForAuthUser(User $user, Model $relatedModel, int $count = 1, array $data = []): Model|Collection
{
    for ($i = 0; $i < $count; $i++) {
        $comment = $relatedModel->comments()->create([
            'text' => Str::random(),
            ...$data
        ]);
        $user->comments()->save($comment);
    }

//    if ($data['text'] ?? '' === 'pin comment') {
//        //dd('laksha');
//
//        dd($user->comments->toArray());
//    }

    return $count === 1 ? $user->comments()->orderByDesc('id')->first() : $user->comments;
}

function createCommentsForGuest(Model $relatedModel, int $count = 1, array $data = [], bool $forCurrentUser = false, Guest $guest = null): Comment|Collection
{
    for ($i = 0; $i < $count; $i++) {
        $comment = $relatedModel->comments()->create([
            'text' => Str::random(),
            ...$data
        ]);

        if (is_null($guest)) {
            $guest = guest($forCurrentUser);
        }

        $guest->comments()->save($comment);
    }

    if ($count === 1) {
        return $comment;
    }

    $comments = Comment::all();


    return $comments;
}

function createCommentRepliesForGuestMode(Comment $comment, int $count = 1, array $data = [], bool $forCurrentUser = false): Reply|Collection
{
    $email = fake()->email();
    $name = fake()->name();

    for ($i = 0; $i < $count; $i++) {
        $reply = $comment->replies()->create([
            'text' => Str::random(),
            ...$data,
        ]);

        $guest = guest($forCurrentUser);
        $guest->comments()->save($reply);
    }


    $replies = $comment->replies()->get();

    if ($count === 1) {
        return $replies->last();
    }

    return $replies;
}

function createCommentRepliesForAuthMode(Comment $comment, User $user, int $count = 1, array $data = []): Reply|Collection
{
    for ($i = 0; $i < $count; $i++) {
        $reply = $comment->replies()->create([
            'text' => Str::random(),
            ...$data,
        ]);

        $user->comments()->save($reply);
    }

    $replies = $comment->replies()->orderBy('id')->get();

    if ($count === 1) {
        return $replies->last();
    }

    return $replies;
}

function createNestedRepliesForGuestMode(Reply $reply, int $count = 1, array $data = [], bool $forCurrentUser = false): Reply|Collection
{
    $email = fake()->email();
    $name = fake()->name();


    for ($i = 0; $i < $count; $i++) {
        $nestedReply = $reply->replies()->create([
            'text' => Str::random(),
            ...$data,
        ]);

        $guest = guest($forCurrentUser);

        $nestedReply->commenter()->associate($guest)->save();
    }

    $replies = $reply->fresh()->replies;

    if ($count === 1) {
        return $replies->last();
    }

    return $replies;
}

function createReactionForGuestMode(Comment $comment, string $type, int $count = 1, bool $forCurrentUser = false): Reaction|Collection
{
    for ($i = 0; $i < $count; $i++) {
        $guest = guest($forCurrentUser);

        $reaction = $guest
            ->reactions()
            ->create([
                'type' => $type,
                'comment_id' => $comment->getKey(),
            ]);
    }

    if ($count === 1) {
        return $reaction;
    }

    return Reaction::all();
}

function createReactionForAuthMode(Comment $comment, User $user, string $type, int $count = 1): Reaction|Collection
{
    for ($i = 0; $i < $count; $i++) {
        $reaction = $comment
            ->reactions()
            ->create([
                'type' => $type,
                'owner_id' => $user->getKey(),
                'owner_type' => $user->getMorphClass(),
            ]);
    }

    if ($count === 1) {
        return $reaction;
    }

    return Reaction::all();
}

function canPinMessage(bool $comment = true, bool $reply = true): void
{
    config(['commenter.pin.enable_comment' => $comment]);
    config(['commenter.pin.enable_reply' => $reply]);
}

function authorizePinMessage(bool $status = true): void
{
    Gate::define('pin-message', fn () => true);
}
