// sw.js
const CACHE_NAME = 'my-app-cache-v2'; // Ubah versi untuk memastikan cache diperbarui
const urlsToCache = [
    '/',                    // Root situs
    '/index.php',          // Halaman utama pengguna
    '/login.php',          // Halaman login
    '/manage_access.php',  // Halaman manajemen akses
    '/admin.php',          // Halaman admin (tambahan)
    '/admin_login.php',    // Halaman login admin (tambahan)
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap',
    '/offline.html'        // Halaman fallback khusus saat offline
];

// Install: Cache semua aset
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Caching files');
                return cache.addAll(urlsToCache);
            })
            .catch(err => console.log('Caching failed:', err))
    );
    self.skipWaiting(); // Aktifkan SW segera tanpa menunggu refresh
});

// Activate: Hapus cache lama
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.filter(name => name !== CACHE_NAME)
                    .map(name => caches.delete(name))
            );
        })
    );
    self.clients.claim(); // Ambil kendali halaman segera
});

// Fetch: Tangani request dengan cache-first strategy
self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(cachedResponse => {
                // Jika ada di cache, kembalikan dari cache
                if (cachedResponse) {
                    return cachedResponse;
                }
                // Jika tidak ada di cache, coba fetch dari jaringan
                return fetch(event.request).catch(() => {
                    // Jika offline dan tidak ada di cache, kembalikan offline.html
                    return caches.match('/offline.html');
                });
            })
    );
});