// sw.js - Service Worker corregido para Sistema Financiero PWA
const CACHE_NAME = 'finanzas-pwa-v1.1.0';
const STATIC_CACHE = 'static-v1.1.0';
const API_CACHE = 'api-v1.1.0';

// URLs que definitivamente existen y deben cachearse
const urlsToCache = [
  '/',
  '/index.php',
  '/login.php',
  '/manifest.json',
  '/offline.html'  // Página de fallback offline
];

// URLs de API que deben cachearse para offline
const apiUrls = [
  '/api.php?action=obtener_resumen',
  '/api.php?action=obtener_transacciones',
  '/auth.php?action=check'
];

// Instalación del Service Worker
self.addEventListener('install', event => {
  console.log('🔧 Service Worker: Instalando versión', CACHE_NAME);
  
  event.waitUntil(
    Promise.all([
      // Cache de archivos estáticos
      caches.open(STATIC_CACHE).then(cache => {
        console.log('📦 Cacheando archivos estáticos');
        return cache.addAll(urlsToCache).catch(error => {
          console.warn('⚠️ Algunos archivos no se pudieron cachear:', error);
          // Continuar aunque algunos archivos no existan
          return Promise.resolve();
        });
      }),
      
      // Cache de API
      caches.open(API_CACHE).then(cache => {
        console.log('📡 Preparando cache de API');
        return Promise.resolve();
      }),
      
      // Crear página offline si no existe
      createOfflinePage()
    ]).then(() => {
      console.log('✅ Service Worker instalado correctamente');
      return self.skipWaiting();
    }).catch(error => {
      console.error('❌ Error durante la instalación:', error);
    })
  );
});

// Crear página offline dinámica
function createOfflinePage() {
  const offlineHTML = `
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sin Conexión - Sistema Financiero</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            padding: 20px;
        }
        .offline-container {
            max-width: 500px;
            background: rgba(255,255,255,0.1);
            padding: 40px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
        }
        h1 { font-size: 3em; margin-bottom: 0.5em; }
        p { font-size: 1.2em; opacity: 0.9; margin: 15px 0; }
        button {
            margin: 10px;
            padding: 15px 30px;
            font-size: 16px;
            background: white;
            color: #667eea;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: transform 0.3s;
        }
        button:hover { transform: translateY(-2px); }
        .features {
            background: rgba(255,255,255,0.1);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            text-align: left;
        }
        .features ul { list-style: none; padding: 0; }
        .features li { margin: 10px 0; }
        .features li:before { content: "✓ "; color: #4CAF50; font-weight: bold; }
    </style>
</head>
<body>
    <div class="offline-container">
        <h1>📱💰</h1>
        <h1>Modo Offline</h1>
        <p>No hay conexión a Internet, pero tu Sistema Financiero sigue funcionando.</p>
        
        <div class="features">
            <h3>🚀 Disponible Offline:</h3>
            <ul>
                <li>Crear nuevas transacciones</li>
                <li>Ver datos guardados localmente</li>
                <li>Usar reconocimiento de voz</li>
                <li>Navegar por la aplicación</li>
                <li>Consultar estadísticas</li>
            </ul>
        </div>
        
        <button onclick="window.location.reload()">🔄 Reintentar Conexión</button>
        <button onclick="goOffline()">📱 Continuar Offline</button>
        
        <p><small>Los datos se sincronizarán automáticamente al reconectar</small></p>
    </div>
    
    <script>
        function goOffline() {
            if (window.location.pathname === '/offline.html') {
                window.location.href = '/index.php?offline=true';
            } else {
                window.location.reload();
            }
        }
        
        // Auto-retry cada 30 segundos
        setInterval(() => {
            if (navigator.onLine) {
                console.log('🌐 Conexión restaurada');
                window.location.reload();
            }
        }, 30000);
        
        // Mostrar estado de conexión
        window.addEventListener('online', () => {
            console.log('✅ Back online');
            window.location.reload();
        });
    </script>
</body>
</html>`;

  return caches.open(STATIC_CACHE).then(cache => {
    return cache.put('/offline.html', new Response(offlineHTML, {
      headers: { 'Content-Type': 'text/html' }
    }));
  });
}

