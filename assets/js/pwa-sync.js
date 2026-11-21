// ══════════════════════════════════════════════════════════════
//  Xprod Fit — PWA & Offline Sync Manager (Patrón Outbox)
// ══════════════════════════════════════════════════════════════

(function (window) {
  'use strict';

  const STORAGE_KEY = 'xp_offline_queue';
  let isSyncing = false;

  const XpSync = {
    // Reutilizar el registro único de Service Worker para evitar colisiones de versión entre vistas
    initSW() {
      const swVer = window.XP_SW_VER || '4.4';
      const swUrl = window.XP_SW_URL || ((window.APP || '') + '/sw.js?v=' + swVer);
      if ('serviceWorker' in navigator && !window.XP_SW_URL) {
        const hadCtrl = !!navigator.serviceWorker.controller;
        window.addEventListener('load', () => {
          navigator.serviceWorker.register(swUrl, {
            scope: (window.APP || '') + '/',
            updateViaCache: 'none'
          }).then(reg => {
            reg.update().catch(() => {});
          }).catch(err => {
            console.warn('[PWA] Error registrando Service Worker:', err);
          });
        });
        navigator.serviceWorker.addEventListener('controllerchange', () => {
          if (!hadCtrl) return;
          try {
            if (sessionStorage.getItem('xp_sw_reloaded') === swVer) return;
            sessionStorage.setItem('xp_sw_reloaded', swVer);
          } catch (_) {}
          window.location.reload();
        });
      }

      // Prefetch instantáneo al tocar o pulsar pestañas de navegación (acelera iOS PWA y navegadores sin speculationrules)
      const prefetchedTabs = new Set();
      const triggerTabPrefetch = (e) => {
        if (!navigator.onLine) return;
        const a = e.target && e.target.closest ? e.target.closest('.xp-nav a[href]') : null;
        if (!a) return;
        const href = a.href;
        if (!href || href.includes('logout') || prefetchedTabs.has(href) || href === window.location.href) return;
        prefetchedTabs.add(href);
        fetch(href, { credentials: 'same-origin', headers: { 'Accept': 'text/html' }, priority: 'low' })
          .then(res => {
            if (res && res.ok && 'caches' in window) {
              const cName = window.XP_USER_CACHE || ('atlas-fit-user-' + (window.XP_SW_VER || '5.0'));
              caches.open(cName).then(c => c.put(href, res.clone()).catch(() => {})).catch(() => {});
            }
          })
          .catch(() => {});
      };
      document.addEventListener('pointerdown', triggerTabPrefetch, { passive: true });
      document.addEventListener('touchstart', triggerTabPrefetch, { passive: true });

      // Al tocar cualquier botón de Cerrar Sesión, limpiar SOLO la sesión y caché privada del usuario (mantener App Shell estático)
      document.addEventListener('click', (e) => {
        const logoutLink = e.target && e.target.closest ? e.target.closest('a[href*="auth/logout"]') : null;
        if (logoutLink) {
          try {
            localStorage.removeItem('xp_auth_active');
            localStorage.removeItem('xp_auth_role');
            localStorage.removeItem('xp_auth_uid');
            localStorage.removeItem('xp_auth_home');
            localStorage.removeItem('xp_offline_ready_sig');
            localStorage.removeItem(STORAGE_KEY);
            Object.keys(localStorage).forEach(k => {
              if (k && k.startsWith('xprod_')) localStorage.removeItem(k);
            });
            sessionStorage.removeItem('xp_prewarm_ts');
          } catch (_) { }
          if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
            try { navigator.serviceWorker.controller.postMessage({ type: 'CLEAR_USER_CACHE' }); } catch (_) {}
          }
          if ('caches' in window) {
            caches.keys().then(ks => {
              ks.forEach(k => {
                if (k.startsWith('atlas-fit-user-') || (k.startsWith('atlas-fit-') && !k.startsWith('atlas-fit-static-'))) {
                  caches.delete(k).catch(() => {});
                }
              });
            }).catch(() => {});
          }
        }
      }, true);

      // Escuchar cambios de conectividad
      window.addEventListener('online', () => {
        this.updatePillUI();
        this.flushQueue();
        setTimeout(() => this.runOfflinePrewarm(false), 700);
      });

      window.addEventListener('offline', () => {
        this.updatePillUI();
      });

      // Crear pastilla de conectividad en el DOM
      this.ensurePillInDOM();
      this.updatePillUI();

      // Si arrancamos online y hay pendientes de una sesión anterior, sincronizar
      if (navigator.onLine && this.getQueue().length > 0) {
        setTimeout(() => this.flushQueue(), 1200);
      }

      // Precalentar todos los módulos de las 3 vistas (Cliente, Entrenador, Admin) con progreso real % solo cuando falta caché
      if (navigator.onLine && 'caches' in window) {
        setTimeout(() => this.runOfflinePrewarm(false), 350);
      }
    },

    // Estado interno del progreso de descarga offline
    _cacheProgress: null,
    _isPrewarming: false,

    async runOfflinePrewarm(forceRefresh = false) {
      if (this._isPrewarming || !navigator.onLine || !('caches' in window)) return;
      const curPath = window.location.pathname.toLowerCase();
      if (curPath.includes('/auth/')) return;

      const role = (window.XP_AUTH_ROLE || localStorage.getItem('xp_auth_role') || '').toLowerCase();
      const isAdmin      = curPath.includes('/admin') || role === 'admin';
      const isEntrenador = !isAdmin && (curPath.includes('/entrenador') || role === 'entrenador');
      const isAtleta     = !isAdmin && !isEntrenador && (curPath.includes('/cliente') || role === 'cliente');
      if (!isAtleta && !isEntrenador && !isAdmin) return;

      const effectiveRole = isAdmin ? 'admin' : (isEntrenador ? 'entrenador' : 'cliente');
      const homePath = isAdmin ? '/admin/rutina' : (isEntrenador ? '/entrenador/dashboard' : '/cliente/dashboard');
      const curBuild = String(window.XP_SW_VER || '5.0');
      const uid      = String(window.XP_AUTH_UID || localStorage.getItem('xp_auth_uid') || '0');
      const readySig = `${curBuild}|${effectiveRole}|${uid}`;
      const userCacheName = window.XP_USER_CACHE || ('atlas-fit-user-' + curBuild);
      const base = (window.APP || window.location.origin).replace(/\/+$/, '');

      const nowLocal = new Date();
      const todayIso = `${nowLocal.getFullYear()}-${String(nowLocal.getMonth() + 1).padStart(2, '0')}-${String(nowLocal.getDate()).padStart(2, '0')}`;

      // Paquete completo por vista (TODOS los módulos, subpáginas y APIs de lectura de cada rol)
      const bundle = isAtleta
        ? [
            { path: '/cliente/dashboard', type: 'html' },
            { path: '/cliente/rutina-dia', type: 'html', extractWeekDays: true },
            { path: '/cliente/estadisticas', type: 'html', extractStats: true },
            { path: '/cliente/perfil', type: 'html' },
            { path: '/cliente/pago', type: 'html' },
            { path: '/cliente/notificaciones', type: 'html' },
            { path: '/cliente/api-peso-historial', type: 'api' },
            { path: '/cliente/api-tasa-bcv', type: 'api' },
            { path: '/cliente/api-tienda-get', type: 'api' },
            { path: '/cliente/api-notificaciones', type: 'api' }
          ]
        : isEntrenador
          ? [
              { path: '/entrenador/dashboard', type: 'html' },
              { path: '/entrenador/clientes', type: 'html', extractAthleteProfiles: true },
              { path: '/entrenador/asistencias', type: 'html' },
              { path: '/entrenador/configuracion', type: 'html' },
              { path: '/entrenador/pagos-recibidos', type: 'html' },
              { path: '/entrenador/metodos-pago', type: 'html' },
              { path: '/entrenador/crear-rutina', type: 'html' },
              { path: '/entrenador/registrar-cliente', type: 'html' },
              { path: '/entrenador/notificaciones', type: 'html' },
              { path: '/entrenador/api-catalogo-list', type: 'api' },
              { path: '/entrenador/api-listar-plantillas-catalogo', type: 'api' },
              { path: '/entrenador/api-tienda-get', type: 'api' },
              { path: '/entrenador/api-notificaciones', type: 'api' },
              { path: '/entrenador/asistencias-data?date=' + encodeURIComponent(todayIso), type: 'api', extractAsistenciasAthletes: true }
            ]
          : [
              { path: '/admin/rutina', type: 'html' },
              { path: '/admin/gestion', type: 'html' },
              { path: '/admin/configuracion', type: 'html' },
              { path: '/admin/notificaciones', type: 'html' },
              { path: '/admin/api-catalogo-list', type: 'api' },
              { path: '/admin/api-listar-plantillas-catalogo', type: 'api' },
              { path: '/admin/api-notificaciones', type: 'api' },
              { path: '/admin/api-dia-detalle?fecha=' + encodeURIComponent(todayIso), type: 'api' }
            ];

      this._isPrewarming = true;
      try {
        const cache = await caches.open(userCacheName);
        const metaUrl = base + '/__atlas_offline_meta__';

        // Asegurar metadatos de sesión offline para el Service Worker (rescate de start_url '/' sin red)
        await cache.put(
          metaUrl,
          new Response(JSON.stringify({ role: effectiveRole, homePath, uid, updatedAt: Date.now() }), {
            headers: { 'Content-Type': 'application/json; charset=utf-8' }
          })
        ).catch(() => {});

        // Rutas dinámicas descubiertas (rutinas de atletas de hoy en Asistencias, perfiles hasta 8 atletas, pagos, días de la semana)
        const dynamicPrimaryQueue = [];
        const dynamicBgQueue = [];
        const seenDyn = new Set();
        const pushDyn = (arr, item) => {
          if (!item || !item.path || seenDyn.has(item.path)) return;
          seenDyn.add(item.path);
          arr.push(item);
        };

        const extractAsistenciasFromJson = (json) => {
          if (!json || typeof json !== 'object') return;
          const lists = [
            ...(json.pendientes || []),
            ...(json.verificar || []),
            ...(json.completadas || []),
            ...(json.modificaciones || []),
            ...(json.sinAsignar || []),
            ...(json.sin_asignar || [])
          ];
          const ids = [];
          lists.forEach(row => {
            const cid = parseInt(row && (row.cliente_id || row.id) || 0, 10);
            if (cid > 0 && !ids.includes(cid)) ids.push(cid);
          });
          ids.forEach(cid => {
            pushDyn(dynamicPrimaryQueue, {
              path: `/entrenador/api-dia-detalle?cliente_id=${cid}&fecha=${encodeURIComponent(todayIso)}`,
              type: 'api'
            });
          });
          (json.modificaciones || []).forEach(mRow => {
            const cid = parseInt(mRow && mRow.cliente_id || 0, 10);
            const sid = parseInt(mRow && mRow.rutina_id || 0, 10);
            if (cid > 0 && sid > 0) {
              pushDyn(dynamicPrimaryQueue, {
                path: `/entrenador/api-dia-detalle?cliente_id=${cid}&fecha=${encodeURIComponent(todayIso)}&solicitud_id=${sid}`,
                type: 'api'
              });
            }
          });
          // Hasta 8 perfiles completos + sus pagos dentro del progreso real 0% -> 100%
          ids.slice(0, 8).forEach(cid => {
            pushDyn(dynamicPrimaryQueue, { path: `/entrenador/perfil-cliente?id=${cid}`, type: 'html' });
            pushDyn(dynamicPrimaryQueue, { path: `/entrenador/api-pagos-cliente?cliente_id=${cid}`, type: 'api' });
          });
        };

        const extractProfilesFromHtml = (html) => {
          if (!html) return;
          const re = /entrenador\/perfil-cliente\?id=(\d+)/g;
          let m;
          let count = 0;
          const foundIds = new Set();
          while ((m = re.exec(html)) !== null && count < 8) {
            const cid = parseInt(m[1], 10);
            if (cid > 0 && !foundIds.has(cid)) {
              foundIds.add(cid);
              pushDyn(dynamicPrimaryQueue, { path: `/entrenador/perfil-cliente?id=${cid}`, type: 'html' });
              pushDyn(dynamicPrimaryQueue, { path: `/entrenador/api-dia-detalle?cliente_id=${cid}&fecha=${encodeURIComponent(todayIso)}`, type: 'api' });
              pushDyn(dynamicPrimaryQueue, { path: `/entrenador/api-pagos-cliente?cliente_id=${cid}`, type: 'api' });
              count++;
            }
          }
        };

        // Verificar qué módulos realmente faltan en Cache Storage
        const missing = [];
        for (const item of bundle) {
          const fullUrl = base + item.path;
          const matchOpts = item.type === 'html' ? { ignoreSearch: true } : undefined;
          const existing = await cache.match(fullUrl, matchOpts);
          const needStats = Boolean(item.extractStats && !localStorage.getItem('xprod_stats_snapshot_v1'));
          if (!existing || needStats) {
            missing.push(item);
          } else if (isEntrenador) {
            // Aunque asistencias-data o /entrenador/clientes ya estén cacheados, re-inspeccionarlos para asegurar que TODOS los api-dia-detalle y perfiles (<=8) estén en caché
            if (item.extractAsistenciasAthletes) {
              const cachedJson = await existing.clone().json().catch(() => null);
              extractAsistenciasFromJson(cachedJson);
            } else if (item.extractAthleteProfiles) {
              const cachedHtml = await existing.clone().text().catch(() => '');
              extractProfilesFromHtml(cachedHtml);
            }
          }
        }

        // Comprobar si algún recurso dinámico descubierto ya falta en caché (p. ej. un atleta nuevo o corte previo al llegar a 100%)
        const missingDynamic = [];
        for (const dyn of dynamicPrimaryQueue) {
          const exDyn = await cache.match(base + dyn.path).catch(() => null);
          if (!exDyn) missingDynamic.push(dyn);
        }

        const rootCached = await cache.match(base + '/', { ignoreSearch: true });
        const hasReadySig = (localStorage.getItem('xp_offline_ready_sig') === readySig);
        const lastPrewarm = parseInt(sessionStorage.getItem('xp_prewarm_ts') || '0', 10);
        const isStale = forceRefresh || ((Date.now() - lastPrewarm) > 900000); // 15 min

        // Si ya tenemos el 100% de módulos + recursos dinámicos críticos + raíz + firma vigente -> entrar directo a Online sin mostrar %
        const showProgressUI = (missing.length > 0 || missingDynamic.length > 0 || !rootCached || !hasReadySig);
        const targets = showProgressUI
          ? (missing.length > 0 ? missing : (missingDynamic.length > 0 ? [] : bundle.filter(b => b.path === homePath)))
          : (isStale ? bundle : []);

        if (targets.length === 0 && missingDynamic.length === 0) {
          this._isPrewarming = false;
          return;
        }

        let totalItems = showProgressUI ? (bundle.length + missingDynamic.length) : targets.length;
        let completedItems = showProgressUI ? Math.max(0, bundle.length - targets.length) : 0;

        if (showProgressUI) {
          const startPct = Math.min(95, Math.max(6, Math.round((completedItems / Math.max(1, totalItems)) * 100)));
          this._cacheProgress = { pct: startPct, done: completedItems, total: totalItems };
          this.updatePillUI('caching');
        }

        const fetchOne = async (item) => {
          if (!navigator.onLine) return false;
          const fullUrl = base + item.path;
          const opts = item.type === 'html'
            ? { credentials: 'same-origin', headers: { 'Accept': 'text/html' } }
            : { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } };

          const res = await fetch(fullUrl, opts).catch(() => null);
          if (!res || !res.ok) return false;
          const finalUrl = (res.url || fullUrl).toLowerCase();
          if (finalUrl.includes('/auth/')) return false;

          if (item.type === 'api') {
            const ct = (res.headers.get('content-type') || '').toLowerCase();
            if (!ct.includes('application/json')) return false;
          }

          await cache.put(fullUrl, res.clone()).catch(() => {});

          // Guardar el home del rol también como '/' (start_url de la PWA)
          if (item.path === homePath) {
            await cache.put(base + '/', res.clone()).catch(() => {});
          }

          // Extraer atletas del día desde asistencias-data para precargar sus rutinas (api-dia-detalle) y perfiles (<=8)
          if (item.extractAsistenciasAthletes) {
            try {
              const json = await res.clone().json().catch(() => null);
              extractAsistenciasFromJson(json);
            } catch (_) {}
          }

          // Extraer enlaces de perfiles de atletas visibles en /entrenador/clientes (hasta 8) dentro del paquete real 0->100%
          if (item.extractAthleteProfiles) {
            try {
              const html = await res.clone().text().catch(() => '');
              extractProfilesFromHtml(html);
            } catch (_) {}
          }

          // Extraer los otros días de la semana en /cliente/rutina-dia para consultar toda la semana offline
          if (item.extractWeekDays) {
            try {
              const html = await res.clone().text().catch(() => '');
              const re = /\/cliente\/rutina-dia\?dia=\d+(?:&amp;rutina=\d+|&rutina=\d+)?/g;
              let m;
              let count = 0;
              while ((m = re.exec(html)) !== null && count < 7) {
                const cleanPath = m[0].replace(/&amp;/g, '&');
                pushDyn(dynamicBgQueue, { path: cleanPath, type: 'html' });
                count++;
              }
            } catch (_) {}
          }

          // Extraer snapshot JSON de estadísticas para que el calendario y gráficas funcionen 100% offline
          if (item.extractStats) {
            const html = await res.text().catch(() => '');
            const m = html ? html.match(/<script id="xpStatsOfflineSnapshot" type="application\/json">([\s\S]*?)<\/script>/) : null;
            if (m && m[1]) {
              try {
                const parsed = JSON.parse(m[1]);
                if (parsed && parsed.cliente_id) {
                  localStorage.setItem('xprod_stats_snapshot_v1', JSON.stringify(parsed));
                  if (parsed.dias_historial) {
                    Object.entries(parsed.dias_historial).forEach(([f, det]) => {
                      localStorage.setItem('xprod_cal_dia_' + f, JSON.stringify(det));
                    });
                  }
                  if (parsed.grupos_ejercicios) {
                    Object.entries(parsed.grupos_ejercicios).forEach(([g, gData]) => {
                      localStorage.setItem('xprod_st_ej_' + g, JSON.stringify(gData));
                    });
                  }
                  if (parsed.ejercicios_historial) {
                    Object.entries(parsed.ejercicios_historial).forEach(([nom, hData]) => {
                      localStorage.setItem('xprod_st_hist_' + nom, JSON.stringify(hData));
                    });
                  }
                }
              } catch (_) {}
            }
          }
          return true;
        };

        // Descargar en lotes paralelos de 3 (rápido y equilibrado)
        const waitIdle = (ms) => new Promise(r => setTimeout(r, ms));
        let okCount = 0;
        const runBatchList = async (list, batchSize, updatePct) => {
          for (let i = 0; i < list.length; i += batchSize) {
            if (!navigator.onLine) break;
            while (document.body && document.body.classList.contains('is-typing')) {
              await waitIdle(450);
            }
            const batch = list.slice(i, i + batchSize);
            const results = await Promise.all(batch.map(it => fetchOne(it)));
            results.forEach(ok => {
              if (ok) okCount++;
              if (updatePct) {
                completedItems++;
                if (showProgressUI) {
                  const pct = Math.min(99, Math.round((completedItems / Math.max(1, totalItems)) * 100));
                  this._cacheProgress = { pct, done: completedItems, total: totalItems };
                  this.updatePillUI('caching');
                }
              }
            });
            if (i + batchSize < list.length) await waitIdle(80);
          }
        };

        await runBatchList(targets, 3, true);

        // Fase 2 dentro del 100%: rutinas de hoy de todos los atletas + hasta 8 perfiles completos y pagos (solo los que falten o en refresh)
        if (dynamicPrimaryQueue.length > 0 && navigator.onLine) {
          const dynToFetch = [];
          for (const dyn of dynamicPrimaryQueue) {
            if (isStale) {
              dynToFetch.push(dyn);
            } else {
              const exDyn = await cache.match(base + dyn.path).catch(() => null);
              if (!exDyn) dynToFetch.push(dyn);
            }
          }
          if (dynToFetch.length > 0) {
            const extraBeyondInit = Math.max(0, dynToFetch.length - missingDynamic.length);
            totalItems += extraBeyondInit;
            await runBatchList(dynToFetch, 3, true);
          }
        }

        // Asegurar que '/' quede siempre respaldado con el home del rol
        const homeResAfter = await cache.match(base + homePath, { ignoreSearch: true });
        if (homeResAfter) {
          await cache.put(base + '/', homeResAfter.clone()).catch(() => {});
        }

        if (navigator.onLine && (okCount > 0 || !showProgressUI)) {
          try {
            localStorage.setItem('xp_offline_ready_sig', readySig);
            localStorage.setItem('xp_prewarm_build', curBuild);
            sessionStorage.setItem('xp_prewarm_ts', String(Date.now()));
          } catch (_) {}
        }

        if (showProgressUI && navigator.onLine) {
          this._cacheProgress = { pct: 100, done: totalItems, total: totalItems };
          this.updatePillUI('caching');
          setTimeout(() => {
            this._cacheProgress = null;
            this.updatePillUI();
          }, 650);
        } else {
          this._cacheProgress = null;
        }

        // Fase 3 silenciosa en segundo plano (sin bloquear el paso a Online): perfiles de atletas activos y días de la semana
        if (dynamicBgQueue.length > 0 && navigator.onLine) {
          setTimeout(async () => {
            const toFetchBg = [];
            for (const it of dynamicBgQueue) {
              const ex = await cache.match(base + it.path).catch(() => null);
              if (!ex) toFetchBg.push(it);
            }
            if (toFetchBg.length > 0) {
              await runBatchList(toFetchBg, 2, false);
            }
          }, 900);
        }
      } catch (_) {
        this._cacheProgress = null;
        this.updatePillUI();
      } finally {
        this._isPrewarming = false;
      }
    },

    // Obtener la cola de mutaciones pendientes
    getQueue() {
      try {
        const raw = localStorage.getItem(STORAGE_KEY);
        return raw ? JSON.parse(raw) : [];
      } catch (e) {
        return [];
      }
    },

    // Guardar una acción en la cola offline
    enqueue(item) {
      const queue = this.getQueue();
      item.id = Date.now() + '_' + Math.random().toString(36).substr(2, 6);
      item.timestamp = item.timestamp || Date.now();
      queue.push(item);
      localStorage.setItem(STORAGE_KEY, JSON.stringify(queue));
      this.updatePillUI();
      return item;
    },

    // Eliminar un elemento procesado de la cola
    dequeue(id) {
      let queue = this.getQueue();
      queue = queue.filter(it => it.id !== id);
      localStorage.setItem(STORAGE_KEY, JSON.stringify(queue));
      this.updatePillUI();
    },

    // Procesar la cola secuencialmente al recuperar internet
    async flushQueue() {
      if (isSyncing || !navigator.onLine) return;
      const queue = this.getQueue();
      if (queue.length === 0) return;

      isSyncing = true;
      this.updatePillUI('syncing');

      let successCount = 0;

      for (const item of queue) {
        try {
          let fetchOpts;
          if (item.format === 'json') {
            const payload = Object.assign({}, item.data || {});
            if (window.CSRF) payload.csrf_token = window.CSRF;
            fetchOpts = {
              method: 'POST',
              credentials: 'same-origin',
              redirect: 'manual',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': window.CSRF || ''
              },
              body: JSON.stringify(payload)
            };
          } else {
            const fd = new FormData();
            if (item.data) {
              for (const [k, v] of Object.entries(item.data)) {
                fd.append(k, v);
              }
            }
            if (window.CSRF) {
              fd.set('csrf_token', window.CSRF);
            }
            fetchOpts = {
              method: 'POST',
              credentials: 'same-origin',
              redirect: 'manual',
              headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': window.CSRF || ''
              },
              body: fd
            };
          }

          const res = await fetch(item.url, fetchOpts);

          // Redirección opaca (302 a /auth/login) o 401/403 -> sesión caducada, NO borrar cola!
          if (res.type === 'opaqueredirect' || (res.status >= 300 && res.status < 400) || res.status === 401 || res.status === 403) {
            break;
          }

          // Errores definitivos de recurso/validación (404/422) -> desencolar para no bloquear el resto
          if (res.status === 404 || res.status === 422) {
            this.dequeue(item.id);
            continue;
          }

          if (res.ok) {
            const ct = (res.headers.get('content-type') || '').toLowerCase();
            if (!ct.includes('application/json')) {
              // Respuesta HTML inesperada (ej. login/error): preservar cola
              break;
            }
            const json = await res.json().catch(() => null);
            if (json && json.ok) {
              this.dequeue(item.id);
              successCount++;
            } else if (json && json.msg && /inválido|no encontrado|no pertenece/i.test(String(json.msg))) {
              this.dequeue(item.id);
            }
          }
        } catch (err) {
          // Si falló por corte de red intermitente, detener reintento temporalmente
          console.warn('[PWA Outbox] Error enviando item, reintentará luego:', err);
          break;
        }
      }

      isSyncing = false;

      const remaining = this.getQueue().length;
      if (remaining === 0 && successCount > 0) {
        this.updatePillUI('success');
        this.showSyncNotice('Datos sincronizados');
      } else {
        this.updatePillUI();
      }
    },

    // Aviso pequeño y discreto en la parte superior sin fondo difuminado
    showSyncNotice(texto = 'Datos sincronizados') {
      const prev = document.getElementById('xpSyncSmallNotice');
      if (prev) prev.remove();

      const pill = document.createElement('div');
      pill.id = 'xpSyncSmallNotice';
      pill.setAttribute('role', 'status');
      pill.setAttribute('aria-live', 'polite');
      pill.style.cssText = [
        'position:fixed',
        'top:12px',
        'left:50%',
        'transform:translateX(-50%) translateY(-10px)',
        'z-index:999999',
        'pointer-events:none',
        'display:inline-flex',
        'align-items:center',
        'gap:6px',
        'background:#14141d',
        'border:1px solid rgba(34,197,94,0.4)',
        'border-radius:999px',
        'padding:5px 13px',
        'color:#e2e8f0',
        'font-size:0.74rem',
        'font-weight:600',
        'letter-spacing:0.01em',
        'box-shadow:0 4px 12px rgba(0,0,0,0.35)',
        'opacity:0',
        'transition:opacity 0.22s ease, transform 0.22s ease'
      ].join(';');
      pill.innerHTML = `<i class="ti ti-check" style="color:#4ade80;font-size:0.85rem;line-height:1;"></i><span>${texto}</span>`;
      document.body.appendChild(pill);

      requestAnimationFrame(() => {
        pill.style.opacity = '1';
        pill.style.transform = 'translateX(-50%) translateY(0)';
      });

      setTimeout(() => {
        pill.style.opacity = '0';
        pill.style.transform = 'translateX(-50%) translateY(-10px)';
        setTimeout(() => pill.remove(), 250);
      }, 2200);
    },

    // Inyectar o asegurar el badge visual en la cabecera (por encima de los botones de notificaciones y logout)
    ensurePillInDOM() {
      let pill = document.getElementById('xpPwaSyncPill');
      if (!pill) {
        pill = document.createElement('div');
        pill.id = 'xpPwaSyncPill';
        pill.className = 'xp-pwa-header-pill online';
        pill.title = 'Conectado a la red';
        pill.innerHTML = '<span class="xp-status-dot"></span><span class="xp-status-text">Online</span>';
      }

      const headerActions = document.querySelector('.header-actions');
      if (headerActions) {
        // Asegurar que los botones de notificaciones y logout queden en una fila horizontal inferior
        let btnRow = headerActions.querySelector('.header-action-buttons');
        if (!btnRow) {
          btnRow = document.createElement('div');
          btnRow.className = 'header-action-buttons';
          const notifWrapper = headerActions.querySelector('.notif-wrapper');
          const logoutBtn = headerActions.querySelector('a[href*="logout"], a[title*="Cerrar"], a[title*="Volver"]');
          if (notifWrapper) btnRow.appendChild(notifWrapper);
          if (logoutBtn) btnRow.appendChild(logoutBtn);
          headerActions.appendChild(btnRow);
        } else {
          // Si btnRow existe pero falta el botón de logout/volver adentro, incorporarlo
          const looseBtn = headerActions.querySelector(':scope > a[href*="logout"], :scope > a[title*="Cerrar"], :scope > a[title*="Volver"]');
          if (looseBtn) btnRow.appendChild(looseBtn);
        }
        // El pill siempre debe ubicarse arriba del todo en headerActions
        if (pill.parentElement !== headerActions || headerActions.firstElementChild !== pill) {
          headerActions.insertBefore(pill, headerActions.firstElementChild);
        }
      } else if (!pill.parentElement) {
        document.body.appendChild(pill);
      }
      return pill;
    },

    // Actualizar el estado visual del badge (punto de color + 1 sola palabra)
    updatePillUI(stateOverride) {
      this.ensurePillInDOM();
      const pill = document.getElementById('xpPwaSyncPill');
      if (!pill) return;

      const queue = this.getQueue();
      const count = queue.length;
      const isOffline = !navigator.onLine;

      // 1. Sincronizando outbox: mini loader morado girando mientras se sube
      if (stateOverride === 'syncing') {
        pill.style.removeProperty('--xp-cache-pct');
        pill.className = 'xp-pwa-header-pill syncing';
        pill.innerHTML = '<span class="xp-status-loader" aria-hidden="true"></span><span class="xp-status-text">Sync</span>';
        pill.title = 'Sincronizando series con el servidor...';
        return;
      }

      // 1.5. Descargando paquete offline (solo cuando faltan módulos por guardar en el dispositivo)
      if ((stateOverride === 'caching' || this._cacheProgress) && !isOffline) {
        const pct = Math.max(0, Math.min(100, (this._cacheProgress && this._cacheProgress.pct) || 0));
        pill.className = 'xp-pwa-header-pill caching' + (pct >= 100 ? ' is-done' : '');
        pill.style.setProperty('--xp-cache-pct', pct + '%');
        if (pct >= 100) {
          pill.innerHTML = '<span class="xp-status-dot"></span><span class="xp-status-text">100%</span>';
          pill.title = 'Todos los módulos están guardados para usar sin conexión';
        } else {
          pill.innerHTML = `<span class="xp-status-loader" aria-hidden="true"></span><span class="xp-status-text">${pct}%</span>`;
          pill.title = `Preparando modo offline (${pct}%)...`;
        }
        return;
      }
      pill.style.removeProperty('--xp-cache-pct');

      // 2. Éxito al sincronizar: pasa a verde "Listo" y luego al color correspondiente
      if (stateOverride === 'success') {
        pill.className = 'xp-pwa-header-pill success';
        pill.innerHTML = '<span class="xp-status-dot"></span><span class="xp-status-text">Listo</span>';
        pill.title = '¡Entrenamiento sincronizado exitosamente!';
        setTimeout(() => {
          this.updatePillUI();
        }, 1800);
        return;
      }

      // 3. Offline: punto ámbar FIJO (sin pulsar) + palabra Offline
      if (isOffline) {
        pill.className = 'xp-pwa-header-pill offline';
        pill.innerHTML = `<span class="xp-status-dot"></span><span class="xp-status-text">Offline${count > 0 ? ' (' + count + ')' : ''}</span>`;
        pill.title = count > 0 ? `Modo offline: ${count} serie(s) guardadas localmente` : 'Modo offline (Sin conexión)';
      }
      // 4. Pendiente: punto azul índigo FIJO + palabra Pendiente
      else if (count > 0) {
        pill.className = 'xp-pwa-header-pill pending';
        pill.innerHTML = '<span class="xp-status-dot"></span><span class="xp-status-text">Pendiente</span>';
        pill.title = `${count} serie(s) por guardar en la base de datos`;
      }
      // 5. Online: punto verde esmeralda FIJO + palabra Online
      else {
        pill.className = 'xp-pwa-header-pill online';
        pill.innerHTML = '<span class="xp-status-dot"></span><span class="xp-status-text">Online</span>';
        pill.title = 'Conectado a la red';
      }
    }
  };

  // ══════════════════════════════════════════════════════════════
  //  Captura de Evento de Instalación PWA (Cross-Browser)
  // ══════════════════════════════════════════════════════════════
  let deferredInstallPrompt = null;
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredInstallPrompt = e;
    window.deferredInstallPrompt = e;
  });

  window.addEventListener('appinstalled', () => {
    deferredInstallPrompt = null;
    window.deferredInstallPrompt = null;
    localStorage.setItem('xp_pwa_installed', 'true');
    updateInstallCardsUI();
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'success',
        title: '¡Atlas Fit Instalada!',
        text: 'La aplicación se instaló correctamente en tu dispositivo.',
        timer: 3000,
        showConfirmButton: false,
        background: '#121217',
        color: '#fff'
      });
    }
  });

  function isAppInstalled() {
    if (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) return true;
    if (window.navigator.standalone === true) return true;
    if (document.referrer && document.referrer.includes('android-app://')) return true;
    if (localStorage.getItem('xp_pwa_installed') === 'true') return true;
    return false;
  }

  async function checkInstalledAsync() {
    if ('getInstalledRelatedApps' in navigator) {
      try {
        const relatedApps = await navigator.getInstalledRelatedApps();
        if (relatedApps && relatedApps.length > 0) {
          localStorage.setItem('xp_pwa_installed', 'true');
          updateInstallCardsUI();
          return true;
        }
      } catch (e) {
        // Fallback silencioso si no está soportado en HTTP o plataforma
      }
    }
    return isAppInstalled();
  }

  function updateInstallCardsUI() {
    const installed = isAppInstalled();
    const cards = document.querySelectorAll('.xp-pwa-install-card');

    cards.forEach(card => {
      const badge = card.querySelector('.xp-pwa-install-badge');
      const title = card.querySelector('.xp-pwa-install-title');
      const icon = card.querySelector('.xp-pwa-install-icon-wrap i');
      const btn = card.querySelector('.xp-pwa-install-btn, .xp-pwa-uninstall-btn');

      if (installed) {
        card.classList.add('is-installed');
        if (badge) {
          badge.classList.add('is-installed');
          badge.innerHTML = '<span class="badge-dot"></span> App Instalada';
        }
        if (title) {
          title.textContent = 'Atlas Fit está instalada en este dispositivo';
        }
        if (icon) {
          icon.className = 'ti ti-device-mobile-check';
        }
        if (btn) {
          btn.className = 'xp-pwa-uninstall-btn';
          btn.setAttribute('onclick', 'desinstalarXprodApp()');
          btn.setAttribute('title', 'Desinstalar o desvincular App');
          btn.innerHTML = '<i class="ti ti-trash"></i><span>Desinstalar</span>';
        }
      } else {
        card.classList.remove('is-installed');
        if (badge) {
          badge.classList.remove('is-installed');
          badge.innerHTML = '<span class="badge-dot"></span> App Oficial & Offline';
        }
        if (title) {
          if (title.textContent.includes('instalada')) {
            title.textContent = 'Descargar Atlas Fit en este dispositivo';
          }
        }
        if (icon) {
          icon.className = 'ti ti-device-mobile-down';
        }
        if (btn) {
          btn.className = 'xp-pwa-install-btn';
          btn.setAttribute('onclick', 'instalarXprodApp()');
          btn.setAttribute('title', 'Descargar e instalar App');
          btn.innerHTML = '<i class="ti ti-download"></i><span>Instalar App</span>';
        }
      }
    });

    // Actualizar botones Hero de instalación (ej. en perfil.php)
    const heroBtns = document.querySelectorAll('.btn-hero-install');
    heroBtns.forEach(btn => {
      if (installed) {
        btn.classList.add('is-installed');
        btn.setAttribute('onclick', 'desinstalarXprodApp()');
        btn.setAttribute('title', 'App instalada (Clic para desinstalar)');
        btn.innerHTML = '<i class="ti ti-device-mobile-check"></i><span>App Instalada</span>';
      } else {
        btn.classList.remove('is-installed');
        btn.setAttribute('onclick', 'instalarXprodApp()');
        btn.setAttribute('title', 'Descargar e instalar App');
        btn.innerHTML = '<i class="ti ti-device-mobile-down"></i><span>Instalar App</span>';
      }
    });
  }

  async function desinstalarXprodApp() {
    let osNotice = 'En tu dispositivo: mantén presionado el icono de Atlas Fit para eliminarlo o desinstalarlo.';
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    const isAndroid = /Android/i.test(navigator.userAgent);
    const isDesktop = !(/Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent));

    if (isIOS) {
      osNotice = 'En iPhone / iPad: mantén presionado el icono de Atlas Fit en tu pantalla de inicio y pulsa <b>"Eliminar marcador"</b> o <b>"Eliminar de la pantalla de inicio"</b>.';
    } else if (isAndroid) {
      osNotice = 'En Android: mantén presionado el icono de Atlas Fit en tu pantalla y selecciona <b>"Desinstalar"</b> o arrástralo a la papelera.';
    } else if (isDesktop) {
      osNotice = 'En Computadora: abre la app instalada, haz clic en el menú (⋮) de la ventana y selecciona <b>"Desinstalar Atlas Fit"</b> (o accede a <code>chrome://apps</code>).';
    }

    if (typeof Swal !== 'undefined') {
      const confirmResult = await Swal.fire({
        title: '<span style="color:#fff; font-size:1.15rem; font-weight:800;"><i class="ti ti-device-mobile-x" style="color:#ef4444;"></i> ¿Desinstalar Atlas Fit?</span>',
        html: `
          <div style="text-align:left; font-size:0.88rem; color:#cbd5e1; display:flex; flex-direction:column; gap:12px; margin-top:8px;">
            <p style="margin:0; line-height:1.45;">Se desvinculará la versión instalada y se liberará la caché de la aplicación en este navegador.</p>
            <div style="background:rgba(239, 68, 68, 0.08); border:1px solid rgba(239, 68, 68, 0.25); padding:10px 12px; border-radius:12px; font-size:0.83rem; color:#fca5a5; line-height:1.4;">
              <i class="ti ti-info-circle" style="vertical-align:middle; margin-right:4px; font-size:1rem;"></i>
              ${osNotice}
            </div>
          </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="ti ti-trash"></i> Desinstalar y limpiar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#374151',
        background: '#121217',
        color: '#fff',
        reverseButtons: true
      });

      if (!confirmResult.isConfirmed) return;
    } else {
      if (!confirm('¿Deseas desinstalar Atlas Fit y limpiar los datos sin conexión?')) return;
    }

    try {
      // 1. Limpiar Service Workers registrados
      if ('serviceWorker' in navigator) {
        const registrations = await navigator.serviceWorker.getRegistrations();
        for (const reg of registrations) {
          await reg.unregister();
        }
      }

      // 2. Limpiar Caches de Storage
      if ('caches' in window) {
        const cacheKeys = await caches.keys();
        for (const key of cacheKeys) {
          await caches.delete(key);
        }
      }

      // 3. Remover indicador de instalación
      localStorage.removeItem('xp_pwa_installed');

      // 4. Actualizar interfaz inmediatamente
      updateInstallCardsUI();

      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'success',
          title: 'Desinstalación completada',
          html: `
            <p style="color:#cbd5e1; font-size:0.9rem; margin-top:6px; line-height:1.45;">
              La aplicación ha sido desvinculada y la caché liberada.<br>
              <span style="font-size:0.8rem; color:#94a3b8; display:block; margin-top:8px;">${osNotice}</span>
            </p>
          `,
          confirmButtonText: 'Entendido',
          confirmButtonColor: '#8a2be2',
          background: '#121217',
          color: '#fff'
        });
      } else {
        alert('Desinstalación completada. Se liberaron los datos locales.');
      }
    } catch (err) {
      console.warn('[PWA] Error al desinstalar:', err);
      localStorage.removeItem('xp_pwa_installed');
      updateInstallCardsUI();
    }
  }

  function instalarXprodApp() {
    if (isAppInstalled()) {
      desinstalarXprodApp();
      return;
    }

    // 1. Si el navegador soporta prompt nativo y está listo (Android Chrome, Edge, Chromium)
    if (deferredInstallPrompt) {
      deferredInstallPrompt.prompt().then(() => {
        return deferredInstallPrompt.userChoice;
      }).then((choiceResult) => {
        if (choiceResult && choiceResult.outcome === 'accepted') {
          localStorage.setItem('xp_pwa_installed', 'true');
          updateInstallCardsUI();
          if (typeof Swal !== 'undefined') {
            Swal.fire({
              icon: 'success',
              title: 'Instalando Atlas Fit...',
              text: 'Se está añadiendo el acceso directo a tu pantalla de inicio.',
              timer: 2500,
              showConfirmButton: false,
              background: '#121217',
              color: '#fff'
            });
          }
        }
        deferredInstallPrompt = null;
        window.deferredInstallPrompt = null;
      }).catch(err => {
        console.warn('[PWA] Error en prompt:', err);
      });
      return;
    }

    // 2. Detección de iOS (iPhone / iPad en Safari)
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    if (isIOS) {
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          title: '<span style="color:#fff; font-size:1.15rem; font-weight:800;"><i class="ti ti-brand-apple" style="color:#a855f7;"></i> Instalar en iPhone / iPad</span>',
          html: `
            <div style="text-align:left; font-size:0.88rem; color:#cbd5e1; display:flex; flex-direction:column; gap:10px; margin-top:8px;">
              <div style="display:flex; align-items:flex-start; gap:12px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08); padding:10px 12px; border-radius:12px;">
                <span style="background:linear-gradient(135deg, #8a2be2, #9d4edd); color:#fff; width:24px; height:24px; border-radius:50%; display:grid; place-items:center; font-weight:800; font-size:0.8rem; flex-shrink:0;">1</span>
                <div>Toca el botón <b>Compartir</b> <i class="ti ti-share" style="color:#38bdf8; font-size:1.05rem; vertical-align:middle;"></i> en la barra inferior de Safari.</div>
              </div>
              <div style="display:flex; align-items:flex-start; gap:12px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08); padding:10px 12px; border-radius:12px;">
                <span style="background:linear-gradient(135deg, #8a2be2, #9d4edd); color:#fff; width:24px; height:24px; border-radius:50%; display:grid; place-items:center; font-weight:800; font-size:0.8rem; flex-shrink:0;">2</span>
                <div>Desliza hacia abajo y pulsa <b>"Agregar a pantalla de inicio"</b> <i class="ti ti-square-plus" style="color:#c084fc; font-size:1.05rem; vertical-align:middle;"></i>.</div>
              </div>
              <div style="display:flex; align-items:flex-start; gap:12px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08); padding:10px 12px; border-radius:12px;">
                <span style="background:linear-gradient(135deg, #8a2be2, #9d4edd); color:#fff; width:24px; height:24px; border-radius:50%; display:grid; place-items:center; font-weight:800; font-size:0.8rem; flex-shrink:0;">3</span>
                <div>Toca <b>"Agregar"</b> arriba a la derecha.</div>
              </div>
            </div>
            <p style="font-size:0.75rem; color:#94a3b8; margin:12px 0 0; text-align:center;">
              ✨ Tendrás el icono en tu pantalla de inicio para abrir la App a pantalla completa.
            </p>
          `,
          confirmButtonText: '¡Entendido!',
          confirmButtonColor: '#8a2be2',
          background: '#121217',
          color: '#fff'
        });
      } else {
        alert('En Safari: toca el icono de Compartir y selecciona "Agregar a pantalla de inicio".');
      }
      return;
    }

    // 3. Detección de Escritorio (Desktop Chrome, Edge, etc.)
    const isDesktop = !(/Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent));
    if (isDesktop) {
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          title: '<span style="color:#fff; font-size:1.15rem; font-weight:800;"><i class="ti ti-device-laptop" style="color:#8a2be2;"></i> Instalar en Computadora</span>',
          html: `
            <div style="text-align:left; font-size:0.88rem; color:#cbd5e1; display:flex; flex-direction:column; gap:10px; margin-top:8px;">
              <div style="display:flex; align-items:flex-start; gap:10px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08); padding:10px 12px; border-radius:12px;">
                <i class="ti ti-layout-navbar" style="font-size:1.3rem; color:#38bdf8; flex-shrink:0;"></i>
                <div>En la barra de direcciones de tu navegador (arriba a la derecha), haz clic en el icono <b>Instalar</b> <i class="ti ti-download" style="color:#a855f7;"></i> o <b>(⊕)</b>.</div>
              </div>
              <div style="display:flex; align-items:flex-start; gap:10px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08); padding:10px 12px; border-radius:12px;">
                <i class="ti ti-dots-vertical" style="font-size:1.3rem; color:#c084fc; flex-shrink:0;"></i>
                <div>O haz clic en los <b>tres puntos (⋮)</b> del navegador y selecciona <b>"Instalar Atlas Fit"</b>.</div>
              </div>
            </div>
          `,
          confirmButtonText: 'Entendido',
          confirmButtonColor: '#8a2be2',
          background: '#121217',
          color: '#fff'
        });
      } else {
        alert('Haz clic en el icono de instalación en la barra de direcciones o en el menú del navegador para instalar la App.');
      }
      return;
    }

    // 4. Android u otros navegadores móviles cuando el prompt nativo no saltó automáticamente
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        title: '<span style="color:#fff; font-size:1.15rem; font-weight:800;"><i class="ti ti-device-mobile" style="color:#8a2be2;"></i> Instalar en tu Móvil</span>',
        html: `
          <div style="text-align:left; font-size:0.88rem; color:#cbd5e1; display:flex; flex-direction:column; gap:10px; margin-top:8px;">
            <div style="display:flex; align-items:flex-start; gap:10px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08); padding:10px 12px; border-radius:12px;">
              <i class="ti ti-dots-vertical" style="font-size:1.3rem; color:#a855f7; flex-shrink:0;"></i>
              <div>Toca los <b>tres puntos (⋮)</b> en la esquina superior derecha de tu navegador.</div>
            </div>
            <div style="display:flex; align-items:flex-start; gap:10px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08); padding:10px 12px; border-radius:12px;">
              <i class="ti ti-plus" style="font-size:1.3rem; color:#38bdf8; flex-shrink:0;"></i>
              <div>Selecciona <b>"Instalar aplicación"</b> o <b>"Agregar a la pantalla principal"</b>.</div>
            </div>
          </div>
        `,
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#8a2be2',
        background: '#121217',
        color: '#fff'
      });
    } else {
      alert('Abre el menú del navegador (⋮) y selecciona "Instalar aplicación" o "Agregar a la pantalla principal".');
    }
  }

  // Exponer métodos globalmente
  XpSync.instalarApp = instalarXprodApp;
  XpSync.desinstalarApp = desinstalarXprodApp;
  XpSync.isAppInstalled = isAppInstalled;
  XpSync.updateInstallCardsUI = updateInstallCardsUI;

  window.XpSync = XpSync;
  window.instalarXprodApp = instalarXprodApp;
  window.desinstalarXprodApp = desinstalarXprodApp;
  window.isAppInstalled = isAppInstalled;
  window.updateInstallCardsUI = updateInstallCardsUI;

  // Inicializar al cargar el script y sincronizar UI
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      XpSync.initSW();
      XpSync.updatePillUI();
      updateInstallCardsUI();
      checkInstalledAsync();
    });
  } else {
    XpSync.initSW();
    XpSync.updatePillUI();
    updateInstallCardsUI();
    checkInstalledAsync();
  }

})(window);
