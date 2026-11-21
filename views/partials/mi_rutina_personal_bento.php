<?php
/**
 * views/partials/mi_rutina_personal_bento.php
 * Vista unificada de "Mi Rutina Personal" para Entrenador (en Inicio debajo de los 4 cuadros)
 * y Super Admin (en /admin/rutina), con el diseño exacto de la Imagen 5:
 * - Arriba en hilera únicamente los días registrados (con botón + para asignar otro día y lápiz para editar).
 * - Agrupación por grupo muscular con icono (ej. 🛡️ PECHO).
 * - Tarjeta por ejercicio con miniatura + lupa (abre modal Bento de ejercicio), título, menú ⋮ y chevron ^/v.
 * - Tabla de series con columnas: SERIE | LBS/KG/PLACAS | REPS | RIR | ✓.
 *
 * Variables esperadas:
 *  - $xpMrRole ('entrenador' | 'admin')
 *  - $xpMrClienteId (int)
 *  - $xpMrDiasProgramados (int[])
 */
$xpMrRole            = ($xpMrRole ?? 'entrenador') === 'admin' ? 'admin' : 'entrenador';
$xpMrClienteId       = (int)($xpMrClienteId ?? 0);
$xpMrDiasProgramados = is_array($xpMrDiasProgramados ?? null)
    ? array_values(array_unique(array_filter(array_map('intval', $xpMrDiasProgramados), static fn($d) => $d >= 1 && $d <= 7)))
    : [];
sort($xpMrDiasProgramados);

$xpMrDiasMeta = [
    1 => ['num' => 1, 'corto' => 'Lun', 'largo' => 'Lunes',     'letra' => 'L'],
    2 => ['num' => 2, 'corto' => 'Mar', 'largo' => 'Martes',    'letra' => 'M'],
    3 => ['num' => 3, 'corto' => 'Mié', 'largo' => 'Miércoles', 'letra' => 'X'],
    4 => ['num' => 4, 'corto' => 'Jue', 'largo' => 'Jueves',    'letra' => 'J'],
    5 => ['num' => 5, 'corto' => 'Vie', 'largo' => 'Viernes',   'letra' => 'V'],
    6 => ['num' => 6, 'corto' => 'Sáb', 'largo' => 'Sábado',    'letra' => 'S'],
    7 => ['num' => 7, 'corto' => 'Dom', 'largo' => 'Domingo',   'letra' => 'D'],
];

$xpMrHoyDow = (int)date('N');
if (!empty($xpMrDiasProgramados)) {
    $xpMrInitialDow = in_array($xpMrHoyDow, $xpMrDiasProgramados, true)
        ? $xpMrHoyDow
        : (int)$xpMrDiasProgramados[0];
} else {
    $xpMrInitialDow = $xpMrHoyDow;
}

$xpMrCrearUrlBase = $xpMrRole === 'admin'
    ? (APP_URL . '/admin/crear-rutina')
    : (APP_URL . '/entrenador/crear-rutina?atleta_id=' . $xpMrClienteId . '&propia=1');
