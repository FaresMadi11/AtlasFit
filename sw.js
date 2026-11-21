// ══════════════════════════════════════════════════════════════
//  Atlas Fit — Service Worker (Offline-First 3 Vistas + Fast Race)
// ══════════════════════════════════════════════════════════════

const SW_VER = new URL(self.location.href).searchParams.get('v') || '5.3';
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

function normalizeApiCacheUrl(rawUrl) {
  try {
    const u = new URL(rawUrl);
    u.searchParams.delete('_t');
    u.searchParams.delete('t');
    u.searchParams.delete('nocache');
    u.searchParams.delete('cb');
    return u.toString();
  } catch (_) {
    return rawUrl;
  }
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

// Resolver el HTML offline correcto para las 3 vistas (Cliente, Entrenador, Admin) sin falsas redirecciones a Inicio
async function resolveOfflineHtml(userCache, req, pathname) {
  const keys = await userCache.keys();
  const base = getScopeBase();
  const isRootPath = (
    pathname === '/' ||
    pathname.endsWith('/') ||
    pathname.endsWith('/index.php')
  );

  // 1. Coincidencia exacta (respetando query ?id=..., ?dia=..., etc.)
  const exactMatch = await userCache.match(req);
  if (exactMatch) return exactMatch;

  // 2. Coincidencia por ruta limpia ignorando query (NO mezclar perfiles de distintos atletas ?id=X ni creador de rutinas con ?atleta_id=...&dia=...)
  const isEntityParameterized = pathname.includes('/perfil-cliente') || pathname.includes('/estadisticas-cliente') || pathname.includes('/revisar-modificacion') || pathname.includes('/crear-rutina') || pathname.includes('/asignar-rutina');
  if (!isRootPath && !pathname.includes('/auth/') && !isEntityParameterized) {
    const cleanMatch = await userCache.match(req, { ignoreSearch: true });
    if (cleanMatch) return cleanMatch;
  }

  const meta = await readOfflineMeta(userCache);
  const metaRole = (meta && meta.role) ? meta.role : '';
  const metaHome = (meta && meta.homePath) ? meta.homePath : homePathForRole(metaRole);

  // 3. Raíz (start_url) o rescate offline de /auth/login con sesión activa previa
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

  // 4. Coincidencia semántica ESTRICTA por módulo exacto (NUNCA redirigir silenciosamente a Inicio si falta otra subpágina)
  if (pathname.includes('/admin/')) {
    if (pathname.includes('/crear-rutina')) {
      return await userCache.match(req);
    }
    const mapAdmin = [
      ['/gestion', '/admin/gestion'],
      ['/configuracion', '/admin/configuracion'],
      ['/notificaciones', '/admin/notificaciones'],
      ['/rutina', '/admin/rutina'],
      ['/dashboard', '/admin/rutina']
    ];
    for (const [needle, target] of mapAdmin) {
      if (pathname.includes(needle)) {
        const res = await userCache.match(base + target, { ignoreSearch: true });
        if (res) return res;
      }
    }
    return null;
  }

  if (pathname.includes('/entrenador/')) {
    if (pathname.includes('/crear-rutina') || pathname.includes('/perfil-cliente')) {
      return await userCache.match(req);
    }
    const mapEnt = [
      ['/pagos-recibidos', '/entrenador/pagos-recibidos'],
      ['/metodos-pago', '/entrenador/metodos-pago'],
      ['/registrar-cliente', '/entrenador/registrar-cliente'],
      ['/notificaciones', '/entrenador/notificaciones'],
      ['/clientes', '/entrenador/clientes'],
      ['/asistencias', '/entrenador/asistencias'],
      ['/configuracion', '/entrenador/configuracion'],
      ['/dashboard', '/entrenador/dashboard']
    ];
    for (const [needle, target] of mapEnt) {
      if (pathname.includes(needle)) {
        const res = await userCache.match(base + target, { ignoreSearch: true });
        if (res) return res;
      }
    }
    return null;
  }

  if (pathname.includes('/cliente/')) {
    const mapCli = [
      ['/rutina-dia', '/cliente/rutina-dia'],
      ['/rutinas', '/cliente/rutinas'],
      ['/estadisticas', '/cliente/estadisticas'],
      ['/perfil', '/cliente/perfil'],
      ['/pago', '/cliente/pago'],
      ['/notificaciones', '/cliente/notificaciones'],
      ['/dashboard', '/cliente/dashboard']
    ];
    for (const [needle, target] of mapCli) {
      if (pathname.includes(needle)) {
        const res = await userCache.match(base + target, { ignoreSearch: true });
        if (res) return res;
      }
    }
    if (pathname.includes('/rutina')) {
      const res = await userCache.match(base + '/cliente/rutina-dia', { ignoreSearch: true });
      if (res) return res;
    }
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

  // ── REGLA 1.5: APIs DE LECTURA (Fast-Race 600ms con clave normalizada sin _t/t y respaldo en USER_CACHE) ──
  const isReadOnlyApi = (
    pathname.includes('/api-') ||
    pathname.includes('api_') ||
    pathname.includes('/asistencias-data') ||
    pathname.includes('/grafica-ejercicio')
  );
  if (isReadOnlyApi) {
    event.respondWith((async () => {
      const userCache = await caches.open(USER_CACHE);
      const normUrl = normalizeApiCacheUrl(req.url);
      const cacheKeyReq = new Request(normUrl);
      const cached = (await userCache.match(cacheKeyReq)) || (await userCache.match(req));
      const netPromise = fetch(req).then(networkResponse => {
        const ct = (networkResponse.headers.get('content-type') || '').toLowerCase();
        const finalUrl = (networkResponse.url || req.url).toLowerCase();
        if (networkResponse.ok && ct.includes('application/json') && !finalUrl.includes('/auth/')) {
          safeCachePut(USER_CACHE, cacheKeyReq, networkResponse);
        }
        return networkResponse;
      });
      if (cached) {
        try {
          const netRes = await fetchWithTimeout(netPromise, 600);
          if (netRes && (netRes.status === 401 || netRes.status === 403)) return netRes;
          return netRes;
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
        // Cuando hay conexión a internet, esperar hasta 3500ms para entregar siempre HTML fresco; offline cae a caché al instante
        const navTimeoutMs = (self.navigator && self.navigator.onLine === false) ? 150 : 3500;
        try {
          const fresh = await fetchWithTimeout(netPromise, navTimeoutMs);
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
          `<!DOCTYPE html><html lang="es" data-theme="dark"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Modo Sin Conexión – Atlas Fit</title><style>body{background:#0a0a0f;color:#fff;font-family:system-ui,-apple-system,sans-serif;display:grid;place-items:center;min-height:100vh;margin:0;padding:20px;box-sizing:border-box;text-align:center}.card{background:rgba(20,20,30,0.88);border:1px solid rgba(138,43,226,0.32);border-radius:24px;padding:30px 22px;max-width:380px;box-shadow:0 12px 40px rgba(0,0,0,0.6)}h1{font-size:1.28rem;color:#f8fafc;margin:0 0 10px 0;font-weight:800}.icon{width:54px;height:54px;border-radius:16px;background:rgba(138,43,226,0.18);border:1px solid rgba(138,43,226,0.35);color:#c084fc;display:grid;place-items:center;font-size:1.65rem;margin:0 auto 16px}p{color:#94a3b8;font-size:.86rem;line-height:1.5;margin:0 0 22px}.actions{display:flex;gap:10px;justify-content:center}.btn{background:linear-gradient(135deg,#8a2be2,#6d28d9);color:#fff;padding:11px 18px;border-radius:12px;text-decoration:none;font-weight:700;cursor:pointer;border:none;font-size:0.86rem}.btn-sec{background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);color:#e2e8f0}</style></head><body><div class="card"><div class="icon">⚡</div><h1>Sección no guardada offline</h1><p>Esta vista puntual aún no está en tu caché local. Ábrela una vez con señal para guardarla o vuelve a la pantalla anterior.</p><div class="actions"><button onclick="history.back()" class="btn btn-sec">Volver</button><button onclick="window.location.reload()" class="btn">Reintentar</button></div></div></body></html>`,
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