// Activación del Service Worker
self.addEventListener('activate', event => {
  console.log('🚀 Service Worker: Activándose...');
  
  const cacheWhitelist = [STATIC_CACHE, API_CACHE];
  
  event.waitUntil(
    Promise.all([
      // Limpiar caches antiguos
      caches.keys().then(cacheNames => {
        return Promise.all(
          cacheNames.map(cacheName => {
            if (cacheWhitelist.indexOf(cacheName) === -1) {
              console.log('🗑️ Eliminando cache antiguo:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      }),
      
      // Tomar control inmediatamente
      self.clients.claim()
    ]).then(() => {
      console.log('✅ Service Worker activado y listo');
      
      // Notificar a los clientes
      self.clients.matchAll().then(clients => {
        clients.forEach(client => {
          client.postMessage({
            type: 'SW_ACTIVATED',
            version: CACHE_NAME
          });
        });
      });
    })
  );
});

// Interceptación de requests mejorada
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);
  
  // Solo manejar requests del mismo origen
  if (url.origin !== location.origin) {
    return;
  }
  
  // Estrategia específica para API
  if (request.url.includes('/api.php') || request.url.includes('/auth.php')) {
    event.respondWith(handleApiRequest(request));
    return;
  }
  
  // Estrategia para páginas HTML
  if (request.destination === 'document') {
    event.respondWith(handlePageRequest(request));
    return;
  }
  
  // Estrategia para recursos estáticos
  event.respondWith(handleStaticRequest(request));
});

// Manejo de requests de API - Network First con Cache Fallback
async function handleApiRequest(request) {
  const cacheName = API_CACHE;
  
  try {
    // Intentar primero la red
    const networkResponse = await fetch(request, {
      timeout: 5000  // Timeout de 5 segundos
    });
    
    // Si la respuesta es exitosa, guardarla en cache
    if (networkResponse && networkResponse.status === 200) {
      const responseToCache = networkResponse.clone();
      
      caches.open(cacheName).then(cache => {
        cache.put(request, responseToCache);
      });
    }
    
    return networkResponse;
    
  } catch (error) {
    console.log('📴 Network failed for API, trying cache:', request.url);
    
    // Si falla la red, intentar cache
    const cache = await caches.open(cacheName);
    const cachedResponse = await cache.match(request);
    
    if (cachedResponse) {
      console.log('📱 Serving from cache (offline):', request.url);
      return cachedResponse;
    }
    
    // Si no hay cache, devolver respuesta offline
    return new Response(JSON.stringify({
      success: false,
      message: 'Sin conexión - Funcionalidad limitada',
      offline: true,
      timestamp: new Date().toISOString()
    }), {
      status: 503,
      statusText: 'Service Unavailable - Offline',
      headers: new Headers({
        'Content-Type': 'application/json'
      })
    });
  }
}

// Manejo de requests de páginas - Cache First con Network Update
async function handlePageRequest(request) {
  const cache = await caches.open(STATIC_CACHE);
  
  try {
    // Intentar cache primero para páginas
    const cachedResponse = await cache.match(request);
    
    if (cachedResponse) {
      // Actualizar en background
      fetch(request).then(fetchResponse => {
        if (fetchResponse && fetchResponse.status === 200) {
          cache.put(request, fetchResponse.clone());
        }
      }).catch(() => {
        console.log('Background update failed for:', request.url);
      });
      
      return cachedResponse;
    }
    
    // Si no hay cache, ir a la red
    const networkResponse = await fetch(request);
    
    if (networkResponse && networkResponse.status === 200) {
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
    
  } catch (error) {
    console.log('📄 Page request failed, serving offline page');
    
    // Servir página offline
    const offlinePage = await cache.match('/offline.html');
    if (offlinePage) {
      return offlinePage;
    }
    
    // Fallback final
    return new Response(`
      <!DOCTYPE html>
      <html><head><title>Sin Conexión</title></head>
      <body style="font-family: Arial; text-align: center; padding: 50px;">
        <h1>📴 Sin Conexión</h1>
        <p>No hay conexión a Internet y no se pudo cargar la página.</p>
        <button onclick="location.reload()">🔄 Reintentar</button>
      </body></html>
    `, { headers: { 'Content-Type': 'text/html' } });
  }
}

// Manejo de recursos estáticos
async function handleStaticRequest(request) {
  const cache = await caches.open(STATIC_CACHE);
  
  // Cache First para recursos estáticos
  const cachedResponse = await cache.match(request);
  if (cachedResponse) {
    return cachedResponse;
  }
  
  // Si no está en cache, buscar en la red
  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse && networkResponse.status === 200) {
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    // Para recursos estáticos, no hay fallback
    return new Response('Recurso no disponible offline', { status: 404 });
  }
}

// Manejo de mensajes del cliente
self.addEventListener('message', event => {
  const { type, data } = event.data;
  
  switch (type) {
    case 'SKIP_WAITING':
      console.log('⏩ Service Worker: Skip waiting solicitado');
      self.skipWaiting();
      break;
      
    case 'CLEAR_CACHE':
      console.log('🗑️ Service Worker: Limpiando todos los caches...');
      caches.keys().then(cacheNames => {
        return Promise.all(
          cacheNames.map(cacheName => caches.delete(cacheName))
        );
      }).then(() => {
        console.log('✅ Todos los caches eliminados');
        event.ports[0].postMessage({ type: 'CACHE_CLEARED' });
      });
      break;
      
    case 'FORCE_UPDATE':
      console.log('🔄 Service Worker: Forzando actualización...');
      self.registration.update().then(() => {
        event.ports[0].postMessage({ type: 'UPDATE_COMPLETE' });
      });
      break;
      
    case 'GET_CACHE_STATUS':
      getCacheStatus().then(status => {
        event.ports[0].postMessage({ 
          type: 'CACHE_STATUS', 
          data: status 
        });
      });
      break;
  }
});

// Obtener estado del cache
async function getCacheStatus() {
  const cacheNames = await caches.keys();
  const status = {};
  
  for (const cacheName of cacheNames) {
    const cache = await caches.open(cacheName);
    const keys = await cache.keys();
    status[cacheName] = {
      count: keys.length,
      urls: keys.map(req => req.url)
    };
  }
  
  return status;
}

// Sincronización en background (Background Sync)
self.addEventListener('sync', event => {
  console.log('🔄 Background Sync triggered:', event.tag);
  
  if (event.tag === 'sync-transactions') {
    event.waitUntil(syncPendingTransactions());
  } else if (event.tag === 'cleanup-cache') {
    event.waitUntil(cleanupOldCache());
  }
});

// Sincronizar transacciones pendientes
async function syncPendingTransactions() {
  console.log('💾 Sincronizando transacciones pendientes...');
  
  try {
    // Notificar a todos los clientes que la sincronización comenzó
    const clients = await self.clients.matchAll();
    clients.forEach(client => {
      client.postMessage({
        type: 'SYNC_STARTED',
        message: 'Sincronizando transacciones...'
      });
    });
    
    // La sincronización real se maneja en el cliente
    // Aquí solo notificamos que está disponible
    clients.forEach(client => {
      client.postMessage({
        type: 'SYNC_AVAILABLE',
        message: 'Conexión restaurada - Sincronización disponible'
      });
    });
    
    console.log('✅ Sync notification sent');
    
  } catch (error) {
    console.error('❌ Sync failed:', error);
    
    const clients = await self.clients.matchAll();
    clients.forEach(client => {
      client.postMessage({
        type: 'SYNC_ERROR',
        message: 'Error en sincronización',
        error: error.message
      });
    });
  }
}

// Limpiar cache antiguo
async function cleanupOldCache() {
  console.log('🧹 Limpiando cache antiguo...');
  
  const cache = await caches.open(API_CACHE);
  const requests = await cache.keys();
  
  // Eliminar entradas de cache de más de 24 horas
  const now = Date.now();
  const maxAge = 24 * 60 * 60 * 1000; // 24 horas
  
  for (const request of requests) {
    const response = await cache.match(request);
    const date = response.headers.get('date');
    
    if (date) {
      const responseTime = new Date(date).getTime();
      if (now - responseTime > maxAge) {
        await cache.delete(request);
        console.log('🗑️ Eliminada entrada antigua del cache:', request.url);
      }
    }
  }
}

// Push notifications (opcional)
self.addEventListener('push', event => {
  console.log('📨 Push notification recibida');
  
  const options = {
    body: 'Tienes nuevas transacciones sincronizadas',
    icon: '/icons/icon-192x192.png',
    badge: '/icons/icon-72x72.png',
    tag: 'transaction-sync',
    data: {
      url: '/index.php'
    }
  };
  
  if (event.data) {
    const payload = event.data.json();
    options.body = payload.message || options.body;
  }
  
  event.waitUntil(
    self.registration.showNotification('Sistema Financiero', options)
  );
});

// Click en notificación
self.addEventListener('notificationclick', event => {
  console.log('📱 Notification clicked');
  
  event.notification.close();
  
  const url = event.notification.data.url || '/index.php';
  
  event.waitUntil(
    clients.matchAll().then(clientList => {
      // Si ya hay una ventana abierta, enfocarla
      for (const client of clientList) {
        if (client.url === url && 'focus' in client) {
          return client.focus();
        }
      }
      
      // Si no, abrir nueva ventana
      if (clients.openWindow) {
        return clients.openWindow(url);
      }
    })
  );
});

// Error handling global
self.addEventListener('error', event => {
  console.error('💥 Service Worker error:', event.error);
});

self.addEventListener('unhandledrejection', event => {
  console.error('💥 Service Worker unhandled rejection:', event.reason);
});

console.log('🚀 Service Worker loaded successfully');

// Utilidades adicionales
const utils = {
  // Verificar si una URL es cacheable
  isCacheable: (url) => {
    const uncacheablePatterns = [
      /\/api\.php.*action=logout/,
      /\/auth\.php.*action=logout/,
      /\.(php|asp|jsp).*[\?&]time=/,
      /\/setup\.php/
    ];
    
    return !uncacheablePatterns.some(pattern => pattern.test(url));
  },
  
  // Generar respuesta de error consistente
  createErrorResponse: (message, status = 500) => {
    return new Response(JSON.stringify({
      success: false,
      message,
      offline: true,
      timestamp: new Date().toISOString(),
      source: 'service_worker'
    }), {
      status,
      headers: { 'Content-Type': 'application/json' }
    });
  },
  
  // Log con timestamp
  log: (message, level = 'info') => {
    const timestamp = new Date().toISOString();
    console.log(`[${timestamp}] [SW] [${level.toUpperCase()}] ${message}`);
  }
};

// Exportar utilidades para testing (si está disponible)
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { utils };
}