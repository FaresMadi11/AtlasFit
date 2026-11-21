<?php
/**
 * views/partials/cfg_paleta_colores.php
 * Partial compartido para Apariencia y Paleta de Colores (Entrenador & Super Admin).
 * Params esperados (con defaults seguros):
 *  - $xpPaletaEsAdmin (bool)
 *  - $xpPaletaGlobalActivo (bool)
 *  - $xpPaletaApiUrl (string)
 *  - $csrf (string)
 */
$xpPaletaEsAdmin      = !empty($xpPaletaEsAdmin);
$xpPaletaGlobalActivo = !empty($xpPaletaGlobalActivo);
$xpPaletaApiUrl       = (string)($xpPaletaApiUrl ?? (APP_URL . ($xpPaletaEsAdmin ? '/admin/api-guardar-configuracion' : '/entrenador/api-guardar-tema-sistema')));
$csrf                 = (string)($csrf ?? ($_SESSION['csrf_token'] ?? ''));
?>
<style>
.xp-theme-cfg-card {
  background: linear-gradient(155deg, color-mix(in srgb, var(--accent-primary, #8a2be2) 13%, #111218) 0%, rgba(15, 16, 22, 0.94) 68%) !important;
  border: 1px solid color-mix(in srgb, var(--accent-primary, #8a2be2) 30%, rgba(255, 255, 255, 0.08)) !important;
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.28) !important;
  border-radius: 22px;
  padding: 18px 22px;
  transition: all 0.25s ease;
}
.xp-paleta-header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  flex-wrap: wrap;
  cursor: pointer;
  user-select: none;
}
.xp-theme-cfg-card.is-open .xp-paleta-header-row {
  margin-bottom: 16px;
  padding-bottom: 14px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.xp-paleta-collapse-body {
  display: none;
  animation: xpPaletaFadeIn 0.22s ease;
}
.xp-theme-cfg-card.is-open .xp-paleta-collapse-body {
  display: block;
}
@keyframes xpPaletaFadeIn {
  from { opacity: 0; transform: translateY(-4px); }
  to   { opacity: 1; transform: translateY(0); }
}
.xp-paleta-toggle-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 12px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #e2e8f0;
  font-size: 0.76rem;
  font-weight: 700;
  transition: all 0.2s ease;
}
.xp-paleta-toggle-btn:hover {
  background: rgba(138, 43, 226, 0.16);
  border-color: rgba(168, 85, 247, 0.4);
}
.xp-paleta-chevron {
  font-size: 1.1rem;
  color: var(--accent-light, #b388ff);
  transition: transform 0.25s ease;
}
.xp-theme-cfg-card.is-open .xp-paleta-chevron {
  transform: rotate(180deg);
}
.xp-paleta-scope-box {
  display: flex;
  align-items: center;
  gap: 10px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 999px;
  padding: 6px 8px 6px 14px;
}
.xp-paleta-scope-box.is-global {
  background: rgba(138, 43, 226, 0.14);
  border-color: rgba(168, 85, 247, 0.42);
}
.xp-paleta-scope-lbl {
  font-size: 0.74rem;
  font-weight: 700;
  color: #cbd5e1;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.xp-paleta-scope-box.is-global .xp-paleta-scope-lbl {
  color: #e9d5ff;
}
.xp-bento-switch {
  position: relative;
  display: inline-block;
  width: 52px;
  height: 29px;
  flex-shrink: 0;
  cursor: pointer;
}
.xp-bento-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}
.xp-bento-slider {
  position: absolute;
  inset: 0;
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.16);
  border-radius: 999px;
  transition: all 0.25s cubic-bezier(0.22, 1, 0.36, 1);
}
.xp-bento-slider::before {
  content: "";
  position: absolute;
  height: 21px;
  width: 21px;
  left: 3px;
  top: 3px;
  border-radius: 50%;
  background: #cbd5e1;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
  transition: transform 0.25s cubic-bezier(0.22, 1, 0.36, 1), background 0.25s ease;
}
.xp-bento-switch input:checked + .xp-bento-slider {
  background: linear-gradient(135deg, #8a2be2, #6d28d9);
  border-color: rgba(216, 180, 254, 0.55);
  box-shadow: 0 0 16px rgba(138, 43, 226, 0.45);
}
.xp-bento-switch input:checked + .xp-bento-slider::before {
  transform: translateX(23px);
  background: #ffffff;
}
</style>

<!-- ── BENTO CARD DESPLEGABLE: APARIENCIA Y PALETA DE COLORES ── -->
<div class="form-container xp-theme-cfg-card" id="xpPaletaCfgCard" style="width: 100%; margin-bottom: 25px;">
  <div class="xp-paleta-header-row" onclick="toggleXpPaletaCollapse(event)" role="button" tabindex="0" aria-expanded="false" aria-controls="xpPaletaCollapseBody" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleXpPaletaCollapse(event);}">
    <div style="min-width: 0; flex: 1;">
      <h3 style="margin: 0; font-size: 1.05rem; color: #fff; display: flex; align-items: center; gap: 8px; font-weight: 800;">
        <i class="ti ti-palette" style="color:var(--accent-primary, #8a2be2); font-size: 1.25rem;"></i> Apariencia y Paleta de Colores
      </h3>
      <div id="xpPaletaSubtitulo" style="font-size:0.74rem; color:var(--muted); font-weight:500; margin-top:3px;">
        <?php if ($xpPaletaEsAdmin): ?>
          <?= $xpPaletaGlobalActivo ? 'Modo Global activo: se aplicará a toda la web (entrenadores, atletas y login)' : 'Modo Solo Super Admin: los cambios solo aplican a tu cuenta' ?>
        <?php else: ?>
          Personaliza el estilo visual de tu cuenta y PWA
        <?php endif; ?>
      </div>
    </div>

    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
      <?php if ($xpPaletaEsAdmin): ?>
        <div id="xpPaletaScopeBox" class="xp-paleta-scope-box<?= $xpPaletaGlobalActivo ? ' is-global' : '' ?>" onclick="event.stopPropagation()">
          <span id="xpPaletaScopeLabel" class="xp-paleta-scope-lbl">
            <i class="ti <?= $xpPaletaGlobalActivo ? 'ti-world' : 'ti-user-shield' ?>"></i>
            <span><?= $xpPaletaGlobalActivo ? 'Global (Toda la Web)' : 'Solo Super Admin' ?></span>
          </span>
          <label class="xp-bento-switch" title="Activar paleta global para toda la web">
            <input type="checkbox" id="switchTemaGlobalAdmin" <?= $xpPaletaGlobalActivo ? 'checked' : '' ?> onchange="toggleAlcancePaletaAdmin(this)" aria-label="Aplicar paleta de forma global">
            <span class="xp-bento-slider"></span>
          </label>
        </div>
      <?php endif; ?>

      <span class="xp-paleta-toggle-btn">
        <span id="xpPaletaCurrentNameBadge">Estilo Clásico</span>
        <i class="ti ti-chevron-down xp-paleta-chevron"></i>
      </span>
    </div>
  </div>

  <div class="xp-paleta-collapse-body" id="xpPaletaCollapseBody">
    <div class="xp-theme-grid">
      <!-- Tema 1: Atlas Clásico -->
      <div class="xp-theme-card" id="themeCardClassic" onclick="solicitarCambioTema('classic', 'Estilo Clásico Atlas Fit')">
        <div class="xp-theme-card-head">
          <div class="xp-theme-info">
            <div class="xp-theme-name">Estilo Clásico Atlas Fit</div>
            <div class="xp-theme-desc">Morado Eléctrico, estética nocturna y wallpapers góticos</div>
          </div>
          <span class="xp-theme-badge" id="badgeThemeClassic"><i class="ti ti-check"></i> Activo</span>
        </div>
        <div class="xp-theme-swatches">
          <span class="xp-swatch" style="background:#09090B;" title="Fondo #09090B"></span>
          <span class="xp-swatch" style="background:#121217;" title="Tarjetas #121217"></span>
          <span class="xp-swatch" style="background:#8a2be2;" title="Acento #8a2be2"></span>
          <span class="xp-swatch" style="background:#b388ff;" title="Luz #b388ff"></span>
          <span class="xp-swatch" style="background:#F4F4F5;" title="Texto #F4F4F5"></span>
        </div>
      </div>

      <!-- Tema 2: Atlas Obsidian -->
      <div class="xp-theme-card" id="themeCardObsidian" onclick="solicitarCambioTema('obsidian', 'Estilo Atlas Obsidian')">
        <div class="xp-theme-card-head">
          <div class="xp-theme-info">
            <div class="xp-theme-name">Estilo Atlas Obsidian</div>
            <div class="xp-theme-desc">Minimalismo puro, morado lavanda y fondo limpio</div>
          </div>
          <span class="xp-theme-badge" id="badgeThemeObsidian"><i class="ti ti-check"></i> Activo</span>
        </div>
        <div class="xp-theme-swatches">
          <span class="xp-swatch" style="background:#17151C;" title="#17151C"></span>
          <span class="xp-swatch" style="background:#292531;" title="#292531"></span>
          <span class="xp-swatch" style="background:#45404F;" title="#45404F"></span>
          <span class="xp-swatch" style="background:#716879;" title="#716879"></span>
          <span class="xp-swatch" style="background:#AAA2B3;" title="#AAA2B3"></span>
          <span class="xp-swatch" style="background:#E2DDE7;" title="#E2DDE7"></span>
        </div>
      </div>

      <!-- Tema 3: Deep Ocean -->
      <div class="xp-theme-card" id="themeCardOcean" onclick="solicitarCambioTema('deep-ocean', 'Estilo Deep Ocean')">
        <div class="xp-theme-card-head">
          <div class="xp-theme-info">
            <div class="xp-theme-name">Estilo Deep Ocean</div>
            <div class="xp-theme-desc">Abismo marino, acero y escarcha ártica</div>
          </div>
          <span class="xp-theme-badge" id="badgeThemeOcean"><i class="ti ti-check"></i> Activo</span>
        </div>
        <div class="xp-theme-swatches">
          <span class="xp-swatch" style="background:#06141B;" title="#06141B"></span>
          <span class="xp-swatch" style="background:#11212D;" title="#11212D"></span>
          <span class="xp-swatch" style="background:#253745;" title="#253745"></span>
          <span class="xp-swatch" style="background:#4A5C6A;" title="#4A5C6A"></span>
          <span class="xp-swatch" style="background:#9BA8AB;" title="#9BA8AB"></span>
          <span class="xp-swatch" style="background:#CCD0CF;" title="#CCD0CF"></span>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
window.XP_PALETA_ES_ADMIN = <?= $xpPaletaEsAdmin ? 'true' : 'false' ?>;
window.XP_PALETA_API_URL  = <?= json_encode($xpPaletaApiUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

function actualizarUiAlcancePaletaAdmin(isGlobal) {
  const box = document.getElementById('xpPaletaScopeBox');
  const lbl = document.getElementById('xpPaletaScopeLabel');
  const sub = document.getElementById('xpPaletaSubtitulo');
  if (box) box.classList.toggle('is-global', !!isGlobal);
  if (lbl) {
    lbl.innerHTML = `<i class="ti ${isGlobal ? 'ti-world' : 'ti-user-shield'}"></i><span>${isGlobal ? 'Global (Toda la Web)' : 'Solo Super Admin'}</span>`;
  }
  if (sub && window.XP_PALETA_ES_ADMIN) {
    sub.textContent = isGlobal
      ? 'Modo Global activo: se aplicará a toda la web (entrenadores, atletas y login)'
      : 'Modo Solo Super Admin: los cambios solo aplican a tu cuenta';
  }
}

async function toggleAlcancePaletaAdmin(chk) {
  if (!chk || !window.XP_PALETA_ES_ADMIN) return;
  const isGlobal = !!chk.checked;
  const currentTema = document.getElementById('htmlRoot')?.getAttribute('data-theme') || localStorage.getItem('xp_theme') || 'classic';
  const normalTema = (currentTema === 'dark') ? 'classic' : currentTema;

  chk.disabled = true;
  try {
    const res = await fetch(window.XP_PALETA_API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.CSRF || <?= json_encode($csrf, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?> },
      body: JSON.stringify({
        csrf_token: window.CSRF || <?= json_encode($csrf, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
        tipo: 'tema_alcance',
        alcance: isGlobal ? 'global' : 'personal',
        tema: normalTema
      })
    });
    const d = await res.json();
    if (d && d.ok) {
      actualizarUiAlcancePaletaAdmin(isGlobal);
      if (d.global_ver) {
        try { localStorage.setItem('xp_theme_gver', String(d.global_ver)); } catch (_) {}
      }
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: d.msg || (isGlobal ? 'Modo Global activado' : 'Modo Solo Super Admin activado'),
          showConfirmButton: false,
          timer: 2200,
          background: '#121217',
          color: '#fff'
        });
      }
    } else {
      chk.checked = !isGlobal;
      actualizarUiAlcancePaletaAdmin(!isGlobal);
      Swal.fire({ icon: 'error', title: 'Error', text: (d && d.msg) || 'No se pudo cambiar el alcance.', background: '#121217', color: '#fff' });
    }
  } catch (err) {
    chk.checked = !isGlobal;
    actualizarUiAlcancePaletaAdmin(!isGlobal);
    Swal.fire({ icon: 'error', title: 'Error de red', text: err.message || 'No se pudo conectar.', background: '#121217', color: '#fff' });
  } finally {
    chk.disabled = false;
  }
}

