import './bootstrap';
import { registerSW } from 'virtual:pwa-register';

registerSW({
    onRegistered(registration) {
        console.log('Service worker registered with scope:', registration?.scope);
    },
    onRegisterError(error) {
        console.error('Service worker registration failed:', error);
    },
});
