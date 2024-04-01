<?php

use LakM\Comments\Tests\Fixtures\User;
use LakM\Comments\Tests\TestCase;
use function Pest\Laravel\actingAs;

uses(TestCase::class)->in('');

function actAsAuth()
{
    actingAs(User::create());
}
