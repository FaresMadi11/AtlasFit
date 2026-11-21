<?php
// views/admin/configuracion.php — Módulo 3: Configuración de Mensajes (Aviso y Suspensión)
$pageTitle = 'Configuración';
require BASE_DIR . '/views/layout/header.php';
?>

<!-- ── HEADER DE CONFIGURACIÓN (SOLO BOTÓN DE DESLOGUEO, SIN NOTIFICACIONES) ── -->
<div class="sys-top-header premium-header">
  <div class="user-info-group">
    <div class="rl-header-icon" style="width: 42px; height: 42px; border-radius: 12px; background: linear-gradient(135deg, var(--accent-primary, #8a2be2), var(--accent-hover, #9d4edd)); display: grid; place-items: center; color: #fff; flex-shrink: 0; box-shadow: 0 4px 14px rgba(138, 43, 226, 0.35); font-size: 1.25rem;">
      <i class="ti ti-settings"></i>
    </div>
    <div class="user-text-wrap">
      <h1 class="sys-top-title user-name-gradient" title="Configuración" style="font-size: 1.12rem !important; font-weight: 800; color: var(--text, #fff); margin: 0; line-height: 1.25;">
        <span class="user-name-text">Configuración</span>
      </h1>
      <p style="font-size: .74rem; font-weight: 500; color: #94a3af; margin: 2px 0 0; letter-spacing: 0.2px; line-height: 1.2;">
        Mensajes de aviso y suspensión de membresías
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
  .sa-cfg-shell {
    max-width: 920px;
    margin: 0 auto;
    padding: 8px 4px 90px;
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .sa-cfg-card {
    background: linear-gradient(165deg, rgba(22, 20, 38, 0.95), rgba(14, 13, 24, 0.98));
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 22px;
    padding: 22px 24px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.42);
  }

  /* ── SELECTOR DE TIPO DE MENSAJE (AVISO vs SUSPENSIÓN) CON COLORES INSTITUCIONALES ── */
  .sa-msg-type-selector {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 20px;
  }

  .sa-msg-opt-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 13px 16px;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #94a3b8;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    text-align: left;
    width: 100%;
    outline: none;
  }

  .sa-msg-opt-btn:hover {
    background: rgba(255, 255, 255, 0.06);
    border-color: rgba(255, 255, 255, 0.2);
    color: #f8fafc;
  }

  .sa-msg-opt-btn:focus-visible {
    outline: 2px solid #c084fc;
    outline-offset: 2px;
  }
  .sa-msg-opt-btn.is-suspension:focus-visible {
    outline-color: #f87171;
  }

  .sa-msg-opt-btn .sa-opt-icon {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
    transition: all 0.2s ease;
  }

  .sa-msg-opt-btn .sa-opt-info {
    flex: 1;
    min-width: 0;
  }

  .sa-msg-opt-btn .sa-opt-title {
    display: block;
    font-size: 0.92rem;
    font-weight: 800;
    line-height: 1.2;
  }

  .sa-msg-opt-btn .sa-opt-sub {
    display: block;
    font-size: 0.72rem;
    font-weight: 500;
    color: #64748b;
    margin-top: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .sa-msg-opt-btn .sa-opt-check {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    border: 1.5px solid rgba(255, 255, 255, 0.2);
    color: transparent;
    transition: all 0.2s ease;
    flex-shrink: 0;
  }

  /* ── COLOR OPCIÓN: AVISO (Púrpura / Violeta Institucional) ── */
  .sa-msg-opt-btn.is-aviso .sa-opt-icon {
    background: rgba(168, 85, 247, 0.14);
    color: #c084fc;
    border: 1px solid rgba(168, 85, 247, 0.3);
  }
  .sa-msg-opt-btn.is-aviso.active {
    background: rgba(168, 85, 247, 0.16);
    border: 1.5px solid rgba(168, 85, 247, 0.55);
    color: #f8fafc;
    box-shadow: 0 8px 26px rgba(168, 85, 247, 0.25);
  }
  .sa-msg-opt-btn.is-aviso.active .sa-opt-icon {
    background: #8b5cf6;
    color: #ffffff;
    border-color: #a855f7;
    box-shadow: 0 0 16px rgba(139, 92, 246, 0.45);
  }
  .sa-msg-opt-btn.is-aviso.active .sa-opt-title {
    color: #e9d5ff;
  }
  .sa-msg-opt-btn.is-aviso.active .sa-opt-sub {
    color: #c084fc;
  }
  .sa-msg-opt-btn.is-aviso.active .sa-opt-check {
    background: #a855f7;
    border-color: #a855f7;
    color: #ffffff;
  }

  /* ── COLOR OPCIÓN: SUSPENSIÓN (Rojo Institucional) ── */
  .sa-msg-opt-btn.is-suspension .sa-opt-icon {
    background: rgba(239, 68, 68, 0.14);
    color: #f87171;
    border: 1px solid rgba(239, 68, 68, 0.3);
  }
  .sa-msg-opt-btn.is-suspension.active {
    background: rgba(239, 68, 68, 0.16);
    border: 1.5px solid rgba(239, 68, 68, 0.55);
    color: #f8fafc;
    box-shadow: 0 8px 26px rgba(239, 68, 68, 0.25);
  }
  .sa-msg-opt-btn.is-suspension.active .sa-opt-icon {
    background: #ef4444;
    color: #ffffff;
    border-color: #f87171;
    box-shadow: 0 0 16px rgba(239, 68, 68, 0.45);
  }
  .sa-msg-opt-btn.is-suspension.active .sa-opt-title {
    color: #fecaca;
  }
  .sa-msg-opt-btn.is-suspension.active .sa-opt-sub {
    color: #f87171;
  }
  .sa-msg-opt-btn.is-suspension.active .sa-opt-check {
    background: #ef4444;
    border-color: #ef4444;
    color: #ffffff;
  }

  /* ── CAMPOS DE FORMULARIO ── */
  .sa-cfg-field {
    margin-top: 18px;
  }

  .sa-cfg-field label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.74rem;
    font-weight: 800;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
  }

  .sa-cfg-input,
  .sa-cfg-select {
    width: 100%;
    background: rgba(12, 13, 22, 0.92);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 14px;
    color: #f8fafc;
    font-size: 0.92rem;
    font-weight: 600;
    padding: 13px 16px;
    outline: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
  }

  .sa-cfg-input:focus,
  .sa-cfg-select:focus {
    border-color: #a855f7;
    box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.18);
  }

  .sa-cfg-input.is-suspension:focus {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.18);
  }

  /* ── CAJA DE VISTA PREVIA REACTIVA ── */
  .sa-preview-box {
    margin-top: 18px;
    padding: 16px 18px;
    border-radius: 16px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .sa-preview-box.is-aviso {
    background: rgba(168, 85, 247, 0.08);
    border: 1px solid rgba(168, 85, 247, 0.35);
  }
  .sa-preview-box.is-aviso .sa-prev-icon {
    background: rgba(168, 85, 247, 0.18);
    color: #c084fc;
  }
  .sa-preview-box.is-aviso .sa-prev-tag {
    color: #c084fc;
  }

  .sa-preview-box.is-suspension {
    background: rgba(239, 68, 68, 0.09);
    border: 1px solid rgba(239, 68, 68, 0.35);
  }
  .sa-preview-box.is-suspension .sa-prev-icon {
    background: rgba(239, 68, 68, 0.18);
    color: #f87171;
  }
  .sa-preview-box.is-suspension .sa-prev-tag {
    color: #fca5a5;
  }

  .sa-prev-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
  }

  .sa-prev-tag {
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.6px;
  }

  .sa-prev-text {
    font-size: 0.88rem;
    color: #f8fafc;
    font-weight: 600;
    margin-top: 3px;
    line-height: 1.4;
  }

  /* ── BOTÓN GUARDAR DINÁMICO ── */
  .sa-btn-save {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 22px;
    border-radius: 13px;
    border: none;
    color: #ffffff;
    font-size: 0.88rem;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  .sa-btn-save.is-aviso {
    background: linear-gradient(135deg, #8a2be2, #9333ea);
    box-shadow: 0 8px 22px rgba(138, 43, 226, 0.38);
  }
  .sa-btn-save.is-aviso:hover {
    transform: translateY(-1px);
    box-shadow: 0 12px 28px rgba(138, 43, 226, 0.5);
  }

  .sa-btn-save.is-suspension {
    background: linear-gradient(135deg, #ef4444, #b91c1c);
    box-shadow: 0 8px 22px rgba(239, 68, 68, 0.38);
  }
  .sa-btn-save.is-suspension:hover {
    transform: translateY(-1px);
    box-shadow: 0 12px 28px rgba(239, 68, 68, 0.5);
  }

  @media (max-width: 640px) {
    .sa-msg-type-selector {
      grid-template-columns: 1fr;
      gap: 10px;
    }
  }
</style>

<div class="sa-cfg-shell">
  <?php
  // 1. Catálogo de Ejercicios (Arriba)
  $xpCatalogoEsAdmin = true;
  $xpCatalogoApiBase = 'admin';
  require BASE_DIR . '/views/partials/cfg_catalogo_ejercicios.php';
  ?>

  <!-- 2. Mensajes de Aviso y Suspensión de Entrenadores -->
  <section class="sa-cfg-card">

    <!-- ── SELECTOR DE OPCIÓN: MENSAJE DE AVISO / MENSAJE DE SUSPENSIÓN (CON SUS RESPECTIVOS COLORES) ── -->
    <div class="sa-msg-type-selector" role="radiogroup" aria-label="Tipo de mensaje a configurar">
      <button type="button" role="radio" aria-checked="true" class="sa-msg-opt-btn is-aviso active" id="btnOptAviso" onclick="cambiarTipoMensaje('aviso')">
        <div class="sa-opt-icon" aria-hidden="true"><i class="ti ti-bell"></i></div>
        <div class="sa-opt-info">
          <span class="sa-opt-title">Mensaje de Aviso</span>
          <span class="sa-opt-sub">Semana previa al pago (-7 días)</span>
        </div>
        <div class="sa-opt-check" aria-hidden="true"><i class="ti ti-check"></i></div>
      </button>

      <button type="button" role="radio" aria-checked="false" class="sa-msg-opt-btn is-suspension" id="btnOptSuspension" onclick="cambiarTipoMensaje('suspension')">
        <div class="sa-opt-icon" aria-hidden="true"><i class="ti ti-lock-pause"></i></div>
        <div class="sa-opt-info">
          <span class="sa-opt-title">Mensaje de Suspensión</span>
          <span class="sa-opt-sub">Web pausada o vencida</span>
        </div>
        <div class="sa-opt-check" aria-hidden="true"><i class="ti ti-check"></i></div>
      </button>
    </div>

    <!-- Alcance: Todos o entrenador específico -->
    <div class="sa-cfg-field">
      <label for="cfgAlcanceEntrenador">
        <i class="ti ti-users" id="cfgAlcanceIcon" style="color:#a855f7;"></i> Aplicar configuración a
      </label>
      <select id="cfgAlcanceEntrenador" class="sa-cfg-select" onchange="cambiarAlcanceMensaje(this.value)">
        <option value="todos"
                data-msg-aviso="<?= e($mensajeAvisoGlobal) ?>"
                data-msg-suspension="<?= e($mensajePausaGlobal) ?>">
          Todos los entrenadores (Configuración general)
        </option>
        <?php foreach ($entrenadores as $ent):
          $eid = (int)$ent['id'];
          $msgSuspEnt = trim((string)($ent['membresia']['mensaje_bloqueo'] ?? ''));
          if ($msgSuspEnt === '') $msgSuspEnt = $mensajePausaGlobal;
          $msgAvisoEnt = trim((string)($ent['membresia']['mensaje_aviso'] ?? ''));
          if ($msgAvisoEnt === '') $msgAvisoEnt = $mensajeAvisoGlobal;
        ?>
          <option value="<?= $eid ?>"
                  data-msg-aviso="<?= e($msgAvisoEnt) ?>"
                  data-msg-suspension="<?= e($msgSuspEnt) ?>">
            Entrenador: <?= e($ent['nombre']) ?> (<?= e($ent['email']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Campo dinámico de Mensaje -->
    <div class="sa-cfg-field">
      <label for="cfgInputMensaje" id="cfgLabelMensaje">
        <i class="ti ti-bell" id="cfgLabelIcon" style="color:#c084fc;"></i>
        <span id="cfgLabelTexto">MENSAJE DE AVISO (PREVIO A FECHA DE PAGO)</span>
      </label>
      <input type="text"
             id="cfgInputMensaje"
             class="sa-cfg-input is-aviso"
             maxlength="255"
             value="<?= e($mensajeAvisoGlobal) ?>"
             placeholder="Recordatorio: Se acerca tu fecha de pago de la membresía mensual. Recuerda realizar tu abono a tiempo."
             oninput="actualizarVistaPreviaMensaje(this.value)">
    </div>

    <!-- Vista Previa Reactiva -->
    <div class="sa-preview-box is-aviso" id="saPreviewBox">
      <div class="sa-prev-icon" id="saPrevIcon">
        <i class="ti ti-bell"></i>
      </div>
      <div style="min-width:0;">
        <div class="sa-prev-tag" id="saPrevTag">
          VISTA PREVIA DEL AVISO PREVIO AL PAGO
        </div>
        <div id="cfgPreviewTexto" class="sa-prev-text">
          <?= e($mensajeAvisoGlobal) ?>
        </div>
      </div>
    </div>

    <!-- Botón de Guardado -->
    <div style="display:flex;justify-content:flex-end;margin-top:20px;">
      <button type="button" class="sa-btn-save is-aviso" id="btnGuardarMensaje" onclick="guardarConfiguracionMensaje()">
        <i class="ti ti-device-floppy"></i> <span id="btnGuardarTexto">Guardar Mensaje de Aviso</span>
      </button>
    </div>
  </section>

  <?php
  // 3. Herramientas (Importador IA + Copia de Seguridad)
  $xpHerramientasEsAdmin = true;
  $xpHerramientasApiBase = 'admin';
  require BASE_DIR . '/views/partials/cfg_herramientas_ia_backup.php';
  ?>

  <!-- 4. Gestión de Atletas (Super Admin) -->
  <section class="card-bento" style="margin-top:4px;">
    <div class="gu-header">
      <div class="gu-title">
        <i class="ti ti-users" style="color:var(--accent)"></i>
        <h3>Gestión de Atletas</h3>
      </div>
      <div class="gu-search-wrap">
        <i class="ti ti-search"></i>
        <input type="search" id="atletaSearch" name="xp_admin_atleta_search_nofill" placeholder="Buscar por nombre, email o entrenador..." class="gu-search" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
      </div>
    </div>

    <!-- Panel de detalle inline -->
    <div id="atletaPanel" class="atleta-panel" style="display:none"></div>

    <!-- Tabla de atletas -->
    <div class="gu-table-wrap">
      <table class="gu-table">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Email</th>
          </tr>
        </thead>
        <tbody id="atletasTbody">
          <?php
          $atletasGestorAdmin = $atletasGestorList ?? array_filter($atletasList ?? [], static fn($r) => !empty($r['email']));
          foreach ($atletasGestorAdmin as $a):
            $edadAtl = '--';
            if (!empty($a['fecha_nac'])) {
                try {
                    $edadAtl = (new DateTime((string)$a['fecha_nac']))->diff(new DateTime('today'))->y . ' años';
                } catch (\Throwable $e) {}
            }
          ?>
          <tr class="atleta-row" data-id="<?= (int)($a['usuario_id'] ?? 0) ?>"
              data-cliente="<?= (int)($a['cliente_id'] ?? 0) ?>"
              data-nombre="<?= e($a['nombre'] ?? '') ?>"
              data-email="<?= e($a['email'] ?? '') ?>"
              data-cedula="<?= e($a['cedula'] ?? '--') ?>"
              data-edad="<?= e($edadAtl) ?>"
              data-peso="<?= !empty($a['peso_kg']) ? (float)$a['peso_kg'].' kg' : '--' ?>"
              data-altura="<?= !empty($a['altura_cm']) ? (float)$a['altura_cm'].' cm' : '--' ?>"
              data-objetivo="<?= e(!empty($a['objetivo']) ? $a['objetivo'] : '--') ?>"
              data-entrenador="<?= e(!empty($a['entrenador_nombre']) ? $a['entrenador_nombre'] : 'Sin entrenador') ?>"
              data-activo="<?= (int)($a['activo'] ?? 1) ?>"
              data-estado="<?= e($a['estado_cuenta'] ?? 'activo') ?>"
              data-desde="<?= !empty($a['creado_en']) ? date('d/m/Y', strtotime((string)$a['creado_en'])) : '--' ?>">
            <td>
              <div class="atleta-name-cell">
                <div class="atleta-avatar"><?= strtoupper(substr((string)($a['nombre'] ?? 'AT'), 0, 2)) ?></div>
                <div style="min-width:0;">
                  <strong class="atleta-fullname" style="display:block;"><?= e($a['nombre'] ?? '') ?></strong>
                  <?php if (!empty($a['entrenador_nombre'])): ?>
                    <span style="font-size:0.7rem;color:#a855f7;font-weight:600;">Coach: <?= e($a['entrenador_nombre']) ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </td>
            <td class="gu-muted gu-email-cell" title="<?= e($a['email'] ?? '') ?>"><?= e($a['email'] ?? '') ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($atletasGestorAdmin)): ?>
          <tr><td colspan="2" style="text-align:center;color:var(--muted);padding:32px">No hay atletas registrados en el sistema.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <?php
  // 5. Paleta de Colores (Al final, en desplegable)
  $xpPaletaEsAdmin      = true;
  $xpPaletaGlobalActivo = !empty($temaGlobalActivo);
  require BASE_DIR . '/views/partials/cfg_paleta_colores.php';
  ?>
</div>

<!-- ── Modal Cambiar Contraseña Atleta (Super Admin) ── -->
<div id="pwdModal" class="pwd-modal-backdrop" style="display:none">
  <div class="pwd-modal">
    <div class="pwd-modal-icon"><i class="ti ti-lock"></i></div>
    <h3>Cambiar Contraseña</h3>
    <p id="pwdModalFor" class="pwd-modal-sub"></p>
    <input type="hidden" id="pwdTargetId">
    <div class="form-group" style="margin-top:16px;text-align:left">
      <label>Nueva Contraseña <span style="color:var(--accent)">*</span></label>
      <input type="password" id="pwdNew" placeholder="Mínimo 6 caracteres">
    </div>
    <div class="form-group" style="text-align:left">
      <label>Confirmar Contraseña <span style="color:var(--accent)">*</span></label>
      <input type="password" id="pwdConfirm" placeholder="Repite la contraseña">
    </div>
    <div class="pwd-modal-actions">
      <button type="button" class="btn-xp btn-ghost" onclick="closePwdModal()">
        <i class="ti ti-x"></i> Cancelar
      </button>
      <button type="button" class="btn-xp btn-warn" onclick="submitPwd()">
        <i class="ti ti-lock"></i> Cambiar Contraseña
      </button>
    </div>
  </div>
</div>

<style>
  .gu-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 18px; flex-wrap: wrap; }
  .gu-title { display: flex; align-items: center; gap: 10px; }
  .gu-title h3 { font-size: 1.05rem; font-weight: 700; margin: 0; }
  .gu-search-wrap { display: flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 8px 14px; flex: 1; min-width: 220px; max-width: 340px; }
  .gu-search-wrap i { color: var(--muted); flex-shrink: 0; }
  .gu-search { background: none; border: none; outline: none; color: var(--text); font-family: 'Inter', sans-serif; font-size: .875rem; width: 100%; }
  .gu-search::placeholder { color: var(--muted); }
  .gu-table-wrap { overflow-x: hidden; border-radius: 12px; }
  .gu-table { width: 100%; border-collapse: collapse; font-size: .86rem; table-layout: fixed; }
  .gu-table th { text-align: left; padding: 10px 10px; font-size: .7rem; text-transform: uppercase; letter-spacing: 1px; color: var(--muted); border-bottom: 1px solid rgba(255,255,255,0.07); white-space: nowrap; }
  .gu-table th:first-child { width: 48%; }
  .gu-table th:last-child { width: 52%; }
  .gu-table td { padding: 12px 10px; border-bottom: 1px solid rgba(255,255,255,0.04); vertical-align: middle; }
  .gu-table th:first-child, .gu-table td:first-child { padding-left: 0; }
  .gu-table th:last-child, .gu-table td:last-child { padding-right: 0; }
  .atleta-row { cursor: pointer; transition: background .18s; }
  .atleta-row:hover { background: rgba(255,255,255,0.04); }
  .atleta-row.is-selected { background: rgba(139,92,246,0.06); }
  .atleta-name-cell { display: flex; align-items: center; gap: 10px; min-width: 0; }
  .atleta-fullname { line-height: 1.25; overflow-wrap: break-word; min-width: 0; }
  .atleta-avatar { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), #0ea5e9); display: grid; place-items: center; font-size: .75rem; font-weight: 800; color: #0b0e1a; flex-shrink: 0; }
  .gu-muted { color: var(--muted); font-size: .8rem; }
  .gu-email-cell { overflow-wrap: anywhere; word-break: break-word; line-height: 1.3; }
  .gu-badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: .7rem; font-weight: 700; }
  .gu-badge-on  { background: rgba(139,92,246,.15); color: var(--accent); }
  .gu-badge-off { background: rgba(239,68,68,.15); color: #f87171; }
  .atleta-panel { margin-bottom: 16px; background: linear-gradient(135deg, rgba(139,92,246,0.08), rgba(14,165,233,0.06)); border: 1px solid rgba(139,92,246,0.2); border-radius: 16px; padding: 20px; position: relative; }
  .panel-close { position: absolute; top: 12px; right: 12px; background: rgba(255,255,255,0.08); border: none; color: var(--muted); width: 28px; height: 28px; border-radius: 50%; cursor: pointer; display: grid; place-items: center; }
  .panel-top { display: flex; align-items: center; gap: 14px; margin-bottom: 14px; }
  .panel-avatar { width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), #0ea5e9); display: grid; place-items: center; font-size: 1.1rem; font-weight: 800; color: #0b0e1a; flex-shrink: 0; }
  .panel-name { font-size: 1.1rem; font-weight: 800; margin-bottom: 2px; }
  .panel-role { font-size: .7rem; text-transform: uppercase; letter-spacing: 1px; color: var(--muted); }
  .panel-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); gap: 10px; margin-bottom: 14px; }
  .panel-stat { background: rgba(255,255,255,0.04); border-radius: 10px; padding: 10px 12px; }
  .panel-stat-lbl { font-size: .6rem; text-transform: uppercase; letter-spacing: 1px; color: var(--muted); margin-bottom: 3px; }
  .panel-stat-val { font-size: .9rem; font-weight: 700; color: var(--text); }
  .panel-actions { display: flex; gap: 8px; flex-wrap: wrap; }
  .pwd-modal-backdrop { position: fixed; inset:0; background: rgba(0,0,0,0.65); backdrop-filter: blur(6px); z-index: 9999; display: grid; place-items: center; }
  .pwd-modal { background: #0f1623; border: 1px solid rgba(255,255,255,0.12); border-radius: 20px; padding: 32px; max-width: 440px; width: 90%; text-align: center; }
  .pwd-modal-icon { width: 56px; height: 56px; border-radius: 16px; background: linear-gradient(135deg, #f59e0b, #d97706); display: grid; place-items: center; font-size: 1.6rem; color: #fff; margin: 0 auto 14px; }
  .pwd-modal h3 { font-size: 1.2rem; font-weight: 800; margin-bottom: 4px; }
  .pwd-modal-sub { font-size: .82rem; color: var(--muted); margin-bottom: 4px; }
  .pwd-modal-actions { display: flex; gap: 10px; justify-content: center; margin-top: 20px; }
  .btn-xp.btn-warn { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; border: none; }
  .btn-xp.btn-ghost { background: transparent; border: 1px solid rgba(255,255,255,0.15); color: var(--text); }
</style>

<script>
let currentTipo = 'aviso'; // 'aviso' | 'suspension'

const DEFAULT_MSGS = {
  aviso: <?= json_encode($mensajeAvisoGlobal, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
  suspension: <?= json_encode($mensajePausaGlobal, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
};

function cambiarTipoMensaje(tipo) {
  currentTipo = tipo;

  const btnAviso = document.getElementById('btnOptAviso');
  const btnSusp = document.getElementById('btnOptSuspension');
  const lblIcon = document.getElementById('cfgLabelIcon');
  const lblTexto = document.getElementById('cfgLabelTexto');
  const inp = document.getElementById('cfgInputMensaje');
  const prevBox = document.getElementById('saPreviewBox');
  const prevIcon = document.getElementById('saPrevIcon');
  const prevTag = document.getElementById('saPrevTag');
  const btnSave = document.getElementById('btnGuardarMensaje');
  const btnSaveTexto = document.getElementById('btnGuardarTexto');

  const sel = document.getElementById('cfgAlcanceEntrenador');
  const opt = sel?.options[sel.selectedIndex];

  if (tipo === 'aviso') {
    btnAviso?.classList.add('active');
    btnSusp?.classList.remove('active');
    btnAviso?.setAttribute('aria-checked', 'true');
    btnSusp?.setAttribute('aria-checked', 'false');

    if (lblIcon) { lblIcon.className = 'ti ti-bell'; lblIcon.style.color = '#c084fc'; }
    if (lblTexto) lblTexto.textContent = 'MENSAJE DE AVISO (PREVIO A FECHA DE PAGO)';

    inp?.classList.remove('is-suspension');
    inp?.classList.add('is-aviso');
    if (inp) inp.placeholder = 'Recordatorio: Se acerca tu fecha de pago de la membresía mensual. Recuerda realizar tu abono a tiempo.';

    prevBox?.classList.remove('is-suspension');
    prevBox?.classList.add('is-aviso');
    if (prevIcon) prevIcon.innerHTML = '<i class="ti ti-bell"></i>';
    if (prevTag) prevTag.textContent = 'VISTA PREVIA DEL AVISO PREVIO AL PAGO';

    btnSave?.classList.remove('is-suspension');
    btnSave?.classList.add('is-aviso');
    if (btnSaveTexto) btnSaveTexto.textContent = 'Guardar Mensaje de Aviso';

    const msg = opt ? (opt.getAttribute('data-msg-aviso') || DEFAULT_MSGS.aviso) : DEFAULT_MSGS.aviso;
    if (inp) inp.value = msg;
    actualizarVistaPreviaMensaje(msg);
  } else {
    btnSusp?.classList.add('active');
    btnAviso?.classList.remove('active');
    btnSusp?.setAttribute('aria-checked', 'true');
    btnAviso?.setAttribute('aria-checked', 'false');

    if (lblIcon) { lblIcon.className = 'ti ti-lock-pause'; lblIcon.style.color = '#f87171'; }
    if (lblTexto) lblTexto.textContent = 'MENSAJE DE SUSPENSIÓN (WEB PAUSADA O VENCIDA)';

    inp?.classList.remove('is-aviso');
    inp?.classList.add('is-suspension');
    if (inp) inp.placeholder = 'La web ha sido pausada hasta que logres pagar la membresía.';

    prevBox?.classList.remove('is-aviso');
    prevBox?.classList.add('is-suspension');
    if (prevIcon) prevIcon.innerHTML = '<i class="ti ti-lock-pause"></i>';
    if (prevTag) prevTag.textContent = 'VISTA PREVIA DEL AVISO DE SUSPENSIÓN';

    btnSave?.classList.remove('is-aviso');
    btnSave?.classList.add('is-suspension');
    if (btnSaveTexto) btnSaveTexto.textContent = 'Guardar Mensaje de Suspensión';

    const msg = opt ? (opt.getAttribute('data-msg-suspension') || DEFAULT_MSGS.suspension) : DEFAULT_MSGS.suspension;
    if (inp) inp.value = msg;
    actualizarVistaPreviaMensaje(msg);
  }
}

function cambiarAlcanceMensaje(val) {
  const sel = document.getElementById('cfgAlcanceEntrenador');
  const opt = sel?.options[sel.selectedIndex];
  if (!opt) return;

  const attr = currentTipo === 'aviso' ? 'data-msg-aviso' : 'data-msg-suspension';
  const def = currentTipo === 'aviso' ? DEFAULT_MSGS.aviso : DEFAULT_MSGS.suspension;
  const msg = opt.getAttribute(attr) || def;

  const inp = document.getElementById('cfgInputMensaje');
  if (inp) {
    inp.value = msg;
    actualizarVistaPreviaMensaje(msg);
  }
}

function actualizarVistaPreviaMensaje(val) {
  const prev = document.getElementById('cfgPreviewTexto');
  if (!prev) return;
  const txt = String(val || '').trim();
  const def = currentTipo === 'aviso' ? DEFAULT_MSGS.aviso : DEFAULT_MSGS.suspension;
  prev.textContent = txt !== '' ? txt : def;
}

async function guardarConfiguracionMensaje() {
  const btn = document.getElementById('btnGuardarMensaje');
  const alcance = document.getElementById('cfgAlcanceEntrenador')?.value || 'todos';
  const mensaje = document.getElementById('cfgInputMensaje')?.value || '';

  if (btn) btn.disabled = true;
  try {
    const res = await fetch(APP + '/admin/api-guardar-configuracion', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify({
        csrf_token: CSRF,
        tipo: currentTipo === 'aviso' ? 'mensaje_aviso' : 'mensaje_suspension',
        alcance: alcance,
        mensaje: mensaje,
        mensaje_bloqueo: mensaje
      })
    });
    const d = await res.json();
    if (d.ok) {
      const attr = currentTipo === 'aviso' ? 'data-msg-aviso' : 'data-msg-suspension';
      const sel = document.getElementById('cfgAlcanceEntrenador');
      if (sel) {
        if (alcance === 'todos') {
          Array.from(sel.options).forEach(o => o.setAttribute(attr, d.mensaje));
          if (currentTipo === 'aviso') DEFAULT_MSGS.aviso = d.mensaje;
          else DEFAULT_MSGS.suspension = d.mensaje;
        } else {
          const opt = sel.options[sel.selectedIndex];
          if (opt) opt.setAttribute(attr, d.mensaje);
        }
      }
      Swal.fire({
        icon: 'success',
        title: currentTipo === 'aviso' ? 'Mensaje de aviso guardado' : 'Mensaje de suspensión guardado',
        text: d.msg,
        timer: 1500,
        showConfirmButton: false,
        background: '#12131f',
        color: '#fff'
      });
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: d.msg || 'No se pudo guardar.', background: '#12131f', color: '#fff' });
    }
  } catch (e) {
    Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo conectar con el servidor.', background: '#12131f', color: '#fff' });
  } finally {
    if (btn) btn.disabled = false;
  }
}

/* ── Gestión de Atletas (Super Admin) ── */
document.getElementById('atletaSearch')?.addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.atleta-row').forEach(function(row) {
    const txt = (row.dataset.nombre || '').toLowerCase() + ' ' + (row.dataset.email || '').toLowerCase() + ' ' + (row.dataset.entrenador || '').toLowerCase();
    row.style.display = txt.includes(q) ? '' : 'none';
  });
});

