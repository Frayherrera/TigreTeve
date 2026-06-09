// import '../css/home.css';
import './bootstrap';
import './responsive';
import 'bootstrap';

// Import icons for PWA
import icon192 from '../icons/icon-192.png';
import icon512 from '../icons/icon-512.png';

// Make icons available globally
window.PWA_ICONS = {
    icon192: icon192,
    icon512: icon512
};

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js', { scope: '/' }).then(function (registration) {
        console.log(`SW registered successfully!`);
    }).catch(function (registrationError) {
        console.log(`SW registration failed`);
    });
}

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
