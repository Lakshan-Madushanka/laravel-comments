@php use LakM\Comments\Helpers; @endphp

<div
    x-ref="comment{{ $comment->getKey() }}"
    x-data="{ showReplyList: @js($showReplyList), replyCount: @js($comment->replies_count) }"
    @class([
        "flex gap-x-2 sm:gap-x-4 pb-2 dark:!bg-black dark:!text-white",
        "border rounded-lg p-4" => Helpers::isModernTheme(),
    ])
    @style([
        'color: ' . config('comments.secondary_color') . ';' . 'background: ' . config('comments.bg_secondary_color')
    ])
>
    <div
        @class([
            "basis-14",
            'hidden' => Helpers::isModernTheme()
        ])
    >
        <a href="{{ $profileUrl ?? $comment->ownerPhotoUrl($authMode) }}" target="_blank">
            <img
                class="h-10 w-10 sm:h-12 sm:w-12 rounded-full border border-gray-200"
                src="{{ $comment->ownerPhotoUrl() }}"
                alt="{{ $comment->ownerName($authMode) }}"
            />
        </a>
    </div>

    <div
        x-data="{ showUpdateForm: false }"
        @comment-update-discarded.window="(e) => {
             if(e.detail.commentId === @js($comment->getKey())) {
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
                "flex w-full gap-x-4" => Helpers::isModernTheme(),
            ])
        >
            <div
                @class([
                    "hidden md:block md:w-[4%] rounded-xl",
                    '!hidden' => !Helpers::isModernTheme(),
                ])
                @style([
                    'background: ' . config('comments.bg_primary_color')
                ])
            >
                <div
                    class="h-full flex items-center justify-center font-bold dark:text-black"
                >
                    {{$comment->score}}
                </div>
            </div>

            <div @class(["w-full md:w-[96%]" => Helpers::isModernTheme()])>
                <div
                    @class([
                        "flex items-center justify-between p-1",
                        "mb-2 border-b border-gray-100 bg-gray-100 dark:bg-slate-800 dark:border-slate-900" => Helpers::isGithubTheme()
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
                            <a href="{{ $profileUrl ?? $comment->ownerPhotoUrl($authMode) }}" target="_blank">
                                <img
                                    class="h-8 w-8 sm:h-10 sm:w-10 rounded-full border border-gray-200"
                                    src="{{ $comment->ownerPhotoUrl() }}"
                                    alt="{{ $comment->ownerName($authMode) }}"
                                />
                            </a>
                        </div>

                        <div>
                            <span class="font-semibold sm:hidden mr-1">
                                {{ Str::limit($comment->ownerName($authMode), 10) }}
                            </span>

                            <span class="hidden font-semibold sm:inline mr-1">
                                {{ Str::limit($comment->ownerName($authMode), 25) }}
                            </span>

                            <span class="inline-block h-2 w-[1px] bg-black mr-1"></span>

                            @if (config('comments.date_format') === 'diff')
                                <span class="text-xs">{{ $comment->created_at->diffForHumans() }}</span>
                            @else
                                <span
                                    x-text="moment(@js($comment->created_at)).format('YYYY/M/D H:mm')"
                                    class="text-xs"
                                ></span>
                            @endif

                            @if ($comment->isEdited())
                                <span class="inline-block h-2 w-[1px] bg-black"></span>
                                <span class="text-xs">{{ __('Edited') }}</span>
                            @endif
                        </div>
                    </div>

                    @if ($canManipulate)
                        <div class="flex items-center justify-center space-x-2">
                            <div title="My Comment">
                                <x-comments::user-check height="14" width="14"/>
                            </div>

                            <x-comments::spin wire:loading wire:target="delete({{$comment}})" class="!text-blue-500"/>

                            <div
                                x-data="{ showEditMenu: false }"
                                wire:loading.remove
                                wire:target="delete({{ $comment }})"
                                class="relative cursor-pointer"
                            >
                                <div @click="showEditMenu ? showEditMenu = false : showEditMenu = true">
                                    <x-comments::verticle-ellipsis :height="20" :width="20"/>
                                </div>
                                <ul
                                    x-show="showEditMenu"
                                    @click.outside="showEditMenu=false"
                                    x-transition
                                    class="absolute bottom-[1rem] right-[0.8rem] z-10 min-w-32 space-y-1 rounded border  bg-white dark:border-slate-900 dark:bg-slate-800 p-1 shadow-lg"
                                >
                                    @if ($model->canEditComment($comment))
                                        <li
                                            @click="showUpdateForm = !showUpdateForm; showEditMenu=false"
                                            @class([
                                                "hover:!bg-[" . config('comments.hover_color') . "]",
                                                "flex items-center space-x-2 rounded p-2 dark:hover:!bg-slate-900"
                                            ])
                                        >
                                            <x-comments::pencil height="13" width="13" strokeColor="{{config('comments.primary_color')}}"/>

                                            <x-comments::action class="text-xs hover:!no-underline sm:text-sm">
                                                {{ __('Edit') }}
                                            </x-comments::action>
                                        </li>
                                    @endif

                                    @if ($model->canDeleteComment($comment))
                                        <li
                                            wire:click="delete({{ $comment }})"
                                            wire:confirm="{{ __('Are you sure you want to delete this comment?') }}"
                                            @click="showEditMenu=false"
                                            @class([
                                                "hover:!bg-[" . config('comments.hover_color') . "]",
                                                "flex items-center items-center space-x-2 space-x-2 rounded p-2 dark:hover:!bg-slate-900"
                                            ])
                                        >
                                            <x-comments::trash height="13" width="13" strokeColor="red"/>
                                            <x-comments::action
                                                wire:loading.remove
                                                wire:target="delete({{$comment}})"
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
                    @comment-updated.window="(e) => {
                    let key = @js($comment->getKey());
                    if(e.detail.commentId === key) {
                        $refs.text.innerHTML = e.detail.text;
                        let elm = 'comment' + key;
                         setTimeout(() => {
                             showUpdateForm = false;
                             if (@js($model->approvalRequired())) {
                                  $wire.$parent.total -= 1;
                                  $dispatch('unauthorized-comment-updated');
                             }
                         }, 2000);
                    }
                }"
                    class="p-1"
                >
                    {!! $comment->text !!}
                </div>

                <div
                    class="flex bg-gray-200 my-4 justify-center items-center h-[1px] max-w-[10%] mx-auto bg-gradient-to-r from-transparent via-gray-100 to-transparent">
                </div>

                <!--Reaction manager -->
                <div x-show="!showUpdateForm" @class(['px-2' => Helpers::isGithubTheme()])>
                    <livewire:comments-reactions-manager
                        :key="'reaction-manager-' . $comment->id"
                        :$comment
                        :relatedModel="$model"
                    />
                </div>

                {{-- Replies count--}}
                @if (config('comments.reply.enabled'))
                    <div
                        @reply-created-{{ $comment->getKey() }}.window="
                            if($event.detail.commentId === {{ $comment->getKey() }}) {
                                if(!event.detail.approvalRequired) {
                                    replyCount += 1;
                                }
                            }
                        "
                        @reply-deleted-{{ $comment->getKey() }}.window="
                            if($event.detail.commentId === {{ $comment->getKey() }}) {
                                replyCount -= 1;
                            }
                        "
                        @unauthorized-reply-updated.window="(e) => {
                            let key = @js($comment->getKey());
                            if(e.detail.commentId === key) {
                                    replyCount -= 1;
                            }
                        }"
                        class="mt-2 text-xs"
                    >
                        <div
                            x-show="replyCount > 0"
                            x-transition
                            @click="$dispatch('show-replies.' + @js($comment->getKey())); showReplyList = !showReplyList"
                            class="inline-block"
                        >
                            <x-comments::link
                                type="popup"
                                @class([
                                    "mx-2 dark:!text-white inline-flex text-sm items-center transition dark:!bg-slate-900 dark:hover:!bg-slate-800 [&>*]:pr-1",
                                    "!mx-0 px-2 py-1" => Helpers::isDefaultTheme() || Helpers::isModernTheme(),
                                    "hover:!bg-["  . config('comments.hover_color') . "]" =>  Helpers::isModernTheme(),
                                    "!rounded-[1000px] hover:rounded-[1000px] space-x-2" => Helpers::isModernTheme(),
                                ])
                                @style([
                                    'background: ' . config('comments.bg_primary_color') => Helpers::isModernTheme(),
                                ])
                            >
                                @if(!Helpers::isModernTheme())
                                    <span x-show="!showReplyList">
                                        <x-comments::icons.chevron-down/>
                                    </span>
                                    <span x-show="showReplyList">
                                        <x-comments::icons.chevron-up/>
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
                                        <x-comments::icons.list-down/>
                                    </span>
                                    <span x-show="showReplyList">
                                        <x-comments::icons.list-up/>
                                    </span>
                                @endif
                            </x-comments::link>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Reply List -->
        <div
            x-show="showReplyList"
            x-transtion
            @class([
                "ml-[-2rem] mt-6 sm:ml-8",
                "!mt-6 sm:!ml-24" => Helpers::isModernTheme()
            ])
        >
            <div
                class="flex bg-gray-200 mb-6 justify-center items-center h-[1px] max-w-[100%] mx-auto bg-gradient-to-r from-transparent via-gray-100 to-transparent">
            </div>

            <livewire:comments-reply-list
                :key="'reply-list-'. $comment->id"
                :$comment
                :relatedModel="$model"
                :total="$comment->replies_count"
            />
        </div>


        <!-- Update Form -->
        @if ($model->canEditComment($comment))
            <div x-show="showUpdateForm" x-transition class="basis-full">
                <livewire:comments-update-form :key="'update-form-'. $comment->id" :$comment :$model/>
            </div>
        @endif

    </div>
</div>
