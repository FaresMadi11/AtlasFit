<?php
$temaGlobalLogin = 'classic';
$temaLoginVer    = 1;
try {
    if (function_exists('xpResolverTemaWeb') && function_exists('getDB')) {
        $temaLoginInfo   = xpResolverTemaWeb(getDB(), null);
        $temaGlobalLogin = $temaLoginInfo['tema_global'] ?? 'classic';
        $temaLoginVer    = (int)($temaLoginInfo['global_ver'] ?? 1);
    }
} catch (\Throwable $e) {}
$mapaMetaColorLogin = [
    'classic'    => '#02040a',
    'dark'       => '#02040a',
    'obsidian'   => '#131118',
    'deep-ocean' => '#06141b',
];
$metaColorLogin = $mapaMetaColorLogin[$temaGlobalLogin] ?? '#02040a';
?><!DOCTYPE html>
<html lang="es" data-theme="<?= e($temaGlobalLogin) ?>" id="loginHtmlRoot">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
<title>Acceso – Atlas Fit</title>
<?php
  $xpBuildVer = xpAppBuildVersion();
  $vIcon192   = xpAssetVer('assets/img/icon-192.png');
  $vIcon512   = xpAssetVer('assets/img/icon-512.png');
  $vIconSvg = xpAssetVer('assets/img/favicon.svg');
  $vIcon32  = xpAssetVer('assets/img/favicon-32.png');
  $vApple   = xpAssetVer('assets/img/apple-touch-icon.png');
  $vFavIco    = xpAssetVer('favicon.ico');
  $vWoff2     = xpAssetVer('assets/vendor/tabler-icons/fonts/tabler-icons.woff2');
  $vTabler    = xpAssetVer('assets/vendor/tabler-icons/tabler-icons.min.css');
  $vCsCss     = xpAssetVer('assets/css/custom-select.css');
  $vSwal      = xpAssetVer('assets/vendor/sweetalert2/sweetalert2.all.min.js');
  $vAnime     = xpAssetVer('assets/vendor/animejs/anime.min.js');
?>
<meta name="description" content="Atlas Fit: plataforma profesional para entrenadores personales y atletas con rutinas interactivas, métricas biométricas y seguimiento diario.">
<meta property="og:type" content="website">
<meta property="og:title" content="Atlas Fit — Plataforma de Entrenamiento Inteligente">
<meta property="og:description" content="Gestión integral para entrenadores personales y atletas: rutinas personalizadas, progreso por ejercicio, control diario y seguimiento biométrico.">
<meta property="og:url" content="<?= e(APP_URL . '/auth/login') ?>">
<meta property="og:image" content="<?= e(APP_URL . '/assets/img/icon-512.png?v=' . $vIcon512) ?>">
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="Atlas Fit — Plataforma de Entrenamiento">
<meta name="twitter:description" content="Control profesional de rutinas, métricas de fuerza y seguimiento atleta-entrenador.">
<meta name="twitter:image" content="<?= e(APP_URL . '/assets/img/icon-512.png?v=' . $vIcon512) ?>">
<meta name="theme-color" id="loginThemeColorMeta" content="<?= e($metaColorLogin) ?>">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Atlas Fit">
<meta name="mobile-web-app-capable" content="yes">
<link rel="manifest" href="<?= APP_URL ?>/manifest.webmanifest?v=<?= e($xpBuildVer) ?>">
<link rel="icon" type="image/svg+xml" sizes="any" href="<?= APP_URL ?>/assets/img/favicon.svg?v=<?= e($vIconSvg) ?>">
<link rel="icon" type="image/png" sizes="32x32" href="<?= APP_URL ?>/assets/img/favicon-32.png?v=<?= e($vIcon32) ?>">
<link rel="alternate icon" href="<?= APP_URL ?>/favicon.ico?v=<?= e($vFavIco) ?>">
<link rel="apple-touch-icon" href="<?= APP_URL ?>/assets/img/apple-touch-icon.png?v=<?= e($vApple) ?>">
<link rel="preload" href="<?= APP_URL ?>/assets/vendor/tabler-icons/fonts/tabler-icons.woff2?v=<?= e($vWoff2) ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?= APP_URL ?>/assets/vendor/tabler-icons/tabler-icons.min.css?v=<?= e($vTabler) ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
<link rel="preload" href="<?= APP_URL ?>/assets/css/custom-select.css?v=<?= e($vCsCss) ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript>
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/vendor/tabler-icons/tabler-icons.min.css?v=<?= e($vTabler) ?>">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/custom-select.css?v=<?= e($vCsCss) ?>">
</noscript>
<script>
(function() {
  var dbTheme = <?= json_encode($temaGlobalLogin) ?>;
  var dbGVer  = <?= (int)$temaLoginVer ?>;
  var savedTheme = dbTheme;
  try {
    var lsTheme = localStorage.getItem('xp_theme');
    var lsGVer  = Number(localStorage.getItem('xp_theme_gver') || '0');
    if (lsTheme && ['classic','obsidian','deep-ocean','dark'].indexOf(lsTheme) !== -1 && lsGVer >= dbGVer) {
      savedTheme = (lsTheme === 'dark') ? 'classic' : lsTheme;
    } else {
      localStorage.setItem('xp_theme', dbTheme);
      localStorage.setItem('xp_theme_gver', String(dbGVer));
    }
  } catch (_) {}
  window.XP_LOGIN_THEME = savedTheme;
  document.documentElement.setAttribute('data-theme', savedTheme);
  var metaMap = {
    'classic': '#02040a',
    'dark': '#02040a',
    'obsidian': '#131118',
    'deep-ocean': '#06141b'
  };
  var metaEl = document.getElementById('loginThemeColorMeta');
  if (metaEl && metaMap[savedTheme]) {
    metaEl.setAttribute('content', metaMap[savedTheme]);
  }

  var appBase = <?= json_encode(APP_URL) ?>;
  var qs = window.location.search || '';
  if (qs.indexOf('logged_out=1') !== -1 || qs.indexOf('standby=1') !== -1) {
    try {
      localStorage.removeItem('xp_auth_active');
      localStorage.removeItem('xp_auth_role');
      localStorage.removeItem('xp_auth_uid');
      localStorage.removeItem('xp_auth_home');
      localStorage.removeItem('xp_offline_ready_sig');
      localStorage.removeItem('xp_offline_queue');
      Object.keys(localStorage).forEach(function(k) {
        if (k && k.indexOf('xprod_') === 0) localStorage.removeItem(k);
      });
      sessionStorage.removeItem('xp_prewarm_ts');
    } catch (_) {}
    // Al cerrar sesión borramos SOLO los HTML/datos privados del usuario, manteniendo el App Shell estático en caché
    if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
      try { navigator.serviceWorker.controller.postMessage({ type: 'CLEAR_USER_CACHE' }); } catch (_) {}
    }
    if ('caches' in window) {
      caches.keys().then(function(ks) {
        ks.forEach(function(k) {
          if (k.indexOf('atlas-fit-user-') === 0 || (k.indexOf('atlas-fit-') === 0 && k.indexOf('atlas-fit-static-') !== 0)) {
            caches.delete(k);
          }
        });
      }).catch(function(){});
    }
  } else if (!navigator.onLine && qs.indexOf('ref=') === -1) {
    try {
      if (localStorage.getItem('xp_auth_active') === '1') {
        var targetHome = localStorage.getItem('xp_auth_home') || (appBase + '/cliente/dashboard');
        window.location.replace(targetHome);
        return;
      }
    } catch (_) {}
  }
  if ('serviceWorker' in navigator) {
    var xpSwVer = <?= json_encode($xpBuildVer) ?>;
    var xpHadCtrl = !!navigator.serviceWorker.controller;
    var xpLastCheck = 0;
    navigator.serviceWorker.register(appBase + '/sw.js?v=' + xpSwVer, { scope: appBase + '/', updateViaCache: 'none' })
      .then(function(r) {
        if (r.waiting) r.waiting.postMessage({ type: 'SKIP_WAITING' });
        r.update().catch(function(){});
      })
      .catch(function(){});

    var tryReloadLoginForBuild = function(targetVer) {
      try {
        if (sessionStorage.getItem('xp_sw_reloaded') === targetVer) return;
        var ae = document.activeElement;
        if (ae && (ae.tagName === 'INPUT' || ae.tagName === 'TEXTAREA' || ae.tagName === 'SELECT')) return;
        var regView = document.getElementById('view-register');
        if ((regView && regView.style.display !== 'none') || document.querySelector('.swal2-container')) return;
        sessionStorage.setItem('xp_sw_reloaded', targetVer);
      } catch (_) {}
      window.location.reload();
    };

    var checkLoginBuildUpdate = function(force) {
      if (!navigator.onLine) return;
      var now = Date.now();
      if (!force && (now - xpLastCheck) < 8000) return;
      xpLastCheck = now;
      fetch(appBase + '/public/version?t=' + now, { cache: 'no-store', credentials: 'same-origin' })
        .then(function(res) { return res.ok ? res.json() : null; })
        .then(function(data) {
          if (!data || !data.v || data.v === xpSwVer) return;
          if ('caches' in window) {
            caches.keys().then(function(ks) {
              ks.forEach(function(k) {
                if (k !== ('atlas-fit-static-' + data.v) && k !== ('atlas-fit-user-' + data.v)) caches.delete(k);
              });
            }).catch(function(){});
          }
          navigator.serviceWorker.register(appBase + '/sw.js?v=' + encodeURIComponent(data.v), {
            scope: appBase + '/',
            updateViaCache: 'none'
          }).then(function(r) {
            if (r.waiting) r.waiting.postMessage({ type: 'SKIP_WAITING' });
            r.update().catch(function(){});
          }).catch(function(){});
          tryReloadLoginForBuild(data.v);
        })
        .catch(function(){});
    };

    // Verificar versión nueva inmediatamente al abrir el acceso directo PWA, al volver a primer plano o recuperar red
    checkLoginBuildUpdate(true);
    document.addEventListener('visibilitychange', function() {
      if (document.visibilityState === 'visible') checkLoginBuildUpdate(false);
    });
    window.addEventListener('focus', function() { checkLoginBuildUpdate(false); });
    window.addEventListener('online', function() { checkLoginBuildUpdate(true); });
    window.addEventListener('pageshow', function(ev) { if (ev.persisted) checkLoginBuildUpdate(true); });

    navigator.serviceWorker.addEventListener('controllerchange', function() {
      if (!xpHadCtrl) return;
      var ctrlUrl = navigator.serviceWorker.controller ? navigator.serviceWorker.controller.scriptURL : '';
      var ctrlVer = '';
      try { ctrlVer = new URL(ctrlUrl, window.location.href).searchParams.get('v') || ''; } catch (_) {}
      if (!ctrlVer || ctrlVer === xpSwVer) return;
      tryReloadLoginForBuild(ctrlVer);
    });
  }
})();

function togglePassVisibility(btn, ev) {
  if (ev) {
    if (typeof ev.stopPropagation === 'function') ev.stopPropagation();
    if (typeof ev.preventDefault === 'function') ev.preventDefault();
  }
  if (!btn) return;
  var targetId = btn.getAttribute('data-target');
  var inp = document.getElementById(targetId);
  if (!inp) return;
  var isPass = (inp.type === 'password');
  inp.type = isPass ? 'text' : 'password';
  var icon = btn.querySelector('i');
  if (icon) {
    icon.className = isPass ? 'ti ti-eye-off' : 'ti ti-eye';
  }
  try {
    inp.focus({ preventScroll: true });
    var len = inp.value.length;
    if (typeof inp.setSelectionRange === 'function') {
      inp.setSelectionRange(len, len);
    }
  } catch (_) {}
}
window.togglePassVisibility = togglePassVisibility;
</script>
<style>
:root,
html[data-theme="classic"],
html[data-theme="dark"] {
  color-scheme: dark;
  --login-bg: #02040a;
  --login-overlay: rgba(2, 4, 10, 0.30);
  --login-orb1: rgba(138, 43, 226, 0.16);
  --login-orb2: rgba(56, 100, 240, 0.12);
  --login-accent: #8a2be2;
  --login-accent-glow: rgba(138, 43, 226, 0.28);
}
html[data-theme="obsidian"] {
  --login-bg: #120e1c;
  --login-overlay: rgba(18, 14, 28, 0.28);
  --login-orb1: rgba(170, 162, 179, 0.28);
  --login-orb2: rgba(124, 92, 191, 0.24);
  --login-accent: #AAA2B3;
  --login-accent-glow: rgba(170, 162, 179, 0.28);
}
html[data-theme="deep-ocean"] {
  --login-bg: #051824;
  --login-overlay: rgba(5, 24, 36, 0.28);
  --login-orb1: rgba(56, 189, 248, 0.26);
  --login-orb2: rgba(37, 99, 235, 0.22);
  --login-accent: #9BA8AB;
  --login-accent-glow: rgba(155, 168, 171, 0.28);
}

*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;-webkit-tap-highlight-color:transparent;}
button, a, input, select, .goal-chip, .subnivel-card, .wheel-item {
  touch-action: manipulation;
}
html, body {
  width: 100%;
  height: 100%;
  margin: 0;
  padding: 0;
  overflow: hidden;
  background: var(--login-bg, #02040a);
  transition: background-color 0.35s ease;
}
body {
  font-family: 'Inter', sans-serif;
  position: fixed;
  inset: 0;
}

/* ══════════════════════════════════════════════════════
   MAHORAGA – CODE-GENERATED ANIMATED CHARACTER
   ══════════════════════════════════════════════════════ */
#mahoragaCanvas {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  height: 100lvh;
  z-index: 0;
  pointer-events: none;
  transform: translate3d(0, 0, 0);
  -webkit-transform: translate3d(0, 0, 0);
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
  will-change: transform;
}
/* Vignette suave solo en esquinas, no toca los brazos */
.mh-vignette {
  position: fixed;
  inset: 0;
  z-index: 1;
  pointer-events: none;
  background: radial-gradient(
    ellipse 95% 90% at 50% 50%,
    transparent 60%,
    var(--login-bg, #02040a) 100%
  );
}

/* ── Background orbs (Radial Gradient Zero-GPU-Blur) ── */
.bg-orb{position:fixed;border-radius:50%;pointer-events:none;z-index:1;}
.orb1{width:520px;height:520px;background:radial-gradient(circle, var(--login-orb1, rgba(138,43,226,0.14)) 0%, transparent 70%);top:-150px;left:-150px;}
.orb2{width:440px;height:440px;background:radial-gradient(circle, var(--login-orb2, rgba(56,100,240,0.11)) 0%, transparent 70%);bottom:-120px;right:-120px;}

/* ── Modal / Auth Box (Estable ante apertura de teclado móvil) ── */
main#mainContent{
  position: fixed;
  inset: 0;
  z-index: 2;
  width: 100vw;
  height: 100%;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 24px 16px;
  background: var(--login-overlay, rgba(2, 4, 10, 0.30));
  /* Scroll interno fluido cuando el wizard de registro o recuperación desborda la pantalla */
  overflow-y: auto;
  overscroll-behavior: contain;
  -webkit-overflow-scrolling: touch;
}

.auth-box{
  position:relative;
  width:100%;
  max-width:420px;
  border-radius:24px;
  display:flex;
  flex-direction:column;
  /* El desenfoque ahora está en mainContent */
  padding:48px 36px;
  margin:auto; /* centra y, si desborda, se alinea arriba sin cortar el contenido */
}

