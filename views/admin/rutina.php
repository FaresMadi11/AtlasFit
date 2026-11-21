<?php
// views/admin/rutina.php — Módulo 1: Rutina Personal del Superadmin (Híbrido Atleta + Entrenador)
$pageTitle = 'Mi Rutina';
require BASE_DIR . '/views/layout/header.php';

$diasMeta = [
    1 => ['num' => 1, 'corto' => 'Lun', 'largo' => 'Lunes',     'letra' => 'L'],
    2 => ['num' => 2, 'corto' => 'Mar', 'largo' => 'Martes',    'letra' => 'M'],
    3 => ['num' => 3, 'corto' => 'Mié', 'largo' => 'Miércoles', 'letra' => 'X'],
    4 => ['num' => 4, 'corto' => 'Jue', 'largo' => 'Jueves',    'letra' => 'J'],
    5 => ['num' => 5, 'corto' => 'Vie', 'largo' => 'Viernes',   'letra' => 'V'],
    6 => ['num' => 6, 'corto' => 'Sáb', 'largo' => 'Sábado',    'letra' => 'S'],
    7 => ['num' => 7, 'corto' => 'Dom', 'largo' => 'Domingo',   'letra' => 'D'],
];
?>

<!-- ── HEADER DE RUTINA (SOLO BOTÓN DE DESLOGUEO, SIN NOTIFICACIONES) ── -->
<div class="sys-top-header premium-header">
  <div class="user-info-group">
    <div class="rl-header-icon" style="width: 42px; height: 42px; border-radius: 12px; background: linear-gradient(135deg, var(--accent-primary, #8a2be2), var(--accent-hover, #9d4edd)); display: grid; place-items: center; color: #fff; flex-shrink: 0; box-shadow: 0 4px 14px rgba(138, 43, 226, 0.35); font-size: 1.25rem;">
      <i class="ti ti-barbell"></i>
    </div>
    <div class="user-text-wrap">
      <h1 class="sys-top-title user-name-gradient" title="Mi Rutina" style="font-size: 1.12rem !important; font-weight: 800; color: var(--text, #fff); margin: 0; line-height: 1.25;">
        <span class="user-name-text">Mi Rutina</span>
      </h1>
      <p style="font-size: .74rem; font-weight: 500; color: #94a3af; margin: 2px 0 0; letter-spacing: 0.2px; line-height: 1.2;">
        Programación y registro de entrenamiento personal
      </p>
    </div>
  </div>

  <div class="header-actions">
    <div id="xpPwaSyncPill" class="xp-pwa-header-pill online" title="Conectado a la red">
      <span class="xp-status-dot"></span><span class="xp-status-text">Online</span>
    </div>
    <div class="header-action-buttons">
      <?php $unread_hdr = function_exists('contarNotificacionesNoLeidas') ? contarNotificacionesNoLeidas((int)($_SESSION['user_id'] ?? 0), 'admin') : 0; ?>
      <div class="notif-wrapper" style="position:relative; flex-shrink: 0;">
        <button type="button" class="db-icon-btn btn-notif-trigger xp-bell-trigger" id="btnNotifToggle" title="Notificaciones" aria-label="Notificaciones" onclick="toggleXpNotifDropdown(this, event)" style="cursor:pointer; position:relative;">
          <i class="ti ti-bell"></i>
          <span class="xp-notif-badge" style="<?= $unread_hdr > 0 ? '' : 'display:none;' ?>"><?= $unread_hdr ?></span>
        </button>
      </div>
      <a href="<?= APP_URL ?>/auth/logout" class="db-icon-btn" title="Cerrar sesión" aria-label="Cerrar sesión" style="flex-shrink: 0;">
        <i class="ti ti-logout"></i>
      </a>
    </div>
  </div>
</div>

