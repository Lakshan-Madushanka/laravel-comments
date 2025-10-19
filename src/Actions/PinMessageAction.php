<?php

namespace LakM\Commenter\Actions;

use Illuminate\Support\Facades\DB;
use LakM\Commenter\Contracts\CommentableContract;
use LakM\Commenter\Models\Comment;
use LakM\Commenter\Models\Message;

class PinMessageAction
{
    public function execute(CommentableContract $commentable, Message $msg): void
    {
        DB::transaction(function () use ($commentable, $msg) {
            $this->removeExistingPinComments($commentable);
            $this->removeExistingPinReplies($commentable);

            if ($msg->is_pinned) {
                return;
            }

            $msg->is_pinned = true;
            $msg->save();
        });
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
            ->with(['replies' => fn ($query) => $query->where('is_pinned', true)])
            ->get()
            ->pluck('replies')
            ->collapse()
            ->pluck('id');


        Comment::query()->whereIn('id', $replyIds)->update(['is_pinned' => false]);
    }
}
