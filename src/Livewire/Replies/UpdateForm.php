<?php

namespace LakM\Commenter\Livewire\Replies;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use LakM\Commenter\Actions\Reply\UpdateAction;
use LakM\Commenter\Helpers;
use LakM\Commenter\Models\Reply;
use LakM\Commenter\ValidationRules;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Mews\Purifier\Facades\Purifier;

class UpdateForm extends Component
{
    public string $editorId;

    public string $text;

    #[Locked]
    public Reply $reply;

    public bool $showEditor = false;

    #[Locked]
    public bool $approvalRequired;

    public bool $guestModeEnabled;

    public function mount(Reply $reply, bool $guestModeEnabled): void
    {
        $this->editorId = Str::uuid();

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

        if ($this->canUpdateReply($this->reply) && UpdateAction::execute($this->reply, $data)) {
            $this->dispatch(
                'reply-updated',
                replyId: $this->reply->getKey(),
                approvalRequired: $this->approvalRequired,
                text: $data['text']
            );

            $this->resetValidation();
        }
    }

    private function getFormData(): array
    {
        return Purifier::clean($this->only('text'));
    }

    public function discard(): void
    {
        $this->dispatch('reply-update-discarded', replyId: $this->reply->getKey());
        $this->dispatch('reset-editor-' . $this->editorId, value: $this->reply->text);
    }

    public function setApprovalRequired(): void
    {
        $this->approvalRequired = config('commenter.reply.approval_required');
    }

    public function canUpdateReply(Reply $reply): bool
    {
        return Gate::allows('update-reply', [$reply, $this->guestModeEnabled]);
    }

    #[On('show-reply-update-form-{reply.id}')]
    public function showEditor(bool $show): void
    {
        $this->showEditor = $show;
    }

    public function render(): Factory|View|Application
    {
        return view(Helpers::getLivewireViewString('replies.update-form'));
    }
}