<style>
  .sa-rutina-shell {
    max-width: 1120px;
    margin: 0 auto;
    padding: 8px 4px 100px;
  }

  /* Contenedor principal idéntico a la Imagen 1 (#calRutinaDiaInline) */
  .cal-rutina-inline {
    background: linear-gradient(165deg, rgba(22, 20, 38, 0.96), rgba(14, 13, 24, 0.98));
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 22px;
    padding: 24px 26px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45);
    color: #f8fafc;
  }

  .cri-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
    flex-wrap: wrap;
    padding-bottom: 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.07);
    margin-bottom: 16px;
  }

  .cri-kicker {
    display: block;
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.9px;
    color: #a855f7;
    margin-bottom: 4px;
  }

  .cri-fecha {
    margin: 0;
    font-size: 1.38rem;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: -0.3px;
  }

  .cri-head-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
  }

  .cri-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 13px;
    border-radius: 99px;
    font-size: 0.76rem;
    font-weight: 700;
    border: 1px solid transparent;
  }
  .cri-pill.is-programado {
    background: rgba(138, 43, 226, 0.16);
    color: #d8b4fe;
    border-color: rgba(138, 43, 226, 0.42);
  }
  .cri-pill.is-ok {
    background: rgba(34, 197, 94, 0.16);
    color: #4ade80;
    border-color: rgba(34, 197, 94, 0.4);
  }
  .cri-pill.is-warn {
    background: rgba(245, 158, 11, 0.16);
    color: #fbbf24;
    border-color: rgba(245, 158, 11, 0.4);
  }
  .cri-pill.is-none {
    background: rgba(239, 68, 68, 0.13);
    color: #fca5a5;
    border-color: rgba(239, 68, 68, 0.32);
  }

  .cri-btn-delete-routine {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 13px;
    border-radius: 10px;
    font-size: 0.76rem;
    font-weight: 700;
    background: rgba(239, 68, 68, 0.12);
    color: #f87171;
    border: 1px solid rgba(239, 68, 68, 0.32);
    cursor: pointer;
    transition: all 0.18s ease;
  }
  .cri-btn-delete-routine:hover {
    background: rgba(239, 68, 68, 0.22);
    color: #fca5a5;
  }

  /* Barra de días de la semana */
  .cri-days-top-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 14px;
  }

  .cri-days-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
  }

  .cri-day-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 15px;
    border-radius: 12px;
    font-size: 0.82rem;
    font-weight: 700;
    background: rgba(255, 255, 255, 0.04);
    color: #94a3b8;
    border: 1px solid rgba(255, 255, 255, 0.08);
    cursor: pointer;
    transition: all 0.18s ease;
  }
  .cri-day-chip:hover {
    background: rgba(138, 43, 226, 0.12);
    color: #e2e8f0;
    border-color: rgba(138, 43, 226, 0.35);
  }
  .cri-day-chip.has-routine {
    color: #e2e8f0;
    border-color: rgba(168, 85, 247, 0.3);
  }
  .cri-day-chip.active {
    background: linear-gradient(135deg, #9333ea, #7e22ce);
    color: #ffffff;
    border-color: rgba(192, 132, 252, 0.65);
    box-shadow: 0 6px 18px rgba(138, 43, 226, 0.4);
  }

  /* Subheader de botones Guardar rutina / Actualizar / Asignar */
  .cri-subhead {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 16px;
  }
  .cri-subhead-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: auto;
    flex-wrap: wrap;
  }

  .cri-btn-save-tpl,
  .cri-btn-update-routine,
  .cri-btn-assign-new {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 15px;
    border-radius: 11px;
    font-size: 0.8rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.18s ease;
  }

  .cri-btn-save-tpl {
    background: rgba(138, 43, 226, 0.12);
    color: #d8b4fe;
    border: 1px solid rgba(138, 43, 226, 0.38);
  }
  .cri-btn-save-tpl:hover {
    background: rgba(138, 43, 226, 0.24);
    color: #ffffff;
  }

  .cri-btn-update-routine {
    background: rgba(138, 43, 226, 0.22);
    color: #f3e8ff;
    border: 1px solid rgba(168, 85, 247, 0.55);
  }
  .cri-btn-update-routine:hover,
  .cri-btn-update-routine.is-editing {
    background: linear-gradient(135deg, #8a2be2, #6d28d9);
    color: #ffffff;
    border-color: #a855f7;
  }

  .cri-btn-assign-new {
    background: linear-gradient(135deg, #8a2be2, #6d28d9);
    color: #ffffff;
    border: 1px solid rgba(192, 132, 252, 0.45);
    box-shadow: 0 6px 18px rgba(138, 43, 226, 0.3);
  }
  .cri-btn-assign-new:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 22px rgba(138, 43, 226, 0.42);
  }

  /* Bloque Cardio idéntico a la Imagen 1 */
  .cri-cardio-banner {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 15px 18px;
    border-radius: 15px;
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.13), rgba(249, 115, 22, 0.06));
    border: 1px solid rgba(239, 68, 68, 0.28);
    margin-bottom: 14px;
  }
  .cri-cardio-icon {
    width: 38px;
    height: 38px;
    border-radius: 11px;
    background: rgba(239, 68, 68, 0.18);
    color: #f87171;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
  }

  /* Lista de ejercicios en acordeón idéntica a la Imagen 1 */
  .cri-ejercicios-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .cri-ej-accordion {
    background: rgba(255, 255, 255, 0.025);
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 14px;
    overflow: hidden;
    transition: border-color 0.2s ease, background 0.2s ease;
  }
  .cri-ej-accordion.is-open {
    border-color: rgba(168, 85, 247, 0.4);
    background: rgba(22, 20, 38, 0.85);
  }
  .cri-ej-accordion.all-done {
    border-color: rgba(34, 197, 94, 0.38);
  }

  .cri-ej-summary {
    width: 100%;
    background: transparent;
    border: none;
    padding: 15px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    color: #f8fafc;
    cursor: pointer;
    text-align: left;
  }
  .cri-ej-summary:hover {
    background: rgba(255, 255, 255, 0.02);
  }

  .cri-ej-summary-left {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
  }

  .cri-ej-orden {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.06);
    color: #94a3b8;
    font-size: 0.78rem;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .cri-ej-accordion.all-done .cri-ej-orden {
    background: rgba(34, 197, 94, 0.2);
    color: #4ade80;
  }

  .cri-ej-nombre {
    font-size: 0.92rem;
    font-weight: 800;
    color: #ffffff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .cri-ej-summary-right {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
  }

  .cri-ej-progress-pill {
    font-size: 0.72rem;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 99px;
    background: rgba(255, 255, 255, 0.05);
    color: #94a3b8;
    border: 1px solid rgba(255, 255, 255, 0.08);
  }
  .cri-ej-progress-pill.is-done {
    background: rgba(34, 197, 94, 0.16);
    color: #4ade80;
    border-color: rgba(34, 197, 94, 0.35);
  }

  .cri-ej-chevron {
    color: #94a3b8;
    font-size: 1.05rem;
    transition: transform 0.2s ease;
  }
  .cri-ej-accordion.is-open .cri-ej-chevron {
    transform: rotate(180deg);
    color: #c084fc;
  }

  .cri-ej-details {
    display: none;
    padding: 4px 18px 18px;
    border-top: 1px solid rgba(255, 255, 255, 0.05);
  }
  .cri-ej-accordion.is-open .cri-ej-details {
    display: block;
  }

  /* Tabla de registro en vivo (Modo Atleta + Entrenador) */
  .sa-series-grid {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 10px;
  }
  .sa-serie-row {
    display: grid;
    grid-template-columns: 44px 1fr 1fr 44px;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    border-radius: 12px;
    background: rgba(15, 14, 26, 0.75);
    border: 1px solid rgba(255, 255, 255, 0.07);
    transition: all 0.18s ease;
  }
  .sa-serie-row.is-completed {
    background: rgba(34, 197, 94, 0.08);
    border-color: rgba(34, 197, 94, 0.32);
  }
  .sa-serie-num {
    width: 32px;
    height: 32px;
    border-radius: 9px;
    background: rgba(138, 43, 226, 0.16);
    color: #d8b4fe;
    font-size: 0.78rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .sa-serie-row.is-completed .sa-serie-num {
    background: rgba(34, 197, 94, 0.22);
    color: #4ade80;
  }
  .sa-serie-field {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    padding: 6px 10px;
  }
  .sa-serie-field input {
    width: 100%;
    background: transparent;
    border: none;
    color: #ffffff;
    font-size: 0.92rem;
    font-weight: 800;
    text-align: center;
    outline: none;
  }
  .sa-serie-unit {
    font-size: 0.7rem;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    flex-shrink: 0;
  }
  .sa-btn-check-serie {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.14);
    background: rgba(255, 255, 255, 0.05);
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    cursor: pointer;
    transition: all 0.18s ease;
  }
  .sa-btn-check-serie:hover {
    border-color: #a855f7;
    color: #d8b4fe;
  }
  .sa-serie-row.is-completed .sa-btn-check-serie {
    background: #22c55e;
    border-color: #22c55e;
    color: #052e16;
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.35);
  }

  /* Editor de Rutina (Modo Entrenador) */
  .sa-editor-card {
    background: rgba(15, 14, 26, 0.85);
    border: 1px solid rgba(168, 85, 247, 0.28);
    border-radius: 16px;
    padding: 16px;
    margin-bottom: 12px;
  }
  .sa-imp-chips {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin: 8px 0;
  }
  .sa-imp-chip {
    padding: 5px 10px;
    border-radius: 8px;
    font-size: 0.74rem;
    font-weight: 700;
    background: rgba(255, 255, 255, 0.04);
    color: #94a3b8;
    border: 1px solid rgba(255, 255, 255, 0.1);
    cursor: pointer;
  }
  .sa-imp-chip.active {
    background: rgba(138, 43, 226, 0.22);
    color: #e9d5ff;
    border-color: #a855f7;
  }
