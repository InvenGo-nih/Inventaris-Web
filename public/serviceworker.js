var staticCacheName = "pwa-v" + new Date().getTime();
var filesToCache = [
    "/offline",
    // Tambahkan file-file penting untuk halaman offline di sini
];

// Cache on install - hanya cache file offline
self.addEventListener("install", (event) => {
    this.skipWaiting();
    event.waitUntil(
        caches.open(staticCacheName).then((cache) => {
            return cache.addAll(filesToCache);
        })
    );
});

// Clear cache on activate
self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((cacheName) => cacheName.startsWith("pwa-"))
                    .filter((cacheName) => cacheName !== staticCacheName)
                    .map((cacheName) => caches.delete(cacheName))
            );
        })
    );
});

// Strategi "Network Only" dengan fallback ke cache hanya saat offline
self.addEventListener("fetch", (event) => {
    // Jangan intercept permintaan non-GET
    if (event.request.method !== "GET") {
        return;
    }

    // Jangan intercept permintaan ke CDN atau sumber eksternal
    if (
        event.request.url.includes("fonts.googleapis.com") ||
        event.request.url.includes("fonts.gstatic.com") ||
        event.request.url.match(
            /\.(css|js|jpg|jpeg|png|gif|ico|woff|woff2|ttf|svg)$/
        )
    ) {
        return; // Biarkan browser menangani permintaan ini secara normal
    }

    // Hanya tangani permintaan navigasi (HTML)
    if (event.request.mode === "navigate") {
        event.respondWith(
            fetch(event.request).catch(() => {
                // Jika offline, tampilkan halaman offline
                return caches.match("/offline");
            })
        );
    }
});
