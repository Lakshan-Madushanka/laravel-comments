<?php

namespace LakM\Comments\Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Authenticatable;
use LakM\Comments\Concerns\Commenter;

class User extends Authenticatable
{
    use Commenter;

    protected $guarded = [];
}
