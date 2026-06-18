const CACHE_NAME = 'prontuario-pwa-v1';
const OFFLINE_URL = '/offline.html';

// Arquivos para cachear imediatamente durante a instalação
const PRECACHE_ASSETS = [
    OFFLINE_URL,
    '/favicon.ico',
    '/favicon-16x16.png',
    '/favicon-32x32.png',
    '/android-chrome-192x192.png',
    '/android-chrome-512x512.png',
    '/apple-touch-icon.png'
];

// Instalação do Service Worker
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS);
        }).then(() => self.skipWaiting())
    );
});

// Ativação e limpeza de caches antigos
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Intercepção de requisições
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Ignorar requisições não-GET (como posts do Livewire, uploads, etc.)
    if (request.method !== 'GET') {
        return;
    }

    // Estratégia para navegação (páginas HTML)
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => {
                // Se o fetch falhar (sem internet), retorna o fallback offline
                return caches.match(OFFLINE_URL);
            })
        );
        return;
    }

    // Estratégia para assets estáticos (CSS, JS, Imagens, Fontes)
    const isStaticAsset = 
        url.pathname.includes('/build/assets/') || // Pasta de builds do Vite
        url.pathname.endsWith('.css') ||
        url.pathname.endsWith('.js') ||
        url.pathname.endsWith('.png') ||
        url.pathname.endsWith('.jpg') ||
        url.pathname.endsWith('.jpeg') ||
        url.pathname.endsWith('.svg') ||
        url.pathname.endsWith('.ico') ||
        url.pathname.endsWith('.webp') ||
        url.pathname.endsWith('.woff') ||
        url.pathname.endsWith('.woff2') ||
        url.pathname.endsWith('.ttf') ||
        url.pathname.endsWith('.eot');

    if (isStaticAsset) {
        event.respondWith(
            caches.open(CACHE_NAME).then((cache) => {
                return cache.match(request).then((cachedResponse) => {
                    // Retorna do cache se existir, mas busca atualizações em background (Stale-While-Revalidate)
                    const fetchPromise = fetch(request).then((networkResponse) => {
                        if (networkResponse.status === 200) {
                            cache.put(request, networkResponse.clone());
                        }
                        return networkResponse;
                    }).catch(() => {
                        // Se falhar a rede, não faz nada, pois já retornamos o cache
                    });

                    return cachedResponse || fetchPromise;
                });
            })
        );
        return;
    }

    // Para qualquer outra requisição, usar apenas a rede (Network Only)
    // Isso garante que requisições de API, updates do Livewire, etc., não sejam cacheados.
});
