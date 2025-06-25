<?php

namespace LakM\Comments\Livewire;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use LakM\Comments\Actions\DeleteReplyAction;
use LakM\Comments\Contracts\CommentableContract;
use LakM\Comments\Models\Message;
use LakM\Comments\Models\Reply;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ReplyItem extends Component
{
    /** @var Model&CommentableContract */
    #[Locked]
    public Model $relatedModel;

    #[Locked]
    public Message $message;

    #[Locked]
    public Reply $reply;

    #[Locked]
    public bool $guestMode;

    #[Locked]
    public bool $authMode;

    public bool $show = false;

    #[Locked]
    public bool $canManipulate;

    public ?string $profileUrl;

    public bool $shouldEnableShareButton = true;

    public bool $showReplyList = false;

    public int $replyCount = 0;

    /**
     * @param  Message $message
     * @param  Reply $reply
     * @param  Model&CommentableContract $relatedModel
     * @param  bool $guestMode
     * @return void
     */
    public function mount(
        Message $message,
        Reply $reply,
        Model $relatedModel,
        bool $guestMode,
    ): void {
        $this->message = $message;
        $this->reply = $reply;

        $this->guestMode = $guestMode;
        $this->authMode = !$guestMode;

        $this->relatedModel = $relatedModel;

        $this->setProfileUrl();
        $this->setCanManipulate();

        $this->replyCount = $reply->replies_count ?? 0;
    }

    public function canUpdateReply(Reply $reply): bool
    {
        return Gate::allows('update-reply', [$reply, $this->guestMode]);
    }

    public function canDeleteReply(Reply $reply): bool
    {
        return Gate::allows('delete-reply', [$reply, $this->guestMode]);
    }

    public function delete(Reply $reply): void
    {
        $this->skipRender();

        if ($this->canDeleteReply($reply) && DeleteReplyAction::execute($reply)) {
            $this->dispatch('reply-deleted-' . $this->message->getKey(), replyId: $reply->getKey(), messageId: $this->message->getKey());
        }
    }

    private function setProfileUrl(): void
    {
        $this->profileUrl = $this->message->commenter->profileUrl();
    }

    public function setCanManipulate(): bool
    {
        return $this->canManipulate = $this->canUpdateReply($this->reply) || $this->canDeleteReply($this->reply);
    }

    public function loadReplies(): void
    {
        $this->showReplyList = !$this->showReplyList;
    }

    public function render(): View|Factory|Application
    {
        return view('comments::livewire.comment-reply-item');
    }
}
