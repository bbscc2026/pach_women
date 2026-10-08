{{-- Service worker for the shop and the admin. Served at /sw.js by PwaController. --}}
/* PACH WOMEN service worker — version {{ $version }} */
const VERSION = @json($version);
const STATIC_CACHE = 'pw-static-' + VERSION;
const PAGE_CACHE = 'pw-pages-v1';
const IMAGE_CACHE = 'pw-images-v1';
const PRECACHE = @json($precache);
const OFFLINE_URL = '/offline';

// Pages that must never be served from the offline copy.
const NEVER_CACHE = [/^\/checkout/, /^\/payment\//, /^\/reset-password\//, /^\/order\/.+\/thank-you/, /^\/logout/, /^\/admin\/logout/, /^\/sw\.js/];

self.addEventListener('install', (event) => {
    // Don't take over straight away: the page shows "Update" and the user decides.
    event.waitUntil(caches.open(STATIC_CACHE).then((cache) => cache.addAll(PRECACHE)));
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const keys = await caches.keys();
        await Promise.all(keys.filter((k) => k.startsWith('pw-static-') && k !== STATIC_CACHE).map((k) => caches.delete(k)));
        await self.clients.claim();
    })());
});

self.addEventListener('message', (event) => {
    if (event.data === 'SKIP_WAITING') self.skipWaiting();
});

async function trim(cacheName, max) {
    const cache = await caches.open(cacheName);
    const keys = await cache.keys();
    for (let i = 0; i < keys.length - max; i++) await cache.delete(keys[i]);
}

// CSS, JS, fonts, icons: serve from cache, fetch once.
async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) return cached;
    const response = await fetch(request);
    if (response.ok) (await caches.open(STATIC_CACHE)).put(request, response.clone());
    return response;
}

// Product photos: show the saved copy instantly, refresh it in the background.
async function staleWhileRevalidate(request) {
    const cache = await caches.open(IMAGE_CACHE);
    const cached = await cache.match(request);
    const network = fetch(request).then((response) => {
        if (response.ok) cache.put(request, response.clone()).then(() => trim(IMAGE_CACHE, 200));
        return response;
    }).catch(() => cached);
    return cached || network;
}

// Pages: always try the internet first (fresh prices and stock); fall back to the last saved copy.
async function networkFirst(request, cacheable) {
    try {
        const response = await fetch(request);
        if (cacheable && response.ok && response.type === 'basic') {
            const cache = await caches.open(PAGE_CACHE);
            cache.put(request, response.clone()).then(() => trim(PAGE_CACHE, 80));
        }
        return response;
    } catch (error) {
        const cached = await caches.match(request, { ignoreVary: true });
        if (cached) return cached;
        return (await caches.match(OFFLINE_URL)) || new Response('You are offline.', { status: 503, headers: { 'Content-Type': 'text/plain' } });
    }
}

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Only same-origin GETs; Livewire actions, form posts and payment scripts go straight to the network.
    if (request.method !== 'GET' || url.origin !== self.location.origin) return;

    const path = url.pathname;

    if (path.startsWith('/build/') || path.startsWith('/icons/') || path.startsWith('/fonts/') ||
        path.startsWith('/css/') || path.startsWith('/js/') || path === '/pwa.js' ||
        (path.startsWith('/livewire') && path.endsWith('.js'))) {
        event.respondWith(cacheFirst(request));
        return;
    }

    if (path.startsWith('/storage/')) {
        event.respondWith(staleWhileRevalidate(request));
        return;
    }

    const wantsHtml = request.mode === 'navigate' || (request.headers.get('accept') || '').includes('text/html');
    if (wantsHtml) {
        event.respondWith(networkFirst(request, ! NEVER_CACHE.some((rule) => rule.test(path))));
    }
});
