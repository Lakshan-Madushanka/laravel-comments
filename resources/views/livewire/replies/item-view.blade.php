@php use LakM\Commenter\Helpers; @endphp
<div
    x-ref="reply{{ $reply->getKey() }}"
    @class([
       "flex flex-col gap-x-2 sm:gap-x-4 dark:!text-white",
       "border rounded-lg p-4" => Helpers::isModernTheme(),
   ])
    @style([
        'color: ' . config('commenter.secondary_color')
    ])
>
    <div
        @class([
                "basis-14",
                'hidden' => Helpers::isModernTheme()
        ])
    >
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
                "rounded border border-gray-200 dark:border-slate-700" => Helpers::isGithubTheme(),
            ])
        >
            <div
                @class([
                    "flex items-start justify-between gap-x-4 p-1 sm:flex-row sm:items-center sm:justify-between",
                    "mb-2 border-b border-gray-200 bg-gray-100 dark:bg-slate-800 dark:border-slate-900" => Helpers::isGithubTheme(),
                ])
            >
                <div
                    @class([
                        "flex items-center gap-4" => Helpers::isModernTheme()
                    ])
                >

                    <div
                        @class([
                            "hidden" => !Helpers::isModernTheme()
                        ])
                    >
                        <a href="{{ $profileUrl ?? $reply->ownerPhotoUrl($authMode) }}" target="_blank">
                            <img
                                class="h-8 w-8 sm:h-10 sm:w-10 rounded-full border border-gray-200"
                                src="{{ $reply->ownerPhotoUrl() }}"
                                alt="{{ $reply->ownerName($authMode) }}"
                            />
                        </a>
                    </div>

                    <div>
                        <span class="font-semibold sm:hidden me-1">
                            {{ Str::limit($reply->ownerName($authMode), 10) }}
                        </span>

                        <span class="hidden font-semibold sm:inline me-1">
                        {{ Str::limit($reply->ownerName($authMode), 25) }}
                        </span>

                        <span class="inline-block h-2 w-[1px] bg-black me-1"></span>

                        @if (config('commenter.date_format') === 'diff')
                            <span
                                class="text-xs"
                                :title="moment(@js($reply->created_at)).format('YYYY/M/D H:mm')">
                                {{ $reply->created_at->diffForHumans() }}
                            </span>
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
                </div>


                @if ($canManipulate)
                    <div class="flex items-center justify-center gap-x-2">
                        <div title="{{__('My Reply')}}">
                            <x-commenter::user-check height="14" width="14"/>
                        </div>

                        <x-commenter::spin wire:loading wire:target="delete({{$reply}})" class="!text-blue-500"/>

                        <div
                            x-data="{ showEditMenu: false }"
                            wire:loading.remove
                            wire:target="delete({{ $reply }})"
                            class="relative cursor-pointer"
                        >
                            <div @click="showEditMenu ? showEditMenu = false : showEditMenu = true">
                                <x-commenter::verticle-ellipsis :height="20" :width="20"/>
                            </div>

                            <ul
                                x-show="showEditMenu"
                                @click.outside="showEditMenu=false"
                                x-transition
                                class="absolute bottom-[1rem] end-[0.8rem] z-10 min-w-32 space-y-1 rounded border border-[gray-100] bg-white dark:border-slate-900 dark:bg-slate-800 p-1 shadow-lg"
                            >
                                @if ($this->canUpdateReply($reply))
                                    <li
                                        @click="
                                         showUpdateForm = !showUpdateForm;
                                         showEditMenu=false
                                         $dispatch('show-reply-update-form-@js($reply->getKey())', {show: showUpdateForm})
                                         "
                                        class="flex items-center gap-x-2 rounded p-2 hover:!bg-gray-200 dark:hover:!bg-slate-900"
                                    >
                                        <x-commenter::pencil height="13" width="13" strokeColor="blue"/>
                                        <x-commenter::action class="text-sm hover:!no-underline sm:text-sm">
                                            {{ __('Edit') }}
                                        </x-commenter::action>
                                    </li>
                                @endif

                                @if ($this->canDeleteReply($reply))
                                    <li
                                        wire:click="delete({{ $reply }})"
                                        wire:confirm="{{ __('Are you sure you want to delete this reply?') }}"
                                        @click="showEditMenu=false"
                                        class="flex items-center gap-x-2 rounded p-2 hover:!bg-gray-200 dark:hover:!bg-slate-900"
                                    >
                                        <x-commenter::trash height="13" width="13" strokeColor="red"/>
                                        <x-commenter::action
                                            wire:loading.remove
                                            wire:target="delete({{$reply}})"
                                            class="!text-red align-text-bottom text-xs hover:!no-underline sm:text-sm"
                                        >
                                            {{ __('Delete') }}
                                        </x-commenter::action>
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
                                           $dispatch('unauthorized-reply-updated', {'messageId': @js($message->getKey()) })
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

            <div
                class="flex bg-gray-200 my-4 justify-center items-center h-[1px] max-w-[10%] mx-auto bg-gradient-to-r from-transparent via-gray-100 to-transparent">
            </div>

            <!--Reaction manager -->
            <div x-show="!showUpdateForm" class="mt-2">
                <livewire:reaction-manager
                    :key="'reply-reaction-manager' . $reply->getKey()"
                    :message="$reply"
                    :$guestMode
                    :$relatedModel
                    :$shouldEnableShareButton
                />
            </div>
        </div>

        <div x-show="showUpdateForm" x-transition class="basis-full">
            @if ($this->canUpdateReply($reply))
                <livewire:replies.update-form
                    :key="'reply-update-form' . $reply->getKey()"
                    :$reply
                    :guestModeEnabled="$guestMode"
                />
            @endif
        </div>

        {{-- Replies count--}}
        @if (config('commenter.reply.enabled'))
            <div
                x-data="{replyCount: @js($replyCount), showReplyList: $wire.entangle('showReplyList')}"
                @reply-created-{{ $reply->getKey() }}.window="
                            if($event.detail.messageId === {{ $reply->getKey() }}) {
                                if(!event.detail.approvalRequired) {
                                    replyCount += 1;
                                }
                            }
                        "
                @reply-deleted-{{ $reply->getKey() }}.window="
                            if($event.detail.messageId === {{ $reply->getKey() }}) {
                                replyCount -= 1;
                            }
                        "
                @unauthorized-reply-updated.window="(e) => {
                            let key = @js($reply->getKey());
                            if(e.detail.messageId === key) {
                                    replyCount -= 1;
                            }
                        }"
                class="mt-2 text-xs"
            >
                <div
                    x-show="replyCount > 0"
                    x-transition
                    @click="$dispatch('show-replies.' + @js($reply->getKey()));"
                    wire:click="loadReplies"
                    class="inline-block"
                >
                    <x-commenter::link
                        type="popup"
                        @class([
                            "mx-2 dark:!text-white inline-flex text-sm items-center transition dark:!bg-slate-900 dark:hover:!bg-slate-800 [&>*]:pe-1",
                            "!mx-0 px-2 py-1" => Helpers::isDefaultTheme() || Helpers::isModernTheme(),
                            "hover:!bg-["  . config('commenter.hover_color') . "]" =>  Helpers::isModernTheme(),
                            "!rounded-[1000px] hover:rounded-[1000px] gap-x-2" => Helpers::isModernTheme(),
                        ])
                        @style([
                            'background: ' . config('commenter.bg_primary_color') => Helpers::isModernTheme(),
                        ])
                    >
                        @if(!Helpers::isModernTheme())
                            <span x-show="!showReplyList">
                                        <x-commenter::icons.chevron-down />
                                    </span>
                            <span x-show="showReplyList">
                                        <x-commenter::icons.chevron-up />
                                    </span>
                        @endif

                        <span
                            x-text="replyCount"
                                    @class([
                                        "inline-block text-center",
                                        "border text-xs !py-1 !px-2 rounded-full bg-white dark:bg-slate-800" => Helpers::isModernTheme(),
                                    ])
                                >

                                </span>
                        <span>{{ __('Replies') }}</span>

                        @if(Helpers::isModernTheme())
                            <span x-show="!showReplyList">
                                        <x-commenter::icons.list-down />
                                    </span>
                            <span x-show="showReplyList">
                                        <x-commenter::icons.list-up />
                                    </span>
                        @endif
                    </x-commenter::link>
                </div>
            </div>
        @endif
    </div>

    @if($showReplyList)
        <div class="ml-2">
            <div class="flex bg-gray-200 mb-6 mt-4 justify-center items-center h-[1px] max-w-[100%] mx-auto bg-gradient-to-r from-transparent via-gray-100 to-transparent">
            </div>
            <livewire:replies.list-view
                :key="'nested-reply-list-'. $reply->id"
                :message="$reply"
                :$relatedModel
                :total="$replyCount"
                :show="true"
            />
        </div>
    @endif
</div>