?>
<style>
  .xp-mr-shell {
    margin: 0 0 24px 0;
    background: var(--card-glass, rgba(16, 16, 24, 0.94));
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 22px;
    padding: 16px;
    box-shadow: 0 14px 36px rgba(0, 0, 0, 0.42);
    position: relative;
    z-index: 2;
  }

  /* Hilera superior de días registrados + acciones rápidas */
  .xp-mr-top-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 14px;
    flex-wrap: nowrap;
  }
  .xp-mr-days-strip {
    display: flex;
    align-items: center;
    gap: 8px;
    overflow-x: auto;
    scrollbar-width: none;
    flex: 1;
    min-width: 0;
    padding: 2px 0;
  }
  .xp-mr-days-strip::-webkit-scrollbar { display: none; }

  .xp-mr-day-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-width: 58px;
    height: 38px;
    padding: 0 14px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.04);
    border: 1.5px solid rgba(255, 255, 255, 0.12);
    color: #cbd5e1;
    font-size: 0.85rem;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.18s ease;
    flex-shrink: 0;
    user-select: none;
  }
  .xp-mr-day-pill * { pointer-events: none; }
  .xp-mr-day-pill:hover {
    background: rgba(138, 43, 226, 0.16);
    border-color: rgba(168, 85, 247, 0.45);
    color: #ffffff;
  }
  .xp-mr-day-pill.is-active {
    background: linear-gradient(135deg, var(--accent-primary, #8a2be2), #6d28d9);
    border-color: rgba(192, 132, 252, 0.75);
    color: #ffffff;
    box-shadow: 0 4px 16px rgba(138, 43, 226, 0.42);
  }

  .xp-mr-actions-bar {
    display: flex;
    align-items: center;
    gap: 7px;
    flex-shrink: 0;
  }
  .xp-mr-action-icon {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: #e2e8f0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.18s ease;
    flex-shrink: 0;
    user-select: none;
  }
  .xp-mr-action-icon * { pointer-events: none; }
  .xp-mr-action-icon:hover {
    background: rgba(138, 43, 226, 0.22);
    border-color: rgba(168, 85, 247, 0.55);
    color: #ffffff;
  }
  .xp-mr-action-icon.is-primary {
    background: linear-gradient(135deg, var(--accent-primary, #8a2be2), #6d28d9);
    border-color: rgba(192, 132, 252, 0.5);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(138, 43, 226, 0.32);
  }
  .xp-mr-action-icon.is-danger:hover {
    background: rgba(239, 68, 68, 0.2);
    border-color: rgba(239, 68, 68, 0.48);
    color: #fca5a5;
  }

  /* Botón "+ Crear Mi Rutina" 100% clickeable en toda su superficie */
  .xp-mr-create-cta {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 11px 22px;
    min-height: 44px;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--accent-primary, #8a2be2), #6d28d9);
    border: 1px solid rgba(192, 132, 252, 0.45);
    color: #ffffff !important;
    font-size: 0.88rem;
    font-weight: 800;
    text-decoration: none !important;
    cursor: pointer !important;
    box-shadow: 0 6px 18px rgba(138, 43, 226, 0.38);
    transition: transform 0.16s ease, box-shadow 0.16s ease;
    position: relative;
    z-index: 15;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
  }
  .xp-mr-create-cta * {
    pointer-events: none !important;
  }
  .xp-mr-create-cta:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 22px rgba(138, 43, 226, 0.5);
  }
  .xp-mr-create-cta:active {
    transform: scale(0.98);
  }

  /* ════ ESTILOS IMAGEN 5: GRUPOS MUSCULARES + TARJETA DE EJERCICIO + TABLA DE SERIES ════ */
  .xp-mr-grupos-container {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }
  .xp-mr-group-block {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }
  .xp-mr-group-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 2px 4px;
  }
  .xp-mr-group-title {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #94a3b8;
  }
  .xp-mr-group-title i {
    font-size: 1rem;
    color: #94a3b8;
  }

  .xp-mr-ej-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .xp-mr-ejercicio-card {
    background: rgba(20, 22, 34, 0.92);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 18px;
    padding: 14px 16px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.32);
    transition: border-color 0.2s ease, background 0.2s ease;
  }
  .xp-mr-ejercicio-card.is-collapsed .xp-mr-series-table {
    display: none !important;
  }
  .xp-mr-ej-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    cursor: pointer;
    user-select: none;
  }
  .xp-mr-ej-left {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
    flex: 1;
  }
  .xp-mr-ej-thumb-btn {
    width: 38px;
    height: 38px;
    border-radius: 11px;
    background: rgba(139, 92, 246, 0.08);
    border: 1.5px solid rgba(139, 92, 246, 0.38);
    overflow: hidden;
    position: relative;
    display: grid;
    place-items: center;
    cursor: pointer;
    flex-shrink: 0;
    padding: 0;
    transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
  }
  .xp-mr-ej-thumb-btn:hover {
    transform: scale(1.05);
    border-color: #b388ff;
    box-shadow: 0 0 12px rgba(139, 92, 246, 0.35);
  }
  .xp-mr-ej-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }
  .xp-mr-ej-thumb-placeholder {
    color: #a78bfa;
    font-size: 1.15rem;
    display: grid;
    place-items: center;
    width: 100%;
    height: 100%;
  }
  .xp-mr-ej-thumb-zoom {
    position: absolute;
    bottom: 1px;
    right: 1px;
    background: rgba(0, 0, 0, 0.82);
    color: #fff;
    border-radius: 4px;
    font-size: 0.6rem;
    padding: 1px 2px;
    line-height: 1;
    pointer-events: none;
  }
  .xp-mr-ej-nombre {
    font-size: 1rem;
    font-weight: 800;
    color: #ffffff;
    line-height: 1.25;
    word-break: break-word;
  }
  .xp-mr-ej-right {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
  }
  .xp-mr-btn-dots {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    border: none;
    background: transparent;
    color: #94a3b8;
    display: grid;
    place-items: center;
    font-size: 1.1rem;
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease;
  }
  .xp-mr-btn-dots:hover {
    background: rgba(255, 255, 255, 0.06);
    color: #ffffff;
  }
  .xp-mr-ej-chevron {
    font-size: 1.15rem;
    color: #94a3b8;
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .xp-mr-ejercicio-card:not(.is-collapsed) .xp-mr-ej-chevron {
    transform: rotate(180deg);
    color: #ffffff;
  }

  /* Tabla de series idéntica a la Imagen 5 */
  .xp-mr-series-table {
    margin-top: 14px;
    padding-top: 10px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
  }
  .xp-mr-st-head,
  .xp-mr-st-row {
    display: grid;
    grid-template-columns: 44px 1fr 1fr 48px 38px;
    gap: 8px;
    align-items: center;
  }
  .xp-mr-series-table.no-rir .xp-mr-st-head,
  .xp-mr-series-table.no-rir .xp-mr-st-row {
    grid-template-columns: 44px 1fr 1fr 38px;
  }
  .xp-mr-st-head {
    padding: 0 4px 8px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  }
  .xp-mr-st-th {
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #64748b;
    text-align: center;
  }
  .xp-mr-st-th.col-left { text-align: left; }
  .xp-mr-st-th.col-right { text-align: center; }

  .xp-mr-st-body {
    display: flex;
    flex-direction: column;
  }
  .xp-mr-st-row {
    padding: 8px 4px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    border-radius: 10px;
    transition: background 0.18s ease;
  }
  .xp-mr-st-row:last-child {
    border-bottom: none;
  }
  .xp-mr-st-row.is-serie-completed {
    background: rgba(16, 185, 129, 0.05);
  }

  .xp-mr-box-num {
    width: 32px;
    height: 30px;
    border-radius: 9px;
    background: rgba(139, 92, 246, 0.12);
    border: 1px solid rgba(139, 92, 246, 0.28);
    color: #a78bfa;
    font-size: 0.82rem;
    font-weight: 800;
    display: grid;
    place-items: center;
    margin: 0 auto;
  }
  .xp-mr-st-row.is-serie-completed .xp-mr-box-num {
    background: rgba(16, 185, 129, 0.14);
    border-color: rgba(16, 185, 129, 0.45);
    color: #10b981;
  }
  .xp-mr-box-num.is-warmup {
    background: rgba(245, 158, 11, 0.15);
    border-color: rgba(245, 158, 11, 0.4);
    color: #fbbf24;
  }
  .xp-mr-box-num.is-failure {
    background: rgba(239, 68, 68, 0.15);
    border-color: rgba(239, 68, 68, 0.4);
    color: #f87171;
  }
  .xp-mr-box-num.is-drop {
    background: rgba(59, 130, 246, 0.15);
    border-color: rgba(59, 130, 246, 0.4);
    color: #60a5fa;
  }

  .xp-mr-input-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
  }
  .xp-mr-live-input {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 10px;
    color: #ffffff;
    font-weight: 800;
    font-size: 0.9rem;
    text-align: center;
    width: 62px;
    max-width: 100%;
    height: 34px;
    padding: 0 4px;
    outline: none;
    font-family: inherit;
    transition: all 0.18s ease;
  }
  .xp-mr-live-input:focus {
    border-color: #8a2be2;
    background: rgba(138, 43, 226, 0.14);
    box-shadow: 0 0 0 2px rgba(138, 43, 226, 0.22);
  }
  .xp-mr-live-input::-webkit-outer-spin-button,
  .xp-mr-live-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }
  .xp-mr-live-input[type=number] {
    -moz-appearance: textfield;
  }

  .xp-mr-data-rir {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 9px;
    border-radius: 8px;
    background: rgba(139, 92, 246, 0.16);
    border: 1px solid rgba(139, 92, 246, 0.3);
    color: #d8b4fe;
    font-size: 0.76rem;
    font-weight: 800;
  }

  .xp-mr-btn-check {
    width: 32px;
    height: 32px;
    border-radius: 9px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: rgba(255, 255, 255, 0.05);
    color: #64748b;
    display: grid;
    place-items: center;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.18s ease;
    padding: 0;
    margin: 0 auto;
  }
  .xp-mr-btn-check:hover {
    border-color: #10b981;
    color: #10b981;
    background: rgba(16, 185, 129, 0.1);
  }
  .xp-mr-btn-check.is-checked {
    background: #10b981 !important;
    border-color: #10b981 !important;
    color: #ffffff !important;
    box-shadow: 0 0 12px rgba(16, 185, 129, 0.42);
  }

  /* Modal Bento de Detalle de Ejercicio (Lupa) */
  .xp-mr-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(4, 5, 10, 0.82);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 10050;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.25s ease, visibility 0.25s ease;
  }
  .xp-mr-modal-backdrop.is-open {
    opacity: 1;
    visibility: visible;
  }
  .xp-mr-modal-container {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -46%) scale(0.96);
    width: min(92vw, 520px);
    max-height: 86vh;
    background: #0d0f18;
    border: 1px solid rgba(138, 43, 226, 0.35);
    border-radius: 22px;
    z-index: 10051;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.75);
    opacity: 0;
    visibility: hidden;
    transition: all 0.25s ease;
  }
  .xp-mr-modal-container.is-open {
    opacity: 1;
    visibility: visible;
    transform: translate(-50%, -50%) scale(1);
  }
