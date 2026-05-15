import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

let echo = null;

export function getEcho() {
  if (echo) return echo;

  window.Pusher = Pusher;

  echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY || 'eldorado-key',
    wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
    wsPort: Number(import.meta.env.VITE_REVERB_PORT || 8080),
    wssPort: Number(import.meta.env.VITE_REVERB_PORT || 8080),
    forceTLS: false,
    enabledTransports: ['ws'],
    authEndpoint: '/api/broadcasting/auth',
    auth: {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('eldorado_token') || ''}`,
        Accept: 'application/json'
      }
    }
  });

  return echo;
}

export function leaveChannel(channel) {
  if (echo && channel) {
    echo.leave(channel);
  }
}