.form-title{font-size:1.65rem;font-weight:800;color:#fff;margin-bottom:8px;letter-spacing:-.5px;text-align:center;}
.form-sub{font-size:.875rem;color:rgba(255,255,255,.5);margin-bottom:32px;text-align:center;}

/* ── Fields (Touch targets & Typography) ── */
.field{
  display:flex;
  flex-direction:column;
  background:#0a0b12;
  border:1px solid rgba(255,255,255,0.15);
  border-radius:12px;
  padding:14px 16px; 
  margin-bottom:20px; 
  transition:border-color .25s,box-shadow .25s;
}
.field:focus-within{border-color:#8a2be2;box-shadow: 0 0 12px rgba(138,43,226,0.25);}
.field.has-error{border-color:rgba(248,113,113,0.65) !important;box-shadow:0 0 0 2px rgba(248,113,113,0.14) !important;}
.field.has-error .ibg-error{display:inline-flex;}
.field label{display:block;font-size:0.75rem;color:rgba(255,255,255,.6);font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:8px;} 
.field input{
  width:100%;background:transparent;border:none !important;box-shadow:none !important;-webkit-box-shadow:none !important;color:#FFFFFF;font-size:1.05rem;font-weight:500;outline:none;font-family:'Inter',sans-serif;
} 
.field input::placeholder{color:rgba(255,255,255,.18);}
/* Autofill integrado a la tarjeta oscura: evita rectángulos blancos/azules al volver al login */
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus,
input:-webkit-autofill:active,
input:-internal-autofill-selected,
textarea:-webkit-autofill,
textarea:-webkit-autofill:hover,
textarea:-webkit-autofill:focus,
textarea:-webkit-autofill:active,
select:-webkit-autofill,
select:-webkit-autofill:hover,
select:-webkit-autofill:focus,
select:-webkit-autofill:active {
    -webkit-text-fill-color: #ffffff !important;
    caret-color: #ffffff !important;
    -webkit-box-shadow: 0 0 0 1000px #0a0b12 inset !important;
    box-shadow: 0 0 0 1000px #0a0b12 inset !important;
    transition: background-color 9999999s step-end 0s, color 9999999s step-end 0s !important;
}
.input-box-group input:-webkit-autofill,
.input-box-group input:-webkit-autofill:hover,
.input-box-group input:-webkit-autofill:focus,
.input-box-group input:-webkit-autofill:active,
.input-box-group input:-internal-autofill-selected {
    -webkit-box-shadow: 0 0 0 1000px #0f1017 inset !important;
    box-shadow: 0 0 0 1000px #0f1017 inset !important;
}
.pass-row{display:flex;align-items:center;position:relative;width:100%;}
.pass-row input{flex:1;min-width:0;padding-right:48px !important;}
.btn-eye{
  position:absolute;
  right:4px;
  top:50%;
  transform:translateY(-50%);
  background:transparent;
  border:none;
  color:rgba(255,255,255,.45);
  cursor:pointer;
  font-size:1.25rem;
  padding:10px;
  min-width:44px;
  min-height:44px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  transition:color .15s ease, transform .1s ease;
  touch-action:manipulation;
  -webkit-tap-highlight-color:transparent;
  z-index:3;
}
.btn-eye:hover,.btn-eye:focus{color:#ffffff;}
.btn-eye:active{transform:translateY(-50%) scale(0.92);color:#a855f7;}
.btn-eye i{pointer-events:none;}

/* ── Submit ── */
.btn-submit{
  display:block;
  width:100%;margin-top:12px;margin-bottom:12px;
  background:rgba(255,255,255,0.9);
  color:#0a0a0a;
  border:none;border-radius:50px;
  padding:14px;font-size:1rem;font-weight:700;
  letter-spacing:.4px;text-transform:uppercase;
  cursor:pointer;transition:transform .2s ease, opacity .2s ease;
  box-shadow:0 6px 22px rgba(255,255,255,.15);
}
.btn-submit:hover{opacity:0.85;transform:scale(0.98);}
.btn-submit:disabled{opacity:.55;cursor:not-allowed;transform:none;}
.link-center{text-align:center;margin-top:16px;font-size:.95rem;}
.link-center a{color:rgba(255,255,255,0.7);text-decoration:none;font-weight:600;transition:color 0.2s;}
.link-center a:hover{color:#407E8C;}

.link-bottom{text-align:center;margin-top:30px;font-size:.95rem;color:rgba(255,255,255,.6);}
.link-bottom a{color:rgba(255,255,255,0.7);text-decoration:none;font-weight:700;margin-left:5px;transition:color 0.2s;}
.link-bottom a:hover{color:#407E8C;}

/* ── Divisor ── */
.divider{display:flex;align-items:center;gap:12px;margin:20px 0 16px;}
.divider span{color:rgba(255,255,255,.25);font-size:.78rem;white-space:nowrap;text-transform:uppercase;letter-spacing:.8px;}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:rgba(255,255,255,.08);}

/* ── Botón Google ── */
.btn-google{
  display:flex;align-items:center;justify-content:center;gap:10px;
  width:100%;padding:12px 16px;
  background:rgba(255,255,255,0.06);
  border:1px solid rgba(255,255,255,0.12);
  border-radius:50px;color:#fff;
  font-size:.95rem;font-weight:600;
  font-family:'Inter',sans-serif;
  cursor:pointer;text-decoration:none;
  transition:background .2s,border-color .2s,transform .15s;
}
.btn-google:hover{
  background:rgba(255,255,255,0.12);
  border-color:rgba(255,255,255,0.25);
  transform:translateY(-1px);
  color:#fff;
}
.btn-google:disabled{opacity:.5;cursor:not-allowed;transform:none;}
.btn-google svg{flex-shrink:0;width:20px;height:20px;}

/* ── Spinner ── */
.spin{display:inline-block;width:14px;height:14px;border-radius:50%;border:2.5px solid rgba(255,255,255,.3);border-top-color:#fff;animation:rot .6s linear infinite;vertical-align:middle;margin-right:6px;}
@keyframes rot{to{transform:rotate(360deg);}}

/* ── Skip link (keyboard-nav / voiceover-sr) ── */
.skip-link{position:absolute;left:-9999px;top:8px;background:#4b9b54;color:#fff;padding:10px 18px;border-radius:8px;font-weight:700;font-size:1rem;z-index:9999;text-decoration:none;}
.skip-link:focus{left:16px;}

/* ── Focus states ── */
:focus-visible{outline:3px solid #6ecf76;outline-offset:3px;border-radius:6px;}
.btn-submit:focus-visible{outline-color:#000;outline-offset:4px;}
.btn-eye:focus-visible{outline:3px solid #6ecf76;outline-offset:2px;border-radius:4px;}
.link-sm a:focus-visible{outline:2px solid #6ecf76;outline-offset:2px;border-radius:3px;}

/* ── Reduced Motion (Apple/MD) ── */
@media (prefers-reduced-motion: reduce) {
  *,*::before,*::after{
    animation-duration:0.01ms !important;
    animation-iteration-count:1 !important;
    transition-duration:0.01ms !important;
  }
  .bg-orb{animation:none;}
  .mh-img{animation:none;}
}


/* ══ SPA TOGGLE LOGIN ⇄ REGISTRO ⇄ RECUPERACIÓN (mismo contenedor, mismo fondo) ══ */
.auth-view.fade-in{animation:viewFadeIn .35s ease both;}
@keyframes viewFadeIn{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}
#view-register .form-sub,
#view-recovery .form-sub{margin-bottom:24px;}

/* Icono escudo con resplandor morado para recuperación */
.recovery-icon-wrap {
  width: 68px;
  height: 68px;
  margin: 0 auto 16px auto;
  border-radius: 50%;
  background: rgba(138, 43, 226, 0.12);
  border: 1px solid rgba(138, 43, 226, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 0 24px rgba(138, 43, 226, 0.25);
  transition: transform .25s ease, box-shadow .25s ease;
}
.recovery-icon-wrap:hover {
  transform: scale(1.05);
  box-shadow: 0 0 32px rgba(138, 43, 226, 0.4);
}
.recovery-icon-wrap i {
  font-size: 2.2rem;
  color: #b388ff;
  filter: drop-shadow(0 0 12px rgba(138, 43, 226, 0.45));
}

/* Fila doble Nombres / Apellidos */
.reg-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:8px;}
.reg-grid-2 .input-box-group{min-width:0;}

/* Campo = bloque redondeado compacto con la etiqueta DENTRO (arriba a la izquierda) */
.input-box-group{
  display: flex;
  flex-direction: column;
  background: #0f1017;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 14px;
  padding: 9px 13px;
  margin-bottom: 9px;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.input-box-group:focus-within{border-color:#8a2be2;box-shadow:0 0 0 3px rgba(138,43,226,.15);}
.input-box-group label{font-size:0.75rem;font-weight:600;color:#94a3b8;display:block;margin-bottom:4px;}
.input-box-group input,
.input-box-group select{
  background-color: transparent;
  border: none !important;
  outline: none !important;
  box-shadow: none !important;
  -webkit-box-shadow: none !important;
  color: #fff;
  width: 100%;
  font-size: 1rem;
  font-family:'Inter',sans-serif;
}
.input-box-group input{border-radius:6px;}
.input-box-group input::placeholder{color:rgba(255,255,255,.28);}

/* <select> oscuro con flecha propia */
.input-box-group select{
  -webkit-appearance:none;appearance:none;cursor:pointer;text-overflow:ellipsis;padding-right:24px;
  background-image:url("data:image/svg+xml;charset=US-ASCII,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' d='M1 1.5l5 5 5-5'/%3E%3C/svg%3E");
  background-repeat:no-repeat;background-position:right 2px center;
}
.input-box-group select:invalid{color:rgba(255,255,255,.4);}
.input-box-group select option{background:#15151a;color:#fff;}

/* Aviso único y discreto por encima del campo que falta o es inválido */
.input-box-group.has-error{
  border-color: rgba(248, 113, 113, 0.65) !important;
  box-shadow: 0 0 0 2px rgba(248, 113, 113, 0.14) !important;
}
@keyframes ibgShake {
  0%, 100% { transform: translateX(0); }
  25%, 75% { transform: translateX(-3px); }
  50% { transform: translateX(3px); }
}
.ibg-error{
  display:none;
  order:-1;
  align-items:center;
  gap:5px;
  width:fit-content;
  max-width:100%;
  margin:0 0 6px 0;
  padding:3px 9px;
  border-radius:999px;
  background:rgba(248,113,113,0.12);
  border:1px solid rgba(248,113,113,0.28);
  font-size:.72rem;
  color:#fca5a5;
  line-height:1.3;
  font-weight:500;
}
.input-box-group.has-error .ibg-error{display:inline-flex;}
.reg-email-row{display:flex;align-items:center;gap:8px;}
.reg-email-row input{flex:1;min-width:0;}
.btn-verify-email{
  flex-shrink:0;white-space:nowrap;border:1px solid rgba(168,85,247,.42);
  background:rgba(138,43,226,.22);color:#e9d5ff;border-radius:10px;
  padding:7px 11px;font-size:.75rem;font-weight:700;cursor:pointer;
  transition:all .2s ease;display:inline-flex;align-items:center;gap:5px;
}
.btn-verify-email:hover:not(:disabled){background:rgba(138,43,226,.38);color:#fff;}
.btn-verify-email:disabled{opacity:.55;cursor:not-allowed;}
.reg-otp-wrap{
  margin-top:9px;padding-top:9px;border-top:1px dashed rgba(255,255,255,.1);
  display:flex;align-items:center;gap:8px;flex-wrap:wrap;
}
.reg-otp-input{
  flex:1;min-width:130px;padding:7px 10px !important;border-radius:9px !important;
  background:rgba(255,255,255,.06) !important;border:1px solid rgba(168,85,247,.35) !important;
  color:#fff !important;font-size:.92rem !important;font-weight:700;letter-spacing:3px;text-align:center;
}
.reg-otp-input:focus{border-color:#a855f7 !important;box-shadow:0 0 0 2px rgba(138,43,226,.22) !important;}
.btn-confirm-otp{
  flex-shrink:0;border:none;border-radius:9px;padding:8px 13px;font-size:.76rem;font-weight:700;
  background:linear-gradient(135deg,#8a2be2,#6d28d9);color:#fff;cursor:pointer;
  display:inline-flex;align-items:center;gap:5px;transition:all .2s ease;
}
.btn-confirm-otp:hover:not(:disabled){filter:brightness(1.12);}
.btn-confirm-otp:disabled{opacity:.55;cursor:not-allowed;}
.badge-email-ok{
  font-size:.68rem;padding:2px 8px;border-radius:99px;font-weight:700;text-transform:none;
  background:rgba(34,197,94,.16);color:#4ade80;border:1px solid rgba(34,197,94,.35);
  display:none;align-items:center;gap:4px;
}
.badge-email-ok.is-visible{display:inline-flex;}

@media (max-width:330px){.reg-grid-2{grid-template-columns:1fr;gap:0;}}

/* ══ ONBOARDING WIZARD 3 PASOS (UI/UX Pro-Max Bento UI) ══ */
.wizard-progress-wrap {
  margin-bottom: 22px;
}
.wizard-steps-nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
  margin-bottom: 12px;
}
.wizard-step-node {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  position: relative;
  z-index: 2;
  cursor: default;
}
.wizard-step-bubble {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #12101e;
  border: 2px solid rgba(255,255,255,0.16);
  color: #94a3b8;
  font-size: 0.8rem;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.25s ease;
}
.wizard-step-node.active .wizard-step-bubble {
  border-color: #8a2be2;
  background: #8a2be2;
  color: #fff;
  box-shadow: 0 0 16px rgba(138,43,226,0.65);
}
.wizard-step-node.completed .wizard-step-bubble {
  border-color: #b388ff;
  background: rgba(138,43,226,0.25);
  color: #b388ff;
}
.wizard-step-label {
  font-size: 0.7rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: color 0.25s ease;
}
.wizard-step-node.active .wizard-step-label {
  color: #fff;
}
.wizard-bar-track {
  width: 100%;
  height: 5px;
  background: rgba(255,255,255,0.08);
  border-radius: 999px;
  overflow: hidden;
  position: relative;
}
.wizard-bar-fill {
  height: 100%;
  width: 33.33%;
  background: linear-gradient(90deg, #8a2be2, #b388ff);
  box-shadow: 0 0 12px rgba(138,43,226,0.7);
  border-radius: 999px;
  transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ── Drum Wheel Date Picker (iOS 3D Style) ── */
.date-wheel-container {
  position: relative;
  height: 120px;
  background: rgba(9,6,17,0.85);
  border: 1px solid rgba(138,43,226,0.28);
  border-radius: 12px;
  display: grid;
  grid-template-columns: 1fr 1.15fr 1.25fr;
  overflow: hidden;
  box-shadow: inset 0 2px 10px rgba(0,0,0,0.7);
  mask-image: linear-gradient(to bottom, transparent 0%, black 28%, black 72%, transparent 100%);
  -webkit-mask-image: linear-gradient(to bottom, transparent 0%, black 28%, black 72%, transparent 100%);
  margin-top: 6px;
}
.date-wheel-highlight {
  position: absolute;
  top: 40px;
  left: 6px;
  right: 6px;
  height: 40px;
  border-top: 1px solid rgba(255, 255, 255, 0.12);
  border-bottom: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(255, 255, 255, 0.04);
  border-radius: 8px;
  pointer-events: none;
  z-index: 2;
  box-shadow: none;
}
.wheel-column {
  height: 120px;
  overflow-y: scroll;
  scroll-snap-type: y mandatory;
  scrollbar-width: none;
  -ms-overflow-style: none;
  padding: 40px 0;
  box-sizing: border-box;
  position: relative;
  z-index: 1;
}
.wheel-column::-webkit-scrollbar { display: none; }
.wheel-item {
  height: 40px;
  line-height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  scroll-snap-align: center;
  font-size: 0.92rem;
  font-weight: 500;
  color: #64748b;
  cursor: pointer;
  user-select: none;
  transition: color .15s ease, font-weight .15s ease, font-size .15s ease;
}
.wheel-item.active {
  color: #ffffff;
  font-weight: 600;
  font-size: 0.96rem;
  text-shadow: none;
}
.reg-date-top {
  display: flex !important;
  align-items: center !important;
  justify-content: space-between !important;
  margin-bottom: 8px !important;
  width: 100% !important;
}
.reg-date-top label {
  margin: 0 !important;
  display: inline-flex !important;
  align-items: center !important;
  gap: 6px !important;
}

/* ── Weight Card (Step 2) ── */
.weight-card-wrap {
  background: rgba(9,6,17,0.85);
  border: 1px solid rgba(138,43,226,0.28);
  border-radius: 14px;
  padding: 10px 14px;
  box-shadow: inset 0 2px 10px rgba(0,0,0,0.7);
  margin-bottom: 10px;
}
.weight-top-row {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  margin-bottom: 4px;
}
.weight-label {
  font-size: 0.77rem;
  font-weight: 600;
  color: #94a3b8;
  display: flex;
  align-items: center;
  gap: 5px;
}
.weight-display {
  display: flex;
  align-items: baseline;
  gap: 4px;
}
.weight-num {
  font-size: 1.55rem;
  font-weight: 900;
  color: #b388ff;
  line-height: 1;
  font-variant-numeric: tabular-nums;
  text-shadow: 0 0 14px rgba(179,136,255,0.45);
}
.weight-unit {
  font-size: 0.80rem;
  font-weight: 700;
  color: rgba(179,136,255,0.85);
  text-transform: uppercase;
}
.weight-ctrls-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 4px;
}
.weight-btn {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: rgba(138,43,226,0.15);
  border: 1px solid rgba(138,43,226,0.35);
  color: #b388ff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 0.95rem;
  flex-shrink: 0;
  transition: all .18s ease;
}
.weight-btn:hover {
  background: rgba(138,43,226,0.3);
  color: #fff;
  transform: scale(1.06);
}
.weight-btn:active {
  transform: scale(0.93);
}

/* ── Height Ruler Slider (Step 2) ── */
.ruler-card-wrap {
  background: rgba(9,6,17,0.85);
  border: 1px solid rgba(138,43,226,0.28);
  border-radius: 14px;
  padding: 10px 14px 8px;
  box-shadow: inset 0 2px 10px rgba(0,0,0,0.7);
  margin-bottom: 12px;
}
.ruler-top-row {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  margin-bottom: 4px;
}
.ruler-label {
  font-size: 0.77rem;
  font-weight: 600;
  color: #94a3b8;
  display: flex;
  align-items: center;
  gap: 5px;
  margin: 0;
}
.ruler-display {
  display: flex;
  align-items: baseline;
  gap: 3px;
}
.ruler-num {
  font-size: 1.55rem;
  font-weight: 900;
  color: #b388ff;
  line-height: 1;
  font-variant-numeric: tabular-nums;
  text-shadow: 0 0 14px rgba(179,136,255,0.45);
}
.ruler-unit {
  font-size: 0.80rem;
  font-weight: 700;
  color: rgba(179,136,255,0.85);
  text-transform: uppercase;
}
.ruler-controls-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 4px;
}
.ruler-step-btn {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: rgba(138,43,226,0.15);
  border: 1px solid rgba(138,43,226,0.35);
  color: #b388ff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 0.95rem;
  flex-shrink: 0;
  transition: all .18s ease;
}
.ruler-step-btn:hover {
  background: rgba(138,43,226,0.3);
  color: #fff;
  transform: scale(1.06);
}
.ruler-step-btn:active {
  transform: scale(0.93);
}
.ruler-track-box {
  flex: 1;
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: center;
}
.ruler-range {
  -webkit-appearance: none;
  appearance: none;
  width: 100%;
  height: 8px;
  border-radius: 4px;
  background: rgba(255,255,255,0.08);
  outline: none;
  cursor: ew-resize;
  position: relative;
  z-index: 2;
  margin: 10px 0 6px;
}
.ruler-range::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #b388ff;
  border: 2px solid #ffffff;
  box-shadow: 0 0 12px rgba(179,136,255,0.8);
  cursor: grab;
  transition: transform .15s ease;
}
.ruler-range::-webkit-slider-thumb:active {
  transform: scale(1.22);
  cursor: grabbing;
}
.ruler-range::-moz-range-thumb {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #b388ff;
  border: 2px solid #ffffff;
  box-shadow: 0 0 12px rgba(179,136,255,0.8);
  cursor: grab;
}
.ruler-scale {
  display: flex;
  justify-content: space-between;
  width: 100%;
  padding: 0 8px;
  box-sizing: border-box;
}
.ruler-tick {
  width: 1px;
  height: 6px;
  background: rgba(255,255,255,0.18);
}
.ruler-tick.mid {
  height: 10px;
  background: rgba(179,136,255,0.5);
}
.ruler-tick.major {
  height: 14px;
  background: #b388ff;
  width: 1.5px;
}
.ruler-legend {
  display: flex;
  justify-content: space-between;
  width: 100%;
  padding: 0 2px;
  margin-top: 4px;
  font-size: .66rem;
  color: #64748b;
  font-variant-numeric: tabular-nums;
}

/* ── Goals Grid (Step 3) ── */
.goals-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
  margin-bottom: 14px;
}
.goal-chip {
  background: rgba(15, 15, 20, 0.65);
  border: 1.5px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 9px 11px;
  cursor: pointer;
  position: relative;
  transition: all 0.22s ease;
  user-select: none;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  min-height: 62px;
  box-sizing: border-box;
}
.goal-chip:hover {
  border-color: rgba(138, 43, 226, 0.45);
  background: rgba(138, 43, 226, 0.08);
  transform: translateY(-2px);
}
.goal-chip.selected {
  border-color: #8a2be2;
  background: rgba(138, 43, 226, 0.16);
  box-shadow: 0 0 16px rgba(138, 43, 226, 0.35);
}
.goal-chip.goal-chip-otros.selected {
  grid-column: 1 / -1;
  background: rgba(236, 72, 153, 0.12);
  border-color: #ec4899;
  box-shadow: 0 0 18px rgba(236, 72, 153, 0.3);
}
.goal-chip-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
}
.goal-chip-icon {
  font-size: 1.15rem;
}
.goal-chip-check {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  border: 1.5px solid rgba(255,255,255,0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.65rem;
  color: transparent;
  transition: all 0.2s ease;
}
.goal-chip.selected .goal-chip-check {
  background: #8a2be2;
  border-color: #8a2be2;
  color: #fff;
  box-shadow: 0 0 8px rgba(138,43,226,0.6);
}
.goal-chip.goal-chip-otros.selected .goal-chip-check {
  background: #ec4899;
  border-color: #ec4899;
  box-shadow: 0 0 8px rgba(236, 72, 153, 0.6);
}
.goal-chip-title {
  font-size: 0.75rem;
  font-weight: 600;
  color: #fff;
  line-height: 1.2;
  margin-top: 5px;
}

/* ── Campo integrado para 'Otros' dentro de la tarjeta ── */
.goal-chip-custom-wrap {
  width: 100%;
  margin-top: 8px;
  animation: slideDownCustom 0.22s ease-out forwards;
}
@keyframes slideDownCustom {
  from { opacity: 0; transform: translateY(-4px); }
  to { opacity: 1; transform: translateY(0); }
}
.custom-goal-field {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(10, 10, 16, 0.85);
  border: 1px solid rgba(236, 72, 153, 0.45);
  border-radius: 10px;
  padding: 7px 11px;
  transition: all 0.2s ease;
  box-shadow: inset 0 2px 6px rgba(0,0,0,0.4);
}
.custom-goal-field:focus-within {
  border-color: #ec4899;
  background: rgba(16, 12, 22, 0.95);
  box-shadow: 0 0 0 2px rgba(236, 72, 153, 0.25), inset 0 2px 6px rgba(0,0,0,0.5);
}
.custom-goal-icon {
  color: #ec4899;
  font-size: 0.95rem;
  flex-shrink: 0;
}
.custom-goal-input {
  background: transparent !important;
  border: none !important;
  outline: none !important;
  box-shadow: none !important;
  color: #ffffff !important;
  font-size: 0.82rem !important;
  font-family: 'Inter', sans-serif !important;
  width: 100% !important;
  padding: 0 !important;
}
.custom-goal-input::placeholder {
  color: rgba(255, 255, 255, 0.35);
  font-size: 0.78rem;
}

/* ── Sub-nivel de Hipertrofia compacto Bento UI ── */
.subnivel-drilldown-box {
  margin-top: 10px;
  margin-bottom: 12px;
  padding: 10px;
  background: rgba(138, 43, 226, 0.06);
  border: 1px solid rgba(138, 43, 226, 0.22);
  border-radius: 12px;
}
.subnivel-header h4 {
  font-size: 0.82rem;
  font-weight: 700;
  color: #fff;
  margin: 0 0 3px;
  display: flex;
  align-items: center;
  gap: 5px;
}
.subnivel-header p {
  font-size: 0.70rem;
  color: #94a3b8;
  margin: 0 0 8px;
}
.subnivel-cards-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
}
.subnivel-card {
  background: rgba(15, 15, 20, 0.65);
  border: 1.5px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  padding: 9px 10px;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  box-sizing: border-box;
}
.subnivel-card:hover {
  border-color: rgba(138, 43, 226, 0.45);
  background: rgba(138, 43, 226, 0.1);
}
.subnivel-card.selected {
  border-color: #8a2be2;
  background: rgba(138, 43, 226, 0.18);
  box-shadow: 0 0 14px rgba(138, 43, 226, 0.35);
}
.subnivel-silhouette {
  font-size: 1.15rem;
  margin-bottom: 2px;
}
.subnivel-card-title {
  font-size: 0.74rem;
  font-weight: 700;
  color: #fff;
  margin-bottom: 2px;
}
.subnivel-card-desc {
  font-size: 0.65rem;
  color: #94a3b8;
  line-height: 1.2;
  margin-bottom: 5px;
}
.subnivel-card-pill {
  font-size: 0.62rem;
  padding: 2px 5px;
  border-radius: 5px;
  background: rgba(138, 43, 226, 0.2);
  color: #d8b4fe;
  align-self: flex-start;
  font-weight: 600;
}

/* ── Wizard Navigation Buttons ── */
.wizard-nav-row {
  display: flex;
  gap: 10px;
  align-items: stretch;
  margin-top: 18px;
  margin-bottom: 10px;
}
.btn-wizard-back {
  min-height: 48px;
  padding: 0 18px;
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.14);
  color: #e2e8f0;
  border-radius: 16px;
  font-weight: 600;
  font-size: 0.92rem;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  white-space: nowrap;
  flex-shrink: 0;
  font-family: 'Inter', sans-serif;
  transition: all 0.2s ease;
}
.btn-wizard-back:hover {
  background: rgba(255,255,255,0.11);
  border-color: rgba(255,255,255,0.24);
  color: #fff;
  transform: translateY(-1px);
}
.btn-wizard-next, .btn-wizard-finish {
  background: linear-gradient(135deg, #8a2be2 0%, #6d28d9 100%) !important;
  color: #fff !important;
  box-shadow: 0 8px 24px rgba(138,43,226,0.36) !important;
  border: 1px solid rgba(216,180,254,0.28) !important;
  border-radius: 16px !important;
  min-height: 48px !important;
  padding: 0 20px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  gap: 7px !important;
  white-space: nowrap !important;
  text-transform: none !important;
  letter-spacing: 0.2px !important;
  font-size: 0.95rem !important;
  font-weight: 700 !important;
}
.btn-wizard-finish:hover {
  filter: brightness(1.08);
  box-shadow: 0 10px 28px rgba(138,43,226,0.48) !important;
  transform: translateY(-1px);
}
.btn-skip-goals {
  width: 100%;
  min-height: 42px;
  padding: 10px 16px;
  background: rgba(255,255,255,0.035);
  border: 1px dashed rgba(179,136,255,0.30);
  color: #cbd5e1;
  border-radius: 14px;
  font-family: 'Inter', sans-serif;
  font-size: 0.86rem;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.2s ease;
}
.btn-skip-goals i {
  font-size: 0.95rem;
  color: #b388ff;
  transition: transform 0.2s ease;
}
.btn-skip-goals:hover {
  background: rgba(138,43,226,0.12);
  border-color: rgba(179,136,255,0.58);
  color: #fff;
}
.btn-skip-goals:hover i {
  transform: translateX(3px);
}
.btn-wizard-next:hover, .btn-wizard-finish:hover {
  opacity: 0.92;
  box-shadow: 0 8px 28px rgba(138,43,226,0.55) !important;
}

/* ── Switch "Soy entrenador" junto al campo Código de Entrenador ── */
.coach-code-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  flex-wrap: wrap;
  margin-bottom: 6px;
}
.coach-code-header > label {
  margin: 0 !important;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}
.trainer-switch-pill {
  display: inline-flex !important;
  align-items: center;
  gap: 7px;
  margin: 0 !important;
  padding: 4px 9px 4px 10px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #cbd5e1 !important;
  font-size: 0.72rem !important;
  font-weight: 700 !important;
  letter-spacing: 0.2px;
  text-transform: none !important;
  cursor: pointer;
  user-select: none;
  transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
}
.trainer-switch-pill:hover {
  border-color: rgba(179, 136, 255, 0.45);
  color: #fff !important;
}
.trainer-switch-pill.is-active {
  background: rgba(138, 43, 226, 0.22);
  border-color: rgba(179, 136, 255, 0.65);
  color: #e9d5ff !important;
  box-shadow: 0 0 14px rgba(138, 43, 226, 0.28);
}
.trainer-switch-pill input[type="checkbox"] {
  position: absolute;
  opacity: 0;
  width: 1px;
  height: 1px;
  pointer-events: none;
}
.trainer-switch-track {
  position: relative;
  width: 32px;
  height: 18px;
  border-radius: 999px;
  background: rgba(148, 163, 184, 0.32);
  border: 1px solid rgba(255, 255, 255, 0.14);
  flex-shrink: 0;
  transition: background 0.22s ease, border-color 0.22s ease;
}
.trainer-switch-thumb {
  position: absolute;
  top: 2px;
  left: 2px;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #e2e8f0;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.45);
  transition: transform 0.22s cubic-bezier(0.22, 1, 0.36, 1), background 0.22s ease;
}
.trainer-switch-pill.is-active .trainer-switch-track {
  background: linear-gradient(135deg, #8a2be2, #a855f7);
  border-color: rgba(216, 180, 254, 0.65);
}
.trainer-switch-pill.is-active .trainer-switch-thumb {
  transform: translateX(14px);
  background: #ffffff;
}
.trainer-switch-pill:focus-within {
  outline: 2px solid #b388ff;
  outline-offset: 2px;
}
#group_reg_codigo_entrenador.is-trainer-locked {
  border-color: rgba(138, 43, 226, 0.45) !important;
  background: rgba(138, 43, 226, 0.08) !important;
}
#group_reg_codigo_entrenador.is-trainer-locked input:disabled {
  opacity: 0.55;
  cursor: not-allowed;
  text-transform: none !important;
  letter-spacing: 0 !important;
  font-weight: 500 !important;
}
.trainer-mode-hint {
  display: none;
  align-items: center;
  gap: 6px;
  margin-top: 6px;
  font-size: 0.74rem;
  color: #d8b4fe;
  line-height: 1.35;
}
#group_reg_codigo_entrenador.is-trainer-locked .trainer-mode-hint {
  display: flex;
}

/* ── Standby View ── */
.standby-icon-wrap {
  width: 74px;
  height: 74px;
  margin: 0 auto 16px auto;
  border-radius: 50%;
  background: rgba(138, 43, 226, 0.15);
  border: 1.5px solid rgba(138, 43, 226, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 0 30px rgba(138, 43, 226, 0.35);
  animation: pulseStandby 2.6s ease-in-out infinite alternate;
}
@keyframes pulseStandby {
  0% { transform: scale(1); box-shadow: 0 0 20px rgba(138,43,226,0.3); }
  100% { transform: scale(1.05); box-shadow: 0 0 36px rgba(138,43,226,0.55); }
}
.standby-icon-wrap i {
  font-size: 2.3rem;
  color: #b388ff;
  filter: drop-shadow(0 0 12px rgba(179,136,255,0.6));
}
.standby-status-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 14px;
  border-radius: 999px;
  background: rgba(138, 43, 226, 0.18);
  border: 1px solid rgba(138, 43, 226, 0.38);
  color: #d8b4fe;
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 14px;
}
.standby-info-box {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 14px;
  padding: 12px 16px;
  font-size: 0.84rem;
  color: #cbd5e1;
  line-height: 1.45;
  margin: 18px 0;
  display: flex;
  align-items: center;
  gap: 10px;
  text-align: left;
}
.standby-info-box i {
  color: #fbbf24;
  font-size: 1.3rem;
  flex-shrink: 0;
}
</style>
</head>
<body>
<a href="#mainContent" class="skip-link">Saltar al contenido principal</a>

<!-- ══ MAHORAGA CANVAS ══ -->
<canvas id="mahoragaCanvas" aria-hidden="true"></canvas>
<div class="mh-vignette" aria-hidden="true"></div>

<div class="bg-orb orb1" aria-hidden="true"></div>
<div class="bg-orb orb2" aria-hidden="true"></div>

<script>
// ── MAHORAGA CANVAS GENERATOR (High-Definition Razor-Sharp Engine & Continuous 60fps) ──
(function() {
  const canvas = document.getElementById('mahoragaCanvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d', { alpha: false });
  const img = new Image();
  img.crossOrigin = 'anonymous';

  let wheelParticles = [];
  let headParticles  = [];
  let chestParticles = [];
  let lowerParticles = [];

  let imgW = 0, imgH = 0;
  let scale = 1, offsetX = 0, offsetY = 0;
  let dpr = 1;
  let w = 0, h = 0;
  let lastWidth = 0;
  let initialized = false;

  function initParticles() {
    if (initialized) return;
    initialized = true;
    imgW = img.naturalWidth || img.width || 266;
    imgH = img.naturalHeight || img.height || 472;

    try {
      const offCanvas = document.createElement('canvas');
      offCanvas.width = imgW;
      offCanvas.height = imgH;
      const offCtx = offCanvas.getContext('2d', { willReadFrequently: true });
      offCtx.drawImage(img, 0, 0);
      const imgData = offCtx.getImageData(0, 0, imgW, imgH).data;

      wheelParticles = [];
      headParticles  = [];
      chestParticles = [];
      lowerParticles = [];

      // Muestreo 1:1 de máxima resolución y nitidez absoluta (sin cuadrículas ni puntos separados)
      const step = Math.max(1, Math.floor(imgW / 900)); // = 1
      for (let y = 0; y < imgH; y += step) {
        const yp = y / imgH;
        for (let x = 0; x < imgW; x += step) {
          const i = (y * imgW + x) * 4;
          const r = imgData[i], g = imgData[i+1], b = imgData[i+2];
          const lum = 0.299 * r + 0.587 * g + 0.114 * b;

          if (lum > 18) {
            // Tamaño proporcional al brillo: contornos suaves, vectoriales y ultra nítidos
            const size = (lum / 255) * step * 1.12;
            const p = { x: x, y: y, size: size };

            if (yp < 0.22) {
              wheelParticles.push(p);
            } else if (yp < 0.38) {
              headParticles.push(p);
            } else if (yp < 0.65) {
              chestParticles.push(p);
            } else {
              lowerParticles.push(p);
            }
          }
        }
      }
    } catch (err) {
      console.warn('[Mahoraga] Failsafe: modo fallback directo activo:', err);
    }

    window.addEventListener('resize', () => {
      // Ignorar cambios de altura provocados por el teclado virtual móvil
      if (Math.abs(window.innerWidth - lastWidth) > 30) {
        resize();
      }
    }, { passive: true });

    if (window.visualViewport) {
      window.visualViewport.addEventListener('scroll', () => {
        window.scrollTo(0, 0);
      }, { passive: true });
    }

    resize();
    requestAnimationFrame(animate);
  }

  const basePath = <?= json_encode(rtrim(str_replace(' ', '%20', BASE_PATH), '/')) ?>;
  const appBase  = <?= json_encode(rtrim(str_replace(' ', '%20', APP_URL), '/')) ?>;
  const relSrc   = (basePath ? basePath : '') + '/assets/img/mahoraga1.jpg';

  img.onload = initParticles;
  img.onerror = () => {
    if (!img._retried) {
      img._retried = 1;
      img.src = appBase + '/assets/img/mahoraga1.jpg';
    } else if (img._retried === 1) {
      img._retried = 2;
      img.src = './assets/img/mahoraga1.jpg';
    }
  };
  img.src = relSrc;

  if (img.complete && img.naturalWidth > 0) {
    initParticles();
  }

  function resize() {
    const isMob = window.innerWidth < 768;
    dpr = isMob ? Math.min(window.devicePixelRatio || 1, 1.5) : Math.min(window.devicePixelRatio || 1, 2);
    w = window.innerWidth;

    // En móviles, fijar altura estable para que el teclado NUNCA cambie la escala de Mahoraga
    if (isMob) {
      if (!window._mhBaseH || Math.abs(window.innerWidth - lastWidth) > 30) {
        window._mhBaseH = Math.max(window.innerHeight, window.screen?.availHeight || 0);
      }
      h = window._mhBaseH;
    } else {
      h = window.innerHeight;
    }
    lastWidth = w;

    canvas.width = Math.round(w * dpr);
    canvas.height = Math.round(h * dpr);
    canvas.style.width = w + 'px';
    canvas.style.height = h + 'px';
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

    // Mahoraga nítido, imponente y posicionado con la rueda y cuernos flotando en la parte superior
    if (isMob) {
      scale = Math.max((w * 1.45) / imgW, (h * 1.20) / imgH);
      offsetX = (w - imgW * scale) / 2;
      offsetY = Math.max(-25, (h - imgH * scale) * 0.10);
    } else {
      scale = Math.max((w * 0.65) / imgW, (h * 1.22) / imgH);
      offsetX = (w - imgW * scale) / 2;
      offsetY = (h - imgH * scale) * 0.18;
    }
    drawFrame();
  }

  let time = 0;
  let lastFrameTs = 0;

  const ambientParticles = [];
  const ambientCount = window.innerWidth < 768 ? 16 : 28;
  for (let i = 0; i < ambientCount; i++) {
    ambientParticles.push({
      x: Math.random(), y: Math.random(),
      speed: 0.0002 + Math.random() * 0.0004,
      size: 1 + Math.random() * 2,
      alpha: 0.05 + Math.random() * 0.12,
      phase: Math.random() * Math.PI * 2
    });
  }

  const LOGIN_THEME_COLORS = {
    'classic':    { bg: '#02040a', glow: 'rgba(138, 43, 226, 0.12)', particle: '#ffffff', showMahoraga: true },
    'dark':       { bg: '#02040a', glow: 'rgba(138, 43, 226, 0.12)', particle: '#ffffff', showMahoraga: true },
    'obsidian':   { bg: '#120e1c', glow: 'rgba(148, 128, 185, 0.22)', particle: '#e2dde7', showMahoraga: false },
    'deep-ocean': { bg: '#051824', glow: 'rgba(56, 189, 248, 0.20)', particle: '#ccd0cf', showMahoraga: false }
  };

  function getActiveLoginThemeColors() {
    const t = document.documentElement.getAttribute('data-theme') || window.XP_LOGIN_THEME || 'classic';
    return LOGIN_THEME_COLORS[t] || LOGIN_THEME_COLORS['classic'];
  }

  function drawFrame() {
    const tc = getActiveLoginThemeColors();
    ctx.fillStyle = tc.bg;
    ctx.fillRect(0, 0, w, h);

    if (!tc.showMahoraga) {
      // Fondo oscuro con tonalidad atmosférica de la paleta seleccionada (sin Mahoraga)
      const grad = ctx.createRadialGradient(w * 0.5, h * 0.38, 20, w * 0.5, h * 0.45, Math.max(w, h) * 0.72);
      grad.addColorStop(0, tc.glow);
      grad.addColorStop(1, 'transparent');
      ctx.fillStyle = grad;
      ctx.fillRect(0, 0, w, h);
    } else if (wheelParticles.length > 0 || headParticles.length > 0 || chestParticles.length > 0 || lowerParticles.length > 0) {
      ctx.fillStyle = tc.particle;

      // 1. Rueda flotando (halo superior independiente)
      const wheelDy = Math.sin(time * 1.2) * 12 * scale;
      for (let i = 0, len = wheelParticles.length; i < len; i++) {
        const p = wheelParticles[i];
        ctx.fillRect(offsetX + p.x * scale, offsetY + p.y * scale + wheelDy, p.size * scale, p.size * scale);
      }

      // 2. Cabeza y cuernos
      const headDy = Math.sin(time * 0.8) * 3 * scale;
      for (let i = 0, len = headParticles.length; i < len; i++) {
        const p = headParticles[i];
        ctx.fillRect(offsetX + p.x * scale, offsetY + p.y * scale + headDy, p.size * scale, p.size * scale);
      }

      // 3. Pecho respirando (expande y sube suavemente)
      const chestDy = Math.sin(time * 0.8) * 4 * scale;
      const chestScaleX = Math.sin(time * 0.8) * 0.022;
      const imgCenter = imgW / 2;
      for (let i = 0, len = chestParticles.length; i < len; i++) {
        const p = chestParticles[i];
        const dx = (p.x - imgCenter) * chestScaleX * scale;
        ctx.fillRect(offsetX + p.x * scale + dx, offsetY + p.y * scale + chestDy, p.size * scale, p.size * scale);
      }

      // 4. Torso inferior y silueta completa
      const lowerDy = Math.sin(time * 0.8) * 1.2 * scale;
      for (let i = 0, len = lowerParticles.length; i < len; i++) {
        const p = lowerParticles[i];
        ctx.fillRect(offsetX + p.x * scale, offsetY + p.y * scale + lowerDy, p.size * scale, p.size * scale);
      }
    } else if (img.naturalWidth > 0) {
      // Fallback nítido si el navegador no permite lectura de canvas
      ctx.save();
      ctx.globalAlpha = 0.85;
      ctx.drawImage(img, offsetX, offsetY, imgW * scale, imgH * scale);
      ctx.restore();
    }

    // Partículas ambientales de fondo ultra-ligeras
    ctx.fillStyle = tc.particle;
    for (let i = 0, len = ambientParticles.length; i < len; i++) {
      const ap = ambientParticles[i];
      ap.y -= ap.speed;
      if (ap.y < -0.02) { ap.y = 1.02; ap.x = Math.random(); }
      const ax = ap.x * w + Math.sin(time + ap.phase) * 15;
      const ay = ap.y * h;
      ctx.globalAlpha = ap.alpha * (0.5 + 0.5 * Math.sin(time * 2 + ap.phase));
      ctx.fillRect(ax, ay, ap.size, ap.size);
    }
    ctx.globalAlpha = 1;
  }

  function animate(ts) {
    if (!document.hidden) {
      if (!ts || (ts - lastFrameTs) >= 16) {
        lastFrameTs = ts || 0;
        time += 0.035;
        drawFrame();
      }
    }
    requestAnimationFrame(animate);
  }
})();
</script>

<?php
$vistaInicial = $vistaInicial ?? 'login';
$isGoogleMode = (!empty($_SESSION['google_onboarding']) && !empty($_SESSION['google_onboarding']['email']));
$googleData   = $isGoogleMode ? ($_SESSION['google_onboarding'] ?? []) : [];

$refRawView = isset($_GET['ref']) && is_string($_GET['ref'])
    ? $_GET['ref']
    : (string)($_SESSION['ref_codigo'] ?? ($_COOKIE['xp_ref_code'] ?? ''));
$refEntrenadorInfo = ($refRawView !== '' && function_exists('resolverEntrenadorPorCodigo'))
    ? resolverEntrenadorPorCodigo($refRawView)
    : null;
$refCodigoActivo = $refEntrenadorInfo ? (string)$refEntrenadorInfo['codigo_referido'] : '';

if (isset($_GET['standby']) || (!empty($_SESSION['standby']))) {
    $vistaInicial = 'standby';
} elseif ($isGoogleMode || (!empty($_GET['ref']) && $refEntrenadorInfo)) {
    $vistaInicial = 'register';
}
$esLogin    = ($vistaInicial === 'login');
$esRegistro = ($vistaInicial === 'register');
$esRecovery = ($vistaInicial === 'recovery');
$esStandby  = ($vistaInicial === 'standby');
$esStandbyEntrenador = ($esStandby && (($_GET['rol'] ?? '') === 'entrenador'));
?>
<main id="mainContent">
<div class="auth-box" id="authBox" role="region" aria-label="Portal de autenticación">

  <!-- ══ VISTA LOGIN ══ -->
  <div id="view-login" class="auth-view"<?= $esLogin ? '' : ' style="display: none;" aria-hidden="true"' ?>>
  <!-- Imagotipo sin fondo: silueta blanca + nombre de la web -->
  <div class="login-brand-imagotipo" style="display:flex;align-items:center;justify-content:center;gap:12px;margin:0 auto 22px auto;padding:4px 0;background:transparent;user-select:none;">
    <img src="<?= APP_URL ?>/assets/img/favicon.svg?v=<?= e($vIconSvg) ?>"
         alt="Atlas Fit"
         style="width:46px;height:46px;object-fit:contain;display:block;background:transparent;filter:drop-shadow(0 0 12px rgba(255,255,255,0.22));"
         draggable="false">
    <span style="font-size:1.45rem;font-weight:900;letter-spacing:2.5px;color:#ffffff;text-transform:uppercase;line-height:1;text-shadow:0 2px 14px rgba(0,0,0,0.65);">ATLAS FIT</span>
  </div>
  <?php
    $flashMsg  = is_array($flash ?? null) ? trim((string)($flash['msg'] ?? $flash['error'] ?? '')) : '';
    $flashType = is_array($flash ?? null) ? (string)($flash['type'] ?? (!empty($flash['error']) ? 'error' : 'info')) : '';
  ?>
  <?php if ($flashMsg !== ''): ?>
  <div role="alert" style="margin-bottom:14px;padding:11px 14px;border-radius:14px;font-size:0.84rem;font-weight:600;line-height:1.4;display:flex;align-items:center;gap:9px;<?= $flashType === 'error' ? 'background:rgba(239,68,68,0.14);border:1px solid rgba(248,113,113,0.45);color:#fecaca;' : 'background:rgba(34,197,94,0.14);border:1px solid rgba(74,222,128,0.45);color:#bbf7d0;' ?>">
    <i class="ti <?= $flashType === 'error' ? 'ti-alert-circle' : 'ti-circle-check' ?>" style="font-size:1.1rem;flex-shrink:0;"></i>
    <span><?= e($flashMsg) ?></span>
  </div>
  <?php endif; ?>
  <form id="loginForm" method="POST" action="<?= APP_URL ?>/auth/do-login" novalidate aria-label="Formulario de inicio de sesión">
    <?= csrfField() ?>
    <div class="field" id="group_login_usuario">
      <label for="login_usuario">Usuario o Correo</label>
      <input type="text" id="login_usuario" name="usuario" placeholder="tu@correo.com" required autocomplete="username" aria-required="true" aria-describedby="err_login_usuario">
      <small class="ibg-error" id="err_login_usuario" role="alert"></small>
    </div>
    <div class="field" id="group_login_pass">
      <label for="login_pass">Contraseña</label>
      <div class="pass-row">
        <input type="password" id="login_pass" name="password" placeholder="••••••••" required autocomplete="current-password" aria-required="true" aria-describedby="err_login_pass">
        <button type="button" class="btn-eye" data-target="login_pass" onclick="togglePassVisibility(this, event)" onpointerdown="event.stopPropagation();" aria-label="Mostrar u ocultar contraseña"><i class="ti ti-eye" aria-hidden="true"></i></button>
      </div>
      <small class="ibg-error" id="err_login_pass" role="alert"></small>
    </div>
    <button type="submit" id="btnLogin" class="btn-submit">Iniciar Sesión</button>
  </form>

  <!-- ── Divisor y botón Google ── -->
  <div class="divider"><span>o continúa con</span></div>

  <a href="<?= APP_URL ?>/auth/google"
     id="btnGoogle"
     class="btn-google"
     role="button"
     aria-label="Iniciar sesión con Google">
    <!-- Google G logo SVG -->
    <svg viewBox="0 0 24 24" aria-hidden="true">
      <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
      <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
      <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
      <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
    </svg>
    Continuar con Google
  </a>

  <div class="link-bottom">
    ¿No tienes una cuenta? <a href="<?= APP_URL ?>/auth/registro" id="linkToRegister">Regístrate!</a>
  </div>
  <div class="link-center">
    <a href="<?= APP_URL ?>/auth/recuperar" id="linkToRecovery" aria-label="¿Olvidaste tu contraseña?">¿Olvidaste tu contraseña?</a>
  </div>

  <!-- Links legales -->
  <p style="text-align:center;font-size:.75rem;color:rgba(255,255,255,.3);margin-top:28px;line-height:1.5;">
    Al ingresar aceptas nuestros
    <a href="<?= APP_URL ?>/legal/terminos" style="color:rgba(255,255,255,.5);text-decoration:underline;">Términos</a>
    y
    <a href="<?= APP_URL ?>/legal/privacidad" style="color:rgba(255,255,255,.5);text-decoration:underline;">Política de Privacidad</a>.
  </p>
  </div><!-- /#view-login -->

  <!-- ══ VISTA REGISTRO (ONBOARDING WIZARD 3 PASOS) ══ -->
  <div id="view-register" class="auth-view" style="<?= $esRegistro ? 'display: block;' : 'display: none;' ?>"<?= $esRegistro ? '' : ' aria-hidden="true"' ?>>
    
    <!-- Indicador visual superior de progreso en color morado (#8a2be2) -->
    <div class="wizard-progress-wrap" aria-label="Progreso del Onboarding">
      <div class="wizard-steps-nav">
        <div class="wizard-step-node active" id="nodeStep1">
          <div class="wizard-step-bubble" id="bubbleStep1">1</div>
          <span class="wizard-step-label">Datos & Edad</span>
        </div>
        <div class="wizard-step-node" id="nodeStep2">
          <div class="wizard-step-bubble" id="bubbleStep2">2</div>
          <span class="wizard-step-label">Cuerpo</span>
        </div>
        <div class="wizard-step-node" id="nodeStep3">
          <div class="wizard-step-bubble" id="bubbleStep3">3</div>
          <span class="wizard-step-label">Metas</span>
        </div>
      </div>
      <div class="wizard-bar-track">
        <div class="wizard-bar-fill" id="wizardBarFill"></div>
      </div>
    </div>

    <form id="registerForm" method="POST" action="<?= APP_URL ?>/auth/do-registro" novalidate autocomplete="off" aria-label="Formulario de registro">
      <?= csrfField() ?>
      <input type="hidden" name="auth_provider" id="regAuthProvider" value="<?= $isGoogleMode ? 'google' : 'local' ?>">
      <input type="hidden" name="fecha_nacimiento" id="fecha_nacimiento" value="2005-06-15">
      <input type="hidden" id="regFechaNac" value="2005-06-15">
      <input type="hidden" name="peso_kg" id="regPesoInput" value="70.0">
      <input type="hidden" name="altura_cm" id="regAlturaInput" value="170">
      <input type="hidden" name="metas_json" id="regMetasInput" value="[]">

      <!-- ════ PASO 1 (Datos Personales y Edad) ════ -->
      <div class="wizard-step-pane wizard-step-1" id="wizardStep1">
        <h1 class="form-title" id="regStep1Title"><?= $isGoogleMode ? 'Completa tu Perfil' : 'Crear Cuenta' ?></h1>
        <p class="form-sub" id="regStep1Sub"><?= $isGoogleMode ? 'Paso 1: Completa tu cédula y fecha de nacimiento para continuar' : 'Paso 1: Datos de tu cuenta y fecha de nacimiento' ?></p>

        <?php if (!$isGoogleMode): ?>
        <!-- Botón Registro con Google (1-Click verificado) -->
        <div style="margin-bottom: 20px;">
          <a href="<?= APP_URL ?>/auth/google<?= $refCodigoActivo !== '' ? ('?ref=' . rawurlencode($refCodigoActivo)) : '' ?>" id="btnRegGoogleOAuth" class="btn-google" role="button" aria-label="Registrarse con Google">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
              <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
              <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
              <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Registrarse con Google
          </a>
          <div class="divider" style="margin: 18px 0 14px;"><span>o completa con tus datos</span></div>
        </div>
        <?php endif; ?>

        <!-- Fila doble con Nombre y Apellido -->
        <div class="reg-grid-2">
          <div class="input-box-group" id="group_reg_nombres">
            <label for="reg_nombres">Nombres</label>
            <input type="text" id="reg_nombres" name="nombres" value="<?= e($googleData['nombres'] ?? '') ?>" placeholder="Ej: Juan Carlos" maxlength="50" required autocomplete="given-name" aria-required="true" aria-describedby="err_nombres">
            <small class="ibg-error" id="err_nombres" role="alert"></small>
          </div>
          <div class="input-box-group" id="group_reg_apellidos">
            <label for="reg_apellidos">Apellidos</label>
            <input type="text" id="reg_apellidos" name="apellidos" value="<?= e($googleData['apellidos'] ?? '') ?>" placeholder="Ej: Pérez Gómez" maxlength="50" required autocomplete="family-name" aria-required="true" aria-describedby="err_apellidos">
            <small class="ibg-error" id="err_apellidos" role="alert"></small>
          </div>
        </div>

        <div class="input-box-group" id="group_reg_cedula">
          <label for="reg_cedula">Cédula / Documento (solo números)</label>
          <input type="text" inputmode="numeric" pattern="[0-9]*" id="reg_cedula" name="cedula" value="<?= e($googleData['cedula'] ?? '') ?>" placeholder="Ej: 28123456" maxlength="8" required autocomplete="off" spellcheck="false" aria-required="true" aria-describedby="err_cedula">
          <small class="ibg-error" id="err_cedula" role="alert"></small>
        </div>

        <div class="input-box-group" id="group_reg_codigo_entrenador" style="<?= $refEntrenadorInfo ? 'border-color:rgba(138,43,226,0.55);background:rgba(138,43,226,0.08);' : '' ?>">
          <div class="coach-code-header">
            <label for="reg_codigo_entrenador">
              <span id="lblCodigoEntrenadorTxt"><i class="ti ti-ticket" style="color:#b388ff;"></i> Código de Entrenador <span id="reqStarCodigoEnt" style="color:#ff4d6d;">*</span></span>
            </label>
            <label class="trainer-switch-pill" id="pillSoyEntrenador" for="reg_soy_entrenador" title="Activa esta opción si te estás registrando como entrenador">
              <span>Soy entrenador</span>
              <input type="checkbox" id="reg_soy_entrenador" name="soy_entrenador" value="1" role="switch" <?= !empty($googleData['es_entrenador']) ? 'checked aria-checked="true"' : 'aria-checked="false"' ?> aria-label="Soy entrenador, no necesito código de invitación">
              <span class="trainer-switch-track" aria-hidden="true"><span class="trainer-switch-thumb"></span></span>
            </label>
          </div>
          <div style="margin-bottom:4px;">
            <span id="badgeCoachRef" style="font-size:0.68rem;padding:2px 8px;border-radius:99px;background:rgba(34,197,94,0.18);color:#4ade80;border:1px solid rgba(34,197,94,0.35);font-weight:700;text-transform:none;<?= $refEntrenadorInfo ? 'display:inline-flex;align-items:center;gap:4px;' : 'display:none;' ?>">
              <i class="ti ti-circle-check"></i> <span id="badgeCoachRefNombre">Coach: <?= e($refEntrenadorInfo['entrenador_nombre'] ?? '') ?></span>
            </span>
          </div>
          <input type="text" id="reg_codigo_entrenador" name="codigo_entrenador" value="<?= e($refCodigoActivo) ?>" placeholder="Ej: ENTRENADOR-FIT" maxlength="40" required autocomplete="off" spellcheck="false" style="text-transform:uppercase;font-weight:700;letter-spacing:0.6px;" aria-required="true" aria-describedby="err_codigo_entrenador">
          <div class="trainer-mode-hint" id="trainerModeHint">
            <i class="ti ti-shield-check" style="font-size:0.95rem;flex-shrink:0;"></i>
            <span>Tu solicitud se enviará al administrador para aprobar tu cuenta de entrenador.</span>
          </div>
          <small class="ibg-error" id="err_codigo_entrenador" role="alert"></small>
        </div>

        <div class="input-box-group" id="group_reg_email">
          <label for="reg_email" style="display:flex;align-items:center;justify-content:space-between;gap:6px;">
            <span>Correo electrónico</span>
            <span id="badgeEmailVerified" class="badge-email-ok <?= $isGoogleMode ? 'is-visible' : '' ?>">
              <i class="ti ti-circle-check"></i> <?= $isGoogleMode ? 'Verificado con Google' : 'Correo verificado' ?>
            </span>
          </label>
          <div class="reg-email-row">
            <input type="email" id="reg_email" name="email" value="<?= e($googleData['email'] ?? '') ?>" <?= $isGoogleMode ? 'readonly style="opacity:0.85;cursor:not-allowed;"' : '' ?> placeholder="tu@correo.com" maxlength="150" required autocomplete="email" aria-required="true" aria-describedby="err_email">
            <?php if (!$isGoogleMode): ?>
            <button type="button" id="btnEnviarCodigoEmail" class="btn-verify-email" onclick="enviarCodigoVerificacionEmail()">
              <i class="ti ti-mail-forward"></i> <span>Enviar código</span>
            </button>
            <?php endif; ?>
          </div>
          <?php if (!$isGoogleMode): ?>
          <div id="boxEmailOtp" class="reg-otp-wrap" style="display:none;">
            <input type="text" id="reg_email_otp" class="reg-otp-input" inputmode="numeric" pattern="[0-9]*" maxlength="6" placeholder="Código 6 dígitos" autocomplete="one-time-code" aria-label="Código de verificación enviado a tu correo">
            <button type="button" id="btnVerificarCodigoEmail" class="btn-confirm-otp" onclick="verificarCodigoEmailBtn()">
              <i class="ti ti-check"></i> Confirmar
            </button>
          </div>
          <?php endif; ?>
          <small class="ibg-error" id="err_email" role="alert"></small>
        </div>

        <div class="input-box-group" id="group_reg_pass">
          <label for="reg_pass">Contraseña</label>
          <div class="pass-row">
            <input type="password" id="reg_pass" name="password" placeholder="Mínimo 8 caracteres" required autocomplete="new-password" aria-required="true" aria-describedby="err_password">
            <button type="button" class="btn-eye" data-target="reg_pass" onclick="togglePassVisibility(this, event)" onpointerdown="event.stopPropagation();" aria-label="Mostrar u ocultar contraseña"><i class="ti ti-eye" aria-hidden="true"></i></button>
          </div>
          <small class="ibg-error" id="err_password" role="alert"></small>
        </div>

        <div class="input-box-group" id="group_reg_pregunta">
          <label for="reg_pregunta">Pregunta de Seguridad</label>
          <select id="reg_pregunta" name="pregunta_seguridad" required autocomplete="off" aria-required="true" aria-describedby="err_pregunta_seguridad">
            <option value="<?= e(UsuarioModel::PREGUNTAS_SEGURIDAD[0]) ?>" selected><?= e(UsuarioModel::PREGUNTAS_SEGURIDAD[0]) ?></option>
            <?php for ($i = 1; $i < count(UsuarioModel::PREGUNTAS_SEGURIDAD); $i++): $q = UsuarioModel::PREGUNTAS_SEGURIDAD[$i]; ?>
            <option value="<?= e($q) ?>"><?= e($q) ?></option>
            <?php endfor; ?>
          </select>
          <small class="ibg-error" id="err_pregunta_seguridad" role="alert"></small>
        </div>

        <div class="input-box-group" id="group_reg_respuesta">
          <label for="reg_respuesta">Respuesta de Seguridad</label>
          <input type="text" id="reg_respuesta" name="respuesta_seguridad" placeholder="Tu respuesta secreta" maxlength="100" required autocomplete="off" aria-required="true" aria-describedby="err_respuesta_seguridad">
          <small class="ibg-error" id="err_respuesta_seguridad" role="alert"></small>
        </div>

        <!-- Selector de Edad / Fecha de Nacimiento (Drum Wheel Picker nativo del Perfil) -->
        <div class="input-box-group" style="padding-bottom:14px;" id="group_reg_fecha_nac">
          <div class="reg-date-top">
            <label style="margin:0;"><i class="ti ti-calendar" style="color:#b388ff"></i> Fecha de Nacimiento</label>
          </div>
          <div class="date-wheel-container" id="regDateWheelContainer">
            <div class="date-wheel-highlight" aria-hidden="true"></div>
            <!-- Columna Día: 01 a 31 -->
            <div class="wheel-column" id="regWheelDay" tabindex="0" role="listbox" aria-label="Día de nacimiento">
              <?php for ($d = 1; $d <= 31; $d++): $padD = sprintf('%02d', $d); ?>
                <div class="wheel-item" data-val="<?= $padD ?>" role="option"><?= $padD ?></div>
              <?php endfor; ?>
            </div>
            <!-- Columna Mes: Ene a Dic -->
            <div class="wheel-column" id="regWheelMonth" tabindex="0" role="listbox" aria-label="Mes de nacimiento">
              <?php
              $mesesLista = [
                '01' => 'Ene', '02' => 'Feb', '03' => 'Mar', '04' => 'Abr',
                '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago',
                '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dic'
              ];
              foreach ($mesesLista as $mNum => $mNom): ?>
                <div class="wheel-item" data-val="<?= $mNum ?>" role="option"><?= $mNom ?></div>
              <?php endforeach; ?>
            </div>
            <!-- Columna Año: 1950 al año actual -->
            <div class="wheel-column" id="regWheelYear" tabindex="0" role="listbox" aria-label="Año de nacimiento">
              <?php $anoMax = (int)date('Y'); for ($y = 1950; $y <= $anoMax; $y++): ?>
                <div class="wheel-item" data-val="<?= $y ?>" role="option"><?= $y ?></div>
              <?php endfor; ?>
            </div>
          </div>
          <script>
            (function() {
              try {
                var dCol = document.getElementById('regWheelDay');
                var mCol = document.getElementById('regWheelMonth');
                var yCol = document.getElementById('regWheelYear');
                if (dCol) dCol.scrollTop = (15 - 1) * 40;
                if (mCol) mCol.scrollTop = (6 - 1) * 40;
                if (yCol) yCol.scrollTop = (2005 - 1950) * 40;
              } catch(_) {}
            })();
          </script>
        </div>

        <button type="button" id="btnWizardStep1Next" class="btn-submit btn-wizard-next btn-wizard-next-step1">
          SIGUIENTE <i class="ti ti-arrow-right" style="vertical-align:middle;margin-left:4px;"></i>
        </button>

        <div class="link-bottom" style="margin-top:16px;">
          ¿Ya tienes cuenta? <a href="<?= APP_URL ?>/auth/login" class="link-reg-to-login">Inicia sesión</a>
        </div>
      </div><!-- /#wizardStep1 -->

      <!-- ════ PASO 2 (Composición Corporal) ════ -->
      <div class="wizard-step-pane wizard-step-2" id="wizardStep2" style="display:none;">
        <h1 class="form-title">Composición Corporal</h1>
        <p class="form-sub">Paso 2: Define tu peso actual y estatura</p>

        <!-- Arriba: Selector interactivo de Peso actual (kg) -->
        <div class="weight-card-wrap">
          <div class="weight-top-row">
            <span class="weight-label"><i class="ti ti-scale" style="color:#b388ff"></i> Peso actual</span>
            <div class="weight-display">
              <span class="weight-num" id="regPesoNum">70.0</span>
              <span class="weight-unit">kg</span>
            </div>
          </div>
          <div class="weight-ctrls-row">
            <button type="button" class="weight-btn" id="btnRegPesoMinus" title="Disminuir 0.5 kg" aria-label="Disminuir peso">
              <i class="ti ti-minus"></i>
            </button>
            <div class="ruler-track-box">
              <input type="range" id="regPesoRange" min="35" max="160" step="0.5" value="70.0" class="ruler-range" aria-label="Selector de peso corporal">
              <div class="ruler-legend" aria-hidden="true">
                <span>35 kg</span>
                <span>65 kg</span>
                <span>95 kg</span>
                <span>125 kg</span>
                <span>160 kg</span>
              </div>
            </div>
            <button type="button" class="weight-btn" id="btnRegPesoPlus" title="Aumentar 0.5 kg" aria-label="Aumentar peso">
              <i class="ti ti-plus"></i>
            </button>
          </div>
        </div>

        <!-- Abajo: Selector de Estatura (cm) usando Selector de Cadena / Regla Métrica Deslizable -->
        <div class="ruler-card-wrap">
          <div class="ruler-top-row">
            <span class="ruler-label"><i class="ti ti-ruler-measure" style="color:#b388ff"></i> Estatura</span>
            <div class="ruler-display">
              <span class="ruler-num" id="regHeightValueNumber">170</span>
              <span class="ruler-unit">cm</span>
            </div>
          </div>
          <div class="ruler-controls-row">
            <button type="button" class="ruler-step-btn" id="btnRegRulerMinus" title="Disminuir 1 cm" aria-label="Disminuir estatura">
              <i class="ti ti-minus"></i>
            </button>
            <div class="ruler-track-box">
              <input type="range" id="regAlturaRange" min="120" max="220" step="1" value="170" class="ruler-range" aria-label="Selector deslizable de estatura">
              <div class="ruler-scale" aria-hidden="true">
                <?php for ($t = 120; $t <= 220; $t += 5): ?>
                  <div class="ruler-tick <?= ($t % 10 === 0) ? 'major' : 'mid' ?>"></div>
                <?php endfor; ?>
              </div>
              <div class="ruler-legend" aria-hidden="true">
                <span>120</span>
                <span>145</span>
                <span>170</span>
                <span>195</span>
                <span>220</span>
              </div>
            </div>
            <button type="button" class="ruler-step-btn" id="btnRegRulerPlus" title="Aumentar 1 cm" aria-label="Aumentar estatura">
              <i class="ti ti-plus"></i>
            </button>
          </div>
        </div>

        <div class="wizard-nav-row">
          <button type="button" id="btnWizardStep2Back" class="btn-wizard-back" onclick="forceStepSwitch(2, 1)">
            <i class="ti ti-arrow-left"></i> Atrás
          </button>
          <button type="button" id="btnWizardStep2Next" class="btn-submit btn-wizard-next btn-wizard-next-step2" onclick="forceStepSwitch(2, 3)" style="margin:0;flex:2;">
            SIGUIENTE <i class="ti ti-arrow-right" style="vertical-align:middle;margin-left:4px;"></i>
          </button>
        </div>
      </div><!-- /#wizardStep2 -->

      <!-- ════ PASO 3 (Encuesta de Metas en el Gym) ════ -->
      <div class="wizard-step-pane wizard-step-3" id="wizardStep3" style="display:none;">
        <h1 class="form-title">¿Cuáles son tus metas?</h1>
        <p class="form-sub">Selecciona tu objetivo principal para personalizar tu experiencia</p>

        <!-- Cuadrícula de tarjetas/chips interactivos -->
        <small class="ibg-error" id="err_goals" role="alert" style="margin:0 0 10px 0;"></small>
        <div class="goals-grid" id="goalsGrid">
          <div class="goal-chip" data-goal="Perder grasa / Definición" data-id="perder_grasa" data-color="#f87171" data-icon="ti-flame" role="checkbox" aria-checked="false" tabindex="0">
            <div class="goal-chip-top">
              <span class="goal-chip-icon" style="color:#f87171;"><i class="ti ti-flame"></i></span>
              <span class="goal-chip-check"><i class="ti ti-check"></i></span>
            </div>
            <div class="goal-chip-title">Perder grasa / Definición</div>
          </div>

          <div class="goal-chip" data-goal="Ganar masa muscular (Hipertrofia)" data-id="ganar_masa" data-color="#818cf8" data-icon="ti-barbell" role="checkbox" aria-checked="false" tabindex="0">
            <div class="goal-chip-top">
              <span class="goal-chip-icon" style="color:#818cf8;"><i class="ti ti-barbell"></i></span>
              <span class="goal-chip-check"><i class="ti ti-check"></i></span>
            </div>
            <div class="goal-chip-title">Ganar masa muscular (Hipertrofia)</div>
          </div>

          <div class="goal-chip" data-goal="Aumentar fuerza máxima" data-id="fuerza_maxima" data-color="#fbbf24" data-icon="ti-bolt" role="checkbox" aria-checked="false" tabindex="0">
            <div class="goal-chip-top">
              <span class="goal-chip-icon" style="color:#fbbf24;"><i class="ti ti-bolt"></i></span>
              <span class="goal-chip-check"><i class="ti ti-check"></i></span>
            </div>
            <div class="goal-chip-title">Aumentar fuerza máxima</div>
          </div>

          <div class="goal-chip" data-goal="Recomposición corporal" data-id="recomposicion" data-color="#a855f7" data-icon="ti-refresh" role="checkbox" aria-checked="false" tabindex="0">
            <div class="goal-chip-top">
              <span class="goal-chip-icon" style="color:#a855f7;"><i class="ti ti-refresh"></i></span>
              <span class="goal-chip-check"><i class="ti ti-check"></i></span>
            </div>
            <div class="goal-chip-title">Recomposición corporal</div>
          </div>

          <div class="goal-chip" data-goal="Mejorar resistencia y condición" data-id="resistencia" data-color="#22c55e" data-icon="ti-run" role="checkbox" aria-checked="false" tabindex="0">
            <div class="goal-chip-top">
              <span class="goal-chip-icon" style="color:#22c55e;"><i class="ti ti-run"></i></span>
              <span class="goal-chip-check"><i class="ti ti-check"></i></span>
            </div>
            <div class="goal-chip-title">Mejorar resistencia y condición</div>
          </div>

          <div class="goal-chip" data-goal="Salud y bienestar general" data-id="salud_bienestar" data-color="#38bdf8" data-icon="ti-heart" role="checkbox" aria-checked="false" tabindex="0">
            <div class="goal-chip-top">
              <span class="goal-chip-icon" style="color:#38bdf8;"><i class="ti ti-heart"></i></span>
              <span class="goal-chip-check"><i class="ti ti-check"></i></span>
            </div>
            <div class="goal-chip-title">Salud y bienestar general</div>
          </div>

          <!-- Opción 'Otros' con campo integrado Bento UI -->
          <div class="goal-chip goal-chip-otros" data-goal="otros" data-id="otros" data-color="#ec4899" data-icon="ti-edit" role="checkbox" aria-checked="false" tabindex="0">
            <div class="goal-chip-top">
              <span class="goal-chip-icon" style="color:#ec4899;"><i class="ti ti-edit"></i></span>
              <span class="goal-chip-check"><i class="ti ti-check"></i></span>
            </div>
            <div class="goal-chip-title">Otros (Personalizado)</div>
            <div class="goal-chip-custom-wrap" id="boxMetaOtros" style="display:none;" onclick="event.stopPropagation()" onpointerdown="event.stopPropagation()">
              <small class="ibg-error" id="err_meta_otros" role="alert" style="margin:0 0 6px 0;"></small>
              <div class="custom-goal-field">
                <i class="ti ti-sparkles custom-goal-icon" aria-hidden="true"></i>
                <input type="text" id="inputMetaOtros" maxlength="150" placeholder="Ej: Preparación para triatlón..." class="custom-goal-input" autocomplete="off" onclick="event.stopPropagation()" onpointerdown="event.stopPropagation()">
              </div>
            </div>
          </div>
        </div>

        <!-- Sub-Nivel de Hipertrofia (Drill-Down Visual) -->
        <div id="boxSubnivelHipertrofia" class="subnivel-drilldown-box" style="display:none;">
          <div class="subnivel-header">
            <h4><i class="ti ti-stairs" style="color:#818cf8;"></i> ¿Hasta dónde quieres llegar?</h4>
            <p>Selecciona el enfoque corporal que define tu objetivo muscular</p>
          </div>
          <div class="subnivel-cards-grid">
            <!-- Opción A: Físico Estético -->
            <div class="subnivel-card selected" id="cardSubEstetico" data-sub="estetico" role="button" tabindex="0">
              <div class="subnivel-silhouette">
                <i class="ti ti-sparkles" style="color:#38bdf8;"></i>
              </div>
              <div class="subnivel-card-title">Físico Estético</div>
              <div class="subnivel-card-desc">Definición atlética, cintura estrecha y proporciones simétricas clásicas.</div>
              <span class="subnivel-card-pill">Simetría & V-Taper</span>
            </div>
            <!-- Opción B: Fisicoculturista -->
            <div class="subnivel-card" id="cardSubCulturista" data-sub="culturista" role="button" tabindex="0">
              <div class="subnivel-silhouette">
                <i class="ti ti-barbell" style="color:#818cf8;"></i>
              </div>
              <div class="subnivel-card-title">Fisicoculturista</div>
              <div class="subnivel-card-desc">Máxima hipertrofia muscular, densidad, volumen y fuerza extrema.</div>
              <span class="subnivel-card-pill">Volumen & Densidad</span>
            </div>
          </div>
        </div>

        <div class="wizard-nav-row" style="margin-top:20px;">
          <button type="button" id="btnWizardStep3Back" class="btn-wizard-back" onclick="forceStepSwitch(3, 2)">
            <i class="ti ti-arrow-left"></i> <span>Atrás</span>
          </button>
          <button type="submit" id="btnRegister" class="btn-submit btn-wizard-finish" style="margin:0;flex:1;">
            <i class="ti ti-check"></i> <span>Solicitar acceso</span>
          </button>
        </div>

        <!-- Botón 'Omitir por ahora' -->
        <div style="text-align:center; margin-top:4px;">
          <button type="button" id="btnSkipGoals" class="btn-skip-goals" aria-label="Omitir elección de metas y finalizar">
            <span>Omitir por ahora</span>
            <i class="ti ti-arrow-right"></i>
          </button>
        </div>
      </div><!-- /#wizardStep3 -->

    </form>
  </div><!-- /#view-register -->

  <!-- ══ VISTA CONFIRMACIÓN STAND-BY ══ -->
  <div id="view-standby" class="auth-view" style="<?= $esStandby ? 'display: block;' : 'display: none;' ?>"<?= $esStandby ? '' : ' aria-hidden="true"' ?>>
    <div class="standby-icon-wrap" aria-hidden="true">
      <i class="ti ti-clock"></i>
    </div>
    <div style="text-align:center;">
      <span class="standby-status-pill"><i class="ti ti-clock"></i> Pendiente de Aprobación</span>
    </div>
    <h1 class="form-title" id="standbyTitle" style="font-size:1.45rem;margin-bottom:8px;line-height:1.35;"><?= $esStandbyEntrenador ? 'En espera de que el administrador active tu cuenta de entrenador' : 'En espera de que tu entrenador active tu cuenta' ?></h1>
    <p class="form-sub" id="standbySub" style="margin-bottom:20px;font-size:0.92rem;line-height:1.5;"><?= $esStandbyEntrenador ? 'Tu solicitud de entrenador ha sido registrada correctamente. Te avisaremos en cuanto tu acceso sea habilitado.' : 'Tus datos y metas han sido guardados correctamente. Te avisaremos en cuanto tu acceso sea habilitado.' ?></p>

    <div class="standby-info-box">
      <i class="ti ti-bell-ringing"></i>
      <div id="standbyInfoText">
        <?php if ($esStandbyEntrenador): ?>
          <strong>Aviso al administrador enviado:</strong> El administrador revisará y habilitará tu cuenta de entrenador en breve para que puedas ingresar y gestionar a tus atletas.
        <?php else: ?>
          <strong>Aviso al entrenador enviado:</strong> Tu entrenador revisará y habilitará tu acceso en breve para que puedas ingresar y comenzar tus entrenamientos.
        <?php endif; ?>
      </div>
    </div>

    <button type="button" id="btnStandbyToLogin" class="btn-submit">
      <i class="ti ti-arrow-left" style="margin-right:6px;"></i> Volver al Inicio
    </button>
  </div><!-- /#view-standby -->

  <!-- ══ VISTA RECUPERACIÓN (mismo contenedor y mismo fondo) ══ -->
  <div id="view-recovery" class="auth-view" style="<?= $esRecovery ? 'display: block;' : 'display: none;' ?>"<?= $esRecovery ? '' : ' aria-hidden="true"' ?>>
    <div class="recovery-icon-wrap" aria-hidden="true">
      <i class="ti ti-shield-lock"></i>
    </div>
    <h1 class="form-title">Recuperación</h1>
    <p class="form-sub" id="recSubtitulo">Ingresa tu correo electrónico o cédula.</p>

    <form id="recoveryForm" novalidate autocomplete="off" aria-label="Formulario de recuperación de contraseña">
      <?= csrfField() ?>

      <!-- PASO 1: Identificación (Correo o Cédula) -->
      <div id="recPaso1">
        <div class="input-box-group" id="group_rec_identificador">
          <label for="rec_identificador">CORREO ELECTRÓNICO O CÉDULA</label>
          <input type="text" id="rec_identificador" name="identificador" placeholder="tu@correo.com o V-12345678" required autocomplete="username" aria-required="true" aria-describedby="err_rec_identificador">
          <small class="ibg-error" id="err_rec_identificador" role="alert"></small>
        </div>
      </div>

      <!-- PASO 2: Responder la Pregunta de Seguridad (Oculto hasta validar Paso 1) -->
      <div id="recPaso2" style="display: none;">
        <div class="input-box-group" id="group_rec_respuesta">
          <label for="rec_respuesta" id="lblPreguntaSeguridad">PREGUNTA DE SEGURIDAD</label>
          <input type="text" id="rec_respuesta" name="respuesta" placeholder="Tu respuesta secreta" maxlength="100" autocomplete="off" aria-describedby="err_rec_respuesta">
          <small class="ibg-error" id="err_rec_respuesta" role="alert"></small>
        </div>
      </div>

      <!-- PASO 3: Doble campo de Contraseña (Oculto hasta validar la Respuesta Secreta) -->
      <div id="recPaso3" style="display: none;">
        <div class="input-box-group" id="group_rec_nueva_password">
          <label for="rec_nueva_password">NUEVA CONTRASEÑA</label>
          <div class="pass-row">
            <input type="password" id="rec_nueva_password" name="nueva_password" placeholder="Mínimo 8 caracteres" autocomplete="new-password" aria-describedby="err_rec_nueva_password">
            <button type="button" class="btn-eye" data-target="rec_nueva_password" onclick="togglePassVisibility(this, event)" onpointerdown="event.stopPropagation();" aria-label="Mostrar u ocultar contraseña">
              <i class="ti ti-eye" aria-hidden="true"></i>
            </button>
          </div>
          <small class="ibg-error" id="err_rec_nueva_password" role="alert"></small>
        </div>

        <div class="input-box-group" id="group_rec_confirmar_password">
          <label for="rec_confirmar_password">CONFIRMAR CONTRASEÑA</label>
          <div class="pass-row">
            <input type="password" id="rec_confirmar_password" name="confirmar_password" placeholder="Repite tu nueva contraseña" autocomplete="new-password" aria-describedby="err_rec_confirmar_password">
            <button type="button" class="btn-eye" data-target="rec_confirmar_password" onclick="togglePassVisibility(this, event)" onpointerdown="event.stopPropagation();" aria-label="Mostrar u ocultar contraseña">
              <i class="ti ti-eye" aria-hidden="true"></i>
            </button>
          </div>
          <small class="ibg-error" id="err_rec_confirmar_password" role="alert"></small>
        </div>
      </div>

      <button type="submit" id="btnRecovery" class="btn-submit">CONTINUAR</button>
    </form>

    <div class="link-bottom" style="margin-top:18px;">
      <a href="<?= APP_URL ?>/auth/login" id="linkRecoveryToLogin">← Volver al Login</a>
    </div>
  </div><!-- /#view-recovery -->
</div>
</main>

<?php if (!empty($flashMsg)): ?>
<script>
window.addEventListener('load', () => {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: <?= json_encode($flashType === 'error' ? 'error' : 'info') ?>,
            title: <?= json_encode($flashType === 'error' ? 'Atención' : 'Información') ?>,
            text: <?= json_encode($flashMsg, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            background: '#111827',
            color: '#fff',
            confirmButtonColor: '#8a2be2'
        });
    }
});
</script>
<?php endif; ?>

<script src="<?= APP_URL ?>/assets/vendor/sweetalert2/sweetalert2.all.min.js?v=<?= e($vSwal) ?>" defer></script>
<script src="<?= APP_URL ?>/assets/vendor/animejs/anime.min.js?v=<?= e($vAnime) ?>" defer></script>
<script>
window.APP       = '<?= APP_URL ?>';
window.BASE_PATH = '<?= BASE_PATH ?>';
window.CSRF      = '<?= csrfToken() ?>';
const APP        = window.APP;
const CSRF       = window.CSRF;
const API        = APP + '/api';

// ── Fallback Defensivo para Anime.js (asegura que ninguna llamada lance error) ──
const safeAnime = (opts) => (typeof window.anime === 'function' ? window.anime(opts) : { finished: Promise.resolve() });
window.safeAnime = safeAnime;

// Entrance sutil sin ocultar #authBox al arranque (preserva LCP inmediato)
document.addEventListener('DOMContentLoaded', () => {
  safeAnime({ targets:'#authBox', translateY:[8,0], duration:260, easing:'easeOutExpo' });
});

// ── Toast Defensivo ──
const Toast = {
  fire(opts) {
    if (typeof Swal !== 'undefined') {
      return Swal.mixin({
        toast: true, position: 'top-end', showConfirmButton: false, timer: 3200,
        background: '#111827', color: '#fff', timerProgressBar: true,
      }).fire(opts);
    } else {
      console.log('[Toast]', opts.title || opts.text || '');
    }
  }
};

function setBtn(btn, loading, orig) {
  if (!btn) return;
  btn.disabled = loading;
  btn.innerHTML = loading ? '<span class="spin"></span> Procesando...' : orig;
}
async function postApi(endpoint, body) {
  const fd = new FormData();
  Object.entries(body).forEach(([k,v]) => fd.append(k,v));
  const cleanBase = (typeof APP !== 'undefined' && APP ? APP : window.location.origin + window.location.pathname.replace(/\/views\/.*$/i, '').replace(/\/auth\/.*$/i, '')).replace(/\/+$/, '');
  const rawUrl = decodeURI(cleanBase) + '/' + endpoint.replace(/^\/+/, '');
  const url = encodeURI(rawUrl);
  const r = await fetch(url, { 
    method: 'POST', 
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: fd 
  });
  const txt = await r.text();
  try {
    const json = JSON.parse(txt);
    // Si el servidor detectó desincronización de token CSRF tras logout previo, auto-refrescar
    if (json && !json.ok && json.msg && json.msg.toLowerCase().includes('csrf')) {
      Toast.fire({ icon: 'warning', title: 'Renovando sesión segura...' });
      setTimeout(() => window.location.reload(), 800);
    }
    return json;
  } catch(e) {
    throw new Error('Respuesta inválida del servidor (' + r.status + ')');
  }
}

// ── LOGIN ──
const origLogin = 'Iniciar Sesión';
function clearLoginFieldError(fieldEl) {
  if (!fieldEl) return;
  fieldEl.classList.remove('has-error');
  const inp = fieldEl.querySelector('input');
  if (inp) inp.removeAttribute('aria-invalid');
  const errEl = fieldEl.querySelector('.ibg-error');
  if (errEl) { errEl.textContent = ''; errEl.style.display = ''; }
}
function setLoginFieldError(inputId, msg) {
  const inp = document.getElementById(inputId);
  const box = inp?.closest('.field');
  if (box) {
    box.classList.add('has-error');
    const errEl = box.querySelector('.ibg-error');
    if (errEl) { errEl.textContent = msg; errEl.style.display = 'inline-flex'; }
  }
  if (inp) {
    inp.setAttribute('aria-invalid', 'true');
    inp.focus({ preventScroll: true });
  }
}
document.getElementById('loginForm')?.addEventListener('input', e => {
  clearLoginFieldError(e.target?.closest?.('.field'));
});
document.getElementById('loginForm')?.addEventListener('submit', async e => {
  e.preventDefault();
  clearLoginFieldError(document.getElementById('group_login_usuario'));
  clearLoginFieldError(document.getElementById('group_login_pass'));

  const userInp = document.getElementById('login_usuario').value.trim();
  const passInp = document.getElementById('login_pass').value.trim();

  if (!userInp) {
    setLoginFieldError('login_usuario', 'Ingresa tu usuario o correo.');
    return;
  }
  if (!passInp) {
    setLoginFieldError('login_pass', 'Ingresa tu contraseña.');
    return;
  }

  const btn = document.getElementById('btnLogin');
  setBtn(btn, true, origLogin);
  try {
    const data = await postApi('auth/do-login', {
      csrf_token: typeof CSRF !== 'undefined' ? CSRF : '',
      usuario: userInp,
      password: passInp,
    });
    if (data.ok || data.success) {
      Toast.fire({icon:'success', title:'¡Bienvenido!'});
      const target = data.redirect || (APP + '/cliente/dashboard');
      setTimeout(() => window.location.href = target, 800);
    } else if (data.estado_cuenta === 'pendiente' || data.estado_cuenta === 'rechazado' || data.status === 'standby') {
      const esPendiente = (data.estado_cuenta === 'pendiente' || data.status === 'standby');
      if (esPendiente) {
        if (typeof updateStandbyCopy === 'function') {
          updateStandbyCopy(data.rol_solicitado === 'entrenador');
        }
        switchView('standby');
      } else {
        Swal.fire({
          icon: 'warning',
          title: 'Acceso no aprobado',
          text: data.mensaje || data.msg,
          background: '#111827', color: '#fff',
          confirmButtonText: 'Entendido', confirmButtonColor: '#8a2be2',
        });
      }
      setBtn(btn, false, origLogin);
    } else {
      Toast.fire({icon:'error', title: data.mensaje || data.msg || 'Credenciales incorrectas.'});
      setBtn(btn, false, origLogin);
      safeAnime({ targets:'#authBox', translateX:[0,-8,8,-5,5,0], duration:500, easing:'linear' });
    }
  } catch(err) {
    console.error('Error de login:', err);
    Toast.fire({icon:'error', title: err.message || 'Error de conexión'});
    setBtn(btn, false, origLogin);
  }
});

// ── TOGGLE SPA: LOGIN ⇄ REGISTRO ⇄ RECUPERACIÓN (mismo contenedor, mismo fondo, sin recargar) ──
const viewLogin    = document.getElementById('view-login');
const viewRegister = document.getElementById('view-register');
const viewRecovery = document.getElementById('view-recovery');
const viewStandby  = document.getElementById('view-standby');

const TITULOS = {
  login:    'Acceso – Atlas Fit',
  register: 'Registro – Atlas Fit',
  recovery: 'Recuperación – Atlas Fit',
  standby:  'Cuenta en Revisión – Atlas Fit',
};

const RUTAS = {
  login:    '/auth/login',
  register: '/auth/registro',
  recovery: '/auth/recuperar',
  standby:  '/auth/standby',
};

const VISTAS = {
  login:    viewLogin,
  register: viewRegister,
  recovery: viewRecovery,
  standby:  viewStandby,
};

function switchView(name, { focus = true } = {}) {
  const targetName = VISTAS[name] ? name : 'login';

  Object.entries(VISTAS).forEach(([k, el]) => {
    if (!el) return;
    if (k === targetName) {
      el.style.display = 'block';
      el.removeAttribute('aria-hidden');
      el.classList.remove('fade-in');
      void el.offsetWidth;
      el.classList.add('fade-in');
    } else {
      el.style.display = 'none';
      el.setAttribute('aria-hidden', 'true');
    }
  });

  document.title = TITULOS[targetName] || TITULOS.login;
  const main = document.getElementById('mainContent');
  if (main) main.scrollTop = 0;

  try {
    const currentSearch = window.location.search || '';
    history.replaceState(null, '', APP + (RUTAS[targetName] || '/auth/login') + currentSearch);
  } catch (_) {}

  if (focus && VISTAS[targetName]) {
    const first = VISTAS[targetName].querySelector('input:not([type=hidden]):not([disabled]):not([readonly]), select');
    if (first) setTimeout(() => first.focus({ preventScroll: true }), 60);
  }
}

function updateStandbyCopy(esEntrenador) {
  const titleEl = document.getElementById('standbyTitle');
  const subEl   = document.getElementById('standbySub');
  const infoEl  = document.getElementById('standbyInfoText');
  if (esEntrenador) {
    if (titleEl) titleEl.textContent = 'En espera de que el administrador active tu cuenta de entrenador';
    if (subEl)   subEl.textContent   = 'Tu solicitud de entrenador ha sido registrada correctamente. Te avisaremos en cuanto tu acceso sea habilitado.';
    if (infoEl)  infoEl.innerHTML    = '<strong>Aviso al administrador enviado:</strong> El administrador revisará y habilitará tu cuenta de entrenador en breve para que puedas ingresar y gestionar a tus atletas.';
  } else {
    if (titleEl) titleEl.textContent = 'En espera de que tu entrenador active tu cuenta';
    if (subEl)   subEl.textContent   = 'Tus datos y metas han sido guardados correctamente. Te avisaremos en cuanto tu acceso sea habilitado.';
    if (infoEl)  infoEl.innerHTML    = '<strong>Aviso al entrenador enviado:</strong> Tu entrenador revisará y habilitará tu acceso en breve para que puedas ingresar y comenzar tus entrenamientos.';
  }
}

// Validación en vivo del Código de Entrenador, Switch "Soy entrenador" y sincronización con Google OAuth
(function() {
  const inpRef      = document.getElementById('reg_codigo_entrenador');
  const grpRef      = document.getElementById('group_reg_codigo_entrenador');
  const bdgRef      = document.getElementById('badgeCoachRef');
  const bdgNom      = document.getElementById('badgeCoachRefNombre');
  const btnGoo      = document.getElementById('btnRegGoogleOAuth');
  const chkTrainer  = document.getElementById('reg_soy_entrenador');
  const pillTrainer = document.getElementById('pillSoyEntrenador');
  const reqStar     = document.getElementById('reqStarCodigoEnt');
  const wizProgress = document.querySelector('#view-register .wizard-progress-wrap');
  const step1Sub    = document.getElementById('regStep1Sub');
  const btnStep1    = document.getElementById('btnWizardStep1Next');
  if (!inpRef) return;

  const origStep1SubText = step1Sub ? step1Sub.textContent : '';
  const origStep1BtnHtml = btnStep1 ? btnStep1.innerHTML : 'SIGUIENTE <i class="ti ti-arrow-right" style="vertical-align:middle;margin-left:4px;"></i>';
  const origPlaceholder  = inpRef.getAttribute('placeholder') || 'Ej: ENTRENADOR-FIT';
  let savedRefCode       = inpRef.value || '';

  let timerRef = null;
  const checkCodeLive = (rawVal) => {
    if (chkTrainer && chkTrainer.checked) return;
    const code = String(rawVal || '').trim().toUpperCase();
    if (btnGoo) {
      btnGoo.href = APP + '/auth/google' + (code ? ('?ref=' + encodeURIComponent(code)) : '');
    }
    if (code.length < 3) {
      if (bdgRef) bdgRef.style.display = 'none';
      if (grpRef) { grpRef.style.borderColor = ''; grpRef.style.background = ''; }
      return;
    }
    fetch(APP + '/auth/api-validar-codigo?codigo=' + encodeURIComponent(code))
      .then(r => r.json())
      .then(d => {
        if (chkTrainer && chkTrainer.checked) return;
        if (d && d.ok && d.entrenador_nombre) {
          if (bdgNom) bdgNom.textContent = 'Coach: ' + d.entrenador_nombre;
          if (bdgRef) bdgRef.style.display = 'inline-flex';
          if (grpRef) {
            grpRef.style.borderColor = 'rgba(138,43,226,0.55)';
            grpRef.style.background = 'rgba(138,43,226,0.08)';
          }
        } else {
          if (bdgRef) bdgRef.style.display = 'none';
          if (grpRef) { grpRef.style.borderColor = ''; grpRef.style.background = ''; }
        }
      })
      .catch(() => {});
  };

  window.syncTrainerSwitchUI = function() {
    if (!chkTrainer) return;
    const isTrainer = !!chkTrainer.checked;
    chkTrainer.setAttribute('aria-checked', isTrainer ? 'true' : 'false');
    if (pillTrainer) pillTrainer.classList.toggle('is-active', isTrainer);

    if (isTrainer) {
      if (inpRef.value.trim() !== '') savedRefCode = inpRef.value;
      inpRef.value = '';
      inpRef.disabled = true;
      inpRef.required = false;
      inpRef.setAttribute('aria-required', 'false');
      inpRef.setAttribute('placeholder', 'No requerido');
      if (reqStar) reqStar.style.display = 'none';
      if (bdgRef) bdgRef.style.display = 'none';
      if (grpRef) {
        grpRef.classList.remove('has-error', 'ibg-shake');
        grpRef.classList.add('is-trainer-locked');
        const errEl = document.getElementById('err_codigo_entrenador');
        if (errEl) { errEl.textContent = ''; errEl.style.display = 'none'; }
      }
      if (wizProgress) wizProgress.style.display = 'none';
      if (step1Sub) step1Sub.textContent = 'Completa tus datos para solicitar tu cuenta de entrenador';
      if (btnStep1) btnStep1.innerHTML = '<i class="ti ti-check"></i> <span>Solicitar acceso</span>';
      if (btnGoo) btnGoo.href = APP + '/auth/google?role=entrenador';
    } else {
      inpRef.disabled = false;
      inpRef.required = true;
      inpRef.setAttribute('aria-required', 'true');
      inpRef.setAttribute('placeholder', origPlaceholder);
      if (savedRefCode) {
        inpRef.value = savedRefCode;
        checkCodeLive(savedRefCode);
      }
      if (reqStar) reqStar.style.display = '';
      if (grpRef) grpRef.classList.remove('is-trainer-locked');
      if (wizProgress) wizProgress.style.display = '';
      if (step1Sub) step1Sub.textContent = origStep1SubText;
      if (btnStep1) btnStep1.innerHTML = origStep1BtnHtml;
      if (btnGoo) {
        const code = (inpRef.value || '').trim().toUpperCase();
        btnGoo.href = APP + '/auth/google' + (code ? ('?ref=' + encodeURIComponent(code)) : '');
      }
    }
  };

  if (chkTrainer) {
    chkTrainer.addEventListener('change', window.syncTrainerSwitchUI);
    if (chkTrainer.checked) {
      window.syncTrainerSwitchUI();
    }
  }

  inpRef.addEventListener('input', () => {
    savedRefCode = inpRef.value;
    clearTimeout(timerRef);
    timerRef = setTimeout(() => checkCodeLive(inpRef.value), 260);
  });
})();

document.getElementById('linkToRegister')?.addEventListener('click', e => {
  e.preventDefault();
  switchView('register');
  ensureWheelPositioned();
  forceStepSwitch(wizardCurrentStep, 1);
});
document.getElementById('linkToLogin')?.addEventListener('click',    e => { e.preventDefault(); switchView('login'); });
document.getElementById('linkToRecovery')?.addEventListener('click', e => { e.preventDefault(); switchView('recovery'); });
document.getElementById('linkRecoveryToLogin')?.addEventListener('click', e => {
  e.preventDefault();
  resetRecoveryForm();
  switchView('login');
});

// Botón de retorno desde Stand-by al Login
const btnStandbyToLogin = document.getElementById('btnStandbyToLogin');
if (btnStandbyToLogin) {
  btnStandbyToLogin.addEventListener('click', e => {
    e.preventDefault();
    switchView('login');
  });
}

// Botones para volver al login desde dentro del wizard
document.querySelectorAll('.link-reg-to-login').forEach(link => {
  link.addEventListener('click', e => {
    e.preventDefault();
    switchView('login');
  });
});

if (viewRegister && viewRegister.style.display === 'block') document.title = TITULOS.register;
if (viewRecovery && viewRecovery.style.display === 'block') document.title = TITULOS.recovery;
if (viewStandby && viewStandby.style.display === 'block') document.title = TITULOS.standby;

// ══════════════════════════════════════════════════════════════
//  ONBOARDING WIZARD (3 PASOS) & STAND-BY SPA (PRO-MAX DEFENSIVE)
// ══════════════════════════════════════════════════════════════

const registerForm = document.getElementById('registerForm');
const origRegister = '<i class="ti ti-check"></i> <span>Solicitar acceso</span>';

function clearRegErrors() {
  document.querySelectorAll('.input-box-group').forEach(g => {
    g.classList.remove('has-error', 'ibg-shake');
    g.style.borderColor = '';
    g.style.boxShadow = '';
  });
  document.querySelectorAll('#view-register .ibg-error').forEach(s => {
    s.textContent = '';
    s.style.display = 'none';
  });
  document.querySelectorAll('[aria-invalid]').forEach(i => i.removeAttribute('aria-invalid'));
}

function setRegError(campo, msg) {
  const input = document.getElementById(campo) || document.getElementById('reg_' + campo) || (registerForm && registerForm.elements ? registerForm.elements[campo] : null);
  if (!input) return null;
  const box = input.closest('.input-box-group');
  if (box) {
    box.classList.add('has-error');
  }
  input.setAttribute('aria-invalid', 'true');
  const errId = campo.startsWith('reg_') ? campo.replace('reg_', 'err_') : 'err_' + campo;
  const out = (box ? box.querySelector('.ibg-error') : null) || document.getElementById(errId);
  if (out) {
    out.textContent = msg;
    out.style.display = 'inline-flex';
  }
  return input;
}

document.addEventListener('input', e => {
  const g = e.target?.closest?.('.input-box-group');
  if (!g) return;
  g.classList.remove('has-error', 'ibg-shake');
  g.style.borderColor = '';
  g.style.boxShadow = '';
  e.target.removeAttribute('aria-invalid');
  const errEl = g.querySelector('.ibg-error');
  if (errEl && errEl !== e.target) {
    errEl.textContent = '';
    errEl.style.display = 'none';
  }
});

function normalizarCedula(raw) {
  const limpia = String(raw || '').replace(/\D/g, '').slice(0, 8);
  return (limpia.length >= 5 && limpia.length <= 8) ? limpia : null;
}

const inpCedula = document.getElementById('reg_cedula');
if (inpCedula) {
  inpCedula.addEventListener('input', e => {
    e.target.value = e.target.value.replace(/\D/g, '').slice(0, 8);
  });
  inpCedula.addEventListener('blur', e => {
    const n = normalizarCedula(e.target.value);
    if (n) e.target.value = n;
  });
}

/* ── 1. Drum Wheel Date Picker Mechanics (Paso 1) ── */
const regDayCol   = document.getElementById('regWheelDay');
const regMonthCol = document.getElementById('regWheelMonth');
const regYearCol  = document.getElementById('regWheelYear');
const regFechaNac = document.getElementById('fecha_nacimiento') || document.getElementById('regFechaNac');
const DRUM_ITEM_H = 40;
const START_YEAR  = 1950;

function updateRegWheelCol(col) {
  if (!col) return '';
  const items = col.children;
  if (!items || !items.length) return '';
  const idx = Math.max(0, Math.min(items.length - 1, Math.round(col.scrollTop / DRUM_ITEM_H)));
  for (let i = 0; i < items.length; i++) {
    if (i === idx) items[i].classList.add('active');
    else items[i].classList.remove('active');
  }
  return items[idx].getAttribute('data-val') || '';
}

function syncRegWheelDate() {
  if (!regDayCol || !regMonthCol || !regYearCol) return;
  // If wheel is not visible in DOM (clientHeight === 0), do not recalculate from 0 scrollTop
  if (regDayCol.clientHeight === 0 && regDayCol.scrollTop === 0) return;

  const d = updateRegWheelCol(regDayCol);
  const m = updateRegWheelCol(regMonthCol);
  const y = updateRegWheelCol(regYearCol);
  if (d && m && y) {
    const fullDate = `${y}-${String(m).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    const inp1 = document.getElementById('fecha_nacimiento');
    const inp2 = document.getElementById('regFechaNac');
    if (inp1) inp1.value = fullDate;
    if (inp2) inp2.value = fullDate;
  }
}

let regWheelRaf = null;
[regDayCol, regMonthCol, regYearCol].forEach(col => {
  if (!col) return;
  col.addEventListener('scroll', () => {
    if (regWheelRaf) cancelAnimationFrame(regWheelRaf);
    regWheelRaf = requestAnimationFrame(syncRegWheelDate);
  }, { passive: true });

  col.addEventListener('click', e => {
    const item = e.target.closest('.wheel-item');
    if (!item) return;
    const idx = Array.from(col.children).indexOf(item);
    if (idx >= 0) {
      col.scrollTo({ top: idx * DRUM_ITEM_H, behavior: 'smooth' });
    }
  });
});

function positionRegWheel(dateStr) {
  const parts = (dateStr || '2005-06-15').split('-');
  const y = parseInt(parts[0], 10) || 2005;
  const m = parseInt(parts[1], 10) || 6;
  const d = parseInt(parts[2], 10) || 15;

  if (regDayCol)   regDayCol.scrollTop   = (d - 1) * DRUM_ITEM_H;
  if (regMonthCol) regMonthCol.scrollTop = (m - 1) * DRUM_ITEM_H;
  if (regYearCol)  regYearCol.scrollTop  = (y - START_YEAR) * DRUM_ITEM_H;

  syncRegWheelDate();
}

function ensureWheelPositioned() {
  positionRegWheel('2005-06-15');
  setTimeout(() => positionRegWheel('2005-06-15'), 40);
  setTimeout(() => positionRegWheel('2005-06-15'), 120);
  setTimeout(() => positionRegWheel('2005-06-15'), 260);
}

function initRegWheel(dateStr) {
  positionRegWheel(dateStr || '2005-06-15');
}

/* ── 2. Selector de Peso (Paso 2) ── */
const regPesoRange = document.getElementById('regPesoRange');
const regPesoNum   = document.getElementById('regPesoNum');
const regPesoInput = document.getElementById('regPesoInput');
const btnPesoMinus = document.getElementById('btnRegPesoMinus');
const btnPesoPlus  = document.getElementById('btnRegPesoPlus');

function setRegPeso(val) {
  let num = parseFloat(val);
  if (isNaN(num)) num = 70.0;
  num = Math.max(35, Math.min(160, Math.round(num * 10) / 10));
  const formatted = num.toFixed(1);
  if (regPesoRange) regPesoRange.value = formatted;
  if (regPesoNum)   regPesoNum.textContent = formatted;
  if (regPesoInput) regPesoInput.value = formatted;
}
if (regPesoRange) regPesoRange.addEventListener('input', e => setRegPeso(e.target.value));
if (btnPesoMinus) btnPesoMinus.addEventListener('click', () => setRegPeso(parseFloat(regPesoInput?.value || 70) - 0.5));
if (btnPesoPlus)  btnPesoPlus.addEventListener('click', () => setRegPeso(parseFloat(regPesoInput?.value || 70) + 0.5));

/* ── 3. Selector de Estatura (cm) con Regla Métrica Deslizable (Paso 2) ── */
const regAlturaRange = document.getElementById('regAlturaRange');
const regHeightNum   = document.getElementById('regHeightValueNumber');
const regAlturaInput = document.getElementById('regAlturaInput');
const btnRulerMinus  = document.getElementById('btnRegRulerMinus');
const btnRulerPlus   = document.getElementById('btnRegRulerPlus');

function setRegAltura(val) {
  let num = parseInt(val, 10);
  if (isNaN(num)) num = 170;
  num = Math.max(120, Math.min(220, num));
  if (regAlturaRange) regAlturaRange.value = num;
  if (regHeightNum)   regHeightNum.textContent = num;
  if (regAlturaInput) regAlturaInput.value = num;
}
if (regAlturaRange) regAlturaRange.addEventListener('input', e => setRegAltura(e.target.value));
if (btnRulerMinus)  btnRulerMinus.addEventListener('click', () => setRegAltura(parseInt(regAlturaRange?.value || 170, 10) - 1));
if (btnRulerPlus)   btnRulerPlus.addEventListener('click', () => setRegAltura(parseInt(regAlturaRange?.value || 170, 10) + 1));

/* ── 4. Encuesta de Metas en el Gym (Paso 3) ── */
const selectedGoals = new Set();
let activeSublevelHipertrofia = 'estetico';
const regMetasInput = document.getElementById('regMetasInput');
const boxMetaOtros = document.getElementById('boxMetaOtros');
const inputMetaOtros = document.getElementById('inputMetaOtros');
const boxSubHipertrofia = document.getElementById('boxSubnivelHipertrofia');

if (inputMetaOtros) {
  inputMetaOtros.addEventListener('input', () => {
    const errOtros = document.getElementById('err_meta_otros');
    if (errOtros) { errOtros.textContent = ''; errOtros.style.display = 'none'; }
  });
}

document.querySelectorAll('#goalsGrid .goal-chip').forEach(chip => {
  chip.addEventListener('click', (e) => {
    const errGoals = document.getElementById('err_goals');
    if (errGoals) { errGoals.textContent = ''; errGoals.style.display = 'none'; }
    // Si el clic ocurrió dentro del input personalizado de otros, no alternar el chip
    if (e.target && e.target.closest('#inputMetaOtros')) {
      return;
    }

    const goal = chip.getAttribute('data-goal');
    const isSelected = chip.classList.contains('selected');
    const boxMetaOtros = chip.querySelector('#boxMetaOtros') || document.getElementById('boxMetaOtros');

    if (isSelected) {
      chip.classList.remove('selected');
      chip.setAttribute('aria-checked', 'false');
      selectedGoals.delete(goal);

      if (goal === 'otros' && boxMetaOtros) {
        boxMetaOtros.style.display = 'none';
      }
      if (goal === 'Ganar masa muscular (Hipertrofia)' && boxSubHipertrofia) {
        boxSubHipertrofia.style.display = 'none';
      }
    } else {
      chip.classList.add('selected');
      chip.setAttribute('aria-checked', 'true');
      selectedGoals.add(goal);

      if (typeof anime !== 'undefined') {
        safeAnime({ targets: chip, scale: [1, 1.03, 1], duration: 220, easing: 'easeOutQuad' });
      }

      if (goal === 'otros' && boxMetaOtros) {
        boxMetaOtros.style.display = 'block';
        const inp = document.getElementById('inputMetaOtros');
        setTimeout(() => {
          try { inp?.focus(); } catch (_) {}
        }, 120);
      }
      if (goal === 'Ganar masa muscular (Hipertrofia)' && boxSubHipertrofia) {
        boxSubHipertrofia.style.display = 'block';
      }
    }
  });
});

// Selección de Sub-Nivel de Hipertrofia (Físico Estético vs Fisicoculturista)
document.querySelectorAll('#boxSubnivelHipertrofia .subnivel-card').forEach(card => {
  card.addEventListener('click', () => {
    document.querySelectorAll('#boxSubnivelHipertrofia .subnivel-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    activeSublevelHipertrofia = card.getAttribute('data-sub') || 'estetico';
    if (typeof anime !== 'undefined') {
      safeAnime({ targets: card, scale: [0.98, 1.03, 1], duration: 220, easing: 'easeOutQuad' });
    }
  });
});

// Botón Omitir Metas por ahora
const btnSkipGoals = document.getElementById('btnSkipGoals');
if (btnSkipGoals) {
  btnSkipGoals.addEventListener('click', () => {
    submitRegistration(true);
  });
}

/* ── 5. Navegación Secuencial y Cambio Forzado de Paso (DOM Display) ── */
let wizardCurrentStep = 1;

window.forceStepSwitch = function(from, to) {
  const p1 = document.getElementById('wizardStep1');
  const p2 = document.getElementById('wizardStep2');
  const p3 = document.getElementById('wizardStep3');

  // Asegurar visibilidad limpia e incondicional
  if (to === 2) {
    if (p1) p1.style.display = 'none';
    if (p3) p3.style.display = 'none';
    if (p2) {
      p2.style.display = 'block';
      p2.style.opacity = '1';
      p2.style.transform = 'none';
      if (typeof anime !== 'undefined') {
        safeAnime({ targets: p2, opacity: [0, 1], translateY: [12, 0], duration: 240, easing: 'easeOutQuad' });
      }
    }
    wizardCurrentStep = 2;
  } else if (to === 1) {
    if (p2) p2.style.display = 'none';
    if (p3) p3.style.display = 'none';
    if (p1) {
      p1.style.display = 'block';
      p1.style.opacity = '1';
      p1.style.transform = 'none';
      if (typeof anime !== 'undefined') {
        safeAnime({ targets: p1, opacity: [0, 1], translateY: [12, 0], duration: 240, easing: 'easeOutQuad' });
      }
    }
    wizardCurrentStep = 1;
    ensureWheelPositioned();
  } else if (to === 3) {
    if (p1) p1.style.display = 'none';
    if (p2) p2.style.display = 'none';
    if (p3) {
      p3.style.display = 'block';
      p3.style.opacity = '1';
      p3.style.transform = 'none';
      if (typeof anime !== 'undefined') {
        safeAnime({ targets: p3, opacity: [0, 1], translateY: [12, 0], duration: 240, easing: 'easeOutQuad' });
      }
    }
    wizardCurrentStep = 3;
  }

  // Actualizar burbujas superiores de progreso y barra
  for (let i = 1; i <= 3; i++) {
    const node = document.getElementById('nodeStep' + i);
    const bubble = document.getElementById('bubbleStep' + i);
    if (!node || !bubble) continue;
    if (i === to) {
      node.className = 'wizard-step-node active';
      bubble.innerHTML = i;
    } else if (i < to) {
      node.className = 'wizard-step-node completed';
      bubble.innerHTML = '<i class="ti ti-check"></i>';
    } else {
      node.className = 'wizard-step-node';
      bubble.innerHTML = i;
    }
  }

  const bar = document.getElementById('wizardBarFill');
  if (bar) {
    bar.style.width = to === 1 ? '33.33%' : (to === 2 ? '66.66%' : '100%');
  }

  // Scroll al inicio inmediato en el contenedor de scroll
  const main = document.getElementById('mainContent');
  if (main) main.scrollTop = 0;
  const box = document.getElementById('authBox');
  if (box) box.scrollTop = 0;
  window.scrollTo({ top: 0, behavior: 'auto' });
};

window.goToWizardStep = function(step) {
  window.forceStepSwitch(wizardCurrentStep, step);
};

/* ── Verificación Real de Correo por Código OTP ── */
let verifiedEmailAddress = '';
let emailOtpCooldownTimer = null;

function resetEmailVerificationUI(clearOtpInput = true) {
  verifiedEmailAddress = '';
  const badge = document.getElementById('badgeEmailVerified');
  if (badge && document.getElementById('regAuthProvider')?.value !== 'google') {
    badge.classList.remove('is-visible');
  }
  if (clearOtpInput) {
    const otpInp = document.getElementById('reg_email_otp');
    if (otpInp) otpInp.value = '';
  }
}

document.getElementById('reg_email')?.addEventListener('input', function() {
  const cur = (this.value || '').trim().toLowerCase();
  if (verifiedEmailAddress && cur !== verifiedEmailAddress) {
    resetEmailVerificationUI(true);
  }
});

function startEmailOtpCooldown(secs) {
  const btn = document.getElementById('btnEnviarCodigoEmail');
  if (!btn) return;
  if (emailOtpCooldownTimer) clearInterval(emailOtpCooldownTimer);
  let left = Math.max(1, parseInt(secs || 45, 10));
  const updateTxt = () => {
    btn.disabled = true;
    btn.innerHTML = '<i class="ti ti-clock"></i> <span>' + left + 's</span>';
  };
  updateTxt();
  emailOtpCooldownTimer = setInterval(() => {
    left--;
    if (left <= 0) {
      clearInterval(emailOtpCooldownTimer);
      emailOtpCooldownTimer = null;
      btn.disabled = false;
      btn.innerHTML = '<i class="ti ti-mail-forward"></i> <span>Reenviar</span>';
    } else {
      updateTxt();
    }
  }, 1000);
}

window.enviarCodigoVerificacionEmail = async function() {
  const emailInp = document.getElementById('reg_email');
  const errEl = document.getElementById('err_email');
  const box = document.getElementById('group_reg_email');
  const btn = document.getElementById('btnEnviarCodigoEmail');
  const valEmail = (emailInp?.value || '').trim().toLowerCase();

  if (box) { box.classList.remove('has-error', 'ibg-shake'); box.style.borderColor = ''; box.style.boxShadow = ''; }
  if (errEl) { errEl.textContent = ''; errEl.style.display = 'none'; }

  if (!valEmail || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valEmail)) {
    if (box) box.classList.add('has-error');
    if (errEl) { errEl.textContent = 'Ingresa primero un correo electrónico válido.'; errEl.style.display = 'block'; }
    emailInp?.focus();
    return;
  }

  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<span class="spin"></span> <span>Enviando...</span>';
  }

  try {
    const res = await postApi('auth/api-enviar-codigo-email', {
      csrf_token: typeof CSRF !== 'undefined' ? CSRF : '',
      email: valEmail
    });
    if (res && res.ok) {
      resetEmailVerificationUI(true);
      const otpBox = document.getElementById('boxEmailOtp');
      if (otpBox) otpBox.style.display = 'flex';
      Toast.fire({ icon: 'success', title: res.msg || 'Código enviado a tu correo.' });
      startEmailOtpCooldown(res.cooldown || 45);
      setTimeout(() => document.getElementById('reg_email_otp')?.focus(), 80);
    } else {
      if (box) box.classList.add('has-error');
      const baseErr = res?.msg || 'No se pudo enviar el código a tu correo.';
      const fallbackHint = ' Si no llega a tu bandeja o Spam, también puedes continuar con Google en 1 clic.';
      if (errEl) {
        errEl.textContent = res?.cooldown ? baseErr : (baseErr + fallbackHint);
        errEl.style.display = 'block';
      }
      if (res?.cooldown) startEmailOtpCooldown(res.cooldown);
      else if (btn) { btn.disabled = false; btn.innerHTML = '<i class="ti ti-mail-forward"></i> <span>Reintentar</span>'; }
    }
  } catch (err) {
    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="ti ti-mail-forward"></i> <span>Reintentar</span>'; }
    if (errEl) {
      errEl.textContent = (err?.message || 'Error de conexión.') + ' Verifica tu red o usa "Continuar con Google".';
      errEl.style.display = 'block';
    }
  }
};

window.verificarCodigoEmailBtn = async function(silent = false) {
  const emailInp = document.getElementById('reg_email');
  const otpInp = document.getElementById('reg_email_otp');
  const errEl = document.getElementById('err_email');
  const box = document.getElementById('group_reg_email');
  const btn = document.getElementById('btnVerificarCodigoEmail');

  const valEmail = (emailInp?.value || '').trim().toLowerCase();
  const valOtp = (otpInp?.value || '').replace(/\D+/g, '');
  if (otpInp) otpInp.value = valOtp;

  if (valOtp.length !== 6) {
    if (!silent) {
      if (box) box.classList.add('has-error');
      if (errEl) { errEl.textContent = 'Ingresa los 6 dígitos del código enviado a tu correo.'; errEl.style.display = 'block'; }
      otpInp?.focus();
    }
    return { ok: false, msg: 'Ingresa el código de 6 dígitos enviado a tu correo.' };
  }

  if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spin"></span> Validando'; }
  try {
    const res = await postApi('auth/api-verificar-codigo-email', {
      csrf_token: typeof CSRF !== 'undefined' ? CSRF : '',
      email: valEmail,
      codigo: valOtp
    });
    if (res && res.ok) {
      verifiedEmailAddress = valEmail;
      if (box) { box.classList.remove('has-error'); box.style.borderColor = 'rgba(34,197,94,0.45)'; box.style.boxShadow = ''; }
      if (errEl) { errEl.textContent = ''; errEl.style.display = 'none'; }
      document.getElementById('badgeEmailVerified')?.classList.add('is-visible');
      const otpBox = document.getElementById('boxEmailOtp');
      if (otpBox) otpBox.style.display = 'none';
      if (emailOtpCooldownTimer) { clearInterval(emailOtpCooldownTimer); emailOtpCooldownTimer = null; }
      const sendBtn = document.getElementById('btnEnviarCodigoEmail');
      if (sendBtn) {
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="ti ti-refresh"></i> <span>Cambiar</span>';
      }
      if (!silent) Toast.fire({ icon: 'success', title: '¡Correo verificado!' });
      return { ok: true };
    }
    if (!silent) {
      if (box) box.classList.add('has-error');
      if (errEl) { errEl.textContent = res?.msg || 'Código incorrecto.'; errEl.style.display = 'block'; }
    }
    return { ok: false, msg: res?.msg || 'Código incorrecto.' };
  } catch (err) {
    return { ok: false, msg: err?.message || 'Error de conexión.' };
  } finally {
    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="ti ti-check"></i> Confirmar'; }
  }
};

/* ── 6. Manejador Global e Incondicional de SIGUIENTE → (Paso 1) ── */
let isStep1Busy = false;
window.handleWizardStep1Next = async function(event) {
  if (event) {
    if (typeof event.preventDefault === 'function') event.preventDefault();
    if (typeof event.stopPropagation === 'function') event.stopPropagation();
  }
  if (isStep1Busy) return;
  isStep1Busy = true;
  try {

  clearRegErrors();

  const isGoogle      = (document.getElementById('regAuthProvider')?.value === 'google');
  const isTrainerMode = !!document.getElementById('reg_soy_entrenador')?.checked;
  let reNombre;
  try {
    reNombre = new RegExp('^\\p{L}[\\p{L}\\s\'\\u2019.\\-]*$', 'u');
  } catch (_) {
    reNombre = /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\'\u2019.\-]+$/;
  }

  const valNombres    = (document.getElementById('reg_nombres')?.value || '').trim().replace(/\s+/g, ' ');
  const valApellidos  = (document.getElementById('reg_apellidos')?.value || '').trim().replace(/\s+/g, ' ');
  const valCedulaRaw  = (document.getElementById('reg_cedula')?.value || '').trim();
  const valCedulaNorm = normalizarCedula(valCedulaRaw);
  const valCodigoEnt  = isTrainerMode ? '' : (document.getElementById('reg_codigo_entrenador')?.value || '').trim().toUpperCase();
  const valEmail      = (document.getElementById('reg_email')?.value || '').trim();
  const valPass       = (document.getElementById('reg_pass')?.value || '').trim();
  const valPregunta   = (document.getElementById('reg_pregunta')?.value || '').trim();
  const valRespuesta  = (document.getElementById('reg_respuesta')?.value || '').trim();

  let errorField = null;
  let errorMsg   = '';

  if (!valNombres || valNombres.length < 2 || !reNombre.test(valNombres)) {
    errorField = 'reg_nombres';
    errorMsg   = !valNombres ? 'Por favor ingresa tus nombres.' : 'Tus nombres deben tener al menos 2 letras válidas (sin números).';
  } else if (!valApellidos || valApellidos.length < 2 || !reNombre.test(valApellidos)) {
    errorField = 'reg_apellidos';
    errorMsg   = !valApellidos ? 'Por favor ingresa tus apellidos.' : 'Tus apellidos deben tener al menos 2 letras válidas (sin números).';
  } else if (!valCedulaRaw) {
    errorField = 'reg_cedula';
    errorMsg   = 'Por favor ingresa tu número de cédula (solo números).';
  } else if (!valCedulaNorm) {
    errorField = 'reg_cedula';
    errorMsg   = 'La cédula debe contener entre 5 y 8 números sin letras.';
  } else if (!isTrainerMode && (!valCodigoEnt || valCodigoEnt.length < 3)) {
    errorField = 'reg_codigo_entrenador';
    errorMsg   = 'El código o link de entrenador es obligatorio para registrarte.';
  } else if (!valEmail) {
    errorField = 'reg_email';
    errorMsg   = 'Por favor ingresa tu correo electrónico.';
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valEmail)) {
    errorField = 'reg_email';
    errorMsg   = 'Ingresa un correo electrónico con formato válido.';
  } else if (!valPass) {
    errorField = 'reg_pass';
    errorMsg   = 'Por favor ingresa una contraseña para tu cuenta.';
  } else if (valPass.length < 8) {
    errorField = 'reg_pass';
    errorMsg   = 'La contraseña debe tener al menos 8 caracteres.';
  } else if (!valPregunta) {
    errorField = 'reg_pregunta';
    errorMsg   = 'Por favor selecciona una pregunta de seguridad.';
  } else if (!valRespuesta || valRespuesta.length < 2) {
    errorField = 'reg_respuesta';
    errorMsg   = 'Por favor escribe tu respuesta secreta (mínimo 2 letras).';
  }

  // Validación obligatoria de correo verificado por OTP para registros locales
  if (!errorField && !isGoogle && verifiedEmailAddress !== valEmail.toLowerCase()) {
    const valOtpPending = (document.getElementById('reg_email_otp')?.value || '').replace(/\D+/g, '');
    if (valOtpPending.length === 6) {
      const otpCheck = await window.verificarCodigoEmailBtn(true);
      if (!otpCheck.ok) {
        errorField = 'reg_email_otp';
        errorMsg   = otpCheck.msg || 'El código de verificación del correo es inválido.';
      }
    } else {
      errorField = 'reg_email';
      errorMsg   = 'Debes confirmar un correo real: pulsa "Enviar código" e ingresa el código de 6 dígitos enviado a tu bandeja.';
    }
  }

  // Validación remota estricta del código de entrenador (solo atletas)
  if (!errorField && !isTrainerMode && valCodigoEnt) {
    const nextBtn = document.getElementById('btnWizardStep1Next');
    if (nextBtn) nextBtn.disabled = true;
    try {
      const respCode = await fetch(APP + '/auth/api-validar-codigo?codigo=' + encodeURIComponent(valCodigoEnt));
      const dataCode = await respCode.json();
      if (!dataCode || !dataCode.ok) {
        errorField = 'reg_codigo_entrenador';
        errorMsg   = (dataCode && dataCode.msg && dataCode.msg !== 'Código no encontrado')
          ? dataCode.msg
          : 'El código de entrenador ingresado no existe o no está activo.';
      } else {
        const bdgNom = document.getElementById('badgeCoachRefNombre');
        const bdgRef = document.getElementById('badgeCoachRef');
        if (bdgNom && dataCode.entrenador_nombre) bdgNom.textContent = 'Coach: ' + dataCode.entrenador_nombre;
        if (bdgRef) bdgRef.style.display = 'inline-flex';
      }
    } catch (_) {
      errorField = 'reg_codigo_entrenador';
      errorMsg   = 'No se pudo verificar el código de entrenador. Revisa tu conexión.';
    } finally {
      if (nextBtn) nextBtn.disabled = false;
    }
  }

  if (errorField) {
    const inp = document.getElementById(errorField);
    const box = inp ? inp.closest('.input-box-group') : null;
    if (box) {
      box.classList.add('has-error');
    }

    // Scroll suave y confiable hacia el campo con error en móvil y escritorio
    if (box) {
      try { box.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (_) {}
    }

    const errMap = {
      reg_pass: 'err_password',
      reg_pregunta: 'err_pregunta_seguridad',
      reg_respuesta: 'err_respuesta_seguridad'
    };
    const errId = errMap[errorField] || errorField.replace(/^reg_/, 'err_');
    const errSmall = (box ? box.querySelector('.ibg-error') : null) || document.getElementById(errId);
    if (errSmall) {
      errSmall.textContent = errorMsg;
      errSmall.style.display = 'inline-flex';
    } else if (errorMsg) {
      Toast.fire({ icon: 'warning', title: errorMsg });
    }

    if (inp) {
      inp.setAttribute('aria-invalid', 'true');
      setTimeout(() => {
        try { inp.focus({ preventScroll: true }); } catch (_) {}
      }, 100);
    }
    return;
  }

  // Si es solicitud de entrenador, enviar directamente sin pasos de cuerpo/metas de atleta
  if (isTrainerMode) {
    await submitRegistration(true);
    return;
  }

  // Todos los campos válidos: transición inmediata y garantizada al Paso 2
  window.forceStepSwitch(1, 2);
  } finally {
    isStep1Busy = false;
  }
};

// Delegación Global en document para botones del wizard
document.addEventListener('click', e => {
  const btn1 = e.target?.closest?.('#btnWizardStep1Next, .btn-wizard-next-step1');
  if (btn1) {
    window.handleWizardStep1Next(e);
    return;
  }
  const btn2Back = e.target?.closest?.('#btnWizardStep2Back');
  if (btn2Back) {
    e.preventDefault();
    window.forceStepSwitch(2, 1);
    return;
  }
  const btn2Next = e.target?.closest?.('#btnWizardStep2Next, .btn-wizard-next-step2');
  if (btn2Next) {
    e.preventDefault();
    window.forceStepSwitch(2, 3);
    return;
  }
  const btn3Back = e.target?.closest?.('#btnWizardStep3Back');
  if (btn3Back) {
    e.preventDefault();
    window.forceStepSwitch(3, 2);
    return;
  }
});

// Inicialización de selectores nativos
ensureWheelPositioned();
window.addEventListener('load', ensureWheelPositioned);
document.addEventListener('DOMContentLoaded', ensureWheelPositioned);
setRegPeso(70.0);
setRegAltura(170);

/* ── 6. Envío AJAX y Transición a Stand-by ── */
async function submitRegistration(isOmitido = false) {
  clearRegErrors();

  if (!isOmitido && selectedGoals.size === 0) {
    const errGoals = document.getElementById('err_goals');
    if (errGoals) {
      errGoals.textContent = 'Selecciona al menos una meta o pulsa "Omitir por ahora".';
      errGoals.style.display = 'inline-flex';
      errGoals.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    return;
  }

  const f = registerForm.elements;
  const isGoogle = (document.getElementById('regAuthProvider')?.value === 'google');

  // Construcción de la estructura de metas
  let metasData = null;
  if (isOmitido) {
    metasData = { omitido: true, id: null, titulo: null, subnivel: null, custom_texto: null };
  } else if (selectedGoals.has('otros')) {
    const customTxt = (document.getElementById('inputMetaOtros')?.value || '').trim();
    if (!customTxt) {
      const errOtros = document.getElementById('err_meta_otros');
      if (errOtros) {
        errOtros.textContent = 'Escribe tu meta personalizada.';
        errOtros.style.display = 'inline-flex';
      }
      document.getElementById('inputMetaOtros')?.focus({ preventScroll: true });
      return;
    }
    metasData = {
      id: 'otros',
      titulo: 'Objetivo Personalizado',
      categoria: 'personalizado',
      icono: 'ti-edit',
      color: '#ec4899',
      subnivel: null,
      custom_texto: customTxt,
      omitido: false
    };
  } else if (selectedGoals.has('Ganar masa muscular (Hipertrofia)')) {
    const isCulturista = (activeSublevelHipertrofia === 'culturista');
    metasData = {
      id: 'ganar_masa',
      titulo: 'Ganar masa muscular (Hipertrofia)',
      categoria: 'hipertrofia',
      icono: 'ti-barbell',
      color: '#818cf8',
      subnivel: isCulturista ? {
        clave: 'culturista',
        titulo: 'Fisicoculturista',
        subtitulo: 'Máxima hipertrofia, densidad y volumen',
        silueta: 'culturista',
        icono: 'ti-barbell'
      } : {
        clave: 'estetico',
        titulo: 'Físico Estético',
        subtitulo: 'Definición atlética y simetría V-Taper',
        silueta: 'estetico',
        icono: 'ti-sparkles'
      },
      custom_texto: null,
      omitido: false
    };
  } else {
    const firstGoal = Array.from(selectedGoals)[0];
    const chipEl = document.querySelector(`.goal-chip[data-goal="${firstGoal}"]`);
    metasData = {
      id: chipEl?.dataset?.id || 'meta',
      titulo: firstGoal,
      categoria: chipEl?.dataset?.id || 'general',
      icono: chipEl?.dataset?.icon || 'ti-target',
      color: chipEl?.dataset?.color || '#8a2be2',
      subnivel: null,
      custom_texto: null,
      omitido: false
    };
  }

  const isTrainerMode = !!document.getElementById('reg_soy_entrenador')?.checked;
  const payload = {
    csrf_token:          typeof CSRF !== 'undefined' ? CSRF : '',
    auth_provider:       isGoogle ? 'google' : 'local',
    soy_entrenador:      isTrainerMode ? 1 : 0,
    nombres:             (f['nombres']?.value || '').trim().replace(/\s+/g, ' '),
    apellidos:           (f['apellidos']?.value || '').trim().replace(/\s+/g, ' '),
    cedula:              isTrainerMode ? '' : normalizarCedula(f['cedula']?.value || ''),
    codigo_entrenador:   isTrainerMode ? '' : (f['codigo_entrenador']?.value || '').trim().toUpperCase(),
    email:               (f['email']?.value || '').trim(),
    password:            (f['password']?.value || '').trim(),
    pregunta_seguridad:  (f['pregunta_seguridad']?.value || ''),
    respuesta_seguridad: (f['respuesta_seguridad']?.value || '').trim(),
    fecha_nacimiento:    isTrainerMode ? '' : (document.getElementById('fecha_nacimiento') || document.getElementById('regFechaNac')).value,
    peso_kg:             isTrainerMode ? '' : document.getElementById('regPesoInput').value,
    altura_cm:           isTrainerMode ? '' : document.getElementById('regAlturaInput').value,
    metas_json:          isTrainerMode ? '[]' : JSON.stringify(metasData),
  };

  const btn = isTrainerMode ? document.getElementById('btnWizardStep1Next') : document.getElementById('btnRegister');
  const origBtnHtml = origRegister;
  setBtn(btn, true, origBtnHtml);

  try {
    const data = await postApi('auth/do-registro', payload);

    if (data.ok || data.status === 'standby') {
      const fueEntrenador = isTrainerMode || data.rol_solicitado === 'entrenador';
      setBtn(btn, false, origBtnHtml);
      registerForm.reset();
      if (typeof window.syncTrainerSwitchUI === 'function') window.syncTrainerSwitchUI();
      resetEmailVerificationUI(true);
      const otpBoxDone = document.getElementById('boxEmailOtp');
      if (otpBoxDone) otpBoxDone.style.display = 'none';
      const sendBtnDone = document.getElementById('btnEnviarCodigoEmail');
      if (sendBtnDone) { sendBtnDone.disabled = false; sendBtnDone.innerHTML = '<i class="ti ti-mail-forward"></i> <span>Enviar código</span>'; }
      selectedGoals.clear();
      document.querySelectorAll('#goalsGrid .goal-chip').forEach(c => {
        c.classList.remove('selected');
        c.setAttribute('aria-checked', 'false');
      });
      goToWizardStep(1);

      // Transición Visual a la vista #view-standby dentro del contenedor de autenticación
      updateStandbyCopy(fueEntrenador);
      switchView('standby');

      Swal.fire({
        icon: 'success',
        title: '¡Solicitud enviada!',
        text: data.mensaje || data.msg || (fueEntrenador
          ? 'En espera de que el administrador active tu cuenta de entrenador'
          : 'En espera de que tu entrenador active tu cuenta'),
        background: '#111827', color: '#fff',
        confirmButtonText: 'Entendido', confirmButtonColor: '#8a2be2',
      });
    } else {
      setBtn(btn, false, origBtnHtml);
      const errMsg = data.msg || data.mensaje || 'No se pudo completar el registro.';
      if (data.campo) {
        goToWizardStep(1);
        const input = setRegError(data.campo, errMsg);
        const box = input ? input.closest('.input-box-group') : null;
        if (box) {
          try { box.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (_) {}
          if (input && input.type !== 'hidden') input.focus();
        } else {
          // Campo oculto o sin contenedor visible: nunca tragar errores en silencio
          Toast.fire({ icon: 'error', title: errMsg });
        }
      } else {
        Toast.fire({ icon: 'error', title: errMsg });
      }
      if (typeof anime !== 'undefined') {
        safeAnime({ targets: '#authBox', translateX: [0, -8, 8, -5, 5, 0], duration: 500, easing: 'linear' });
      }
    }
  } catch (err) {
    console.error('Error de registro:', err);
    Toast.fire({ icon: 'error', title: err.message || 'Error de conexión' });
    setBtn(btn, false, origBtnHtml);
  }
}

if (registerForm) {
  registerForm.addEventListener('submit', e => {
    e.preventDefault();
    if (wizardCurrentStep === 1) {
      window.handleWizardStep1Next(e);
    } else if (wizardCurrentStep === 2) {
      window.forceStepSwitch(2, 3);
    } else {
      submitRegistration(false);
    }
  });
}

// ── RECUPERACIÓN DE CONTRASEÑA DINÁMICA (Bento UI + Pregunta de Seguridad + Doble Contraseña) ──
let recoveryStep = 1;
let recoveryIdentificador = '';
const recoveryForm = document.getElementById('recoveryForm');
const btnRecovery  = document.getElementById('btnRecovery');
const recPaso1     = document.getElementById('recPaso1');
const recPaso2     = document.getElementById('recPaso2');
const recPaso3     = document.getElementById('recPaso3');
const recSubtitulo = document.getElementById('recSubtitulo');
const lblPregunta  = document.getElementById('lblPreguntaSeguridad');

function resetRecoveryForm() {
  recoveryStep = 1;
  recoveryIdentificador = '';
  if (recoveryForm) recoveryForm.reset();
  if (recPaso2) recPaso2.style.display = 'none';
  if (recPaso3) recPaso3.style.display = 'none';
  const inpId = document.getElementById('rec_identificador');
  if (inpId) {
    inpId.readOnly = false;
    inpId.style.opacity = '1';
  }
  const inpResp = document.getElementById('rec_respuesta');
  if (inpResp) {
    inpResp.readOnly = false;
    inpResp.style.opacity = '1';
  }
  const grpResp = document.getElementById('group_rec_respuesta');
  if (grpResp) grpResp.style.borderColor = '';
  if (recSubtitulo) recSubtitulo.textContent = 'Ingresa tu correo electrónico o cédula.';
  if (lblPregunta)  lblPregunta.textContent  = 'PREGUNTA DE SEGURIDAD';
  if (btnRecovery) {
    btnRecovery.disabled = false;
    btnRecovery.textContent = 'CONTINUAR';
  }
  clearRecoveryErrors();
}

function clearRecoveryErrors() {
  if (!recoveryForm) return;
  recoveryForm.querySelectorAll('.input-box-group').forEach(g => g.classList.remove('has-error'));
  recoveryForm.querySelectorAll('.ibg-error').forEach(s => { s.textContent = ''; });
  recoveryForm.querySelectorAll('[aria-invalid]').forEach(i => i.removeAttribute('aria-invalid'));
}

function setRecoveryError(campo, msg) {
  if (!recoveryForm) return null;
  const input = recoveryForm.elements[campo];
  if (!input) return null;
  input.closest('.input-box-group').classList.add('has-error');
  input.setAttribute('aria-invalid', 'true');
  const out = document.getElementById('err_rec_' + campo);
  if (out) out.textContent = msg;
  return input;
}

if (recoveryForm) {
  recoveryForm.addEventListener('input', e => {
    const g = e.target.closest && e.target.closest('.input-box-group');
    if (!g) return;
    g.classList.remove('has-error');
    e.target.removeAttribute('aria-invalid');
  });

  recoveryForm.addEventListener('submit', async e => {
    e.preventDefault();
    clearRecoveryErrors();

    if (recoveryStep === 1) {
      // ── PASO 1: Identificación (Correo o Cédula) ──
      const idVal = document.getElementById('rec_identificador').value.trim();
      if (!idVal) {
        const inp = setRecoveryError('identificador', 'Ingresa tu correo electrónico o cédula.');
        if (inp) inp.focus();
        safeAnime({ targets:'#authBox', translateX:[0,-8,8,-5,5,0], duration:500, easing:'linear' });
        return;
      }

      setBtn(btnRecovery, true, 'CONTINUAR');
      try {
        const data = await postApi('auth/do-recuperar-identificar', {
          csrf_token: typeof CSRF !== 'undefined' ? CSRF : '',
          identificador: idVal,
        });

        if (data.ok) {
          recoveryIdentificador = idVal;
          recoveryStep = 2;

          const inpId = document.getElementById('rec_identificador');
          inpId.readOnly = true;
          inpId.style.opacity = '0.75';

          const pregLimpia = String(data.pregunta || '').replace(/^\¿+|\?+$/g, '').trim().toUpperCase();
          lblPregunta.textContent = 'PREGUNTA: ¿' + pregLimpia + '?';
          recSubtitulo.textContent = 'Responde tu pregunta de seguridad.';

          recPaso2.style.display = 'block';
          safeAnime({
            targets: '#recPaso2',
            opacity: [0, 1],
            translateY: [12, 0],
            duration: 380,
            easing: 'easeOutQuad'
          });

          btnRecovery.textContent = 'VERIFICAR RESPUESTA';
          setBtn(btnRecovery, false, 'VERIFICAR RESPUESTA');
          setTimeout(() => document.getElementById('rec_respuesta').focus(), 80);
        } else {
          const inp = setRecoveryError(data.campo || 'identificador', data.msg || 'No se encontró la cuenta.');
          if (inp) inp.focus();
          setBtn(btnRecovery, false, 'CONTINUAR');
          safeAnime({ targets:'#authBox', translateX:[0,-8,8,-5,5,0], duration:500, easing:'linear' });
        }
      } catch (err) {
        console.error('Error al identificar:', err);
        Toast.fire({ icon: 'error', title: err.message || 'Error de conexión' });
        setBtn(btnRecovery, false, 'CONTINUAR');
      }

    } else if (recoveryStep === 2) {
      // ── PASO 2: Verificación de la Respuesta Secreta ──
      const respVal = document.getElementById('rec_respuesta').value.trim();
      if (respVal.length < 2) {
        const inp = setRecoveryError('respuesta', 'Ingresa tu respuesta de seguridad.');
        if (inp) inp.focus();
        safeAnime({ targets:'#authBox', translateX:[0,-8,8,-5,5,0], duration:500, easing:'linear' });
        return;
      }

      setBtn(btnRecovery, true, 'VERIFICAR RESPUESTA');
      try {
        const data = await postApi('auth/do-recuperar-verificar-resp', {
          csrf_token: typeof CSRF !== 'undefined' ? CSRF : '',
          identificador: recoveryIdentificador,
          respuesta: respVal,
        });

        if (data.ok) {
          recoveryStep = 3;

          // Bloquear campo de respuesta para mostrar confirmación visual
          const inpResp = document.getElementById('rec_respuesta');
          inpResp.readOnly = true;
          inpResp.style.opacity = '0.75';
          const grpResp = document.getElementById('group_rec_respuesta');
          if (grpResp) grpResp.style.borderColor = 'rgba(138, 43, 226, 0.6)';

          recSubtitulo.textContent = 'Establece y confirma tu nueva contraseña.';

          // Desplegar Paso 3 con doble campo de contraseña
          recPaso3.style.display = 'block';
          safeAnime({
            targets: '#recPaso3',
            opacity: [0, 1],
            translateY: [12, 0],
            duration: 380,
            easing: 'easeOutQuad'
          });

          btnRecovery.textContent = 'CAMBIAR CONTRASEÑA';
          setBtn(btnRecovery, false, 'CAMBIAR CONTRASEÑA');
          setTimeout(() => document.getElementById('rec_nueva_password').focus(), 80);
        } else {
          const inp = setRecoveryError(data.campo || 'respuesta', data.msg || 'La respuesta de seguridad es incorrecta.');
          if (inp) inp.focus();
          setBtn(btnRecovery, false, 'VERIFICAR RESPUESTA');
          safeAnime({ targets:'#authBox', translateX:[0,-8,8,-5,5,0], duration:500, easing:'linear' });
        }
      } catch (err) {
        console.error('Error al verificar respuesta:', err);
        Toast.fire({ icon: 'error', title: err.message || 'Error de conexión' });
        setBtn(btnRecovery, false, 'VERIFICAR RESPUESTA');
      }

    } else {
      // ── PASO 3: Nueva Contraseña y Confirmar Contraseña ──
      const passVal = document.getElementById('rec_nueva_password').value.trim();
      const confVal = document.getElementById('rec_confirmar_password').value.trim();

      let hasErr = false;
      if (passVal.length < 8) {
        setRecoveryError('nueva_password', 'La nueva contraseña debe tener al menos 8 caracteres.');
        hasErr = true;
      }
      if (confVal.length < 1) {
        setRecoveryError('confirmar_password', 'Por favor confirma tu nueva contraseña.');
        hasErr = true;
      } else if (passVal !== confVal) {
        setRecoveryError('confirmar_password', 'Las contraseñas no coinciden.');
        hasErr = true;
      }

      if (hasErr) {
        safeAnime({ targets:'#authBox', translateX:[0,-8,8,-5,5,0], duration:500, easing:'linear' });
        return;
      }

      setBtn(btnRecovery, true, 'CAMBIAR CONTRASEÑA');
      try {
        const data = await postApi('auth/do-recuperar-cambiar', {
          csrf_token: typeof CSRF !== 'undefined' ? CSRF : '',
          nueva_password: passVal,
          confirmar_password: confVal,
        });

        if (data.ok) {
          Swal.fire({
            icon: 'success',
            title: '¡Contraseña actualizada!',
            text: data.msg || 'Tu contraseña ha sido actualizada con éxito.',
            background: '#111827',
            color: '#fff',
            confirmButtonText: 'Iniciar Sesión',
            confirmButtonColor: '#8B5CF6',
          }).then(() => {
            const loginUserInp = document.getElementById('login_usuario');
            if (loginUserInp) loginUserInp.value = data.email || recoveryIdentificador;
            resetRecoveryForm();
            switchView('login');
          });
          setBtn(btnRecovery, false, 'CAMBIAR CONTRASEÑA');
        } else {
          const inp = setRecoveryError(data.campo || 'confirmar_password', data.msg || 'Error al actualizar contraseña.');
          if (inp) inp.focus();
          setBtn(btnRecovery, false, 'CAMBIAR CONTRASEÑA');
          safeAnime({ targets:'#authBox', translateX:[0,-8,8,-5,5,0], duration:500, easing:'linear' });
        }
      } catch (err) {
        console.error('Error al cambiar contraseña:', err);
        Toast.fire({ icon: 'error', title: err.message || 'Error de conexión' });
        setBtn(btnRecovery, false, 'CAMBIAR CONTRASEÑA');
      }
    }
  });
}

</script>
<script src="<?= APP_URL ?>/assets/js/custom-select.js?v=<?= @filemtime(__DIR__ . '/../../assets/js/custom-select.js') ?: '1.0' ?>" defer></script>
</body>
</html>
