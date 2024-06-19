<?php

namespace LakM\Comments\Livewire;

use GrahamCampbell\Security\Facades\Security;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use LakM\Comments\Actions\UpdateCommentReplyAction;
use LakM\Comments\Models\Reply;
use LakM\Comments\ValidationRules;
use Livewire\Attributes\Locked;
use Livewire\Component;

class UpdateCommentReplyForm extends Component
{
    public string $editorId;
    public string $toolbarId;

    public string $text;

    #[Locked]
    public Reply $reply;

    #[Locked]
    public bool $approvalRequired;

    public bool $guestModeEnabled;

    public function mount(Reply $reply, bool $guestModeEnabled): void
    {
        $this->editorId = 'editor' . Str::random();
        $this->toolbarId = 'toolbar' . Str::random();

        $this->reply = $reply;
        $this->text = $this->reply->text;

        $this->setApprovalRequired();
    }

    public function rules(): array
    {
        return ValidationRules::get($this->reply, 'update');
    }

    public function save(): void
    {
        $this->validate();

        $data = $this->getFormData();

        if ($this->canUpdateReply($this->reply) && UpdateCommentReplyAction::execute($this->reply, $data)) {
            $this->dispatch('reply-updated', replyId: $this->reply->getKey(), approvalRequired: $this->approvalRequired, text: $data['text']);

            $this->resetValidation();
        }
    }

    private function getFormData(): array
    {
        return Security::clean($this->only('text'));
    }

    public function discard(): void
    {
        $this->dispatch('reply-update-discarded', replyId: $this->reply->getKey());
    }

    public function setApprovalRequired(): void
    {
        $this->approvalRequired = config('comments.reply.approval_required');
    }

    public function canUpdateReply(Reply $reply): bool
    {
        return Gate::allows('update-reply', [$reply,  $this->guestModeEnabled]);
    }

    public function render()
    {
        return view('comments::livewire.update-comment-reply-form');
    }
}
