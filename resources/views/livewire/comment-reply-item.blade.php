@php use LakM\Comments\Helpers; @endphp
<div x-ref="reply{{ $reply->getKey() }}" class="flex gap-x-2 sm:gap-x-4">
    <div class="basis-14">
        <a href="{{ $profileUrl ?? $reply->ownerPhotoUrl($authMode) }}" target="_blank">
            <img
                class="h-10 w-10 sm:h-12 sm:w-12 rounded-full border border-gray-200"
                src="{{ $reply->ownerPhotoUrl($authMode) }}"
                alt="{{ $reply->ownerName($authMode) }}"
            />
        </a>
    </div>
    <div
        x-data="{ showUpdateForm: false }"
        @reply-update-discarded.window="(e) => {
             if(e.detail.replyId === @js($reply->getKey())) {
                   showUpdateForm = false;
             }
        }"
        class="basis-full"
    >
        <div
            x-show="!showUpdateForm"
            x-transition
            @class([
                "rounded border border-gray-200" => Helpers::isGithubTheme(),
            ])>
            <div
                @class([
                    "flex items-start justify-between space-x-4 p-1 sm:flex-row sm:items-center sm:justify-between",
                    "mb-2 border-b border-gray-200 bg-gray-100" => Helpers::isGithubTheme(),
                ])
            >
                <div>
                    <span class="font-semibold sm:hidden mr-1">
                        {{ Str::limit($guestMode ? $reply->guest_name : $reply->commenter->name, 10) }}
                    </span>

                    <span class="hidden font-semibold sm:inline mr-1">
                        {{ Str::limit($guestMode ? $reply->guest_name : $reply->commenter->name, 25) }}
                    </span>

                    <span class="inline-block h-2 w-[1px] bg-black mr-1"></span>

                    @if (config('comments.date_format') === 'diff')
                        <span class="text-xs">{{ $reply->created_at->diffForHumans() }}</span>
                    @else
                        <span
                            x-text="moment(@js($reply->created_at)).format('YYYY/M/D H:mm')"
                            class="text-xs"
                        ></span>
                    @endif

                    @if ($reply->isEdited())
                        <span class="inline-block h-2 w-[1px] bg-black"></span>
                        <span class="text-xs">{{ __('Edited') }}</span>
                    @endif
                </div>

                @if ($canManipulate)
                    <div class="flex items-center justify-center space-x-2">
                        <div title="My Reply">
                            <x-comments::user-check height="14" width="14" />
                        </div>

                        <x-comments::spin wire:loading wire:target="delete({{$reply}})" class="!text-blue-500" />

                        <div
                            x-data="{ showEditMenu: false }"
                            wire:loading.remove
                            wire:target="delete({{ $reply }})"
                            class="relative cursor-pointer"
                        >
                            <div @click="showEditMenu ? showEditMenu = false : showEditMenu = true">
                                <x-comments::verticle-ellipsis :height="20" :width="20" />
                            </div>
                            <ul
                                x-show="showEditMenu"
                                @click.outside="showEditMenu=false"
                                x-transition
                                class="absolute bottom-[1rem] right-[0.8rem] z-10 min-w-32 space-y-1 rounded border border-[gray-100] bg-white p-1 shadow-lg"
                            >
                                @if ($this->canUpdateReply($reply))
                                    <li
                                        @click="
                                         showUpdateForm = !showUpdateForm;
                                         showEditMenu=false
                                         $dispatch('show-reply-update-form-@js($reply->getKey())', {show: showUpdateForm})
                                         "
                                        class="flex items-center space-x-2 rounded p-2 hover:!bg-gray-200"
                                    >
                                        <x-comments::pencil height="13" width="13" strokeColor="blue" />
                                        <x-comments::action class="text-sm hover:!no-underline sm:text-sm">
                                            {{ __('Edit') }}
                                        </x-comments::action>
                                    </li>
                                @endif

                                @if ($this->canDeleteReply($reply))
                                    <li
                                        wire:click="delete({{ $reply }})"
                                        wire:confirm="{{ __('Are you sure you want to delete this reply?') }}"
                                        @click="showEditMenu=false"
                                        class="flex items-center items-center space-x-2 space-x-2 rounded p-2 hover:!bg-gray-200"
                                    >
                                        <x-comments::trash height="13" width="13" strokeColor="red" />
                                        <x-comments::action
                                            wire:loading.remove
                                            wire:target="delete({{$reply}})"
                                            class="!text-red align-text-bottom text-xs hover:!no-underline sm:text-sm"
                                        >
                                            {{ __('Delete') }}
                                        </x-comments::action>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                @endif
            </div>

            <div
                x-ref="text"
                @reply-updated.window="(e) => {
                            let key = @js($reply->getKey());
                            if(e.detail.replyId === key) {
                                    let elm = 'reply'+ key;
                                     setTimeout(() => {
                                         if(e.detail.approvalRequired) {
                                           $refs[elm].classList.add('hidden')
                                           $dispatch('unauthorized-reply-updated', {'commentId': @js($comment->getKey()) })
                                         }
                                         showUpdateForm = false;
                                     }, 2000);

                                    $refs.text.innerHTML = e.detail.text;
                                }
                            }"
                class="p-1"
            >
                {!! $reply->text !!}
            </div>
        </div>

        <div x-show="!showUpdateForm" class="mt-2">
            <livewire:comments-reactions-manager
                :key="'reply-reaction-manager' . $reply->getKey()"
                :comment="$reply"
                :$guestMode
                :$relatedModel
                :enableReply="false"
            />
        </div>

        <div x-show="showUpdateForm" x-transition class="basis-full">
            @if ($this->canUpdateReply($reply))
                <livewire:comments-reply-update-form
                    :key="'reply-update-form' . $reply->getKey()"
                    :$reply
                    :guestModeEnabled="$guestMode"
                />
            @endif
        </div>
    </div>
</div>