</style>

<section class="xp-mr-shell" id="entMyRoutineSection" aria-label="Mi Rutina Personal">
  <!-- Fila Superior: Hilera con los días registrados + Botones de Acción -->
  <div class="xp-mr-top-row" id="xpMrTopRow" style="<?= empty($xpMrDiasProgramados) ? 'display:none;' : '' ?>">
    <div class="xp-mr-days-strip" id="xpMrDaysBar">
      <?php foreach ($xpMrDiasProgramados as $dNum):
        $dm = $xpMrDiasMeta[$dNum] ?? ['num' => $dNum, 'corto' => 'Día ' . $dNum];
      ?>
        <button type="button"
                class="xp-mr-day-pill <?= $dNum === $xpMrInitialDow ? 'is-active' : '' ?>"
                data-dow="<?= (int)$dNum ?>"
                onclick="xpMrCargarDia(<?= (int)$dNum ?>)">
          <i class="ti ti-calendar-check"></i>
          <span><?= e($dm['corto']) ?></span>
        </button>
      <?php endforeach; ?>
    </div>

    <div class="xp-mr-actions-bar">
      <a href="<?= e($xpMrCrearUrlBase . (str_contains($xpMrCrearUrlBase, '?') ? '&' : '?') . 'dia=' . $xpMrInitialDow) ?>"
         class="xp-mr-action-icon"
         id="xpMrBtnEditLink"
         data-no-skeleton="1"
         title="Modificar rutina de este día">
        <i class="ti ti-pencil"></i>
      </a>
      <a href="<?= e($xpMrCrearUrlBase) ?>"
         class="xp-mr-action-icon is-primary"
         id="xpMrBtnAddDayLink"
         data-no-skeleton="1"
         title="Asignar o crear rutina para otro día">
        <i class="ti ti-plus"></i>
      </a>
      <button type="button"
              class="xp-mr-action-icon is-danger"
              id="xpMrBtnDeleteRoutine"
              style="display:none;"
              onclick="xpMrEliminarRutinaActual()"
              title="Eliminar rutina de este día">
        <i class="ti ti-trash"></i>
      </button>
    </div>
  </div>

  <!-- Contenedor dinámico de Ejercicios (Formato Imagen 5) -->
  <div id="entMrBodyContainer">
    <div style="text-align:center; padding:28px 16px; color:#94a3b8; font-size:0.86rem;">
      <i class="ti ti-loader" style="font-size:1.4rem; color:#a855f7; display:block; margin-bottom:6px;"></i>
      Cargando tu rutina...
    </div>
  </div>
</section>

