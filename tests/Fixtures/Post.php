<?php

namespace LakM\Commenter\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use LakM\Commenter\Concerns\Commentable;
use LakM\Commenter\Contracts\CommentableContract;

class Post extends Model implements CommentableContract
{
    use Commentable;

    /**
     * @var false|mixed
     */
    protected $guarded = [];

    public bool $guestMode = false;
}
