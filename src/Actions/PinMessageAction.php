<?php

namespace LakM\Commenter\Actions;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use LakM\Commenter\Contracts\CommentableContract;
use LakM\Commenter\Helpers;
use LakM\Commenter\Models\Comment;
use LakM\Commenter\Models\Guest;
use LakM\Commenter\Models\Message;
use LakM\Commenter\Models\Reply;
use LakM\NoPass\Facades\NoPass;

class PinMessageAction
{
    public function execute(CommentableContract $commentable, Message $msg): void
    {
        $this->removeExistingPinComments($commentable);
        $this->removeExistingPinReplies($commentable);

        $msg->is_pinned = true;
        $msg->save();
    }

    /**
     * @param CommentableContract $commentable
     * @return void
     */
    protected function removeExistingPinComments(CommentableContract $commentable): void
    {
        $commentable->comments()->update(['is_pinned' => false]);
    }

    /**
     * @param CommentableContract $commentable
     * @return void
     */
    protected function removeExistingPinReplies(CommentableContract $commentable): void
    {
        $replyIds = $commentable
            ->comments()
            ->with(['replies' => fn($query) => $query->where('is_pinned', true)])
            ->get()
            ->pluck('replies')
            ->collapse()
            ->pluck('id');


        Comment::query()->whereIn('id', $replyIds)->update(['is_pinned' => false]);
    }
}
