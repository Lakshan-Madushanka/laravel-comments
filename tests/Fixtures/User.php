<?php

namespace LakM\Comments\Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Authenticatable;
use LakM\Comments\Concerns\Commenter;
use LakM\Comments\Contracts\CommenterContract;

class User extends Authenticatable implements CommenterContract
{
    use Commenter;

    protected $guarded = [];
}
