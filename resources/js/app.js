import Quill from 'quill';
window.Quill = Quill;

import moment from 'moment';
window.moment = moment;

import hljs from 'highlight.js';

const hljsInstance = hljs.configure({
    ignoreUnescapedHTML: true,
});

window.hljs = hljs;

import '../css/app.css';
import 'quill/dist/quill.snow.css';
import 'highlight.js/styles/github.css';

document.addEventListener('alpine:init', () => {
    Alpine.data('message', (messageId) => ({
        showMsg: false,
        timeout: 2000,

        show(id) {
            if (messageId !== id) {
                return;
            }
            this.showMsg = true;
            setTimeout(() => {
                this.showMsg = false;
            }, this.timeout);
        },
    }));
});

const highlightSyntax = (selector = 'div.ql-code-block') => {
    document.querySelectorAll(selector).forEach(
        (el) => {
            el.removeAttribute('data-highlighted');
            window.hljs.highlightElement(el);
        },
        { once: true }
    );
};

window.highlightSyntax = highlightSyntax;

const debounce = function (func, timeout = 500) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => {
            func.apply(this, args);
        }, timeout);
    };
};

window.debounce = debounce;

const showUserList = () => {
    let userMentioned = false;

    return (content, $wire) => {
        let subContent = content.split(' ').slice(-1);

        if (subContent.toString()[0] === '@') {
            $wire.dispatch('user-mentioned.' + $wire.editorId, {
                id: $wire.editorId,
                content: subContent.toString().slice(1),
            });
            userMentioned = true;
        } else if (userMentioned) {
            $wire.dispatch('user-not-mentioned.' + $wire.editorId);
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

window.handleEditorTextChange = handleEditorTextChange;

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

window.onMentionedUserSelected = onMentionedUserSelected;
