<div
    x-data="{show: false, protocol: '', domain: '', path: '', fullUrl: '', isURLValid: false}"
    x-show="show"
    @click.window="function(event){
            const elmType = event.target.tagName;
            const target = event.target;
            const link = target.getAttribute('href');

            let editor = document.querySelector('.ql-editor');

            if (elmType === 'SPAN' && link) {
                show=true;
                try {
                    const url = new URL(link);

                    protocol = url.protocol + '//';
                    path = url.href.replace(url.origin, '');
                    domain = url.origin.replace(protocol, '');

                    isURLValid = true;
                } catch {
                    domain = link + ' ' + '(Invalid URL)';
                    isURLValid = false;
                }

                fullUrl = link;
            }
        }"
    class="lakm_commenter"
>
    <x-comments::modal class="!w-[32rem]">
        <div class="px-4 py-2 space-y-6">
            <div class="flex flex-col items-center space-y-2">
                <span class="font-bold text-xl">{{ __('Leaving') }} {{ config('app.name') }}</span>
                <span x-show="isURLValid">{{ __('Your about to visit the following url') }}</span>
                <span x-show="!isURLValid">{{ __('Invalid URL') }}</span>
            </div>
            <div class="border p-4 rounded">
                <p class="overflow-auto break-all"><span x-text=protocol></span><strong x-text="domain"></strong><span x-text="path"></span></p>
            </div>
            <div x-show="isURLValid" @click="window.open(fullUrl, '_blank')" class="flex justify-end">
                <x-comments::button size="md" type="button">
                    {{ __('Visit Site') }}
                </x-comments::button>
            </div>
        </div>
    </x-comments::modal>
</div>
