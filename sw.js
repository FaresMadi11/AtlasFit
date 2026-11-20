// ══════════════════════════════════════════════════════════════
//  Atlas Fit — Service Worker (Offline-First 3 Vistas + Fast Race)
// ══════════════════════════════════════════════════════════════

const SW_VER = new URL(self.location.href).searchParams.get('v') || '5.0';
const STATIC_CACHE = 'atlas-fit-static-' + SW_VER;
const USER_CACHE   = 'atlas-fit-user-' + SW_VER;
const META_PATH    = '/__atlas_offline_meta__';

const TRUSTED_CDN_HOSTS = new Set([
  'cdn.jsdelivr.net',
  'fonts.googleapis.com',
  'fonts.gstatic.com',
  'cdnjs.cloudflare.com'
]);

function getScopeBase() {
  return (self.registration && self.registration.scope
    ? self.registration.scope
    : (self.location.origin + '/')
  ).replace(/\/+$/, '');
}

function getMetaUrl() {
  return getScopeBase() + META_PATH;
}

function safeCachePut(cacheName, req, response, allowOpaque = false) {
  if (!req || !response) return;
  const reqUrl = typeof req === 'string' ? req : req.url;
  if (!reqUrl) return;
  const okStatus = response.status === 200 || (allowOpaque && response.type === 'opaque');
  if (!okStatus) return;
  if (!reqUrl.startsWith('http://') && !reqUrl.startsWith('https://')) return;
  try {
    const copy = response.clone();
    caches.open(cacheName).then(cache => cache.put(req, copy).catch(() => {})).catch(() => {});
  } catch (_) {}
}

async function readOfflineMeta(userCache) {
  try {
    const res = await userCache.match(getMetaUrl());
    if (!res) return null;
    const data = await res.json();
    return (data && typeof data === 'object') ? data : null;
  } catch (_) {
    return null;
  }
}

async function writeOfflineMeta(userCache, role, homePath) {
  try {
    const payload = JSON.stringify({
      role: role || '',
      homePath: homePath || '',
      updatedAt: Date.now()
    });
    await userCache.put(
      getMetaUrl(),
      new Response(payload, { headers: { 'Content-Type': 'application/json; charset=utf-8' } })
    );
  } catch (_) {}
}

function inferRoleFromUrl(u) {
  const low = String(u || '').toLowerCase();
  if (low.includes('/admin/')) return 'admin';
  if (low.includes('/entrenador/')) return 'entrenador';
  if (low.includes('/cliente/')) return 'cliente';
  return '';
}

function homePathForRole(role) {
  if (role === 'admin') return '/admin/rutina';
  if (role === 'entrenador') return '/entrenador/dashboard';
  if (role === 'cliente') return '/cliente/dashboard';
  return '';
}

function fetchWithTimeout(reqOrPromise, ms) {
  return new Promise((resolve, reject) => {
    let settled = false;
    const timer = setTimeout(() => {
      if (!settled) {
        settled = true;
        reject(new Error('SW network timeout'));
      }
    }, ms);
    Promise.resolve(reqOrPromise).then(
      val => { if (!settled) { settled = true; clearTimeout(timer); resolve(val); } },
      err => { if (!settled) { settled = true; clearTimeout(timer); reject(err); } }
    );
  });
}

// Recursos críticos del App Shell (nunca se borran al cerrar sesión)
const PRECACHE_ASSETS = [
  './favicon.ico',
  './assets/css/style.css',
  './assets/css/custom-select.css',
  './assets/css/custom-date.css',
  './assets/css/cookies.css',
  './assets/js/app.js',
  './assets/js/pwa-sync.js',
  './assets/js/custom-select.js',
  './assets/js/custom-date.js',
  './assets/js/cookies.js',
  './assets/vendor/sweetalert2/sweetalert2.all.min.js',
  './assets/vendor/animejs/anime.min.js',
  './assets/vendor/chartjs/chart.min.js',
  './assets/vendor/tabler-icons/tabler-icons.min.css',
  './assets/vendor/tabler-icons/fonts/tabler-icons.woff2',
  './assets/img/icon-192.png',
  './assets/img/icon-512.png',
  './assets/img/icon-maskable-512.png',
  './assets/img/favicon.svg',
  './assets/img/favicon-32.png',
  './assets/img/apple-touch-icon.png'
];

// Instalación: Precarga fresca del App Shell en STATIC_CACHE
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(STATIC_CACHE).then(cache => {
      return Promise.allSettled(
        PRECACHE_ASSETS.map(url =>
          fetch(new Request(url, { cache: 'reload' }))
            .then(res => {
              if (res.ok) return cache.put(url, res);
            })
            .catch(err => console.warn('[PWA] Fallo precache:', url, err))
        )
      );
    }).then(() => self.skipWaiting())
  );
});

