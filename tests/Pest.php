<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;
use LakM\Comments\Models\Comment;
use LakM\Comments\Models\Reaction;
use LakM\Comments\Tests\Fixtures\Post;
use LakM\Comments\Tests\Fixtures\User;
use LakM\Comments\Tests\Fixtures\Video;
use LakM\Comments\Tests\TestCase;
use function Pest\Laravel\actingAs;

uses(TestCase::class, LazilyRefreshDatabase::class)->in('');

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

function post(): Post
{
    return Post::create(['name' => 'post']);
}

function video(): Video
{
    return Video::create(['name' => 'post']);
}

function onGuestMode($status = true): void
{
    config(['comments.guest_mode.enabled' => $status]);
}

function createCommentsForAuthUser(User $user, Model $relatedModel, int $count = 1, array $data =  []): Model|Collection
{
    for ($i = 0; $i < $count; $i++) {
        $comment = $relatedModel->comments()->create([
            'text' => Str::random(),
            ...$data
        ]);
        $user->comments()->save($comment);
    }

    return $count === 1 ? $user->comments[0] : $user->comments;
}

function createCommentsForGuest(Model $relatedModel, int $count = 1, array $data =  []): Comment|Collection
{
    $email = fake()->email();
    $name = fake()->name();

    for ($i = 0; $i < $count; $i++) {
        $relatedModel->comments()->create([
            'text' => Str::random(),
            'guest_name' => $name,
            'guest_email' => $email,
            ...$data,
        ]);
    }

    $comments =  Comment::where('guest_email', $email)->get();

    if ($comments->count() === 1){
        return  $comments[0];
    }

    return $comments;
}

function createReaction(int $commentId, string $type, ?int $userId = null, int $count = 1, array $data = []): Reaction|Collection
{
    for ($i = 0; $i < $count; $i++) {
        Reaction::query()->create([
            'comment_id' => $commentId,
            'type' => $type,
            'user_id' => $userId,
            'ip_address' => request()->ip(),
            ...$data
        ]);
    }

    if ($count === 1) {
        return Reaction::query()->first();
    }

    return Reaction::all();
}
