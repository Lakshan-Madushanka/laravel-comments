<?php

namespace LakM\Comments\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use LakM\Comments\Concerns\Commentable;
use LakM\Comments\Contracts\CommentableContract;

class Video extends Model implements CommentableContract
{
    use Commentable;

    protected $guarded = [];
}
