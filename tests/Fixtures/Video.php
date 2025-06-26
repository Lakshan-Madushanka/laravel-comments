<?php

namespace LakM\Commenter\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use LakM\Commenter\Concerns\Commentable;
use LakM\Commenter\Contracts\CommentableContract;

class Video extends Model implements CommentableContract
{
    use Commentable;

    protected $guarded = [];
}
