import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: window.location.hostname,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
});

// Listen for your broadcasted event
window.Echo.channel('uploads')
    .listen('UploadStatusUpdated', (e) => {
        console.log('Realtime update:', e);
        // Example: update status cell
        $(`#row-${e.upload.id} .status`).text(e.upload.status);
        $(`#row-${e.upload.id} .file_progress`).text(e.upload.progress + "%");
    });
