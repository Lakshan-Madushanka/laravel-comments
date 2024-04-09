<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;
use LakM\Comments\Models\Comment;
use LakM\Comments\Tests\Fixtures\Post;
use LakM\Comments\Tests\Fixtures\User;
use LakM\Comments\Tests\Fixtures\Video;
use LakM\Comments\Tests\TestCase;
use function Pest\Laravel\actingAs;

uses(TestCase::class, LazilyRefreshDatabase::class)->in('');

function actAsAuth(): User
{
    $user = User::create();

    actingAs($user);

    return $user;
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

function createCommentsForAuthUser(User $user, Model $relatedModel, int $count = 1): Collection
{
    for ($i = 0; $i < $count; $i++) {
        $comment = $relatedModel->comments()->create(['text' => Str::random()]);
        $user->comments()->save($comment);
    }

    return $user->comments;
}

function createCommentsForGuest(Model $relatedModel, int $count = 1, bool $approved = false): Collection
{
    $email = fake()->email();
    $name = fake()->name();

    for ($i = 0; $i < $count; $i++) {
        $relatedModel->comments()->create([
            'text' => Str::random(),
            'guest_name' => $name,
            'guest_email' => $email,
            'approved' =>$approved,
        ]);
    }

    return Comment::whereEmail($email)->get();
}