async function solicitarCambioTema(nuevoTema, nombre) {
  const currentTema = document.getElementById('htmlRoot')?.getAttribute('data-theme') || localStorage.getItem('xp_theme') || 'classic';
  const normalCurrent = (currentTema === 'dark') ? 'classic' : currentTema;
  const swGlobal = document.getElementById('switchTemaGlobalAdmin');
  const esGlobalAdmin = window.XP_PALETA_ES_ADMIN && swGlobal ? !!swGlobal.checked : false;

  if (normalCurrent === nuevoTema) {
    if (typeof Xp !== 'undefined' && Xp.toast) {
      Xp.toast('info', `El estilo "${nombre}" ya está activo.`);
    } else {
      Swal.fire({ icon: 'info', title: 'Tema actual', text: `El estilo "${nombre}" ya está activo.`, timer: 1500, showConfirmButton: false, background: '#121217', color: '#fff' });
    }
    return;
  }

  const descConfirm = window.XP_PALETA_ES_ADMIN
    ? (esGlobalAdmin
        ? 'Se aplicará a toda la web (Login, Super Admin, Entrenadores y Atletas).'
        : 'Se aplicará únicamente a tu vista de Super Admin.')
    : 'Se actualizarán los colores y fondos de pantalla en tu cuenta.';

  const result = await Swal.fire({
    title: `¿Cambiar al ${nombre}?`,
    text: descConfirm,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sí, cambiar estilo',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#8a2be2',
    cancelButtonColor: '#475569',
    background: '#121217',
    color: '#ffffff'
  });

  if (!result.isConfirmed) return;

  const rootEl = document.getElementById('htmlRoot');
  if (rootEl) rootEl.setAttribute('data-theme', nuevoTema);

  try {
    localStorage.setItem('xp_theme', nuevoTema);
    if (window.XP_AUTH_UID) {
      localStorage.setItem('xp_theme_uid', String(window.XP_AUTH_UID));
    }
  } catch (_) {}
  actualizarUISelectorTema(nuevoTema);
  purgarHtmlCachePorCambioTema();

  const token = window.CSRF || <?= json_encode($csrf, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  const payload = window.XP_PALETA_ES_ADMIN
    ? { tipo: 'tema_sistema', tema: nuevoTema, alcance: esGlobalAdmin ? 'global' : 'personal', csrf_token: token }
    : { tema: nuevoTema, csrf_token: token };

  try {
    const res = await fetch(window.XP_PALETA_API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data && data.ok) {
      if (data.global_ver) {
        try { localStorage.setItem('xp_theme_gver', String(data.global_ver)); } catch (_) {}
      }
      purgarHtmlCachePorCambioTema();
      const okMsg = data.msg || `¡${nombre} aplicado con éxito!`;
      if (typeof Xp !== 'undefined' && Xp.toast) {
        Xp.toast('success', okMsg);
      } else {
        Swal.fire({ icon: 'success', title: 'Estilo aplicado', text: okMsg, timer: 1600, showConfirmButton: false, background: '#121217', color: '#fff' });
      }
    } else if (data && data.msg) {
      Swal.fire({ icon: 'error', title: 'Error', text: data.msg, background: '#121217', color: '#fff' });
    }
  } catch (err) {
    console.warn('Error al guardar tema en backend:', err);
  }
}

function toggleXpPaletaCollapse(ev) {
  if (ev && ev.target && ev.target.closest('#xpPaletaScopeBox')) return;
  const card = document.getElementById('xpPaletaCfgCard');
  if (!card) return;
  const isOpen = card.classList.toggle('is-open');
  const hdr = card.querySelector('.xp-paleta-header-row');
  if (hdr) hdr.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
}

function purgarHtmlCachePorCambioTema() {
  if (!('caches' in window) || !window.XP_USER_CACHE) return;
  caches.open(window.XP_USER_CACHE).then(function(cache) {
    cache.keys().then(function(reqs) {
      reqs.forEach(function(req) {
        if (req.url.indexOf('/api-') === -1 && req.url.indexOf('/assets/') === -1) {
          cache.delete(req);
        }
      });
    });
  }).catch(function(){});
}

function actualizarUISelectorTema(tema) {
  const validos = ['classic', 'obsidian', 'deep-ocean'];
  let normTema = (tema === 'dark') ? 'classic' : String(tema || 'classic').toLowerCase();
  if (validos.indexOf(normTema) === -1) normTema = 'classic';
  const mapa = {
    'classic': 'themeCardClassic',
    'obsidian': 'themeCardObsidian',
    'deep-ocean': 'themeCardOcean'
  };
  const nombres = {
    'classic': 'Estilo Clásico',
    'obsidian': 'Atlas Obsidian',
    'deep-ocean': 'Deep Ocean'
  };
  Object.entries(mapa).forEach(([slug, elId]) => {
    const card = document.getElementById(elId);
    if (card) card.classList.toggle('is-active', (normTema === slug));
  });
  const badgeName = document.getElementById('xpPaletaCurrentNameBadge');
  if (badgeName) badgeName.textContent = nombres[normTema] || 'Estilo Clásico';
}

document.addEventListener('DOMContentLoaded', () => {
  const currentTema = document.getElementById('htmlRoot')?.getAttribute('data-theme') || localStorage.getItem('xp_theme') || 'classic';
  actualizarUISelectorTema(currentTema);
});
</script>

