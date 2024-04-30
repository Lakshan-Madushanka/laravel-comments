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
            console.log(messageId === id)
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
