<?php

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use LakM\Comments\Tests\Fixtures\Post;
use LakM\Comments\Tests\Fixtures\User;
use LakM\Comments\Tests\Fixtures\Video;
use LakM\Comments\Tests\TestCase;
use function Pest\Laravel\actingAs;

uses(TestCase::class, LazilyRefreshDatabase::class)->in('');

function actAsAuth(): Authenticatable
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
