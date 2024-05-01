import Quill from 'quill';
window.Quill = Quill;

import moment from 'moment';
window.moment = moment;

import hljs from 'highlight.js';
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

const highlightSyntax = (selector = "div.ql-code-block") => {
    document.querySelectorAll(selector).forEach((el) => {
        el.removeAttribute("data-highlighted");
        window.hljs.highlightElement(el);
    }, { once: true });
};

window.highlightSyntax = highlightSyntax;
