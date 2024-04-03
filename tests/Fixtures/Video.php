<?php

namespace LakM\Comments\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use LakM\Comments\Concerns\Commentable;

class Video extends Model
{
    use Commentable;

    protected $guarded = [];
}
