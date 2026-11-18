const CACHE_NAME = 'v1.0.0';

const cacheAssets = [
    '/favicon.ico',
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            cache.addAll(cacheAssets);
        })
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keyList => {
            return Promise.all(keyList.map(key => {
                if (key !== CACHE_NAME) {
                    return caches.delete(key);
                }
            }));
        })
    );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        caches.open(CACHE_NAME).then(cache => {
            return cache.match(event.request).then(response => {
                return response || fetch(event.request);
            });
        })
    );
});

// ===== Notificaciones push de noticias =====
self.addEventListener('push', event => {
    let datos = {};
    try {
        datos = event.data ? event.data.json() : {};
    } catch (e) {
        datos = { title: 'TigreTeve', body: event.data ? event.data.text() : '' };
    }

    const opciones = {
        body: datos.body || '',
        icon: '/img/logo.png',
        badge: '/favicon.ico',
        tag: datos.tag || 'tigreteve',
        data: { url: datos.url || '/' },
        lang: 'es',
    };
    if (datos.image) {
        opciones.image = datos.image;
    }

    event.waitUntil(self.registration.showNotification(datos.title || 'TigreTeve', opciones));
});

self.addEventListener('notificationclick', event => {
    event.notification.close();
    const url = (event.notification.data && event.notification.data.url) || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(ventanas => {
            for (const ventana of ventanas) {
                if (ventana.url === url && 'focus' in ventana) {
                    return ventana.focus();
                }
            }
            return clients.openWindow(url);
        })
    );
});