let selectedRow = null;
document.querySelectorAll('.atleta-row').forEach(function(row) {
  row.addEventListener('click', function() {
    if (selectedRow === row) {
      row.classList.remove('is-selected');
      selectedRow = null;
      closePanel();
      return;
    }
    if (selectedRow) selectedRow.classList.remove('is-selected');
    selectedRow = row;
    row.classList.add('is-selected');
    openPanel(row.dataset);
  });
});

function escCfgHtml(str) {
  return String(str == null ? '' : str)
    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function openPanel(d) {
  const panel = document.getElementById('atletaPanel');
  if (!panel) return;
  const initials = escCfgHtml((d.nombre || '').substring(0, 2).toUpperCase());
  const badgeClass = d.estado === 'activo' ? 'gu-badge-on' : 'gu-badge-off';
  const badgeTxt   = d.estado === 'activo' ? 'Activo' : (d.estado === 'rechazado' ? 'Rechazado' : 'Pendiente');
  const uidNum = parseInt(d.id, 10) || 0;

  const btnDesactivar = d.estado === 'activo'
    ? `<button type="button" class="btn-xp" data-cfg-action="estado" data-accion="rechazar" style="font-size:.8rem;padding:7px 14px;background:rgba(239,68,68,0.15);color:#ef4444;border:1px solid rgba(239,68,68,0.3);">
         <i class="ti ti-user-off"></i> Desactivar
       </button>`
    : `<button type="button" class="btn-xp" data-cfg-action="estado" data-accion="aprobar" style="font-size:.8rem;padding:7px 14px;background:rgba(16,185,129,0.15);color:#10b981;border:1px solid rgba(16,185,129,0.3);">
         <i class="ti ti-user-check"></i> Activar
       </button>`;

  panel.innerHTML = `
    <button type="button" class="panel-close" onclick="closePanel()"><i class="ti ti-x"></i></button>
    <div class="panel-top">
      <div class="panel-avatar">${initials}</div>
      <div>
        <div class="panel-name">${escCfgHtml(d.nombre)}</div>
        <div class="panel-role">Atleta &bull; Coach: ${escCfgHtml(d.entrenador || 'Sin entrenador')} &bull; <span class="gu-badge ${badgeClass}">${badgeTxt}</span></div>
      </div>
    </div>
    <div class="panel-stats">
      <div class="panel-stat"><div class="panel-stat-lbl">Email</div><div class="panel-stat-val" style="font-size:.78rem;word-break:break-all">${escCfgHtml(d.email)}</div></div>
      <div class="panel-stat"><div class="panel-stat-lbl">Cédula</div><div class="panel-stat-val">${escCfgHtml(d.cedula || '--')}</div></div>
      <div class="panel-stat"><div class="panel-stat-lbl">Edad</div><div class="panel-stat-val">${escCfgHtml(d.edad || '--')}</div></div>
      <div class="panel-stat"><div class="panel-stat-lbl">Peso Actual</div><div class="panel-stat-val">${escCfgHtml(d.peso)}</div></div>
      <div class="panel-stat"><div class="panel-stat-lbl">Altura</div><div class="panel-stat-val">${escCfgHtml(d.altura || '--')}</div></div>
      <div class="panel-stat"><div class="panel-stat-lbl">Objetivo</div><div class="panel-stat-val" style="font-size:.78rem">${escCfgHtml(d.objetivo)}</div></div>
      <div class="panel-stat"><div class="panel-stat-lbl">Desde</div><div class="panel-stat-val">${escCfgHtml(d.desde)}</div></div>
    </div>
    <div class="panel-actions" style="display:flex; flex-wrap:wrap; gap:8px;">
      ${btnDesactivar}
      <button type="button" class="btn-xp btn-warn" data-cfg-action="pwd" style="font-size:.8rem;padding:7px 14px">
        <i class="ti ti-lock"></i> Contraseña
      </button>
      <button type="button" class="btn-xp" data-cfg-action="eliminar" style="font-size:.8rem;padding:7px 14px;background:rgba(239,68,68,0.18);color:#ef4444;border:1px solid rgba(239,68,68,0.4);">
        <i class="ti ti-trash"></i> Eliminar
      </button>
    </div>
  `;
  const btnEstado = panel.querySelector('[data-cfg-action="estado"]');
  if (btnEstado) btnEstado.addEventListener('click', () => toggleEstadoAtleta(uidNum, btnEstado.dataset.accion));
  const btnPwd = panel.querySelector('[data-cfg-action="pwd"]');
  if (btnPwd) btnPwd.addEventListener('click', () => openPwdModal(uidNum, d.nombre || ''));
  const btnDel = panel.querySelector('[data-cfg-action="eliminar"]');
  if (btnDel) btnDel.addEventListener('click', () => eliminarAtleta(uidNum, d.nombre || ''));
  panel.style.display = 'block';
}

function closePanel() {
  const panel = document.getElementById('atletaPanel');
  if (panel) {
    panel.style.display = 'none';
    panel.innerHTML = '';
  }
  if (selectedRow) { selectedRow.classList.remove('is-selected'); selectedRow = null; }
}

function toggleEstadoAtleta(usuarioId, accion) {
  const isActivar = accion === 'aprobar';
  Swal.fire({
    title: isActivar ? '¿Activar Atleta?' : '¿Desactivar Atleta?',
    text: isActivar ? 'El usuario podrá ingresar al sistema.' : 'El usuario no podrá ingresar al sistema.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: isActivar ? '#10b981' : '#ef4444',
    cancelButtonColor: '#6b7280',
    confirmButtonText: isActivar ? 'Sí, activar' : 'Sí, desactivar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      fetch(APP + '/admin/api-aprobar-atleta', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ csrf_token: CSRF, usuario_id: usuarioId, accion: accion })
      })
      .then(res => res.json())
      .then(data => {
        if (data.ok) {
          Swal.fire({ icon: 'success', title: '¡Actualizado!', text: data.msg, showConfirmButton: false, timer: 1400 })
            .then(() => location.reload());
        } else {
          Swal.fire('Error', data.msg, 'error');
        }
      })
      .catch(() => Swal.fire('Error', 'Problema de red', 'error'));
    }
  });
}

