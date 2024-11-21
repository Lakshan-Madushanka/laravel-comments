<?php

namespace LakM\Comments\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use LakM\Comments\Builders\MessageBuilder;
use LakM\Comments\ModelResolver;
use LakM\Comments\ModelResolver as M;
use LakM\Comments\Models\Concerns\HasOwner;
use LakM\Comments\Models\Concerns\HasProfilePhoto;

/**
 * @property string $text
 * @property string $guest_name
 * @property string $guest_email
 * @property string $ip_address
 * @property bool $approved
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @method MessageBuilder<Comment> query()
 * @method Builder addScore()
 */
class Comment extends Message
{
    use HasOwner;
    use HasProfilePhoto;

    public $table = 'comments';

    protected $fillable = [
        'text',
        'commenter_type',
        'commenter_id',
        'approved',
    ];

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return HasMany
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class, 'reply_id', 'id');
    }

    public function replyReactions(): HasManyThrough
    {
        return $this->hasManyThrough(M::reactionClass(), Reply::class, 'reply_id', 'comment_id');
    }

    public function scopeAddScore(Builder $query): Builder
    {
        $reactionsTable = ModelResolver::reactionModel()->getTable();
        $commentsTable = ModelResolver::commentModel()->getTable();

        $reactionsCount = "(select count(*) from {$reactionsTable} where
            {$commentsTable}.id = {$reactionsTable}.comment_id)";

        $dislikesCountQuery = "(select (count(*) * 2) from {$reactionsTable} where
            {$commentsTable}.id = {$reactionsTable}.comment_id and type = 'dislike')";

        $repliesCountQuery = "(select (count(*) * 2) from {$commentsTable} as laravel_reserved_0 where
            {$commentsTable}.id = laravel_reserved_0.reply_id)";

        $replyReactionsCount = "(select count(*) from {$reactionsTable} inner join {$commentsTable} as laravel_reserved_1
            on laravel_reserved_1.id = {$reactionsTable}.comment_id where {$commentsTable}.id = laravel_reserved_1.reply_id)";

        $replyReactionsDislikeCount = "(select (count(*) * 2) from  {$reactionsTable}  inner join {$commentsTable} as laravel_reserved_2
            on laravel_reserved_2.id = {$reactionsTable}.comment_id  where  {$commentsTable}.id = laravel_reserved_2.reply_id and
            type = 'dislike')";

        return $query->addSelect(
            DB::raw('(select ' .
            $reactionsCount . ' + ' .
            $repliesCountQuery . ' + ' .
            $replyReactionsCount . ' - ' .
            $dislikesCountQuery . ' - ' .
            $replyReactionsDislikeCount . ') ' .
            'as score')
        );
    }
}
