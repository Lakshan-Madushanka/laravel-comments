<?php

namespace LakM\Comments\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use LakM\Comments\Concerns\Commentable;
use LakM\Comments\Contracts\CommentableContract;

class Post extends Model implements CommentableContract
{
    use Commentable;

    /**
     * @var false|mixed
     */
    protected $guarded = [];

    public bool $guestMode = false;
}
