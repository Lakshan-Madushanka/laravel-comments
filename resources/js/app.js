import Quill from 'quill';

const Link = Quill.import('formats/link');

class CustomLink extends Link {
    static tagName = 'span';

    static create(value) {
        let node = super.create(value);
        node.classList.add('link');
        node.style.cursor = 'pointer';
        return node;
    }
}

Quill.register(CustomLink, true);

window.Quill = Quill;

import moment from 'moment';

window.moment = moment;

import hljs from 'highlight.js';

hljs.configure({
    ignoreUnescapedHTML: true,
});

window.hljs = hljs;

import 'quill/dist/quill.snow.css';
import 'highlight.js/styles/github.css';

document.addEventListener('alpine:init', () => {
    Alpine.data('message', (messageId, timeout = 2000) => ({
        showMsg: false,
        timeout: timeout,

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

    Alpine.data('countdown', (count) => ({
        count,

        start(id) {
            const interval = setInterval(() => {
                this.count--;

                if (this.count === 0) {
                    clearInterval(interval);
                    window.dispatchEvent(new Event('counter-finished'));
                }
            }, 1000);
        },
    }));

    Alpine.data('copyToClipboard', () => ({
        isCopied: false,

        copy(text) {
            navigator.clipboard.writeText(text).then(() => {
                this.isCopied = true;
                setTimeout(() => {
                    this.isCopied = false;
                }, 3000);
            });
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
