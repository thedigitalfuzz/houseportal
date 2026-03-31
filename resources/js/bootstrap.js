import axios from 'axios';
window.axios = axios;
import Echo from 'laravel-echo';
window.Pusher = require('pusher-js');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_c7d977f2f9853c9d079b,
    cluster: import.meta.env.VITE_ap2,
    forceTLS: true
});
window.Echo.channel('chat.' + selectedChannel)
    .listen('MessageSent', (e) => {
        Livewire.emit('handleIncomingMessage', e.message);
    });