</style>

<div class="sa-rutina-shell">
  <div id="saRutinaInlineContainer" class="cal-rutina-inline">
    <!-- Renderizado dinámico por JS -->
    <div style="text-align:center;padding:36px 16px;color:#94a3b8;">
      <i class="ti ti-loader" style="font-size:1.8rem;color:#a855f7;"></i>
      <p style="margin:8px 0 0;font-size:0.88rem;">Cargando tu rutina...</p>
    </div>
  </div>
</div>

<!-- Datalist del catálogo de ejercicios para autocompletado rápido -->
<datalist id="saCatalogoEjerciciosList">
  <?php foreach ($catalogoEjercicios as $catEj): ?>
    <option value="<?= e($catEj['nombre']) ?>"><?= e($catEj['grupo_muscular'] ?? '') ?></option>
  <?php endforeach; ?>
</datalist>

<script>
const SA_CATALOGO = <?= json_encode($catalogoEjercicios, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const SA_PLANTILLAS = <?= json_encode($plantillasCatalogo, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const SA_DIAS_META = [
  { num: 1, corto: 'Lun', largo: 'Lunes', letra: 'L' },
  { num: 2, corto: 'Mar', largo: 'Martes', letra: 'M' },
  { num: 3, corto: 'Mié', largo: 'Miércoles', letra: 'X' },
  { num: 4, corto: 'Jue', largo: 'Jueves', letra: 'J' },
  { num: 5, corto: 'Vie', largo: 'Viernes', letra: 'V' },
  { num: 6, corto: 'Sáb', largo: 'Sábado', letra: 'S' },
  { num: 7, corto: 'Dom', largo: 'Domingo', letra: 'D' }
];

let saCurrentFecha = '<?= date('Y-m-d') ?>';
let saCurrentData  = null;
let saEditMode     = false;
let saCreateMode   = false;
let saDraftData    = null;

function saEsc(str) {
  return String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function saFechaPorDiaSemana(targetDow) {
  const ref = new Date(saCurrentFecha + 'T12:00:00');
  const currentDow = ref.getDay() === 0 ? 7 : ref.getDay();
  const diff = targetDow - currentDow;
  ref.setDate(ref.getDate() + diff);
  const y = ref.getFullYear();
  const m = String(ref.getMonth() + 1).padStart(2, '0');
  const d = String(ref.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}`;
}

function saFormatearFechaLarga(isoDate) {
  const d = new Date(isoDate + 'T12:00:00');
  if (isNaN(d.getTime())) return isoDate;
  const str = d.toLocaleDateString('es-ES', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
  return str.charAt(0).toUpperCase() + str.slice(1);
}

async function saCargarDia(fecha) {
  saCurrentFecha = fecha;
  saEditMode = false;
  saCreateMode = false;
  const box = document.getElementById('saRutinaInlineContainer');
  try {
    const res = await fetch(`${APP}/admin/api-dia-detalle?fecha=${encodeURIComponent(fecha)}&_t=${Date.now()}`, {
      cache: 'no-store'
    });
    const data = await res.json();
    saCurrentData = data;
    saRender();
  } catch (err) {
    if (box) {
      box.innerHTML = `<div style="padding:24px;text-align:center;color:#f87171;">Error al cargar la rutina del día.</div>`;
    }
  }
}

function saRender() {
  const box = document.getElementById('saRutinaInlineContainer');
  if (!box || !saCurrentData) return;

  if (saCreateMode || saEditMode) {
    saRenderEditor(box);
    return;
  }

  const data = saCurrentData;
  const fechaBonita = saFormatearFechaLarga(saCurrentFecha);
  const dtObj = new Date(saCurrentFecha + 'T12:00:00');
  const currentDow = dtObj.getDay() === 0 ? 7 : dtObj.getDay();
  const diasProg = Array.isArray(data.dias_programados) ? data.dias_programados.map(Number) : [];

  const tieneRutina = !!(data.rutina && data.rutina.id && Array.isArray(data.ejercicios) && data.ejercicios.length > 0);
  const asistio = !!data.asistio;

  let pillClass = 'is-none';
  let pillIcon  = 'ti-calendar-off';
  let pillText  = 'Sin Rutina';
  if (asistio) {
    pillClass = 'is-ok';
    pillIcon  = 'ti-circle-check';
    pillText  = 'Completado';
  } else if (tieneRutina) {
    pillClass = 'is-programado';
    pillIcon  = 'ti-barbell';
    pillText  = 'Programado';
  }

  // Barra de días (si hay días programados se muestran resaltados, y siempre permite cambiar entre Lun..Dom)
  const mostrarSoloProgramados = false;
  let daysHtml = `<div class="cri-days-top-row"><div class="cri-days-bar">`;
  SA_DIAS_META.forEach(dm => {
    const isCurrent = dm.num === currentDow;
    const isProg    = diasProg.includes(dm.num);
    const fTarget   = saFechaPorDiaSemana(dm.num);
    daysHtml += `
      <button type="button"
              class="cri-day-chip ${isCurrent ? 'active' : ''} ${isProg ? 'has-routine' : ''}"
              onclick="saCargarDia('${fTarget}')">
        <i class="ti ${isProg ? 'ti-calendar-check' : 'ti-calendar-event'}"></i>
        <span>${dm.corto}</span>
      </button>`;
  });
  daysHtml += `</div>`;
  daysHtml += `
    <button type="button" class="cri-btn-assign-new" onclick="saAbrirCrearRutina(${currentDow})">
      <i class="ti ti-plus"></i> Asignar / Crear Rutina
    </button>
  </div>`;

  // Subhead con Guardar rutina y Actualizar (idéntico a Imagen 1)
  let subheadHtml = '';
  if (tieneRutina) {
    subheadHtml = `
      <div class="cri-subhead">
        <div style="font-size:0.84rem;color:#94a3b8;font-weight:600;">
          ${data.rutina.nombre ? `<i class="ti ti-Clipboard-text" style="color:#a855f7;"></i> ${saEsc(data.rutina.nombre)}` : ''}
        </div>
        <div class="cri-subhead-actions">
          <button type="button" class="cri-btn-save-tpl" onclick="saGuardarComoPlantilla(${Number(data.rutina.id)})">
            <i class="ti ti-device-floppy"></i> Guardar rutina
          </button>
          <button type="button" class="cri-btn-update-routine" onclick="saActivarModoEdicion()">
            <i class="ti ti-pencil"></i> Actualizar
          </button>
        </div>
      </div>`;
  }

  // Bloque Cardio (idéntico a Imagen 1)
  let cardioHtml = '';
  if (tieneRutina && Number(data.rutina.cardio_activo || 0) === 1) {
    const cMin = Number(data.rutina.cardio_tiempo || 0);
    const cInd = String(data.rutina.cardio_indicaciones || '').trim();
    cardioHtml = `
      <div class="cri-cardio-banner">
        <div class="cri-cardio-icon">
          <i class="ti ti-flame"></i>
        </div>
        <div style="flex:1;min-width:0;">
          <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <span style="font-size:0.88rem;font-weight:800;color:#ffffff;">Trote / Cardio</span>
            ${cMin > 0 ? `<span style="font-size:0.82rem;font-weight:800;color:#f87171;">· ${cMin} min</span>` : ''}
          </div>
          ${cInd ? `<div style="font-size:0.78rem;color:#cbd5e1;margin-top:2px;white-space:pre-line;line-height:1.35;">${saEsc(cInd)}</div>` : ''}
        </div>
      </div>`;
  }

  // Lista de ejercicios en acordeón (con registro de series en vivo al expandir)
  let bodyHtml = '';
  if (!tieneRutina) {
    bodyHtml = `
      <div style="text-align:center;padding:42px 20px;background:rgba(255,255,255,0.015);border:1px dashed rgba(255,255,255,0.08);border-radius:18px;margin-top:8px;">
        <div style="width:54px;height:54px;border-radius:16px;background:rgba(239,68,68,0.12);color:#f87171;display:inline-flex;align-items:center;justify-content:center;font-size:1.6rem;margin-bottom:10px;">
          <i class="ti ti-calendar-off"></i>
        </div>
        <h4 style="margin:0 0 6px;font-size:1.05rem;font-weight:800;color:#ffffff;">Sin Rutina Programada</h4>
        <p style="margin:0 0 18px;font-size:0.85rem;color:#94a3b8;">No tienes una rutina asignada para este día. Asígnate una rutina nueva o carga una plantilla.</p>
        <button type="button" class="cri-btn-assign-new" onclick="saAbrirCrearRutina(${currentDow})">
          <i class="ti ti-plus"></i> Asignar Mi Rutina para ${SA_DIAS_META[currentDow - 1].largo}
        </button>
      </div>`;
  } else {
    bodyHtml = `<div class="cri-ejercicios-list">`;
    data.ejercicios.forEach((ej, idx) => {
      const ejId = Number(ej.id || 0);
      const rawSeries = Array.isArray(ej.series_detalle) && ej.series_detalle.length > 0
        ? ej.series_detalle
        : (Array.isArray(ej.series) && ej.series.length > 0 ? ej.series : []);
      const seriesArr = rawSeries;
      const totalS = seriesArr.length || Number(ej.series_total || 3);
      const hechasS = seriesArr.filter(s => s.completado).length;
      const allDone = totalS > 0 && hechasS >= totalS;

      const impRaw = String(ej.tipo_implemento || 'barra').toLowerCase();
      const esSinPeso = impRaw === 'sin_peso';
      const uniRaw = String(ej.unidad_carga || 'kg').toLowerCase();
      const uniLabel = esSinPeso ? 'CORP' : (uniRaw === 'placas' ? 'PLACAS' : (uniRaw === 'lbs' ? (impRaw === 'mancuerna' ? 'LBS c/u ×2' : 'LBS') : (impRaw === 'mancuerna' ? 'KG c/u ×2' : 'KG')));
      const descansoSeg = Number(ej.descanso_segundos || 0);

      let seriesRowsHtml = '';
      let workIdx = 0;
      for (let i = 0; i < totalS; i++) {
        const sObj = seriesArr[i] || { numero: i + 1, reps: ej.repeticiones || 10, peso: '', completado: false, tipo: 'normal' };
        const sNum = Number(sObj.numero || (i + 1));
        const sTipo = String(sObj.tipo || sObj.tipo_serie || 'normal').toLowerCase();
        if (sTipo !== 'calentamiento') workIdx++;
        const sBadgeTxt = sTipo === 'calentamiento' ? 'W' : (sTipo === 'fallo' ? 'F' : (sTipo === 'drop' ? 'D' : String(workIdx || sNum)));
        const sBadgeStyle = sTipo === 'calentamiento'
          ? 'background:rgba(251,191,36,0.18);color:#fbbf24;border:1px solid rgba(251,191,36,0.35);'
          : (sTipo === 'fallo'
              ? 'background:rgba(248,113,113,0.18);color:#f87171;border:1px solid rgba(248,113,113,0.35);'
              : (sTipo === 'drop'
                  ? 'background:rgba(96,165,250,0.18);color:#60a5fa;border:1px solid rgba(96,165,250,0.35);'
                  : ''));
        const isComp = !!sObj.completado;
        const pVal = (sObj.peso !== null && sObj.peso !== undefined && Number(sObj.peso) > 0) ? sObj.peso : '';
        const rVal = (sObj.reps !== null && sObj.reps !== undefined && String(sObj.reps).trim() !== '') ? sObj.reps : (ej.repeticiones || 10);

        seriesRowsHtml += `
          <div class="sa-serie-row ${isComp ? 'is-completed' : ''}" id="saSerieRow_${ejId}_${sNum}">
            <div class="sa-serie-num" style="${sBadgeStyle}" title="Tipo: ${saEsc(sTipo)}">${sBadgeTxt}</div>
            ${esSinPeso ? `
              <div class="sa-serie-field" style="justify-content:center;color:#94a3b8;font-size:0.78rem;font-weight:700;">
                Peso Corporal
                <input type="hidden" id="saInpPeso_${ejId}_${sNum}" value="0">
              </div>
            ` : `
              <div class="sa-serie-field">
                <input type="number" step="0.5" min="0" id="saInpPeso_${ejId}_${sNum}" value="${saEsc(pVal)}" placeholder="0">
                <span class="sa-serie-unit">${uniLabel}</span>
              </div>
            `}
            <div class="sa-serie-field">
              <input type="number" step="1" min="1" id="saInpReps_${ejId}_${sNum}" value="${saEsc(rVal)}" placeholder="10">
              <span class="sa-serie-unit">REPS</span>
            </div>
            <button type="button"
                    class="sa-btn-check-serie"
                    title="${isComp ? 'Desmarcar serie' : 'Registrar serie'}"
                    onclick="saToggleSerie(${ejId}, ${sNum}, '${impRaw}', '${uniRaw}', ${isComp ? 1 : 0}, this)">
              <i class="ti ti-check"></i>
            </button>
          </div>`;
      }

      bodyHtml += `
        <div class="cri-ej-accordion ${allDone ? 'all-done' : ''}" id="saAcordEj_${ejId}">
          <button type="button" class="cri-ej-summary" onclick="document.getElementById('saAcordEj_${ejId}').classList.toggle('is-open')">
            <div class="cri-ej-summary-left">
              <span class="cri-ej-orden">${idx + 1}</span>
              <span class="cri-ej-nombre">${saEsc(ej.nombre)}</span>
            </div>
            <div class="cri-ej-summary-right">
              <span class="cri-ej-progress-pill ${allDone ? 'is-done' : ''}">${hechasS}/${totalS} series</span>
              <span class="cri-ej-chevron"><i class="ti ti-chevron-down"></i></span>
            </div>
          </button>
          <div class="cri-ej-details">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;margin-top:6px;margin-bottom:8px;font-size:0.76rem;color:#94a3b8;">
              <span><i class="ti ti-barbell" style="color:#a855f7;"></i> Meta: <strong>${totalS} series × ${saEsc(ej.repeticiones || '10')} reps</strong> ${!ej.rir_desmarcado && ej.rir !== null && ej.rir !== undefined && ej.rir !== '' ? `· RIR ${saEsc(ej.rir)}` : ''}${descansoSeg > 0 ? ` · <i class="ti ti-clock"></i> ${descansoSeg}s` : ''}</span>
              <span>Unidad: <strong style="color:#e2e8f0;">${uniLabel}</strong></span>
            </div>
            <div class="sa-series-grid">
              ${seriesRowsHtml}
            </div>
          </div>
        </div>`;
    });
    bodyHtml += `</div>`;
  }

  box.innerHTML = `
    <div class="cri-head">
      <div>
        <span class="cri-kicker">RUTINA ASIGNADA</span>
        <h4 class="cri-fecha">${saEsc(fechaBonita)}</h4>
      </div>
      <div class="cri-head-actions">
        <span class="cri-pill ${pillClass}">
          <i class="ti ${pillIcon}"></i> ${pillText}
        </span>
        ${tieneRutina ? `
          <button type="button" class="cri-btn-delete-routine" onclick="saConfirmarEliminarRutina(${Number(data.rutina.id)})">
            <i class="ti ti-trash"></i> Eliminar
          </button>
        ` : ''}
      </div>
    </div>
    ${daysHtml}
    ${subheadHtml}
    ${cardioHtml}
    ${bodyHtml}
  `;
}

async function saToggleSerie(ejercicioId, numSerie, imp, unidad, estabaCompletada, btnEl) {
  const inpPeso = document.getElementById(`saInpPeso_${ejercicioId}_${numSerie}`);
  const inpReps = document.getElementById(`saInpReps_${ejercicioId}_${numSerie}`);
  const peso = inpPeso ? inpPeso.value : '0';
  const reps = inpReps ? inpReps.value : '10';

  if (btnEl) btnEl.disabled = true;
  try {
    const res = await fetch(`${APP}/admin/api-guardar-serie`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify({
        csrf_token: CSRF,
        fecha: saCurrentFecha,
        ejercicio_id: ejercicioId,
        numero_serie: numSerie,
        peso: peso,
        reps: reps,
        tipo_implemento: imp,
        unidad_carga: unidad,
        accion: estabaCompletada ? 'desmarcar' : 'guardar'
      })
    });
    const d = await res.json();
    if (!d.ok) {
      Swal.fire({ icon: 'error', title: 'Error', text: d.msg || 'No se pudo registrar la serie.', background: '#12111a', color: '#fff' });
    } else {
      const openIds = Array.from(document.querySelectorAll('.cri-ej-accordion.is-open')).map(el => el.id);
      await saCargarDia(saCurrentFecha);
      openIds.forEach(id => document.getElementById(id)?.classList.add('is-open'));
    }
  } catch (e) {
    Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo conectar con el servidor.', background: '#12111a', color: '#fff' });
  } finally {
    if (btnEl) btnEl.disabled = false;
  }
}

function saActivarModoEdicion() {
  if (!saCurrentData || !saCurrentData.rutina) return;
  const rId = Number(saCurrentData.rutina.id || 0);
  if (rId > 0) {
    window.location.href = `${APP}/admin/crear-rutina?editar=${rId}`;
    return;
  }
}

function saAbrirCrearRutina(defaultDow) {
  const dow = Number(defaultDow || 0);
  window.location.href = `${APP}/admin/crear-rutina` + (dow >= 1 && dow <= 7 ? `?dia=${dow}` : '');
}

function saCargarPlantillaEnBorrador(plId) {
  if (!plId) return;
  fetch(`${APP}/admin/api-listar-plantillas-catalogo`)
    .then(r => r.json())
    .then(d => {
      if (!d.ok || !Array.isArray(d.plantillas)) return;
      const pl = d.plantillas.find(p => Number(p.id) === Number(plId));
      if (!pl) return;
      saDraftData.titulo = pl.titulo || saDraftData.titulo;
      saDraftData.cardio_activo = Number(pl.cardio_activo || 0) === 1;
      saDraftData.cardio_tiempo = Number(pl.cardio_tiempo || 10);
      saDraftData.cardio_indicaciones = String(pl.cardio_indicaciones || '');
      if (Array.isArray(pl.ejercicios) && pl.ejercicios.length > 0) {
        saDraftData.ejercicios = pl.ejercicios.map(ex => ({
          id: 0,
          nombre: ex.nombre || '',
          tipo_implemento: ex.tipo_implemento || 'barra',
          unidad_carga: ex.unidad_carga || 'kg',
          rir: 2,
          series_detalle: (Array.isArray(ex.series) && ex.series.length > 0 ? ex.series : [{ kg_objetivo: '', reps_objetivo: 10 }, { kg_objetivo: '', reps_objetivo: 10 }, { kg_objetivo: '', reps_objetivo: 10 }]).map((s, idx) => ({
            numero_serie: idx + 1,
            peso: s.kg_objetivo !== null && s.kg_objetivo !== undefined ? s.kg_objetivo : '',
            reps: s.reps_objetivo || 10
          }))
        }));
      }
      saRender();
    });
}

function saRenderEditor(box) {
  const d = saDraftData;
  const esNuevo = saCreateMode;

  let diasSelectorHtml = '';
  SA_DIAS_META.forEach(dm => {
    const sel = d.dias_semana.includes(dm.num);
    diasSelectorHtml += `
      <button type="button"
              class="cri-day-chip ${sel ? 'active' : ''}"
              onclick="saToggleDiaBorrador(${dm.num})">
        ${dm.corto}
      </button>`;
  });

  let ejsHtml = '';
  d.ejercicios.forEach((ej, idx) => {
    const imp = ej.tipo_implemento || 'barra';
    const uni = ej.unidad_carga || 'kg';
    const esSinPeso = imp === 'sin_peso';

    let sRows = '';
    ej.series_detalle.forEach((s, sIdx) => {
      sRows += `
        <div style="display:grid;grid-template-columns:38px 1fr 1fr 34px;gap:8px;align-items:center;margin-top:6px;">
          <span style="font-size:0.75rem;font-weight:800;color:#a855f7;text-align:center;">S${sIdx + 1}</span>
          ${esSinPeso ? `
            <span style="font-size:0.76rem;color:#94a3b8;text-align:center;">Corporal</span>
          ` : `
            <input type="number" step="0.5" min="0" value="${saEsc(s.peso)}" placeholder="Peso (${uni.toUpperCase()})"
                   oninput="saDraftData.ejercicios[${idx}].series_detalle[${sIdx}].peso = this.value"
                   style="width:100%;padding:7px 10px;border-radius:9px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.12);color:#fff;font-weight:700;font-size:0.84rem;">
          `}
          <input type="number" step="1" min="1" value="${saEsc(s.reps)}" placeholder="Reps"
                 oninput="saDraftData.ejercicios[${idx}].series_detalle[${sIdx}].reps = this.value"
                 style="width:100%;padding:7px 10px;border-radius:9px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.12);color:#fff;font-weight:700;font-size:0.84rem;">
          <button type="button" onclick="saQuitarSerieBorrador(${idx}, ${sIdx})" style="background:rgba(239,68,68,0.14);border:1px solid rgba(239,68,68,0.3);color:#f87171;border-radius:8px;height:32px;cursor:pointer;">
            <i class="ti ti-x"></i>
          </button>
        </div>`;
    });

    ejsHtml += `
      <div class="sa-editor-card">
        <div style="display:flex;align-items:center;gap:10px;justify-content:space-between;margin-bottom:8px;">
          <span style="font-size:0.78rem;font-weight:800;color:#c084fc;">EJERCICIO #${idx + 1}</span>
          <button type="button" onclick="saQuitarEjercicioBorrador(${idx})" style="background:rgba(239,68,68,0.14);border:1px solid rgba(239,68,68,0.3);color:#f87171;border-radius:8px;padding:4px 9px;font-size:0.74rem;font-weight:700;cursor:pointer;">
            <i class="ti ti-trash"></i> Quitar
          </button>
        </div>
        <input type="text" list="saCatalogoEjerciciosList" value="${saEsc(ej.nombre)}" placeholder="Escribe o elige un ejercicio (ej. Press Inclinado)..."
               oninput="saDraftData.ejercicios[${idx}].nombre = this.value"
               style="width:100%;padding:10px 13px;border-radius:11px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.14);color:#fff;font-weight:700;font-size:0.9rem;margin-bottom:8px;">

        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
          <div class="sa-imp-chips">
            <button type="button" class="sa-imp-chip ${imp === 'mancuerna' ? 'active' : ''}" onclick="saSetImpBorrador(${idx}, 'mancuerna')">Mancuernas</button>
            <button type="button" class="sa-imp-chip ${imp === 'barra' ? 'active' : ''}" onclick="saSetImpBorrador(${idx}, 'barra')">Barra / Libre</button>
            <button type="button" class="sa-imp-chip ${imp === 'polea_maquina' ? 'active' : ''}" onclick="saSetImpBorrador(${idx}, 'polea_maquina')">Polea / Máq</button>
            <button type="button" class="sa-imp-chip ${imp === 'sin_peso' ? 'active' : ''}" onclick="saSetImpBorrador(${idx}, 'sin_peso')">Sin peso</button>
          </div>
          ${!esSinPeso ? `
            <div class="sa-imp-chips">
              <button type="button" class="sa-imp-chip ${uni === 'kg' ? 'active' : ''}" onclick="saSetUniBorrador(${idx}, 'kg')">KG</button>
              <button type="button" class="sa-imp-chip ${uni === 'lbs' ? 'active' : ''}" onclick="saSetUniBorrador(${idx}, 'lbs')">LBS</button>
              <button type="button" class="sa-imp-chip ${uni === 'placas' ? 'active' : ''}" onclick="saSetUniBorrador(${idx}, 'placas')">Placas</button>
            </div>
          ` : ''}
        </div>

        ${sRows}

        <button type="button" onclick="saAgregarSerieBorrador(${idx})" style="margin-top:10px;width:100%;padding:7px;border-radius:9px;background:rgba(138,43,226,0.12);border:1px dashed rgba(168,85,247,0.4);color:#d8b4fe;font-size:0.76rem;font-weight:700;cursor:pointer;">
          <i class="ti ti-plus"></i> Agregar Serie
        </button>
      </div>`;
  });

  box.innerHTML = `
    <div class="cri-head">
      <div>
        <span class="cri-kicker">${esNuevo ? 'ASIGNAR NUEVA RUTINA PERSONAL' : 'MODO EDICIÓN DE RUTINA'}</span>
        <h4 class="cri-fecha">${saEsc(saFormatearFechaLarga(saCurrentFecha))}</h4>
      </div>
      <div class="cri-head-actions">
        <button type="button" class="cri-btn-delete-routine" onclick="saEditMode=false;saCreateMode=false;saRender();">
          <i class="ti ti-x"></i> Cancelar
        </button>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px;margin-bottom:14px;">
      <div>
        <label style="display:block;font-size:0.72rem;font-weight:800;color:#94a3b8;text-transform:uppercase;margin-bottom:5px;">Nombre / Enfoque de la Rutina</label>
        <input type="text" value="${saEsc(d.titulo)}" oninput="saDraftData.titulo = this.value" placeholder="Ej: Torso / Empuje / Pierna"
               style="width:100%;padding:10px 13px;border-radius:11px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.14);color:#fff;font-weight:700;font-size:0.9rem;">
      </div>
      ${SA_PLANTILLAS.length > 0 ? `
      <div>
        <label style="display:block;font-size:0.72rem;font-weight:800;color:#94a3b8;text-transform:uppercase;margin-bottom:5px;">Cargar desde Plantilla Guardada</label>
        <select onchange="saCargarPlantillaEnBorrador(this.value)" style="width:100%;padding:10px 13px;border-radius:11px;background:#141320;border:1px solid rgba(255,255,255,0.14);color:#fff;font-weight:700;font-size:0.86rem;">
          <option value="">-- Seleccionar plantilla opcional --</option>
          ${SA_PLANTILLAS.map(pl => `<option value="${Number(pl.id)}">${saEsc(pl.titulo)} (${Number(pl.total_ejercicios || 0)} ej.)</option>`).join('')}
        </select>
      </div>
      ` : ''}
    </div>

    <div style="margin-bottom:14px;">
      <label style="display:block;font-size:0.72rem;font-weight:800;color:#94a3b8;text-transform:uppercase;margin-bottom:6px;">Días de la Semana Asignados</label>
      <div class="cri-days-bar">${diasSelectorHtml}</div>
    </div>

    <!-- Configuración de Cardio -->
    <div style="padding:14px;border-radius:14px;background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);margin-bottom:16px;">
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.84rem;font-weight:800;color:#fca5a5;">
        <input type="checkbox" ${d.cardio_activo ? 'checked' : ''} onchange="saDraftData.cardio_activo = this.checked; saRender();">
        <i class="ti ti-flame"></i> Incluir Bloque de Trote / Cardio
      </label>
      ${d.cardio_activo ? `
        <div style="display:grid;grid-template-columns:120px 1fr;gap:10px;margin-top:10px;">
          <div>
            <label style="font-size:0.7rem;color:#fca5a5;font-weight:700;">Minutos</label>
            <input type="number" min="1" max="180" value="${Number(d.cardio_tiempo || 10)}" oninput="saDraftData.cardio_tiempo = this.value"
                   style="width:100%;padding:8px;border-radius:9px;background:rgba(0,0,0,0.3);border:1px solid rgba(239,68,68,0.35);color:#fff;font-weight:700;">
          </div>
          <div>
            <label style="font-size:0.7rem;color:#fca5a5;font-weight:700;">Indicaciones</label>
            <input type="text" value="${saEsc(d.cardio_indicaciones)}" oninput="saDraftData.cardio_indicaciones = this.value" placeholder="Ej: Trote a ritmo constante e inclinación 4"
                   style="width:100%;padding:8px 10px;border-radius:9px;background:rgba(0,0,0,0.3);border:1px solid rgba(239,68,68,0.35);color:#fff;">
          </div>
        </div>
      ` : ''}
    </div>

    ${ejsHtml}

    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px;">
      <button type="button" onclick="saAgregarEjercicioBorrador()" style="flex:1;min-width:200px;padding:12px;border-radius:12px;background:rgba(255,255,255,0.05);border:1px dashed rgba(255,255,255,0.2);color:#e2e8f0;font-weight:800;font-size:0.86rem;cursor:pointer;">
        <i class="ti ti-plus"></i> Agregar Ejercicio a la Rutina
      </button>
      <button type="button" onclick="saGuardarBorradorRutina()" style="flex:1;min-width:220px;padding:12px 18px;border-radius:12px;background:linear-gradient(135deg,#8a2be2,#6d28d9);border:none;color:#fff;font-weight:800;font-size:0.9rem;cursor:pointer;box-shadow:0 8px 22px rgba(138,43,226,0.38);">
        <i class="ti ti-device-floppy"></i> ${esNuevo ? 'Crear y Asignar Rutina' : 'Guardar Rutina Actualizada'}
      </button>
    </div>
  `;
}

function saToggleDiaBorrador(dow) {
  const idx = saDraftData.dias_semana.indexOf(dow);
  if (idx >= 0) {
    if (saDraftData.dias_semana.length > 1) saDraftData.dias_semana.splice(idx, 1);
  } else {
    saDraftData.dias_semana.push(dow);
  }
  saRender();
}

function saSetImpBorrador(ejIdx, imp) {
  saDraftData.ejercicios[ejIdx].tipo_implemento = imp;
  if (imp === 'sin_peso') saDraftData.ejercicios[ejIdx].unidad_carga = 'ninguna';
  else if (saDraftData.ejercicios[ejIdx].unidad_carga === 'ninguna') saDraftData.ejercicios[ejIdx].unidad_carga = 'kg';
  saRender();
}

function saSetUniBorrador(ejIdx, uni) {
  saDraftData.ejercicios[ejIdx].unidad_carga = uni;
  saRender();
}

function saAgregarSerieBorrador(ejIdx) {
  const list = saDraftData.ejercicios[ejIdx].series_detalle;
  const last = list[list.length - 1] || { peso: '', reps: 10 };
  list.push({ numero_serie: list.length + 1, peso: last.peso, reps: last.reps });
  saRender();
}

function saQuitarSerieBorrador(ejIdx, sIdx) {
  const list = saDraftData.ejercicios[ejIdx].series_detalle;
  if (list.length <= 1) return;
  list.splice(sIdx, 1);
  saRender();
}

function saAgregarEjercicioBorrador() {
  saDraftData.ejercicios.push({
    id: 0,
    nombre: '',
    tipo_implemento: 'barra',
    unidad_carga: 'kg',
    rir: 2,
    series_detalle: [
      { numero_serie: 1, peso: '', reps: 10 },
      { numero_serie: 2, peso: '', reps: 10 },
      { numero_serie: 3, peso: '', reps: 10 }
    ]
  });
  saRender();
}

function saQuitarEjercicioBorrador(ejIdx) {
  if (saDraftData.ejercicios.length <= 1) return;
  saDraftData.ejercicios.splice(ejIdx, 1);
  saRender();
}

async function saGuardarBorradorRutina() {
  const d = saDraftData;
  const validEjs = d.ejercicios.filter(e => String(e.nombre || '').trim() !== '');
  if (validEjs.length === 0) {
    Swal.fire({ icon: 'warning', title: 'Faltan ejercicios', text: 'Escribe el nombre de al menos un ejercicio.', background: '#12111a', color: '#fff' });
    return;
  }

  const endpoint = saCreateMode ? '/admin/api-crear-rutina-propia' : '/admin/api-actualizar-rutina-completa';
  const payload = {
    csrf_token: CSRF,
    rutina_id: d.rutina_id,
    titulo: d.titulo || 'Mi Rutina',
    dias_semana: d.dias_semana,
    cardio_activo: d.cardio_activo ? 1 : 0,
    cardio_tiempo: d.cardio_tiempo,
    cardio_indicaciones: d.cardio_indicaciones,
    ejercicios: validEjs.map(e => ({
      id: e.id,
      nombre: e.nombre.trim(),
      tipo_implemento: e.tipo_implemento,
      unidad_carga: e.unidad_carga,
      rir: e.rir,
      series: e.series_detalle.length,
      repeticiones: String(e.series_detalle[0]?.reps || 10),
      series_detalle: e.series_detalle
    }))
  };

  try {
    const res = await fetch(APP + endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify(payload)
    });
    const out = await res.json();
    if (out.ok) {
      Swal.fire({ icon: 'success', title: '¡Guardado!', text: out.msg, timer: 1400, showConfirmButton: false, background: '#12111a', color: '#fff' });
      saEditMode = false;
      saCreateMode = false;
      await saCargarDia(saCurrentFecha);
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: out.msg || 'No se pudo guardar la rutina.', background: '#12111a', color: '#fff' });
    }
  } catch (e) {
    Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo conectar con el servidor.', background: '#12111a', color: '#fff' });
  }
}

async function saGuardarComoPlantilla(rutinaId) {
  const { value: nombrePl } = await Swal.fire({
    title: 'Guardar en Catálogo de Plantillas',
    input: 'text',
    inputLabel: 'Nombre para identificar esta plantilla',
    inputValue: saCurrentData?.rutina?.nombre || 'Mi Plantilla',
    showCancelButton: true,
    confirmButtonText: 'Guardar Plantilla',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#8a2be2',
    background: '#12111a',
    color: '#fff'
  });
  if (!nombrePl) return;

  const res = await fetch(`${APP}/admin/api-guardar-rutina-como-plantilla`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
    body: JSON.stringify({ csrf_token: CSRF, rutina_id: rutinaId, titulo: nombrePl })
  });
  const d = await res.json();
  Swal.fire({
    icon: d.ok ? 'success' : 'error',
    title: d.ok ? 'Plantilla guardada' : 'Error',
    text: d.msg,
    background: '#12111a',
    color: '#fff'
  });
}

async function saConfirmarEliminarRutina(rutinaId) {
  const ask = await Swal.fire({
    icon: 'warning',
    title: '¿Eliminar esta rutina?',
    text: 'Se eliminará la rutina asignada para este día.',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
    background: '#12111a',
    color: '#fff'
  });
  if (!ask.isConfirmed) return;

  const res = await fetch(`${APP}/admin/api-eliminar-rutina`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
    body: JSON.stringify({ csrf_token: CSRF, rutina_id: rutinaId })
  });
  const d = await res.json();
  if (d.ok) {
    saCargarDia(saCurrentFecha);
  } else {
    Swal.fire({ icon: 'error', title: 'Error', text: d.msg || 'No se pudo eliminar.', background: '#12111a', color: '#fff' });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  saCargarDia(saCurrentFecha);
});
</script>

<?php require BASE_DIR . '/views/layout/footer.php'; ?>