self.addEventListener('message', event => {
  if (!event.data) return;
  if (event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  } else if (event.data.type === 'CLEAR_USER_CACHE') {
    // Al cerrar sesión se borra SOLO la caché privada del usuario; el App Shell estático permanece intacto
    event.waitUntil(
      caches.keys().then(keys => Promise.all(
        keys
          .filter(k => k.startsWith('atlas-fit-user-') || k === USER_CACHE)
          .map(k => caches.delete(k))
      ))
    );
  } else if (event.data.type === 'CLEAR_CACHES') {
    event.waitUntil(
      caches.keys().then(keys => Promise.all(keys.map(k => caches.delete(k))))
    );
  }
});

// Activación: Purga de cachés de versiones anteriores + Navigation Preload
self.addEventListener('activate', event => {
  event.waitUntil(
    Promise.all([
      caches.keys().then(keys => Promise.all(
        keys.filter(key => key !== STATIC_CACHE && key !== USER_CACHE).map(key => {
          console.log('[PWA] Purgando caché obsoleta:', key);
          return caches.delete(key);
        })
      )),
      (self.registration && self.registration.navigationPreload)
        ? self.registration.navigationPreload.enable().catch(() => {})
        : Promise.resolve()
    ]).then(() => self.clients.claim())
  );
});

// Resolver el HTML offline correcto para las 3 vistas (Cliente, Entrenador, Admin)
async function resolveOfflineHtml(userCache, req, pathname) {
  const keys = await userCache.keys();
  const base = getScopeBase();
  const isRootPath = (
    pathname === '/' ||
    pathname.endsWith('/') ||
    pathname.endsWith('/index.php')
  );

  // 1. Coincidencia exacta (respetando query si existe, por ej. perfil-cliente?id=5)
  const exactMatch = await userCache.match(req);
  if (exactMatch) return exactMatch;

  // 2. Coincidencia por ruta base ignorando query (excepto raíz para no servir vista equivocada)
  if (!isRootPath && !pathname.includes('/auth/')) {
    const cleanMatch = await userCache.match(req, { ignoreSearch: true });
    if (cleanMatch) return cleanMatch;
  }

  const meta = await readOfflineMeta(userCache);
  const metaRole = (meta && meta.role) ? meta.role : '';
  const metaHome = (meta && meta.homePath) ? meta.homePath : homePathForRole(metaRole);

  // 3. Si es raíz (start_url) o un /auth/login rescatado offline con sesión previa activa
  if (isRootPath || pathname.includes('/auth/login') || pathname.endsWith('/auth')) {
    if (metaHome) {
      const homeRes = await userCache.match(base + metaHome, { ignoreSearch: true });
      if (homeRes) return homeRes;
    }
    const rootRes = await userCache.match(base + '/', { ignoreSearch: true });
    if (rootRes) return rootRes;

    const preferredOrder = [
      '/admin/rutina',
      '/entrenador/dashboard',
      '/cliente/dashboard',
      '/cliente/rutina-dia',
      '/cliente/perfil',
      '/entrenador/clientes',
      '/admin/gestion'
    ];
    for (const p of preferredOrder) {
      const k = keys.find(item => item.url.toLowerCase().includes(p));
      if (k) {
        const matched = await userCache.match(k);
        if (matched) return matched;
      }
    }
    return null;
  }

  // 4. Fallbacks semánticos por vista y módulo (sin cruzar vistas entre roles)
  if (pathname.includes('/admin/')) {
    const adminTargets = ['/admin/rutina', '/admin/gestion', '/admin/configuracion'];
    if (pathname.includes('/gestion')) {
      const k = keys.find(item => item.url.toLowerCase().includes('/admin/gestion'));
      if (k) return await userCache.match(k);
    }
    if (pathname.includes('/configuracion')) {
      const k = keys.find(item => item.url.toLowerCase().includes('/admin/configuracion'));
      if (k) return await userCache.match(k);
    }
    for (const p of adminTargets) {
      const k = keys.find(item => item.url.toLowerCase().includes(p));
      if (k) return await userCache.match(k);
    }
    return null;
  }

  if (pathname.includes('/entrenador/')) {
    const mapEnt = [
      ['/perfil-cliente', '/entrenador/perfil-cliente'],
      ['/crear-rutina', '/entrenador/crear-rutina'],
      ['/clientes', '/entrenador/clientes'],
      ['/asistencias', '/entrenador/asistencias'],
      ['/configuracion', '/entrenador/configuracion'],
      ['/dashboard', '/entrenador/dashboard']
    ];
    for (const [needle, target] of mapEnt) {
      if (pathname.includes(needle)) {
        const k = keys.find(item => item.url.toLowerCase().includes(target));
        if (k) return await userCache.match(k);
      }
    }
    const fallbackEnt = keys.find(item => item.url.toLowerCase().includes('/entrenador/dashboard'))
      || keys.find(item => item.url.toLowerCase().includes('/entrenador/'));
    if (fallbackEnt) return await userCache.match(fallbackEnt);
    return null;
  }

  if (pathname.includes('/cliente/')) {
    const mapCli = [
      ['/rutina', '/cliente/rutina-dia'],
      ['/estadisticas', '/cliente/estadisticas'],
      ['/perfil', '/cliente/perfil'],
      ['/dashboard', '/cliente/dashboard']
    ];
    for (const [needle, target] of mapCli) {
      if (pathname.includes(needle)) {
        const k = keys.find(item => item.url.toLowerCase().includes(target));
        if (k) return await userCache.match(k);
      }
    }
    const fallbackCli = keys.find(item => item.url.toLowerCase().includes('/cliente/dashboard'))
      || keys.find(item => item.url.toLowerCase().includes('/cliente/'));
    if (fallbackCli) return await userCache.match(fallbackCli);
  }

  return null;
}

