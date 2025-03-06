<?php

namespace LakM\Comments\Livewire;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LakM\Comments\Abstracts\AbstractQueries;
use LakM\Comments\Contracts\CommentableContract;
use LakM\Comments\Facades\SecureGuestMode;
use LakM\Comments\Models\Comment;
use LakM\Comments\Models\Reply;
use LakM\Comments\Reactions\ReactionManager;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ReactionsManager extends Component
{
    public array $lReactions;
    public array $rReactions;

    public mixed $id;

    #[Locked]
    public Reply|Comment $comment;

    /** @var Model&CommentableContract */
    #[Locked]
    public Model $relatedModel;

    public array $reactions = [];

    public array $reactedUsers = [];

    public ?string $lastReactedUserName = '';

    public string $selectedReactionType;

    public int $total = 0;

    #[Locked]
    public bool $guestMode;

    #[Locked]
    public bool $authMode;

    #[Locked]
    public bool $authenticated;

    #[Locked]
    public bool $loginRequired;


    #[Locked]
    public bool $secureGuestModeAllowed;

    #[Locked]
    public bool $enableReply;

    public bool $shouldEnableShareButton = true;

    /**
     * @param  Reply|Comment  $comment
     * @param  Model&CommentableContract  $relatedModel
     * @param  bool  $enableReply
     * @return void
     */
    public function mount(Reply|Comment $comment, Model $relatedModel, bool $enableReply = true, bool $shouldEnableShareButton = false): void
    {
        $this->lReactions = $this->getLeftSideReactions();
        $this->rReactions = $this->getRightSideReactions();

        $this->comment = $comment;

        $this->relatedModel = $relatedModel;

        $this->id = $comment->getKey();

        $this->setEnableReply($enableReply);

        $this->shouldEnableShareButton = $shouldEnableShareButton;

        $this->authenticated = $this->relatedModel->authCheck();

        $this->guestMode = $this->relatedModel->guestModeEnabled();

        $this->authMode = !$this->guestMode;

        $this->setLoginRequired();

        $this->setSecureGuestModeAllowed();

        $this->setReactions($comment);

        $this->setReactedStatus($comment->ownerReactions);
    }

    public function handle(ReactionManager $reactionManager, string $type): void
    {
        if ($this->loginRequired || !$this->secureGuestModeAllowed) {
            return;
        }

        if (!$reactionManager->handle(
            $type,
            $this->comment,
            $this->authMode,
            $this->relatedModel->getAuthUser()?->getAuthIdentifier()
        )) {
            return;
        }

        if ($type === 'like') {
            $this->refineLikeStatus();
            return;
        }

        if ($type === 'dislike') {
            $this->refineDislikeStatus();
            return;
        }

        $this->refineReactionStatus($type);
    }

    private function getLeftSideReactions(): array
    {
        return array_filter(config('comments.reactions'), function (array $data) {
            return $data['position'] === 'left';
        });
    }

    private function getRightSideReactions(): array
    {
        return array_filter(config('comments.reactions'), function (array $data) {
            return $data['position'] === 'right';
        });
    }

    private function setReactions(Reply|Comment $comment): void
    {
        $reactions = array_keys(config('comments.reactions'));

        foreach ($reactions as $reaction) {
            $countName = $this->reactionCountName($reaction);
            $this->setReactionCount($reaction, $comment->{$countName});
            $this->total += $comment->{$countName};
        }
    }

    private function reactionCountName(string $key): string
    {
        return Str::plural($key) . '_' . 'count';
    }

    private function setReactionCount(string $key, ?int $count): void
    {
        $this->reactions[$key]['count'] = $count ?? 0;
    }

    private function setReactedStatus(Collection $ownerReactions): void
    {
        $ownerReactionsTypes = $ownerReactions->pluck('type')->toArray();

        foreach ($this->reactions as $type => $reaction) {
            $this->reactions[$type]['reacted'] = is_int(array_search($type, $ownerReactionsTypes));
        }
    }


    public function refineLikeStatus(): void
    {
        if ($this->reactions['dislike']['reacted']) {
            $this->reactions['dislike']['count'] -= 1;
            $this->reactions['dislike']['status'] = false;
        }

        if (!$this->reactions['like']['reacted']) {
            $this->reactions['like']['count'] += 1;

            $this->reactions['like']['reacted'] = true;
            $this->reactions['dislike']['reacted'] = false;
            return;
        }

        $this->reactions['like']['reacted'] = false;
        $this->reactions['like']['count'] -= 1;
    }

    public function refineDislikeStatus(): void
    {
        if ($this->reactions['like']['reacted']) {
            $this->reactions['like']['count'] -= 1;
            $this->reactions['like']['reacted'] = false;
        }

        if (!$this->reactions['dislike']['reacted']) {
            $this->reactions['dislike']['count'] += 1;

            $this->reactions['dislike']['reacted'] = true;
            $this->reactions['like']['reacted'] = false;
            return;
        }

        $this->reactions['dislike']['reacted'] = false;
        $this->reactions['dislike']['count'] -= 1;
    }

    public function refineReactionStatus(string $type): void
    {
        if ($this->reactions[$type]['reacted']) {
            $this->reactions[$type]['reacted'] = false;
            $this->reactions[$type]['count'] -= 1;
            return;
        }

        $this->reactions[$type]['reacted'] = true;
        $this->reactions[$type]['count'] += 1;
    }

    public function fillColor(string $reaction): string
    {
        return config("comments.reactions.{$reaction}.fill");
    }

    public function loadReactedUsers(string $type): void
    {
        $this->selectedReactionType = $type;
        $limit = config('comments.pagination.per_page');

        if (Arr::get($this->reactedUsers, $type)) {
            $limit += $this->reactedUsers[$type]['limit'];
        }

        $user = app(AbstractQueries::class)->reactedUsers($this->comment, $type, $limit, $this->authMode);

        $this->reactedUsers[$type]['users'] = $user;
        $this->reactedUsers[$type]['limit'] = $limit;
    }

    public function lastReactedUser(string $type): void
    {
        $user = app(AbstractQueries::class)->lastReactedUser($this->comment, $type, $this->authMode);
        $this->lastReactedUserName = $user?->name;
    }

    public function getReactedUsers(string $type)
    {
        return $this->reactedUsers[$type]['users'] ?? [];
    }

    public function getReactedUsersLimit(string $type)
    {
        return $this->reactedUsers[$type]['limit'] ?? 0;
    }

    public function setLoginRequired(): void
    {
        $this->loginRequired = !$this->authenticated && !$this->guestMode;
    }

    public function setSecureGuestModeAllowed(): void
    {
        $this->secureGuestModeAllowed = SecureGuestMode::allowed();
    }

    public function setEnableReply(bool $enabled): void
    {
        if (!config('comments.reply.enabled')) {
            $this->enableReply = false;
            return;
        }

        $this->enableReply = $enabled;
    }

    public function redirectToLogin(string $intendedUrl): void
    {
        session(['url.intended' => $intendedUrl]);
        $this->redirect(config('comments.login_route'));
    }

    public function render(): View|Factory|Application
    {
        return view('comments::livewire.reactions-manager');
    }
}
