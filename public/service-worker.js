// CereiaPet Service Worker v1.0.1
const CACHE_NAME = 'cereiapet-v1';

// Install event - skip precaching to avoid errors
self.addEventListener('install', (event) => {
    console.log('[SW] Installing...');
    self.skipWaiting();
});

// Activate event - clean old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => {
                        console.log('[SW] Deleting old cache:', name);
                        return caches.delete(name);
                    })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch event - Network first, cache for offline
self.addEventListener('fetch', (event) => {
    // Skip cross-origin requests
    if (!event.request.url.startsWith(self.location.origin)) {
        return;
    }

    // Skip non-GET requests
    if (event.request.method !== 'GET') {
        return;
    }

    // Skip API and dynamic requests
    if (event.request.url.includes('/salvar') ||
        event.request.url.includes('/login') ||
        event.request.url.includes('/logout') ||
        event.request.url.includes('/excluir')) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Only cache successful responses
                if (response.ok) {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseClone);
                    });
                }
                return response;
            })
            .catch(() => {
                // Network failed, try cache
                return caches.match(event.request)
                    .then((cachedResponse) => {
                        if (cachedResponse) {
                            return cachedResponse;
                        }
                        // Return basic offline response
                        if (event.request.mode === 'navigate') {
                            return new Response(`
                                <!DOCTYPE html>
                                <html>
                                <head>
                                    <meta charset="UTF-8">
                                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                    <title>Offline - CereiaPet</title>
                                    <style>
                                        body { font-family: sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: linear-gradient(135deg, #1e3a8a, #3b82f6); color: white; text-align: center; }
                                        .container { padding: 20px; }
                                        h1 { font-size: 1.5rem; margin-bottom: 10px; }
                                        p { opacity: 0.8; }
                                        button { margin-top: 20px; padding: 12px 24px; background: white; color: #1e40af; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; }
                                    </style>
                                </head>
                                <body>
                                    <div class="container">
                                        <h1>🐾 Você está offline</h1>
                                        <p>Verifique sua conexão e tente novamente.</p>
                                        <button onclick="location.reload()">Tentar Novamente</button>
                                    </div>
                                </body>
                                </html>
                            `, { headers: { 'Content-Type': 'text/html' } });
                        }
                        return new Response('Offline', { status: 503 });
                    });
            })
    );
});
