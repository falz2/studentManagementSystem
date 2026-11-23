const CACHE_NAME = 'farmfresh-demo-v1';
const PAGE_URL = '/farmfresh-demo';

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.add(PAGE_URL))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return;
    }

    const requestUrl = new URL(event.request.url);
    const isFarmPhoto = requestUrl.hostname === 'images.unsplash.com' && event.request.destination === 'image';

    if (isFarmPhoto) {
        event.respondWith(
            caches.open(CACHE_NAME).then((cache) => cache.match(event.request).then((cached) => {
                if (cached) {
                    return cached;
                }

                return fetch(event.request)
                    .then((response) => {
                        if (response.ok || response.type === 'opaque') {
                            return cache.put(event.request, response.clone()).then(() => response);
                        }
                        return response;
                    })
                    .catch(() => Response.error());
            }))
        );
        return;
    }

    if (requestUrl.origin !== self.location.origin) {
        return;
    }

    if (requestUrl.pathname === PAGE_URL) {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    if (response.ok) {
                        const copy = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(PAGE_URL, copy));
                    }
                    return response;
                })
                .catch(() => caches.match(PAGE_URL))
        );
        return;
    }

    if (requestUrl.pathname.startsWith('/build/')) {
        event.respondWith(
            caches.match(event.request).then((cached) => cached || fetch(event.request).then((response) => {
                if (response.ok) {
                    const copy = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy));
                }
                return response;
            }))
        );
    }
});
