const CACHE_NAME = 'mdi-field-app-v1';

// These are the files the phone will download and save for offline use
const urlsToCache = [
    './mobile.php',
    './assets/js/mobile.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://unpkg.com/html5-qrcode'
];

// 1. When the app is opened, install the cache
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Opened cache and saving assets');
                return cache.addAll(urlsToCache);
            })
    );
});

// 2. When the app tries to load something, check the network first. If offline, serve from cache!
self.addEventListener('fetch', event => {
    // Only intercept GET requests (we don't cache POST requests)
    if (event.request.method !== 'GET') return;

    event.respondWith(
        fetch(event.request).catch(() => {
            // If the fetch fails (because there's no internet), pull it from the cache
            return caches.match(event.request);
        })
    );
});

// 3. Clear old caches if we update the app version
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});