// Intercepción de peticiones (Fetch)
self.addEventListener('fetch', event => {
  const req = event.request;
  if (req.method !== 'GET') return;

  const url = new URL(req.url);
  if (url.protocol !== 'http:' && url.protocol !== 'https:') return;

  // ── CDNs UI confiables (Bootstrap, Google Fonts, SortableJS): Cache-First en STATIC_CACHE ──
  if (url.origin !== self.location.origin) {
    if (!TRUSTED_CDN_HOSTS.has(url.hostname)) return;
    event.respondWith(
      caches.open(STATIC_CACHE).then(async cache => {
        const cached = await cache.match(req);
        const netFetch = fetch(req).then(netRes => {
          safeCachePut(STATIC_CACHE, req, netRes, true);
          return netRes;
        });
        if (cached) {
          netFetch.catch(() => {});
          return cached;
        }
        return netFetch.catch(() => new Response('', { status: 408, statusText: 'Offline CDN' }));
      })
    );
    return;
  }

  // Ignorar el propio Service Worker
  const pathname = url.pathname.toLowerCase();
  if (pathname.endsWith('/sw.js')) return;

  // Manifest PWA siempre fresco con fallback estático
  if (pathname.endsWith('/manifest.webmanifest')) {
    event.respondWith(
      fetch(new Request(req, { cache: 'no-store' }))
        .then(res => {
          safeCachePut(STATIC_CACHE, req, res);
          return res;
        })
        .catch(() => caches.open(STATIC_CACHE).then(c => c.match(req)).then(c => c || new Response('{}', {
          headers: { 'Content-Type': 'application/manifest+json; charset=utf-8' }
        })))
    );
    return;
  }

  // ── REGLA ZERO-TRUST 1: NUNCA INTERCEPTAR OAUTH, VERSION CHECK, LOGOUT NI MUTACIONES ──
  if (
    pathname.includes('/public/version') ||
    pathname.includes('/public/manifest') ||
    pathname.includes('/public/ping') ||
    pathname.includes('/logout') ||
    pathname.includes('/google') ||
    pathname.includes('/do-') ||
    pathname.includes('/registro') ||
    pathname.includes('/recuperar') ||
    url.searchParams.has('logged_out') ||
    url.searchParams.has('standby') ||
    pathname.includes('guardar') ||
    pathname.includes('iniciar') ||
    pathname.includes('marcar') ||
    pathname.includes('posponer') ||
    pathname.includes('cancelar')
  ) {
    return;
  }

  const isHtmlNavigation = req.mode === 'navigate' || (req.headers.get('accept') && req.headers.get('accept').includes('text/html'));

  // ── REGLA 1.2: /auth/login -> Online siempre red fresca; Offline con sesión guardada rescata al home cacheado ──
  if (pathname.includes('/auth/') || pathname.endsWith('/auth')) {
    if (isHtmlNavigation && !url.searchParams.has('ref') && (pathname.includes('/auth/login') || pathname.endsWith('/auth'))) {
      event.respondWith((async () => {
        try {
          return await fetch(new Request(req, { cache: 'no-store' }));
        } catch (_) {
          const userCache = await caches.open(USER_CACHE);
          const meta = await readOfflineMeta(userCache);
          if (meta && meta.role) {
            const rescued = await resolveOfflineHtml(userCache, req, pathname);
            if (rescued) return rescued;
          }
          return new Response(
            `<!DOCTYPE html><html lang="es" data-theme="dark"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Sin Conexión – Atlas Fit</title><style>body{background:#0a0a0f;color:#fff;font-family:system-ui,-apple-system,sans-serif;display:grid;place-items:center;min-height:100vh;margin:0;padding:20px;text-align:center}.card{background:rgba(20,20,30,0.9);border:1px solid rgba(138,43,226,0.35);border-radius:22px;padding:28px 22px;max-width:360px}h1{font-size:1.25rem;margin:0 0 8px}p{color:#94a3b8;font-size:.86rem;line-height:1.5;margin:0 0 20px}.btn{background:linear-gradient(135deg,#8a2be2,#6d28d9);color:#fff;padding:11px 22px;border-radius:12px;border:none;font-weight:700;cursor:pointer}</style></head><body><div class="card"><h1>Sin conexión</h1><p>Inicia sesión una vez con internet para guardar tu cuenta y usar Atlas Fit sin datos.</p><button onclick="window.location.reload()" class="btn">Reintentar</button></div></body></html>`,
            { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
          );
        }
      })());
    }
    return;
  }

  // ── REGLA 1.5: APIs DE LECTURA (Fast-Race 600ms con respaldo instantáneo en USER_CACHE + revalidación silenciosa) ──
  const isReadOnlyApi = (
    pathname.includes('/api-') ||
    pathname.includes('api_') ||
    pathname.includes('/asistencias-data')
  );
  if (isReadOnlyApi) {
    event.respondWith((async () => {
      const userCache = await caches.open(USER_CACHE);
      const cached = await userCache.match(req);
      const netPromise = fetch(req).then(networkResponse => {
        const ct = (networkResponse.headers.get('content-type') || '').toLowerCase();
        const finalUrl = (networkResponse.url || req.url).toLowerCase();
        if (networkResponse.ok && ct.includes('application/json') && !finalUrl.includes('/auth/')) {
          safeCachePut(USER_CACHE, req, networkResponse);
        }
        return networkResponse;
      });
      if (cached) {
        try {
          return await fetchWithTimeout(netPromise, 600);
        } catch (_) {
          netPromise.catch(() => {});
          return cached;
        }
      }
      try {
        return await netPromise;
      } catch (_) {
        return new Response(JSON.stringify({ ok: false, offline: true, msg: 'Sin conexión a internet' }), {
          headers: { 'Content-Type': 'application/json; charset=utf-8' }
        });
      }
    })());
    return;
  }

  // ── REGLA 2: NAVEGACIÓN HTML (Fast-Race 500ms en caché + Revalidación en segundo plano para red inestable/offline) ──
  if (isHtmlNavigation) {
    event.respondWith((async () => {
      const userCache = await caches.open(USER_CACHE);
      const base = getScopeBase();

      const fetchAndCacheNav = async () => {
        let networkResponse = null;
        if (event.preloadResponse) {
          networkResponse = await event.preloadResponse.catch(() => null);
        }
        if (!networkResponse) {
          networkResponse = await fetch(new Request(req, { cache: 'no-store' }));
        }
        const finalUrl = (networkResponse && networkResponse.url ? networkResponse.url : req.url).toLowerCase();
        const authRoleHeader = (networkResponse && networkResponse.headers)
          ? (networkResponse.headers.get('X-Atlas-Auth-Role') || '').trim().toLowerCase()
          : '';
        const role = ['cliente', 'entrenador', 'admin'].includes(authRoleHeader)
          ? authRoleHeader
          : inferRoleFromUrl(finalUrl);
        const isAuthPage = Boolean(role) && !finalUrl.includes('/auth/');

        if (networkResponse && networkResponse.ok && isAuthPage) {
          safeCachePut(USER_CACHE, req, networkResponse);
          const homePath = homePathForRole(role);
          writeOfflineMeta(userCache, role, homePath);

          const isHomeLike = (
            finalUrl.includes('/dashboard') ||
            finalUrl.includes('/admin/rutina') ||
            finalUrl.endsWith('/') ||
            finalUrl.endsWith('/index.php')
          );
          if (isHomeLike) {
            try {
              safeCachePut(USER_CACHE, new Request(base + '/'), networkResponse);
              if (homePath) {
                safeCachePut(USER_CACHE, new Request(base + homePath), networkResponse);
              }
            } catch (_) {}
          }
        }
        return networkResponse;
      };

      const cachedFallback = await resolveOfflineHtml(userCache, req, pathname);
      const netPromise = fetchAndCacheNav();

      if (cachedFallback) {
        // Red rápida (<500ms): entrega HTML fresco; red inestable/lenta/offline: entrega caché al instante y actualiza en segundo plano
        try {
          const fresh = await fetchWithTimeout(netPromise, 500);
          // Si estando offline/inestable el servidor devolvió 5xx, priorizar caché estable
          if (fresh && fresh.status >= 500) return cachedFallback;
          return fresh;
        } catch (_) {
          netPromise.catch(() => {});
          return cachedFallback;
        }
      }

      try {
        return await netPromise;
      } catch (_) {
        const lateFallback = await resolveOfflineHtml(userCache, req, pathname);
        if (lateFallback) return lateFallback;
        return new Response(
          `<!DOCTYPE html><html lang="es" data-theme="dark"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Modo Sin Conexión – Atlas Fit</title><style>body{background:#0a0a0f;color:#fff;font-family:system-ui,-apple-system,sans-serif;display:grid;place-items:center;min-height:100vh;margin:0;padding:20px;box-sizing:border-box;text-align:center}.card{background:rgba(20,20,30,0.85);border:1px solid rgba(138,43,226,0.3);border-radius:24px;padding:32px 24px;max-width:380px;box-shadow:0 12px 40px rgba(0,0,0,0.6);backdrop-filter:blur(16px)}h1{font-size:1.35rem;color:#f8fafc;margin:0 0 10px 0;font-weight:800}.icon{width:56px;height:56px;border-radius:16px;background:rgba(138,43,226,0.18);border:1px solid rgba(138,43,226,0.35);color:#c084fc;display:grid;place-items:center;font-size:1.75rem;margin:0 auto 18px}p{color:#94a3b8;font-size:.88rem;line-height:1.5;margin:0 0 24px}.btn{background:linear-gradient(135deg,#8a2be2,#6d28d9);color:#fff;padding:12px 24px;border-radius:12px;text-decoration:none;font-weight:700;display:inline-block;box-shadow:0 4px 18px rgba(138,43,226,0.45);cursor:pointer;border:none;font-size:0.9rem}</style></head><body><div class="card"><div class="icon">⚡</div><h1>Atlas Fit · Modo Offline</h1><p>Estás sin conexión a internet. Abre la app con señal al menos unos segundos hasta que el estado marque <strong>Online</strong> para guardar todos los módulos offline.</p><button onclick="window.location.reload()" class="btn">Reintentar</button></div></body></html>`,
          { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
        );
      }
    })());
    return;
  }

  // ── REGLA 3: RECURSOS ESTÁTICOS (CSS, JS, Fuentes, Iconos, Imágenes/GIFs en STATIC_CACHE) ──
  const isStatic = /\.(css|js|woff2?|ttf|png|jpe?g|svg|webp|gif|ico)$/i.test(url.pathname);

  if (isStatic) {
    const hasVer = url.searchParams.has('v');
    const isIconAsset = (
      pathname.includes('/assets/img/icon-') ||
      pathname.includes('favicon') ||
      pathname.includes('apple-touch-icon')
    );

    event.respondWith(
      caches.open(STATIC_CACHE).then(async cache => {
        if (hasVer) {
          const exactCached = await cache.match(req);
          if (exactCached) return exactCached;
          try {
            const networkResponse = await fetch(req);
            safeCachePut(STATIC_CACHE, req, networkResponse);
            return networkResponse;
          } catch (_) {
            const staleFallback = await cache.match(req, { ignoreSearch: true });
            return staleFallback || new Response('', { status: 408, statusText: 'Offline' });
          }
        }

        if (isIconAsset) {
          try {
            const freshIcon = await fetchWithTimeout(fetch(req, { cache: 'no-cache' }), 900);
            safeCachePut(STATIC_CACHE, req, freshIcon);
            return freshIcon;
          } catch (_) {
            const cachedIcon = (await cache.match(req)) || (await cache.match(req, { ignoreSearch: true }));
            return cachedIcon || new Response('', { status: 408, statusText: 'Offline' });
          }
        }

        const cachedResponse = (await cache.match(req)) || (await cache.match(req, { ignoreSearch: true }));
        if (cachedResponse) {
          fetch(req, { cache: 'no-cache' }).then(networkResponse => {
            safeCachePut(STATIC_CACHE, req, networkResponse);
          }).catch(() => {});
          return cachedResponse;
        }

        return fetch(req).then(networkResponse => {
          safeCachePut(STATIC_CACHE, req, networkResponse);
          return networkResponse;
        }).catch(() => new Response('', { status: 408, statusText: 'Offline' }));
      })
    );
    return;
  }
});
