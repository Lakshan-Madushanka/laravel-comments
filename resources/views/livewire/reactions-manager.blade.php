@php use Illuminate\Support\Number;use LakM\Comments\Helpers; @endphp
<div x-data="{ showReplyForm: false }">
    <div class="flex w-full justify-between gap-x-4">
        <div
            @class([
                "flex items-center gap-x-1 rounded p-1 sm:gap-x-2",
                "border border-gray-200 bg-gray-100" => Helpers::isGithubTheme()
            ])
        >
            @foreach ($lReactions as $key => $value)
                @if ($key === "like")
                    <div
                        x-data="{
                            isLiked: $wire.reactions['like']['reacted'],
                            showUsers: false,
                        }"
                        @mouseleave="showUsers=false"
                        @comment-disliked.window="(e) => {
                            if(e.detail.id === @js($comment->getKey())) {
                                    isLiked = false;
                            }
                        }"
                        @click="
                            if ($wire.loginRequired) {
                                $wire.redirectToLogin('window.location.ref')
                                return;
                            }
                            if(isLiked === true) {
                                $dispatch('comment-liked', {id: @js($comment->getKey())});
                            }
                        "
                        @class([
                           "cursor-pointer rounded px-1",
                           "border hover:bg-gray-200" => Helpers::isDefaultTheme(),
                           "bg-gray-300 hover:bg-gray-400" => Helpers::isGithubTheme()
                       ])
                    >
                        <div
                            @click="if($wire.loginRequired){return}; isLiked = !isLiked; showUsers=false"
                            wire:click="handle('{{ $key }}')"
                            @if ($authMode)
                                @mouseover="
                                    if(!$wire.loginRequired && $wire.reactions['{{ $key }}']['count'] > 0 && !showUsers) {
                                            showUsers = true;
                                            $wire.lastReactedUser('{{ $key }}')
                                        }
                                    "
                            @endif
                            class="min-w-[2.2rem] flex cursor-pointer items-center justify-between"
                            title="like"
                        >
                            <div x-show="!isLiked">
                                <x-dynamic-component component="comments::icons.{{$key}}" />
                            </div>
                            <div x-show="isLiked">
                                <x-dynamic-component
                                    component="comments::icons.{{$key}}"
                                    :fill="$this->fillColor('like')"
                                />
                            </div>
                            <div class="pl-1 sm:pl-2">
                                <span class="text-sm">{{ Number::abbreviate($reactions["like"]["count"]) }}</span>
                            </div>
                        </div>

                        <x-comments::show-reacted-users
                            @mouseleave="showUsers=false"
                            :$lastReactedUserName
                            :$reactions
                            :$key
                            :$comment
                            :$authMode
                            class="bottom-[-3.4rem] left-[-2rem]"
                            wrapperClass="left-1 bottom-[-2.2rem]"
                        />
                    </div>
                @elseif ($key === "dislike")
                    <div
                        x-data="{
                            isDisliked: $wire.reactions['dislike']['reacted'],
                            showUsers: false,
                        }"
                        @mouseleave="showUsers=false"
                        @comment-liked.window="(e) => {
                            if(e.detail.id === @js($comment->getKey())) {
                                    isDisliked = false;
                            }
                        }"
                        @click="
                             if ($wire.loginRequired) {
                                $wire.redirectToLogin('window.location.ref')
                                return;
                            }
                            if(isDisliked === true) {
                                $dispatch('comment-disliked', {id: @js($comment->getKey())});
                            }
                        "
                        @class([
                            "cursor-pointer rounded px-1",
                            "border hover:bg-gray-200" => Helpers::isDefaultTheme(),
                            " bg-gray-300 hover:bg-gray-400" => Helpers::isGithubTheme()
                        ])
                    >
                        <div
                            @click="if($wire.loginRequired){return}; isDisliked = !isDisliked; showUsers=false"
                            wire:click="handle('{{ $key }}')"
                            @if ($authMode)
                                @mouseover="
                                    if(!$wire.loginRequired && $wire.reactions['{{ $key }}']['count'] > 0 && !showUsers) {
                                         showUsers = true;
                                         $wire.lastReactedUser('{{ $key }}')
                                     }
                                     "
                            @endif
                            class="min-w-[2.2rem] flex cursor-pointer justify-between items-center"
                            title="dislike"
                        >
                            <div x-show="!isDisliked">
                                <x-dynamic-component component="comments::icons.{{$key}}" />
                            </div>

                            <div x-show="isDisliked">
                                <x-dynamic-component
                                    component="comments::icons.{{$key}}"
                                    :fill="$this->fillColor('dislike')"
                                />
                            </div>

                            <div class="pl-1 sm:pl-2">
                                <span class="text-sm">{{ Number::abbreviate($reactions["dislike"]["count"]) }}</span>
                            </div>
                        </div>
                        <x-comments::show-reacted-users
                            @mouseleave="showUsers=false"
                            :$lastReactedUserName
                            :$reactions
                            :$key
                            :$comment
                            :$authMode
                            class="bottom-[-3.4rem] left-[-2rem]"
                            wrapperClass="left-1 bottom-[-2.2rem]"
                        />
                    </div>
                @else
                    <x-comments::show-reaction :$comment :$lastReactedUserName :$reactions :$key :$authMode />
                @endif
            @endforeach

            @if ($enableReply)
                <div
                    @click="
                    if ($wire.loginRequired) {
                        $wire.redirectToLogin('window.location.ref')
                        return;
                    }
                     $dispatch('show-create-reply-form-' + @js($comment->getKey()));
                     showReplyForm = !showReplyForm;
                    "
                    @reply-discarded.window="
                        if ($event.detail.commentId === @js($comment->getKey())) {
                            showReplyForm = false;
                        }
                     "
                    @reply-created-{{$comment->getKey()}}.window="
                        if ($event.detail.commentId === @js($comment->getKey())) {
                            setTimeout(() => {showReplyForm = !showReplyForm}, 2000)
                        }
                     "
                >
                    <x-comments::link class="align-text-bottom text-sm" type="popup">{{__('reply')}}</x-comments::link>
                </div>
            @endif
        </div>

        <div
            @class([
                  "flex max-w-40 items-center gap-x-1 overflow-x-auto rounded p-1 sm:gap-x-2 md:max-w-72",
                  "border border-gray-200 bg-gray-100" => Helpers::isGithubTheme(),
                ])
        >
            @foreach ($rReactions as $key => $value)
                <x-comments::show-reaction
                    :$comment
                    :$lastReactedUserName
                    :$reactions
                    :$key
                    :$authMode
                    :$loginRequired
                    :showReactedUsers="false"
                />
            @endforeach
        </div>
    </div>

    @if ($enableReply)
        <div x-show="showReplyForm" x-transition class="my-4 sm:ml-8">
            <livewire:comments-reply-form :$comment :$guestMode :$relatedModel />
        </div>
    @endif

    <div x-data="{ approvalRequired: false }">
        <div
            x-cloak
            x-data="message(@js($comment->getKey()))"
            @reply-created-{{$comment->getKey()}}.window="approvalRequired=$event.detail.approvalRequired"
        >
            <div x-show="showMsg" x-transition class="mt-2 align-top text-xs text-green-500 sm:text-sm">
                <span x-show="approvalRequired">
                    {{ __("Reply created and will be displayed once approved") }}
                </span>
                <span x-show="!approvalRequired">{{ __("Reply created") }}</span>
            </div>
        </div>
    </div>

    @if ($authMode)
        <div
            x-data="{show: false, type: ''}"
            @@show-user-list.window="
                if($wire.get('id') == $event.detail.id) {
                     show=true;
                     type=$event.detail.type;
                }
            "
        >
            <x-comments::modal loadingTarget="loadReactedUsers">
                <div class="flex py-4">
                    <div class="space-y-2 border-r-2 border-gray-200">
                        <div class="mb-4 border-b-2 border-gray-200 p-4">
                            <span class="bg-gray-300 px-4 py-2 font-bold">{{ $total }}</span>
                        </div>
                        @foreach (config("comments.reactions") as $key => $reaction)
                            <div
                                @if ($reactions[$key]["count"] > 0)
                                    wire:click="loadReactedUsers('{{ $key }}')"
                                @click="type = '{{ $key }}'"
                                class="cursor-pointer p-4 relative"
                                @endif
                                wire:loading.class="cursor-not-allowed"
                                target="loadReactedUsers"
                                class="relative p-4"
                                :class="type === '{{ $key }}' ? 'bg-gray-100' : ''"
                            >
                                @if ($reactions[$key]["reacted"])
                                    <x-dynamic-component
                                        component="comments::icons.{{$key}}"
                                        :fill="$this->fillColor($key)"
                                    />
                                @else
                                    <x-dynamic-component component="comments::icons.{{$key}}" />
                                @endif

                                <span class="absolute left-8 top-1 rounded bg-gray-300 px-1 text-xs">
                                {{ $reactions[$key]["count"] }}
                            </span>
                            </div>
                        @endforeach
                    </div>

                    @if ($selectedReactionType)
                        <div class="mt-4 flex w-full flex-col items-start px-4">
                            @foreach ($this->getReactedUsers($selectedReactionType) as $user)
                                <div class="flex w-full items-center space-x-4 border-b border-gray-200 p-2">
                                    <div>
                                        <img
                                            class="h-[1.8rem] w-[1.8rem] rounded-full border border-gray-200"
                                            src="{{ $user->photo }}"
                                            alt="{{ $user->photo }}"
                                        />
                                    </div>
                                    <div>{{ $user->name }}</div>
                                </div>
                            @endforeach

                            @if ($this->getReactedUsersLimit($selectedReactionType) < $reactions[$selectedReactionType]["count"])
                                <div class="!mt-4 flex w-full justify-center">
                                    <x-comments::button
                                        wire:click="loadReactedUsers('{{$selectedReactionType}}')"
                                        type="button"
                                        size="sm"
                                    >
                                        {{__('Load')}}
                                    </x-comments::button>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </x-comments::modal>
        </div>
    @endif
</div>