function openPwdModal(userId, nombre) {
  document.getElementById('pwdTargetId').value = userId;
  document.getElementById('pwdModalFor').textContent = 'Establecer nueva contraseña para: ' + nombre;
  document.getElementById('pwdNew').value = '';
  document.getElementById('pwdConfirm').value = '';
  document.getElementById('pwdModal').style.display = 'grid';
}
function closePwdModal() {
  document.getElementById('pwdModal').style.display = 'none';
}
async function submitPwd() {
  const uid  = document.getElementById('pwdTargetId').value;
  const pwd  = document.getElementById('pwdNew').value;
  const pwd2 = document.getElementById('pwdConfirm').value;
  if (!pwd || pwd.length < 6) { Xp.toast('error', 'Mínimo 6 caracteres.'); return; }
  if (pwd !== pwd2) { Xp.toast('error', 'Las contraseñas no coinciden.'); return; }
  const r = await Xp.post('admin/cambiar-password-atleta', { usuario_id: uid, password: pwd })
                   .catch(() => ({ ok: false, msg: 'Error de conexión.' }));
  if (r.ok) { Xp.toast('success', r.msg); closePwdModal(); }
  else       { Xp.toast('error', r.msg); }
}
document.getElementById('pwdModal')?.addEventListener('click', function(e) {
  if (e.target === this) closePwdModal();
});

