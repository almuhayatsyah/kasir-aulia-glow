const CACHE_NAME = 'aulia-glow-v2';

const STATIC_ASSETS = [
    '/manifest.json',
    '/img/logoaulia.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keyList) => {
            return Promise.all(keyList.map((key) => {
                if (key !== CACHE_NAME) {
                    return caches.delete(key);
                }
            }));
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    // Only handle GET requests, bypass cache for POST/PUT/DELETE
    if (event.request.method !== 'GET') {
        return;
    }

    // Bypass cache for Laravel Livewire updates or non-http protocols
    if (!event.request.url.startsWith(self.location.origin) || event.request.url.includes('/livewire/')) {
        return;
    }

    // Network-First Strategy for document requests (HTML pages)
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .catch(() => {
                    return caches.match(event.request) || caches.match('/offline.html');
                })
        );
        return;
    }

    // Cache-First Strategy for static assets (Images, CSS, JS)
    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }

            return fetch(event.request).then((networkResponse) => {
                // Cache dynamic static assets
                if (networkResponse.status === 200 && 
                    (event.request.url.match(/\.(js|css|png|jpg|jpeg|svg|woff2|ico)$/) || event.request.url.includes('/build/'))) {
                    const responseClone = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseClone);
                    });
                }
                return networkResponse;
            }).catch(() => {
                // Offline fallback for images
                if (event.request.url.match(/\.(png|jpg|jpeg|svg)$/)) {
                    return caches.match('/img/logoaulia.png');
                }
            });
        })
    );
});
