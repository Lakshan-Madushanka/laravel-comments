<?php

namespace LakM\Comments\Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use LakM\Comments\concerns\Commentable;

class User extends Authenticatable
{
    protected $guarded = [];
}
