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
  <?php
    $xpMrRole            = 'admin';
    $xpMrClienteId       = (int)($adminClienteId ?? 0);
    $xpMrDiasProgramados = $diasProgramados ?? [];
    require BASE_DIR . '/views/partials/mi_rutina_personal_bento.php';
  ?>
</div>

<?php require BASE_DIR . '/views/layout/footer.php'; ?>

