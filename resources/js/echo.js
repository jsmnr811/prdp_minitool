// import Echo from 'laravel-echo';

// import Pusher from 'pusher-js';
// window.Pusher = Pusher;

// window.Echo = new Echo({
//     broadcaster: 'reverb',
//     key: import.meta.env.VITE_REVERB_APP_KEY,
//     wsHost: import.meta.env.VITE_REVERB_HOST,
//     wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
//     wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
//     forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });

// window.Echo = new Echo({
//   broadcaster: 'reverb', // must match your custom connector name
//   key: import.meta.env.VITE_REVERB_APP_KEY,
//   wsHost: import.meta.env.VITE_REVERB_HOST,
//   wsPort: import.meta.env.VITE_REVERB_PORT ?? 443,
//   wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
//   secure: true,
//   forceTLS: true,  // since your site uses HTTPS
//   enabledTransports: ['ws', 'wss'],
//   path: '/app', // VERY IMPORTANT — matches Apache ProxyPass path
// });


// // window.Echo = new Echo({
// //     broadcaster: 'reverb',
// //     key: import.meta.env.VITE_REVERB_APP_KEY,
// //     wsHost: import.meta.env.VITE_REVERB_HOST,
// //     wsPort: import.meta.env.VITE_REVERB_PORT,
// //     wssPort: import.meta.env.VITE_REVERB_PORT,
// //     forceTLS: true,
// //     enabledTransports: ['ws', 'wss'],
// // });
// // Echo.channel('commodities-updates')
// //     .listen('.geo.commodity.updated', (e) => {
// //         console.log('✅ Received event:', e);
// //     });

// // Debug: See ALL events, even ones not matched
// Echo.connector.pusher.bind_global((event, data) => {
//     console.log('🔔 Global Event:', event, data);
// });

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const echoConfig = {
    broadcaster: 'reverb', // Custom broadcaster name
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 443, // Default to 443 for secure connection
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443, // Default to 443 for secure WebSocket
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https', // Enforce TLS in production
    secure: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https', // Ensure secure connection
    enabledTransports: ['ws', 'wss'], // Use WebSocket (ws) or Secure WebSocket (wss)
    path: '/app', // Path matching your server-side configuration (e.g., Apache ProxyPass)
};

// Create Echo instance
window.Echo = new Echo(echoConfig);

// Handle connection errors and retries (production best practices)
window.Echo.connector.pusher.connection.bind('state_change', (states) => {
    if (states.current === 'disconnected') {
        console.warn('WebSocket disconnected, attempting to reconnect...');
    }
    if (states.current === 'connected') {
        console.log('WebSocket connected!');
    }
});

// Debug: See ALL events, even ones not matched (remove in production for security)
window.Echo.connector.pusher.bind_global((event, data) => {
    console.log('🔔 Global Event:', event, data);
});