function eliminarAtleta(uid, nombre) {
  Swal.fire({
    title: `¿Eliminar a ${nombre}?`,
    html: `<div style="text-align:left;font-size:0.9rem;color:#cbd5e1;line-height:1.5;">
             <p style="margin-bottom:8px;font-weight:600;color:#f87171;">⚠️ Esta acción es irreversible.</p>
             <p style="margin-bottom:0;color:#94a3b8;">Se eliminará permanentemente al atleta junto con sus rutinas, asistencias, registros de peso y pagos.</p>
           </div>`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#475569',
    confirmButtonText: 'Sí, eliminar atleta',
    cancelButtonText: 'Cancelar',
    background: '#151c2e',
    color: '#e2e8f0'
  }).then(async (result) => {
    if (!result.isConfirmed) return;
    try {
      const r = await Xp.post('admin/eliminar-atleta', { usuario_id: uid });
      if (r.ok) {
        Swal.fire({ icon: 'success', title: 'Atleta eliminado', text: r.msg, background: '#151c2e', color: '#e2e8f0' });
        closePanel();
        const row = document.querySelector(`.atleta-row[data-id="${uid}"]`);
        if (row) row.remove();
      } else {
        Swal.fire('Error', r.msg || 'No se pudo eliminar el atleta.', 'error');
      }
    } catch (_) {
      Swal.fire('Error', 'Problema de conexión con el servidor.', 'error');
    }
  });
}
</script>

<?php require BASE_DIR . '/views/layout/footer.php'; ?>
