<?php

namespace LakM\Commenter\Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Authenticatable;
use LakM\Commenter\Concerns\Commenter;
use LakM\Commenter\Contracts\CommenterContract;

class User extends Authenticatable implements CommenterContract
{
    use Commenter;

    protected $guarded = [];
}
