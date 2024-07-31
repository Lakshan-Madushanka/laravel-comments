<div>
    <div wire:ignore>
        <div @click.stop id="{{ $editorId }}" class="min-h-32 rounded rounded-t-none pointer-events"></div>
        <div id="{{ $toolbarId }}" class="w-full"></div>

        <div
            x-data="{ show: false }"
            @click.outside="if(show){$wire.dispatch('user-not-mentioned-' + '{{ $editorId }}')}"
            @user-mentioned-
            {{ $editorId }}.window="show=true"
            @user-not-mentioned-
            {{ $editorId }}.window="show=false"
            @user-selected-
            {{ $editorId }}.window="show=false"
            class="absolute bottom-[12rem] left-0 z-10 w-full"
        >
            <livewire:comments-user-list :$guestModeEnabled :$editorId />
        </div>
    </div>

    @script
        <script>
            let editorConfig = @js(config('comments.editor_config'));
            const quill = new Quill(`#${$wire.editorId}`, editorConfig);

            const editorElm = document.querySelector(`#${$wire.editorId} .ql-editor`);
            const toolbarParentElm = document.querySelector(`#${$wire.toolbarId}`);

            editorElm.innerHTML = $wire.text;

            const toolbars = Array.from(document.querySelector('.ql-toolbar'));

            toolbarParentElm.append(toolbars.slice(-1));

            if (@js($disableEditor)) {
                quill.disable();
            }

            const showUserList = () => {
                let userMentioned = false;

                return (content, $wire) => {
                    let subContent = content.split(' ').slice(-1);

                    if (subContent.toString()[0] === '@') {
                        $wire.dispatch('user-mentioned-' + $wire.editorId, {
                            id: $wire.editorId,
                            content: subContent.toString().slice(1),
                        });
                        userMentioned = true;
                    } else if (userMentioned) {
                        $wire.dispatch('user-not-mentioned-' + $wire.editorId);
                        userMentioned = false;
                    }
                };
            };

            let showListFunc = showUserList();

            const handleEditorTextChange = (editorElement, $wire) => {
                let html = editorElement.innerHTML;

                if (html === '<p><br></p>' || html === '') {
                    $wire.text = '';
                    return;
                }
                $wire.text = html;

                debounce(() => showListFunc(editorElement.textContent, $wire))();
            };

            const onMentionedUserSelected = (e, quill, editorElm) => {
                const span = document.createElement('strong');
                const textnode = document.createTextNode(e.detail.name + ' ');
                const lastChild = editorElm.lastChild;
                span.appendChild(textnode);

                lastChild.append(span);

                quill.update();

                quill.setSelection(quill.getLength(), 0);
                quill.format('bold', false);
            };

            quill.on('text-change', () => handleEditorTextChange(editorElm, $wire));

            Livewire.on('user-selected-' + $wire.editorId, () =>
                window.onMentionedUserSelected(event, quill, editorElm)
            );

            Livewire.on(`reset-editor-${@js($id)}`, (event) => {
                editorElm.innerHTML = event.value;
            });

            Livewire.on(`disable-editor-${@js($id)}`, (event) => {
                quill.disable();
            });
        </script>
    @endscript
</div>