<!-- Modal Bento Detalle de Ejercicio -->
<div class="xp-mr-modal-backdrop" id="xpMrEjModalBackdrop" onclick="xpMrCerrarModalEjercicio()"></div>
<div class="xp-mr-modal-container" id="xpMrEjModal" role="dialog" aria-modal="true">
  <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;background:rgba(18,20,32,0.96);border-bottom:1px solid rgba(255,255,255,0.08);">
    <div style="min-width:0;">
      <span id="xpMrModalGroup" style="font-size:0.65rem;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:#b388ff;display:block;">EJERCICIO</span>
      <h4 id="xpMrModalTitle" style="margin:0;font-size:1.02rem;font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Detalle</h4>
    </div>
    <button type="button" onclick="xpMrCerrarModalEjercicio()" style="width:32px;height:32px;border-radius:9px;border:1px solid rgba(255,255,255,0.12);background:rgba(255,255,255,0.05);color:#cbd5e1;display:grid;place-items:center;cursor:pointer;">
      <i class="ti ti-x"></i>
    </button>
  </div>
  <div id="xpMrModalBody" style="padding:16px;overflow-y:auto;max-height:calc(86vh - 64px);color:#cbd5e1;font-size:0.86rem;"></div>
</div>

<script>
(function() {
  const MR_ROLE       = <?= json_encode($xpMrRole) ?>;
  const MR_CLIENTE_ID = <?= (int)$xpMrClienteId ?>;
  const MR_CREAR_BASE = <?= json_encode($xpMrCrearUrlBase) ?>;
  const HOY_ISO       = <?= json_encode(date('Y-m-d')) ?>;
  const INITIAL_DOW   = <?= (int)$xpMrInitialDow ?>;
  const DIAS_META     = {
    1: { num: 1, corto: 'Lun', largo: 'Lunes' },
    2: { num: 2, corto: 'Mar', largo: 'Martes' },
    3: { num: 3, corto: 'Mié', largo: 'Miércoles' },
    4: { num: 4, corto: 'Jue', largo: 'Jueves' },
    5: { num: 5, corto: 'Vie', largo: 'Viernes' },
    6: { num: 6, corto: 'Sáb', largo: 'Sábado' },
    7: { num: 7, corto: 'Dom', largo: 'Domingo' }
  };

  window.xpMrActiveDow   = INITIAL_DOW;
  window.xpMrCurrentData = null;
  window.xpMrCurrentIso  = HOY_ISO;

  function mrEsc(str) {
    return String(str == null ? '' : str)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  function mrFechaPorDow(targetDow) {
    const ref = new Date(HOY_ISO + 'T12:00:00');
    const curDow = ref.getDay() === 0 ? 7 : ref.getDay();
    ref.setDate(ref.getDate() + (Number(targetDow) - curDow));
    const y = ref.getFullYear();
    const m = String(ref.getMonth() + 1).padStart(2, '0');
    const d = String(ref.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
  }

  function mrCrearUrl(paramsObj) {
    const sep = MR_CREAR_BASE.includes('?') ? '&' : '?';
    const qs = Object.entries(paramsObj || {})
      .filter(([, v]) => v !== null && v !== undefined && v !== '')
      .map(([k, v]) => `${encodeURIComponent(k)}=${encodeURIComponent(v)}`)
      .join('&');
    return qs ? `${MR_CREAR_BASE}${sep}${qs}` : MR_CREAR_BASE;
  }

  function mrGrupoIcono(grupo) {
    const g = String(grupo || '').toLowerCase();
    if (g.includes('pecho') || g.includes('pectoral')) return 'ti-shield';
    if (g.includes('hombro') || g.includes('deltoid')) return 'ti-flame';
    if (g.includes('espalda') || g.includes('dorsal') || g.includes('remo')) return 'ti-arrow-autofit-up';
    if (g.includes('cuadric') || g.includes('sentadilla') || g.includes('pierna')) return 'ti-run';
    if (g.includes('femoral') || g.includes('gluteo') || g.includes('isquio')) return 'ti-user';
    if (g.includes('bicep')) return 'ti-arm';
    if (g.includes('tricep')) return 'ti-wave-sine';
    if (g.includes('abdom') || g.includes('core')) return 'ti-layout-bottombar';
    if (g.includes('gemelo') || g.includes('pantorrilla')) return 'ti-anchor';
    return 'ti-barbell';
  }

  function mrRenderDaysBar(diasProg, activeDow) {
    const topRow = document.getElementById('xpMrTopRow');
    const strip  = document.getElementById('xpMrDaysBar');
    if (!topRow || !strip) return;

    const cleanDias = Array.isArray(diasProg)
      ? Array.from(new Set(diasProg.map(Number).filter(d => d >= 1 && d <= 7))).sort((a, b) => a - b)
      : [];

    if (cleanDias.length === 0) {
      topRow.style.display = 'none';
      strip.innerHTML = '';
      return;
    }

    topRow.style.display = 'flex';
    strip.innerHTML = cleanDias.map(dNum => {
      const meta = DIAS_META[dNum] || { corto: 'Día ' + dNum };
      const isAct = Number(dNum) === Number(activeDow);
      return `
        <button type="button"
                class="xp-mr-day-pill ${isAct ? 'is-active' : ''}"
                data-dow="${dNum}"
                onclick="xpMrCargarDia(${dNum})">
          <i class="ti ti-calendar-check"></i>
          <span>${mrEsc(meta.corto)}</span>
        </button>`;
    }).join('');
  }

  window.xpMrCargarDia = async function(dow) {
    if (!MR_CLIENTE_ID && MR_ROLE !== 'admin') return;
    window.xpMrActiveDow = Number(dow);
    const fechaIso = mrFechaPorDow(dow);
    window.xpMrCurrentIso = fechaIso;

    document.querySelectorAll('#xpMrDaysBar .xp-mr-day-pill').forEach(btn => {
      btn.classList.toggle('is-active', Number(btn.getAttribute('data-dow')) === Number(dow));
    });

    const box = document.getElementById('entMrBodyContainer');
    const apiUrl = MR_ROLE === 'admin'
      ? `${window.APP}/admin/api-dia-detalle?fecha=${encodeURIComponent(fechaIso)}&_t=${Date.now()}`
      : `${window.APP}/entrenador/api-dia-detalle?cliente_id=${MR_CLIENTE_ID}&fecha=${encodeURIComponent(fechaIso)}&_t=${Date.now()}`;

    try {
      const res = await fetch(apiUrl, { cache: 'no-store' });
      const data = await res.json();
      window.xpMrCurrentData = data;

      if (Array.isArray(data.dias_programados)) {
        const prog = data.dias_programados.map(Number).filter(d => d >= 1 && d <= 7);
        if (prog.length > 0 && !prog.includes(Number(dow)) && !(data.rutina && data.rutina.id)) {
          // Si el día consultado no tiene rutina pero hay otros días registrados, mostrar el primer día registrado
          const firstProg = prog[0];
          if (firstProg !== Number(dow)) {
            return window.xpMrCargarDia(firstProg);
          }
        }
        mrRenderDaysBar(prog, dow);
      }

      mrRenderRutina(data, dow);
    } catch (e) {
      if (box) {
        box.innerHTML = `<div style="text-align:center;padding:24px;color:#94a3b8;font-size:0.85rem;">No se pudo cargar tu rutina en este momento.</div>`;
      }
    }
  };
  window.entCargarMiRutinaDia = window.xpMrCargarDia;

  function mrRenderRutina(data, dow) {
    const box     = document.getElementById('entMrBodyContainer');
    const editBtn = document.getElementById('xpMrBtnEditLink');
    const addBtn  = document.getElementById('xpMrBtnAddDayLink');
    const delBtn  = document.getElementById('xpMrBtnDeleteRoutine');
    if (!box) return;

    const rut = data && data.rutina ? data.rutina : null;
    const ejs = (data && Array.isArray(data.ejercicios)) ? data.ejercicios : [];
    const tieneRutina = !!(rut && rut.id && ejs.length > 0);

    if (editBtn) {
      editBtn.href = tieneRutina
        ? mrCrearUrl({ editar: Number(rut.id) })
        : mrCrearUrl({ dia: Number(dow) });
    }
    if (addBtn) {
      addBtn.href = mrCrearUrl({});
    }
    if (delBtn) {
      delBtn.style.display = tieneRutina ? 'inline-flex' : 'none';
    }

    if (!tieneRutina) {
      const createHref = mrCrearUrl({ dia: Number(dow) });
      box.innerHTML = `
        <div style="text-align:center; padding:26px 16px; border:1px dashed rgba(255,255,255,0.1); border-radius:16px; background:rgba(255,255,255,0.015);">
          <i class="ti ti-barbell" style="font-size:2rem; color:#a855f7; display:block; margin-bottom:8px;"></i>
          <div style="font-weight:800; color:#fff; font-size:0.96rem; margin-bottom:4px;">Sin rutina asignada</div>
          <p style="margin:0 0 16px; font-size:0.8rem; color:#94a3b8;">Crea y programa tu rutina personal para ver tus días registrados y registrar tus series.</p>
          <a href="${mrEsc(createHref)}"
             class="xp-mr-create-cta"
             data-no-skeleton="1"
             onclick="window.location.href=this.href; return false;">
            <i class="ti ti-plus"></i>
            <span>Crear Mi Rutina</span>
          </a>
        </div>`;
      return;
    }

    let html = '';

    // 1. Bloque Cardio si está activo
    if (Number(rut.cardio_activo || 0) === 1) {
      const cMin = Number(rut.cardio_tiempo || 10);
      const cInd = String(rut.cardio_indicaciones || '').trim();
      html += `
        <div style="background:linear-gradient(135deg,rgba(249,115,22,0.14) 0%,rgba(20,16,15,0.96) 100%);border:1px solid rgba(249,115,22,0.35);border-radius:16px;padding:14px 16px;margin-bottom:14px;">
          <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;">
            <div style="display:flex;align-items:center;gap:10px;">
              <div style="width:36px;height:36px;border-radius:10px;background:rgba(249,115,22,0.22);color:#fb923c;display:grid;place-items:center;font-size:1.2rem;">
                <i class="ti ti-run"></i>
              </div>
              <div>
                <div style="font-weight:800;font-size:0.92rem;color:#fff;">Cardio / Trote</div>
                <div style="font-size:0.75rem;color:#fdba74;font-weight:600;">${cMin} minutos sugeridos</div>
              </div>
            </div>
            <span style="background:rgba(249,115,22,0.2);border:1px solid rgba(249,115,22,0.4);color:#fdba74;font-size:0.74rem;font-weight:800;padding:4px 10px;border-radius:999px;">
              <i class="ti ti-clock"></i> ${cMin} min
            </span>
          </div>
          ${cInd ? `<div style="margin-top:8px;font-size:0.78rem;color:#cbd5e1;line-height:1.4;">${mrEsc(cInd)}</div>` : ''}
        </div>`;
    }

    // 2. Agrupar ejercicios por Grupo Muscular (Formato Imagen 5: 🛡️ PECHO)
    const gruposMap = new Map();
    ejs.forEach(ej => {
      const g = (ej.grupo_muscular && String(ej.grupo_muscular).trim()) ? String(ej.grupo_muscular).trim() : 'General';
      if (!gruposMap.has(g)) gruposMap.set(g, []);
      gruposMap.get(g).push(ej);
    });

    html += `<div class="xp-mr-grupos-container">`;
    gruposMap.forEach((ejsGrupo, grupoNombre) => {
      const icono = mrGrupoIcono(grupoNombre);
      html += `
        <div class="xp-mr-group-block">
          <div class="xp-mr-group-header">
            <div class="xp-mr-group-title">
              <i class="ti ${icono}"></i>
              <span>${mrEsc(grupoNombre.toUpperCase())}</span>
            </div>
          </div>
          <div class="xp-mr-ej-list">`;

      ejsGrupo.forEach((ej, ejIdxInGroup) => {
        const ejId = Number(ej.id || 0);
        const rutId = Number(ej.rutina_id || rut.id || 0);
        const impVal = String(ej.tipo_implemento || ej.equipamiento || 'barra').toLowerCase();
        const isSinPeso = impVal === 'sin_peso' || impVal.includes('peso corporal');
        const uCargaEj = String(ej.unidad_carga || 'kg').toLowerCase();

        let unitHeaderLabel = 'KG';
        if (isSinPeso || uCargaEj === 'ninguna') unitHeaderLabel = 'CARGA';
        else if (uCargaEj === 'placas') unitHeaderLabel = 'PLACAS';
        else if (uCargaEj === 'lbs' || uCargaEj === 'lb') unitHeaderLabel = 'LBS';

        const seriesList = (Array.isArray(ej.series_detalle) && ej.series_detalle.length > 0)
          ? ej.series_detalle
          : ((Array.isArray(ej.series) && ej.series.length > 0)
              ? ej.series
              : Array.from({ length: Math.max(1, Number(ej.series_total || ej.series || 3)) }, (_, i) => ({
                  numero: i + 1,
                  tipo: 'normal',
                  reps: ej.repeticiones || 10,
                  peso: null,
                  rir: ej.rir != null ? ej.rir : 2,
                  completado: false
                })));

        const isRirHidden = Boolean(ej.rir_desmarcado || ej.rir === null);
        const rirDefault  = ej.rir != null ? ej.rir : 2;
        let workIdx = 0;

        const rowsHtml = seriesList.map((s, idxS) => {
          const sNum  = Number(s.numero || s.numero_serie || (idxS + 1));
          const sTipo = String(s.tipo || s.tipo_serie || 'normal').toLowerCase();
          if (sTipo !== 'calentamiento') workIdx++;

          let badgeClass = '';
          let badgeText  = String(workIdx || sNum);
          if (sTipo === 'calentamiento') { badgeClass = 'is-warmup'; badgeText = 'W'; }
          else if (sTipo === 'fallo')    { badgeClass = 'is-failure'; badgeText = 'F'; }
          else if (sTipo === 'drop')     { badgeClass = 'is-drop'; badgeText = 'D'; }

          const isDone = Boolean(s.completado);
          const pesoVal = (s.peso != null && parseFloat(s.peso) > 0)
            ? parseFloat(s.peso)
            : ((ej.peso_objetivo != null && parseFloat(ej.peso_objetivo) > 0) ? parseFloat(ej.peso_objetivo) : '');
          const repsVal = (s.reps != null && String(s.reps) !== '')
            ? s.reps
            : (ej.repeticiones || 10);
          const rirVal = (s.rir != null && String(s.rir) !== '') ? s.rir : rirDefault;

          const maxW = uCargaEj === 'placas' ? 40 : (uCargaEj === 'lbs' || uCargaEj === 'lb' ? 999 : 500);
          const pesoControl = isSinPeso
            ? `<span style="font-size:0.72rem;font-weight:700;color:#4ade80;">Corporal</span><input type="hidden" class="xp-mr-live-peso" value="0">`
            : `<div class="xp-mr-input-wrap">
                 <input type="number" step="${uCargaEj === 'placas' ? '1' : '0.5'}" min="0" max="${maxW}"
                        class="xp-mr-live-input xp-mr-live-peso" value="${mrEsc(pesoVal)}" placeholder="0"
                        onchange="xpMrGuardarSerieCambio(${ejId}, ${sNum}, '${mrEsc(impVal)}', '${mrEsc(uCargaEj)}', '${mrEsc(sTipo)}', this.closest('.xp-mr-st-row'))"
                        aria-label="Carga serie ${sNum}">
               </div>`;

          const repsControl = `
            <div class="xp-mr-input-wrap">
              <input type="number" step="1" min="1" max="100"
                     class="xp-mr-live-input xp-mr-live-reps" value="${mrEsc(repsVal)}" placeholder="0"
                     onchange="xpMrGuardarSerieCambio(${ejId}, ${sNum}, '${mrEsc(impVal)}', '${mrEsc(uCargaEj)}', '${mrEsc(sTipo)}', this.closest('.xp-mr-st-row'))"
                     aria-label="Reps serie ${sNum}">
            </div>`;

          const rirCell = isRirHidden
            ? ''
            : `<div style="display:flex;justify-content:center;"><span class="xp-mr-data-rir">@${mrEsc(rirVal)}</span></div>`;

          return `
            <div class="xp-mr-st-row ${isDone ? 'is-serie-completed' : ''}" data-num-serie="${sNum}" data-ej-id="${ejId}">
              <div class="xp-mr-box-num ${badgeClass}" title="Serie ${sNum}">${mrEsc(badgeText)}</div>
              <div>${pesoControl}</div>
              <div>${repsControl}</div>
              ${rirCell}
              <div>
                <button type="button"
                        class="xp-mr-btn-check ${isDone ? 'is-checked' : ''}"
                        onclick="xpMrToggleSerie(${ejId}, ${sNum}, '${mrEsc(impVal)}', '${mrEsc(uCargaEj)}', '${mrEsc(sTipo)}', this)"
                        title="${isDone ? 'Desmarcar serie' : 'Marcar serie completada'}">
                  <i class="ti ti-check"></i>
                </button>
              </div>
            </div>`;
        }).join('');

        const ejJsonAttr = JSON.stringify(ej).replace(/"/g, '&quot;');
        const thumbHtml = ej.imagen_path
          ? `<img src="${window.APP}/${mrEsc(ej.imagen_path)}" alt="${mrEsc(ej.nombre)}" class="xp-mr-ej-thumb-img" loading="lazy">`
          : `<div class="xp-mr-ej-thumb-placeholder"><i class="ti ti-barbell"></i></div>`;

        html += `
          <div class="xp-mr-ejercicio-card" id="xpMrCard_${ejId}">
            <div class="xp-mr-ej-header" onclick="this.closest('.xp-mr-ejercicio-card').classList.toggle('is-collapsed')">
              <div class="xp-mr-ej-left">
                <button type="button" class="xp-mr-ej-thumb-btn"
                        onclick="event.stopPropagation(); xpMrAbrirModalEjercicio(${ejJsonAttr})"
                        title="Ver detalles e indicaciones de ${mrEsc(ej.nombre)}">
                  ${thumbHtml}
                  <span class="xp-mr-ej-thumb-zoom"><i class="ti ti-zoom-in"></i></span>
                </button>
                <span class="xp-mr-ej-nombre">${mrEsc(ej.nombre)}</span>
              </div>
              <div class="xp-mr-ej-right">
                <button type="button" class="xp-mr-btn-dots"
                        onclick="event.stopPropagation(); xpMrOpcionesEjercicio(${rutId}, ${ejJsonAttr})"
                        title="Opciones del ejercicio">
                  <i class="ti ti-dots-vertical"></i>
                </button>
                <i class="ti ti-chevron-down xp-mr-ej-chevron"></i>
              </div>
            </div>
            <div class="xp-mr-series-table ${isRirHidden ? 'no-rir' : ''}">
              <div class="xp-mr-st-head">
                <span class="xp-mr-st-th">SERIE</span>
                <span class="xp-mr-st-th">${mrEsc(unitHeaderLabel)}</span>
                <span class="xp-mr-st-th">REPS</span>
                ${isRirHidden ? '' : '<span class="xp-mr-st-th">RIR</span>'}
                <span class="xp-mr-st-th col-right">✓</span>
              </div>
              <div class="xp-mr-st-body">
                ${rowsHtml}
              </div>
            </div>
          </div>`;
      });

      html += `
          </div>
        </div>`;
    });
    html += `</div>`;

    box.innerHTML = html;
  }

  async function mrEnviarSerie(ejId, sNum, imp, uni, tipoSerie, peso, reps, guardar) {
    const endpoint = MR_ROLE === 'admin'
      ? `${window.APP}/admin/api-guardar-serie`
      : `${window.APP}/entrenador/api-guardar-serie-atleta`;

    const payload = {
      csrf_token: window.CSRF || '',
      cliente_id: MR_CLIENTE_ID,
      ejercicio_id: ejId,
      numero_serie: sNum,
      peso: peso,
      reps: reps,
      tipo_implemento: imp,
      unidad_carga: uni,
      tipo_serie: tipoSerie,
      fecha: window.xpMrCurrentIso || HOY_ISO,
      accion: guardar ? 'guardar' : 'desmarcar',
      completado: guardar ? 1 : 0
    };

    const res = await fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.CSRF || '' },
      body: JSON.stringify(payload)
    });
    return await res.json();
  }

  window.xpMrToggleSerie = async function(ejId, sNum, imp, uni, tipoSerie, btnEl) {
    if (!btnEl || btnEl.disabled) return;
    const rowEl = btnEl.closest('.xp-mr-st-row');
    if (!rowEl) return;

    const wasChecked = btnEl.classList.contains('is-checked');
    const newChecked = !wasChecked;
    const pesoInp = rowEl.querySelector('.xp-mr-live-peso');
    const repsInp = rowEl.querySelector('.xp-mr-live-reps');
    const peso = pesoInp && pesoInp.value !== '' ? pesoInp.value : '0';
    const reps = repsInp && repsInp.value !== '' ? repsInp.value : '10';

    btnEl.classList.toggle('is-checked', newChecked);
    rowEl.classList.toggle('is-serie-completed', newChecked);
    btnEl.disabled = true;

    try {
      const d = await mrEnviarSerie(ejId, sNum, imp, uni, tipoSerie, peso, reps, newChecked);
      if (!d || !d.ok) {
        btnEl.classList.toggle('is-checked', wasChecked);
        rowEl.classList.toggle('is-serie-completed', wasChecked);
      }
    } catch (_) {
      btnEl.classList.toggle('is-checked', wasChecked);
      rowEl.classList.toggle('is-serie-completed', wasChecked);
    } finally {
      btnEl.disabled = false;
    }
  };

  window.xpMrGuardarSerieCambio = async function(ejId, sNum, imp, uni, tipoSerie, rowEl) {
    if (!rowEl) return;
    const btnEl = rowEl.querySelector('.xp-mr-btn-check');
    if (!btnEl || !btnEl.classList.contains('is-checked')) return;
    const pesoInp = rowEl.querySelector('.xp-mr-live-peso');
    const repsInp = rowEl.querySelector('.xp-mr-live-reps');
    const peso = pesoInp && pesoInp.value !== '' ? pesoInp.value : '0';
    const reps = repsInp && repsInp.value !== '' ? repsInp.value : '10';
    try {
      await mrEnviarSerie(ejId, sNum, imp, uni, tipoSerie, peso, reps, true);
    } catch (_) {}
  };

  window.xpMrOpcionesEjercicio = function(rutinaId, ej) {
    if (typeof Swal === 'undefined') {
      window.location.href = mrCrearUrl({ editar: rutinaId });
      return;
    }
    Swal.fire({
      title: ej && ej.nombre ? ej.nombre : 'Opciones del ejercicio',
      showCancelButton: true,
      showDenyButton: true,
      confirmButtonText: '<i class="ti ti-pencil"></i> Modificar Rutina',
      denyButtonText: '<i class="ti ti-zoom-in"></i> Ver Indicaciones',
      cancelButtonText: 'Cerrar',
      confirmButtonColor: '#8a2be2',
      denyButtonColor: '#3b82f6',
      background: '#12121a',
      color: '#ffffff'
    }).then(res => {
      if (res.isConfirmed) {
        window.location.href = mrCrearUrl({ editar: rutinaId });
      } else if (res.isDenied) {
        window.xpMrAbrirModalEjercicio(ej);
      }
    });
  };

  window.xpMrAbrirModalEjercicio = function(ej) {
    if (!ej) return;
    const modal = document.getElementById('xpMrEjModal');
    const back  = document.getElementById('xpMrEjModalBackdrop');
    const tit   = document.getElementById('xpMrModalTitle');
    const grp   = document.getElementById('xpMrModalGroup');
    const body  = document.getElementById('xpMrModalBody');
    if (!modal || !back || !body) return;

    if (tit) tit.textContent = ej.nombre || 'Detalle del Ejercicio';
    if (grp) grp.textContent = (ej.grupo_muscular || 'EJERCICIO').toUpperCase();

    let mediaHtml = '';
    const imgPath = ej.imagen_demo_path || ej.imagen_path || '';
    if (imgPath) {
      const fullSrc = imgPath.startsWith('http') ? imgPath : `${window.APP}/${imgPath}`;
      mediaHtml = `<div style="border-radius:14px;overflow:hidden;margin-bottom:14px;background:#090a10;border:1px solid rgba(255,255,255,0.08);text-align:center;">
        <img src="${mrEsc(fullSrc)}" alt="${mrEsc(ej.nombre)}" style="max-width:100%;max-height:240px;object-fit:contain;">
      </div>`;
    }

    let indArr = [];
    try {
      if (typeof ej.indicaciones === 'string' && ej.indicaciones.trim().startsWith('[')) {
        indArr = JSON.parse(ej.indicaciones);
      } else if (Array.isArray(ej.indicaciones)) {
        indArr = ej.indicaciones;
      } else if (ej.indicaciones) {
        indArr = [{ titulo: 'Indicación biomecánica', descripcion: String(ej.indicaciones) }];
      }
    } catch (_) {}

    let indHtml = '';
    if (indArr.length > 0) {
      indHtml = indArr.map((p, i) => `
        <div style="display:flex;gap:10px;padding:10px 12px;border-radius:12px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);margin-bottom:8px;">
          <div style="width:24px;height:24px;border-radius:7px;background:rgba(138,43,226,0.2);color:#c084fc;font-weight:800;font-size:0.76rem;display:grid;place-items:center;flex-shrink:0;">${i + 1}</div>
          <div>
            <div style="font-weight:800;color:#fff;font-size:0.84rem;">${mrEsc(p.titulo || ('Paso ' + (i + 1)))}</div>
            <div style="color:#94a3b8;font-size:0.8rem;margin-top:2px;">${mrEsc(p.descripcion || p.texto || '')}</div>
          </div>
        </div>`).join('');
    } else {
      indHtml = `<div style="text-align:center;padding:18px;color:#64748b;">Sin indicaciones adicionales registradas para este ejercicio.</div>`;
    }

    body.innerHTML = mediaHtml + indHtml;
    if (back.parentElement !== document.body) document.body.appendChild(back);
    if (modal.parentElement !== document.body) document.body.appendChild(modal);
    back.classList.add('is-open');
    modal.classList.add('is-open');
  };

  window.xpMrCerrarModalEjercicio = function() {
    document.getElementById('xpMrEjModal')?.classList.remove('is-open');
    document.getElementById('xpMrEjModalBackdrop')?.classList.remove('is-open');
  };

  window.xpMrEliminarRutinaActual = async function() {
    const rutId = Number(window.xpMrCurrentData?.rutina?.id || 0);
    if (!rutId) return;
    if (typeof Swal !== 'undefined') {
      const ask = await Swal.fire({
        icon: 'warning',
        title: '¿Eliminar esta rutina?',
        text: 'Se eliminará la rutina asignada para este día.',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        background: '#12121a',
        color: '#fff'
      });
      if (!ask.isConfirmed) return;
    }
    const endpoint = MR_ROLE === 'admin'
      ? `${window.APP}/admin/api-eliminar-rutina`
      : `${window.APP}/entrenador/api-eliminar-rutina`;
    try {
      const res = await fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.CSRF || '' },
        body: JSON.stringify({ csrf_token: window.CSRF || '', rutina_id: rutId, cliente_id: MR_CLIENTE_ID })
      });
      const d = await res.json();
      if (d && d.ok) {
        window.location.reload();
      }
    } catch (_) {}
  };

  document.addEventListener('DOMContentLoaded', function() {
    window.xpMrCargarDia(INITIAL_DOW);
  });
})();
</script>
