<?php
$pageTitle = 'Configuracion';
require __DIR__ . '/../layout/header.php';
?><!-- ── HEADER DE CONFIGURACIÓN (PREMIUM BENTO UI) ── -->
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
        Administra tu perfil y los atletas del sistema
      </p>
    </div>
  </div>
  
  <div class="header-actions">
    <div id="xpPwaSyncPill" class="xp-pwa-header-pill online" title="Conectado a la red">
      <span class="xp-status-dot"></span><span class="xp-status-text">Online</span>
    </div>

    <?php 
      $unread_hdr = contarNotificacionesNoLeidas((int)($_SESSION['user_id'] ?? 0), 'entrenador');
    ?>
    <div class="header-action-buttons">
      <div class="notif-wrapper" style="position:relative; flex-shrink: 0;">
        <button type="button" class="db-icon-btn btn-notif-trigger xp-bell-trigger" id="btnNotifToggle" title="Notificaciones" aria-label="Notificaciones" onclick="toggleXpNotifDropdown(this, event)" style="cursor:pointer; position:relative;">
          <i class="ti ti-bell"></i>
          <span class="xp-notif-badge" style="<?= $unread_hdr > 0 ? '' : 'display:none;' ?>"><?= $unread_hdr ?></span>
        </button>
      </div>

      <!-- Botón Logout -->
      <a href="<?= APP_URL ?>/auth/logout" class="db-icon-btn" title="Cerrar sesión" aria-label="Cerrar sesión" style="flex-shrink: 0;">
        <i class="ti ti-logout"></i>
      </a>
    </div>
  </div>
</div>

<!-- ── BANNER / CARD BENTO DE INSTALACIÓN PWA (ENTRENADOR) ── -->
<div class="xp-pwa-install-card">
  <div class="xp-pwa-install-left">
    <div class="xp-pwa-install-icon-wrap" aria-hidden="true">
      <i class="ti ti-device-mobile-down"></i>
    </div>
    <div class="xp-pwa-install-info">
      <span class="xp-pwa-install-badge"><span class="badge-dot"></span> App Oficial & Offline</span>
      <h3 class="xp-pwa-install-title">Descargar Atlas Fit en este dispositivo</h3>
    </div>
  </div>
  <button type="button" class="xp-pwa-install-btn" onclick="instalarXprodApp()">
    <i class="ti ti-download"></i>
    <span>Instalar App</span>
  </button>
</div>

<?php
$xpPaletaEsAdmin = false;
require __DIR__ . '/../partials/cfg_paleta_colores.php';
?>

<?php
  $codRefCfg  = (string)($membresiaEnt['codigo_referido'] ?? '');
  $linkRefCfg = (string)($membresiaEnt['link_referido'] ?? (str_replace(' ', '%20', rtrim((string)APP_URL, '/')) . '/auth/registro?ref=' . rawurlencode($codRefCfg)));
?>
<!-- ── Código y Link de Invitación de Entrenador ── -->
<div id="cfgCodigoEntCard" class="form-container xp-ref-cfg-card" style="width: 100%; margin-bottom: 25px;">
  <div class="xp-ref-head">
    <div class="xp-ref-cfg-ico" aria-hidden="true">
      <i class="ti ti-qrcode"></i>
    </div>
    <div style="min-width:0;">
      <h3 style="margin: 0; font-size: 1.05rem; color: #fff; font-weight: 800;">Tu Código de Entrenador</h3>
      <p style="margin: 2px 0 0; font-size: 0.74rem; color: #94a3b8;">Comparte tu link o código para vincular atletas al registrarse</p>
    </div>
  </div>

  <!-- Fila 1: Link + Botón Recargar + Botón Copiar Link -->
  <div class="xp-ref-field-group">
    <label for="inputLinkEntCfg" class="xp-ref-field-lbl">Link de invitación</label>
    <div class="xp-ref-inline-row">
      <input type="text" id="inputLinkEntCfg" class="xp-ref-input is-link" readonly value="<?= e($linkRefCfg) ?>" title="<?= e($linkRefCfg) ?>" onclick="this.select()" aria-label="Link de invitación">
      <button type="button" id="btnGenerarCodigoEnt" class="xp-ref-btn xp-ref-btn-icon xp-ref-btn-soft" onclick="generarNuevoCodigoEntrenador()" title="Generar nuevo link y código" aria-label="Generar nuevo link">
        <i class="ti ti-refresh"></i>
      </button>
      <button type="button" id="btnCopiarLinkEnt" class="xp-ref-btn xp-ref-btn-primary" onclick="copiarTextoEntrenador(window.entRefData.link, 'Link copiado')">
        <i class="ti ti-link"></i> <span>Copiar link</span>
      </button>
    </div>
  </div>

  <!-- Fila 2: Código (Editar -> Cancelar | Copiar -> Guardar) -->
  <form class="xp-ref-field-group" id="formEditarCodigoEnt" onsubmit="event.preventDefault(); guardarCodigoEntrenador();" style="margin-bottom:0;">
    <label for="inputCodigoEntCfg" class="xp-ref-field-lbl">Código</label>
    <div class="xp-ref-inline-row">
      <input type="text" id="inputCodigoEntCfg" name="codigo" class="xp-ref-input is-code" readonly value="<?= e($codRefCfg) ?>" maxlength="32" autocomplete="off" spellcheck="false" placeholder="Ej: FARES-FIT" aria-label="Código de entrenador" aria-describedby="hintCodigoEntCfg errCodigoEntCfg">
      <button type="button" id="btnToggleEditCodigoEnt" class="xp-ref-btn xp-ref-btn-soft" onclick="toggleEditarCodigoEnt()" aria-pressed="false">
        <i class="ti ti-pencil"></i> <span>Editar</span>
      </button>
      <button type="button" id="btnCopiarCodigoEnt" class="xp-ref-btn xp-ref-btn-ghost" onclick="if (document.getElementById('inputCodigoEntCfg')?.readOnly) copiarTextoEntrenador(window.entRefData.codigo, 'Código copiado');">
        <i class="ti ti-copy"></i> <span>Copiar</span>
      </button>
    </div>
    <small id="errCodigoEntCfg" class="xp-ref-edit-error" role="alert" style="display:none;"></small>
    <small id="hintCodigoEntCfg" class="xp-ref-edit-hint is-hidden">
      Usa entre 4 y 32 caracteres (letras, números o guion). Presiona Guardar para actualizar tu código y link.
    </small>
  </form>
</div>

<?php
  $tiendaActivaCfg = !empty($membresiaEnt['tienda_activa']) ? 1 : 0;
  $waNumCfg        = (string)($membresiaEnt['whatsapp_numero'] ?? '');
  $waMsgCfg        = (string)($membresiaEnt['whatsapp_mensaje'] ?? 'Hola, me interesa adquirir: {producto}');
?>
<!-- ── Tienda de Suplementos (Activación + Ajustes WhatsApp) ── -->
<div id="cfgTiendaCard" class="form-container xp-store-cfg-card <?= $tiendaActivaCfg === 1 ? 'is-store-on' : '' ?>" style="width: 100%; margin-bottom: 25px;">
  <div class="xp-store-toggle-row">
    <div style="display:flex; align-items:center; gap:12px; min-width:0; flex:1;">
      <div class="xp-store-cfg-ico">
        <i class="ti ti-building-store"></i>
      </div>
      <div style="min-width:0;">
        <h3 style="margin: 0; font-size: 1.08rem; color: #fff; font-weight: 700;">Activar tienda</h3>
        <p id="lblEstadoTiendaDesc" style="margin: 3px 0 0; font-size: 0.78rem; color: #94a3b8;">
          <?= $tiendaActivaCfg === 1
            ? 'Activada: visible para ti y todos tus atletas asignados.'
            : 'Desactivada: activa el switch para habilitar tu tienda y ajustes de WhatsApp.' ?>
        </p>
      </div>
    </div>
    <label class="xp-bento-switch" title="Activar o desactivar tienda de suplementos">
      <input type="checkbox" id="switchActivarTienda" <?= $tiendaActivaCfg === 1 ? 'checked' : '' ?> onchange="toggleActivarTienda(this)">
      <span class="xp-bento-slider"></span>
    </label>
  </div>

  <div id="panelAjustesTiendaWS" class="xp-ws-settings-panel <?= $tiendaActivaCfg === 1 ? 'is-open' : 'is-hidden' ?>">
    <div class="xp-ws-divider"></div>
    <h4 style="margin: 0 0 12px; font-size: 0.98rem; color: #fff; display: flex; align-items: center; gap: 8px; font-weight: 700;">
      <i class="ti ti-brand-whatsapp" style="color:#25D366; font-size: 1.3rem;"></i> Ajustes de Tienda (WhatsApp)
    </h4>
    <form class="form" id="formAjustesWhatsApp" onsubmit="event.preventDefault(); guardarAjustesWhatsApp();">
      <div class="form-group">
        <label for="whatsapp_numero">Número de WhatsApp (con código de país, sin +)</label>
        <input type="text" id="whatsapp_numero" name="whatsapp_numero" value="<?= e($waNumCfg) ?>" placeholder="Ej: 584249705822" inputmode="numeric" <?= $tiendaActivaCfg === 1 ? 'required' : 'disabled' ?> style="max-width: 260px;">
      </div>
      <div class="form-group">
        <label for="whatsapp_mensaje">Mensaje de Compra ({producto} se reemplazará automáticamente)</label>
        <textarea id="whatsapp_mensaje" name="whatsapp_mensaje" <?= $tiendaActivaCfg === 1 ? 'required' : 'disabled' ?>><?= e($waMsgCfg) ?></textarea>
      </div>
      <button class="form-submit-btn" id="btnGuardarWsAjustes" type="submit" <?= $tiendaActivaCfg === 1 ? '' : 'disabled' ?>>
        <i class="ti ti-device-floppy"></i> Guardar Ajustes
      </button>
    </form>
  </div>
</div>

<?php
$xpCatalogoEsAdmin = false;
$xpCatalogoApiBase = 'entrenador';
require __DIR__ . '/../partials/cfg_catalogo_ejercicios.php';

$xpHerramientasEsAdmin = false;
$xpHerramientasApiBase = 'entrenador';
require __DIR__ . '/../partials/cfg_herramientas_ia_backup.php';
?>

<!-- ── Gestion de Usuarios ── -->
<div class="card-bento" style="margin-top:24px">
  <div class="gu-header">
    <div class="gu-title">
      <i class="ti ti-users" style="color:var(--accent)"></i>
      <h3>Gestion de Atletas</h3>
    </div>
    <div class="gu-search-wrap">
      <i class="ti ti-search"></i>
      <input type="search" id="atletaSearch" placeholder="Buscar por nombre o email..." class="gu-search" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
    </div>
  </div>

  <!-- Panel de detalle inline -->
  <div id="atletaPanel" class="atleta-panel" style="display:none"></div>

  <!-- Tabla de atletas (solo Nombre y Email) -->
  <div class="gu-table-wrap">
    <table class="gu-table">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Email</th>
        </tr>
      </thead>
      <tbody id="atletasTbody">
        <?php foreach($atletasList as $a): ?>
        <tr class="atleta-row" data-id="<?= $a['usuario_id'] ?>"
            data-cliente="<?= $a['cliente_id'] ?>"
            data-nombre="<?= e($a['nombre']) ?>"
            data-email="<?= e($a['email']) ?>"
            data-peso="<?= $a['peso_kg'] ? (float)$a['peso_kg'].' kg' : '--' ?>"
            data-objetivo="<?= e($a['objetivo'] ?: '--') ?>"
            data-activo="<?= $a['activo'] ?>"
            data-estado="<?= $a['estado_cuenta'] ?>"
            data-desde="<?= date('d/m/Y', strtotime($a['creado_en'])) ?>">
          <td>
            <div class="atleta-name-cell">
              <div class="atleta-avatar"><?= strtoupper(substr($a['nombre'], 0, 2)) ?></div>
              <strong class="atleta-fullname"><?= e($a['nombre']) ?></strong>
            </div>
          </td>
          <td class="gu-muted gu-email-cell" title="<?= e($a['email']) ?>"><?= e($a['email']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($atletasList)): ?>
        <tr><td colspan="2" style="text-align:center;color:var(--muted);padding:32px">No tienes atletas registrados.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ── Modal Cambiar Contrasena ── -->
<div id="pwdModal" class="pwd-modal-backdrop" style="display:none">
  <div class="pwd-modal">
    <div class="pwd-modal-icon"><i class="ti ti-lock"></i></div>
    <h3>Cambiar Contrasena</h3>
    <p id="pwdModalFor" class="pwd-modal-sub"></p>
    <input type="hidden" id="pwdTargetId">
    <div class="form-group" style="margin-top:16px;text-align:left">
      <label>Nueva Contrasena <span style="color:var(--accent)">*</span></label>
      <input type="password" id="pwdNew" placeholder="Minimo 6 caracteres">
    </div>
    <div class="form-group" style="text-align:left">
      <label>Confirmar Contrasena <span style="color:var(--accent)">*</span></label>
      <input type="password" id="pwdConfirm" placeholder="Repite la contrasena">
    </div>
    <div class="pwd-modal-actions">
      <button class="btn-xp btn-ghost" onclick="closePwdModal()">
        <i class="ti ti-x"></i> Cancelar
      </button>
      <button class="btn-xp btn-warn" onclick="submitPwd()">
        <i class="ti ti-lock"></i> Cambiar Contrasena
      </button>
    </div>
  </div>
</div>

<style>
/* ── Tienda Toggle & Colores Predominantes por Componente ── */
.xp-store-cfg-card {
  background: linear-gradient(155deg, rgba(138, 43, 226, 0.13) 0%, rgba(15, 16, 22, 0.94) 68%) !important;
  border: 1px solid rgba(168, 85, 247, 0.28) !important;
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.28) !important;
  transition: background 0.28s ease, border-color 0.28s ease !important;
}
.xp-store-cfg-card.is-store-on {
  background: linear-gradient(155deg, rgba(16, 185, 129, 0.13) 0%, rgba(15, 16, 22, 0.94) 68%) !important;
  border-color: rgba(37, 211, 102, 0.32) !important;
}
.xp-store-toggle-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
}
.xp-store-cfg-ico {
  width: 42px;
  height: 42px;
  border-radius: 13px;
  background: linear-gradient(135deg, rgba(138, 43, 226, 0.28), rgba(99, 102, 241, 0.22));
  border: 1px solid rgba(168, 85, 247, 0.42);
  color: #d8b4fe;
  display: grid;
  place-items: center;
  font-size: 1.3rem;
  flex-shrink: 0;
  transition: all 0.25s ease;
}
.xp-store-cfg-card.is-store-on .xp-store-cfg-ico {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.26), rgba(37, 211, 102, 0.22));
  border-color: rgba(37, 211, 102, 0.45);
  color: #4ade80;
}
.xp-store-cfg-card.is-store-on .form-group input:focus,
.xp-store-cfg-card.is-store-on .form-group textarea:focus {
  border-color: #25D366 !important;
  box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.18) !important;
}
.xp-store-cfg-card.is-store-on .form-submit-btn {
  background: linear-gradient(135deg, #10b981, #059669);
  border-color: rgba(52, 211, 153, 0.45);
  color: #fff;
}
.xp-store-cfg-card.is-store-on .form-submit-btn:hover {
  background: linear-gradient(135deg, #25D366, #10b981);
  border-color: #34d399;
}
.xp-ws-settings-panel {
  margin-top: 14px;
  transition: opacity 0.22s ease;
}
.xp-ws-settings-panel.is-hidden {
  display: none;
}
.xp-ws-divider {
  height: 1px;
  background: linear-gradient(90deg, rgba(255,255,255,0.08), transparent);
  margin-bottom: 14px;
}

/* ── Tarjeta Código y Link de Entrenador ── */
.xp-ref-cfg-card {
  background: linear-gradient(155deg, rgba(138, 43, 226, 0.14) 0%, rgba(15, 16, 22, 0.94) 68%) !important;
  border: 1px solid rgba(168, 85, 247, 0.30) !important;
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.28) !important;
}
.xp-ref-head {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
}
.xp-ref-cfg-ico {
  width: 42px;
  height: 42px;
  border-radius: 13px;
  background: rgba(138, 43, 226, 0.22);
  border: 1px solid rgba(168, 85, 247, 0.45);
  color: #d8b4fe;
  display: grid;
  place-items: center;
  font-size: 1.35rem;
  flex-shrink: 0;
}
.xp-ref-field-group {
  margin-bottom: 12px;
}
.xp-ref-field-lbl {
  display: block;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #cbd5e1;
  margin-bottom: 6px;
}
.xp-ref-inline-row {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: nowrap;
  min-width: 0;
}
.xp-ref-input {
  flex: 1 1 0%;
  min-width: 0;
  width: 100%;
  background: rgba(10, 11, 16, 0.82);
  border: 1px solid rgba(255, 255, 255, 0.14);
  border-radius: 12px;
  padding: 10px 12px;
  color: #f8fafc;
  font-size: 0.84rem;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
}
.xp-ref-input.is-link {
  color: #cbd5e1;
  text-overflow: ellipsis;
}
.xp-ref-input.is-code {
  font-weight: 800;
  letter-spacing: 0.8px;
  text-transform: uppercase;
  color: #fff;
}
.xp-ref-input.is-code.is-editing {
  background: rgba(138, 43, 226, 0.14);
  border-color: rgba(168, 85, 247, 0.7);
  box-shadow: 0 0 0 3px rgba(138, 43, 226, 0.22);
}
.xp-ref-btn {
  padding: 10px 13px;
  border-radius: 12px;
  font-size: 0.78rem;
  font-weight: 700;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  transition: all 0.2s ease;
  border: 1px solid transparent;
  flex-shrink: 0;
  white-space: nowrap;
  outline: none;
}
.xp-ref-btn:focus-visible {
  box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.35);
}
.xp-ref-edit-hint.is-hidden {
  display: none !important;
}
.xp-ref-btn-icon {
  width: 40px;
  height: 40px;
  padding: 0;
  font-size: 1.05rem;
}
.xp-ref-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}
.xp-ref-btn-soft {
  background: rgba(168, 85, 247, 0.16);
  border-color: rgba(168, 85, 247, 0.38);
  color: #e9d5ff;
}
.xp-ref-btn-soft:hover:not(:disabled) {
  background: rgba(168, 85, 247, 0.28);
  color: #fff;
}
.xp-ref-btn-ghost {
  background: rgba(255, 255, 255, 0.06);
  border-color: rgba(255, 255, 255, 0.14);
  color: #e2e8f0;
}
.xp-ref-btn-ghost:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.12);
  color: #fff;
}
.xp-ref-btn-primary {
  background: linear-gradient(135deg, #8a2be2, #6d28d9);
  color: #fff;
  box-shadow: 0 4px 14px rgba(138, 43, 226, 0.36);
}
.xp-ref-btn-primary:hover:not(:disabled) {
  filter: brightness(1.08);
}
.xp-ref-btn-save {
  background: linear-gradient(135deg, #10b981, #059669);
  color: #fff;
  box-shadow: 0 4px 14px rgba(16, 185, 129, 0.34);
}
.xp-ref-btn-save:hover:not(:disabled) {
  filter: brightness(1.08);
}
.xp-ref-edit-error {
  display: none;
  margin-top: 6px;
  padding: 4px 10px;
  border-radius: 999px;
  width: fit-content;
  max-width: 100%;
  background: rgba(248, 113, 113, 0.12);
  border: 1px solid rgba(248, 113, 113, 0.3);
  color: #fca5a5;
  font-size: 0.74rem;
  font-weight: 600;
}
.xp-ref-edit-hint {
  display: block;
  margin-top: 6px;
  font-size: 0.73rem;
  color: #94a3b8;
}
@media (max-width: 520px) {
  .xp-ref-inline-row {
    flex-wrap: wrap;
  }
  .xp-ref-input {
    flex: 1 1 100%;
  }
  .xp-ref-inline-row .xp-ref-btn:not(.xp-ref-btn-icon) {
    flex: 1 1 auto;
  }
}

/* ── Gestion de Usuarios ── */
.gu-header {
  display: flex; align-items: center; justify-content: space-between;
  gap: 16px; margin-bottom: 18px; flex-wrap: wrap;
}
.gu-title { display: flex; align-items: center; gap: 10px; }
.gu-title h3 { font-size: 1.05rem; font-weight: 700; margin: 0; }
.gu-search-wrap {
  display: flex; align-items: center; gap: 8px;
  background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
  border-radius: 10px; padding: 8px 14px; flex: 1; min-width: 220px; max-width: 340px;
}
.gu-search-wrap i { color: var(--muted); flex-shrink: 0; }
.gu-search {
  background: none; border: none; outline: none; color: var(--text);
  font-family: 'Inter', sans-serif; font-size: .875rem; width: 100%;
}
.gu-search::placeholder { color: var(--muted); }

.gu-table-wrap { overflow-x: hidden; border-radius: 12px; }
.gu-table {
  width: 100%; border-collapse: collapse; font-size: .86rem; table-layout: fixed;
}
.gu-table th {
  text-align: left; padding: 10px 10px; font-size: .7rem; text-transform: uppercase;
  letter-spacing: 1px; color: var(--muted); border-bottom: 1px solid rgba(255,255,255,0.07);
  white-space: nowrap;
}
.gu-table th:first-child { width: 46%; }
.gu-table th:last-child { width: 54%; }
.gu-table td { padding: 12px 10px; border-bottom: 1px solid rgba(255,255,255,0.04); vertical-align: middle; }
.gu-table th:first-child, .gu-table td:first-child { padding-left: 0; }
.gu-table th:last-child, .gu-table td:last-child { padding-right: 0; }
.atleta-row { cursor: pointer; transition: background .18s; }
.atleta-row:hover { background: rgba(255,255,255,0.04); }
.atleta-row.is-selected { background: rgba(139,92,246,0.06); }
.atleta-name-cell { display: flex; align-items: center; gap: 10px; min-width: 0; }
.atleta-fullname { line-height: 1.25; overflow-wrap: break-word; min-width: 0; }
.atleta-avatar {
  width: 34px; height: 34px; border-radius: 50%;
  background: linear-gradient(135deg, var(--accent), #0ea5e9);
  display: grid; place-items: center; font-size: .75rem; font-weight: 800;
  color: #0b0e1a; flex-shrink: 0;
}
.gu-muted { color: var(--muted); font-size: .8rem; }
.gu-email-cell {
  overflow-wrap: anywhere;
  word-break: break-word;
  line-height: 1.3;
}
.gu-dash { color: rgba(255,255,255,0.25); }
.gu-tag {
  display: inline-block; background: rgba(139,92,246,.12);
  border: 1px solid rgba(139,92,246,.25); color: var(--accent);
  border-radius: 6px; padding: 2px 8px; font-size: .7rem; font-weight: 600;
  max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.gu-badge {
  display: inline-block; padding: 3px 10px; border-radius: 999px;
  font-size: .7rem; font-weight: 700;
}
.gu-badge-on  { background: rgba(139,92,246,.15); color: var(--accent); }
.gu-badge-off { background: rgba(239,68,68,.15); color: #f87171; }

/* ── Panel de Detalle ── */
.atleta-panel {
  margin-bottom: 16px;
  background: linear-gradient(135deg, rgba(139,92,246,0.08), rgba(14,165,233,0.06));
  border: 1px solid rgba(139,92,246,0.2);
  border-radius: 16px; padding: 20px;
  animation: panelIn .25s ease;
  position: relative;
}
@keyframes panelIn {
  from { opacity:0; transform: translateY(-8px); }
  to   { opacity:1; transform: translateY(0); }
}
.panel-close {
  position: absolute; top: 12px; right: 12px;
  background: rgba(255,255,255,0.08); border: none; color: var(--muted);
  width: 28px; height: 28px; border-radius: 50%; cursor: pointer;
  display: grid; place-items: center; transition: background .2s;
}
.panel-close:hover { background: rgba(239,68,68,.2); color: #f87171; }
.panel-top { display: flex; align-items: center; gap: 14px; margin-bottom: 14px; }
.panel-avatar {
  width: 48px; height: 48px; border-radius: 50%;
  background: linear-gradient(135deg, var(--accent), #0ea5e9);
  display: grid; place-items: center; font-size: 1.1rem;
  font-weight: 800; color: #0b0e1a; flex-shrink: 0;
}
.panel-name { font-size: 1.1rem; font-weight: 800; margin-bottom: 2px; }
.panel-role { font-size: .7rem; text-transform: uppercase; letter-spacing: 1px; color: var(--muted); }
.panel-stats {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
  gap: 10px; margin-bottom: 14px;
}
.panel-stat { background: rgba(255,255,255,0.04); border-radius: 10px; padding: 10px 12px; }
.panel-stat-lbl { font-size: .6rem; text-transform: uppercase; letter-spacing: 1px; color: var(--muted); margin-bottom: 3px; }
.panel-stat-val { font-size: .9rem; font-weight: 700; color: var(--text); }
.panel-actions { display: flex; gap: 8px; flex-wrap: wrap; }

/* ── Modal Contrasena ── */
.pwd-modal-backdrop {
  position: fixed; inset:0; background: rgba(0,0,0,0.65);
  backdrop-filter: blur(6px); z-index: 9999;
  display: grid; place-items: center;
}
.pwd-modal {
  background: #0f1623; border: 1px solid rgba(255,255,255,0.12);
  border-radius: 20px; padding: 32px; max-width: 440px; width: 90%;
  animation: panelIn .25s ease; text-align: center;
}
.pwd-modal-icon {
  width: 56px; height: 56px; border-radius: 16px;
  background: linear-gradient(135deg, #f59e0b, #d97706);
  display: grid; place-items: center; font-size: 1.6rem;
  color: #fff; margin: 0 auto 14px;
}
.pwd-modal h3 { font-size: 1.2rem; font-weight: 800; margin-bottom: 4px; }
.pwd-modal-sub { font-size: .82rem; color: var(--muted); margin-bottom: 4px; }
.pwd-modal-actions { display: flex; gap: 10px; justify-content: center; margin-top: 20px; }

.btn-xp.btn-warn {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: #fff; border: none;
}
.btn-xp.btn-warn:hover { opacity: .9; }
.btn-xp.btn-ghost {
  background: transparent; border: 1px solid rgba(255,255,255,0.15); color: var(--text);
}
.btn-xp.btn-ghost:hover { background: rgba(255,255,255,0.06); }

/* ── Catálogo de Ejercicios con Silueta ── */
.catsil-layout {
  display: flex;
  flex-direction: column;
  gap: 20px;
}
@media(min-width: 768px) {
  .catsil-layout { flex-direction: row; align-items: flex-start; }
}

.catsil-figures {
  flex: 0 0 auto;
  display: flex;
  flex-direction: column;
  align-items: center;
  background: radial-gradient(ellipse at center, rgba(14,22,40,0.9), rgba(8,12,24,0.95));
  border: 1px solid rgba(139,92,246,0.08);
  border-radius: 16px;
  padding: 16px;
  width: 100%;
}
@media(min-width: 768px) {
  .catsil-figures { width: 380px; }
}

.catsil-hint {
  font-size: .8rem; color: #9ca3af; margin-bottom: 14px;
  display: flex; align-items: center; gap: 6px;
}
.catsil-svgs {
  display: flex; gap: 10px; justify-content: center; width: 100%;
}
.catsil-svg-wrap {
  flex: 1; max-width: 140px;
  background: rgba(0,0,0,.25);
  border: 1px solid rgba(255,255,255,.08);
  border-radius: 12px;
  padding: 10px 8px;
  display: flex; flex-direction: column; align-items: center;
}
.catsil-fig-label {
  font-size: .7rem; font-weight: 700; color: #8b95a5;
  text-transform: uppercase; letter-spacing: .05em; margin-bottom: 8px;
}
.catsil-svg-wrap svg { width: 100%; height: auto; display: block; overflow: visible; }

/* SVG Zonas */
.mz-ns {
  fill: #1e2d45;
  stroke: #111a2c;
  stroke-width: 1;
  stroke-linejoin: round;
}
.mz {
  cursor: pointer;
  fill: #2c3e5c;
  stroke: #1a2740;
  stroke-width: 1.2;
  stroke-linejoin: round;
  transition: fill 0.22s ease, filter 0.22s ease, stroke 0.22s ease, stroke-width 0.22s ease;
  paint-order: stroke fill;
}
.mz:hover {
  fill: rgba(139,92,246,0.45);
  stroke: #8B5CF6;
  stroke-width: 1.8;
  filter: drop-shadow(0 0 6px rgba(139,92,246,0.55));
}
.mz.sel {
  fill: rgba(11,209,209,0.55) !important;
  stroke: #0bd1d1 !important;
  stroke-width: 2 !important;
  filter: drop-shadow(0 0 10px rgba(11,209,209,0.75)) !important;
}
.mz.hovered {
  fill: rgba(139,92,246,0.38) !important;
  stroke: #8B5CF6 !important;
  filter: drop-shadow(0 0 5px rgba(139,92,246,0.5));
}
/* anatomy SVG base */
.anatomy-svg {
  filter: drop-shadow(0 2px 8px rgba(0,0,0,0.6));
}

.catsil-active-chips {
  margin-top: 16px; min-height: 28px;
  display: flex; flex-wrap: wrap; gap: 6px; justify-content: center;
}
.catsil-chip-hint { font-size: .75rem; color: #6b7280; font-style: italic; }
.catsil-chip {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 4px 10px; border-radius: 20px;
  font-size: .72rem; font-weight: 700; text-transform: capitalize;
  background: rgba(139,92,246,.15); color: #8B5CF6;
  border: 1px solid rgba(139,92,246,.3);
}
.catsil-chip button {
  background: none; border: none; color: inherit; padding: 0; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
}
.catsil-chip button:hover { color: #f87171; }

/* Panel de lista */
.catsil-panel {
  flex: 1; min-width: 0;
  background: rgba(255,255,255,.02);
  border: 1px solid rgba(255,255,255,.05);
  border-radius: 16px; padding: 16px;
  min-height: 300px;
}
.catsil-panel-empty {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  height: 100%; text-align: center; color: #6b7280; font-size: .9rem;
}
.catsil-panel-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 16px; padding-bottom: 12px;
  border-bottom: 1px solid rgba(255,255,255,.05);
}
.catsil-panel-title {
  font-size: 1rem; font-weight: 800; color: #fff;
  text-transform: capitalize; display: flex; align-items: center; gap: 8px;
}
.catsil-panel-add {
  background: rgba(139,92,246,.1); color: var(--accent);
  border: 1px solid rgba(139,92,246,.2); border-radius: 8px;
  padding: 6px 10px; font-size: .75rem; font-weight: 700; cursor: pointer;
  display: flex; align-items: center; gap: 4px; transition: all .2s;
}
.catsil-panel-add:hover { background: rgba(139,92,246,.2); }

.catsil-ej-list { display: flex; flex-direction: column; gap: 8px; }

/* Tarjeta Ejercicio */
.cat-ej-card {
  display: flex; align-items: center; gap: 12px;
  background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.06);
  border-radius: 12px; padding: 10px 12px; transition: border-color .18s;
}
.cat-ej-card:hover { border-color: rgba(139,92,246,.3); }
.cat-ej-thumb {
  width: 60px; height: 50px; border-radius: 8px; overflow: hidden; flex-shrink: 0; position: relative;
  background: rgba(255,255,255,.06); display: grid; place-items: center;
}
.cat-ej-thumb img { width: 100%; height: 100%; object-fit: cover; }
.cat-ej-play-badge {
  position: absolute; inset: 0; background: rgba(0,0,0,.45);
  display: grid; place-items: center; font-size: 1rem; color: #fff;
}
.cat-ej-video-placeholder {
  width: 100%; height: 100%; background: rgba(139,92,246,.1);
  display: grid; place-items: center; font-size: 1.4rem; color: var(--accent);
}
.cat-ej-no-media { width: 100%; height: 100%; display: grid; place-items: center; font-size: 1.4rem; color: rgba(255,255,255,.2); }

.cat-ej-info { flex: 1; min-width: 0; }
.cat-ej-name { font-size: .88rem; font-weight: 700; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cat-ej-desc { font-size: .72rem; color: var(--muted); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cat-ej-badges { display: flex; gap: 4px; margin-top: 5px; flex-wrap: wrap; }
.cat-badge {
  display: inline-flex; align-items: center; gap: 3px; font-size: .62rem; font-weight: 700; padding: 2px 7px; border-radius: 999px;
}
.cat-badge-img { background: rgba(56,189,248,.12); color: #38bdf8; border: 1px solid rgba(56,189,248,.25); }
.cat-badge-vid { background: rgba(139,92,246,.12); color: var(--accent); border: 1px solid rgba(139,92,246,.25); }
.cat-badge-yt  { background: rgba(255,0,0,.1); color: #ff4040; border: 1px solid rgba(255,0,0,.2); }

.cat-ej-card { cursor: pointer; }
.cat-ej-chevron {
  width: 28px; height: 28px; border-radius: 8px;
  background: rgba(139,92,246,.08); color: rgba(139,92,246,.5);
  display: grid; place-items: center; font-size: .9rem; flex-shrink: 0;
  transition: all .18s;
}
.cat-ej-card:hover .cat-ej-chevron { background: rgba(139,92,246,.18); color: var(--accent); transform: translateX(3px); }

/* Botón sin grupo */
.catsil-singrupo-btn {
  margin-top: 10px;
  display: flex; align-items: center; gap: 7px;
  padding: 7px 14px; border-radius: 20px;
  background: rgba(245,158,11,.1); color: #f59e0b;
  border: 1px solid rgba(245,158,11,.3);
  font-size: .75rem; font-weight: 700; cursor: pointer;
  transition: all .2s;
}
.catsil-singrupo-btn:hover { background: rgba(245,158,11,.2); border-color: rgba(245,158,11,.5); }
.catsil-singrupo-badge {
  background: rgba(245,158,11,.3); color: #fbbf24;
  border-radius: 999px; padding: 1px 7px; font-size: .68rem; font-weight: 800;
}

/* Subida de media */
.media-upload-zone {
  border: 1.5px dashed rgba(255,255,255,.15); border-radius: 12px; padding: 18px 14px;
  text-align: center; cursor: pointer; transition: border-color .2s, background .2s;
}
.media-upload-zone:hover { border-color: rgba(139,92,246,.4); background: rgba(139,92,246,.04); }
.media-upload-icon { font-size: 1.6rem; color: var(--muted); margin-bottom: 6px; }
.media-upload-text { font-size: .8rem; font-weight: 600; color: #9ca3af; }
.media-upload-sub  { font-size: .7rem; color: var(--muted); margin-top: 3px; }
</style>

<script>


/* ── Busqueda de atletas ── */
document.getElementById('atletaSearch').addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.atleta-row').forEach(function(row) {
    const txt = row.dataset.nombre.toLowerCase() + ' ' + row.dataset.email.toLowerCase();
    row.style.display = txt.includes(q) ? '' : 'none';
  });
});

/* ── Click en fila de atleta ── */
let selectedRow = null;
document.querySelectorAll('.atleta-row').forEach(function(row) {
  row.addEventListener('click', function() {
    if (selectedRow === row) {
      // Si ya estaba seleccionado, cerrar panel
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

function openPanel(d) {
  const panel = document.getElementById('atletaPanel');
  const initials = d.nombre.substring(0, 2).toUpperCase();
  const badgeClass = d.estado === 'activo' ? 'gu-badge-on' : 'gu-badge-off';
  const badgeTxt   = d.estado === 'activo' ? 'Activo' : (d.estado === 'rechazado' ? 'Rechazado' : 'Pendiente');

  const btnDesactivar = d.estado === 'activo' 
    ? `<button class="btn-xp" style="font-size:.8rem;padding:7px 14px;background:rgba(239,68,68,0.15);color:#ef4444;border:1px solid rgba(239,68,68,0.3);" onclick="toggleEstadoAtleta('${d.id}', 'rechazar')">
         <i class="ti ti-user-off"></i> Desactivar
       </button>`
    : `<button class="btn-xp" style="font-size:.8rem;padding:7px 14px;background:rgba(16,185,129,0.15);color:#10b981;border:1px solid rgba(16,185,129,0.3);" onclick="toggleEstadoAtleta('${d.id}', 'aprobar')">
         <i class="ti ti-user-check"></i> Activar
       </button>`;

  panel.innerHTML = `
    <button class="panel-close" onclick="closePanel()"><i class="ti ti-x"></i></button>
    <div class="panel-top">
      <div class="panel-avatar">${initials}</div>
      <div>
        <div class="panel-name">${d.nombre}</div>
        <div class="panel-role">Atleta &bull; <span class="gu-badge ${badgeClass}">${badgeTxt}</span></div>
      </div>
    </div>
    <div class="panel-stats">
      <div class="panel-stat"><div class="panel-stat-lbl">Email</div><div class="panel-stat-val" style="font-size:.78rem;word-break:break-all">${d.email}</div></div>
      <div class="panel-stat"><div class="panel-stat-lbl">Peso Actual</div><div class="panel-stat-val">${d.peso}</div></div>
      <div class="panel-stat"><div class="panel-stat-lbl">Objetivo</div><div class="panel-stat-val" style="font-size:.78rem">${d.objetivo}</div></div>
      <div class="panel-stat"><div class="panel-stat-lbl">Desde</div><div class="panel-stat-val">${d.desde}</div></div>
    </div>
    <div class="panel-actions" style="display:flex; flex-wrap:wrap; gap:8px;">
      ${btnDesactivar}
      <button class="btn-xp btn-warn" style="font-size:.8rem;padding:7px 14px" onclick="openPwdModal('${d.id}','${d.nombre}')">
        <i class="ti ti-lock"></i> Contraseña
      </button>
      <button class="btn-xp" style="font-size:.8rem;padding:7px 14px;background:linear-gradient(135deg, #10b981, #059669);color:#fff;border:none;" onclick="cambiarRol('${d.id}','${d.nombre}')">
        <i class="ti ti-star"></i> Cambiar Rol
      </button>
      <a href="<?= APP_URL ?>/entrenador/perfil-cliente?id=${d.cliente}" class="btn-xp btn-primary" style="font-size:.8rem;padding:7px 14px">
        <i class="ti ti-user"></i> Ver Perfil
      </a>
      <button class="btn-xp" style="font-size:.8rem;padding:7px 14px;background:rgba(239,68,68,0.18);color:#ef4444;border:1px solid rgba(239,68,68,0.4);transition:all .2s;" onclick="eliminarAtleta('${d.id}','${d.nombre}')" onmouseover="this.style.background='rgba(239,68,68,0.3)'" onmouseout="this.style.background='rgba(239,68,68,0.18)'">
        <i class="ti ti-trash"></i> Eliminar
      </button>
    </div>
  `;
  panel.style.display = 'block';
}

function closePanel() {
  const panel = document.getElementById('atletaPanel');
  panel.style.display = 'none';
  panel.innerHTML = '';
  if (selectedRow) { selectedRow.classList.remove('is-selected'); selectedRow = null; }
}

function toggleEstadoAtleta(usuarioId, accion) {
  const isActivar = accion === 'aprobar';
  Swal.fire({
    title: isActivar ? '¿Activar Atleta?' : '¿Desactivar Atleta?',
    text: isActivar ? 'El usuario podrá ingresar al sistema.' : 'El usuario no podrá ingresar al sistema y verá un mensaje de cuenta rechazada.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: isActivar ? '#10b981' : '#ef4444',
    cancelButtonColor: '#6b7280',
    confirmButtonText: isActivar ? 'Sí, activar' : 'Sí, desactivar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      fetch('<?= APP_URL ?>/entrenador/api-aprobar-atleta', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.CSRF || '' },
        body: JSON.stringify({ csrf_token: window.CSRF || '', usuario_id: usuarioId, accion: accion })
      })
      .then(res => res.json())
      .then(data => {
        if (data.ok) {
          Swal.fire({
            icon: 'success',
            title: '¡Actualizado!',
            text: data.msg,
            showConfirmButton: false,
            timer: 1500
          }).then(() => {
            location.reload();
          });
        } else {
          Swal.fire('Error', data.msg, 'error');
        }
      })
      .catch(err => Swal.fire('Error', 'Problema de red', 'error'));
    }
  });
}

/* ── Modal de contrasena ── */
function openPwdModal(userId, nombre) {
  document.getElementById('pwdTargetId').value = userId;
  document.getElementById('pwdModalFor').textContent = 'Establecer nueva contrasena para: ' + nombre;
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
  if (!pwd || pwd.length < 6) { Xp.toast('error', 'Minimo 6 caracteres.'); return; }
  if (pwd !== pwd2) { Xp.toast('error', 'Las contrasenas no coinciden.'); return; }
  const r = await Xp.post('entrenador/cambiar-password-atleta', {usuario_id: uid, password: pwd})
                   .catch(() => ({ok:false, msg:'Error de conexion.'}));
  if (r.ok) { Xp.toast('success', r.msg); closePwdModal(); }
  else       { Xp.toast('error', r.msg); }
}
// Cerrar modal haciendo clic fuera
document.getElementById('pwdModal').addEventListener('click', function(e) {
  if (e.target === this) closePwdModal();
});

/* ── Modal de Rol ── */
function cambiarRol(uid, nombre) {
  Swal.fire({
    title: 'Cambiar Rol',
    html: `¿Qué rol deseas asignarle a <b>${nombre}</b>?`,
    icon: 'question',
    showDenyButton: true,
    showCancelButton: true,
    confirmButtonText: 'Entrenador',
    denyButtonText: 'Atleta',
    cancelButtonText: 'Cancelar'
  }).then(async (result) => {
    let rol = '';
    if (result.isConfirmed) rol = 'entrenador';
    else if (result.isDenied) rol = 'cliente';
    else return;

    const r = await Xp.post('entrenador/cambiar-rol-atleta', { usuario_id: uid, rol })
      .catch(() => ({ok:false, msg:'Error en la petición.'}));
    
    if (r.ok) {
      Xp.toast('success', r.msg);
      setTimeout(() => location.reload(), 1200);
    } else {
      Xp.toast('error', r.msg);
    }
  });
}

/* ── Eliminar Atleta ── */
function eliminarAtleta(uid, nombre) {
  Swal.fire({
    title: `¿Eliminar a ${nombre}?`,
    html: `<div style="text-align:left;font-size:0.9rem;color:#cbd5e1;line-height:1.5;">
             <p style="margin-bottom:8px;font-weight:600;color:#f87171;">⚠️ Esta acción es irreversible.</p>
             <p style="margin-bottom:0;color:#94a3b8;">Se eliminará de forma permanente al atleta, junto con todas sus rutinas asignadas, asistencias, registros de peso, metas y pagos registrados.</p>
           </div>`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#475569',
    confirmButtonText: '<i class="ti ti-trash"></i> Sí, eliminar atleta',
    cancelButtonText: 'Cancelar',
    background: '#151c2e',
    color: '#e2e8f0',
    focusCancel: true
  }).then(async (result) => {
    if (!result.isConfirmed) return;

    Swal.fire({
      title: 'Eliminando atleta...',
      text: 'Procesando eliminación...',
      background: '#151c2e',
      color: '#e2e8f0',
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });

    try {
      const r = await Xp.post('entrenador/eliminar-atleta', { usuario_id: uid });
      if (r.ok) {
        Swal.fire({
          icon: 'success',
          title: 'Atleta eliminado',
          text: r.msg || 'El atleta ha sido eliminado del sistema.',
          background: '#151c2e',
          color: '#e2e8f0',
          confirmButtonColor: '#8a2be2'
        });
        closePanel();
        // Animación suave de salida para la fila correspondiente
        const row = document.querySelector(`.atleta-row[data-id="${uid}"]`);
        if (row) {
          row.style.transition = 'all 0.35s ease';
          row.style.opacity = '0';
          row.style.transform = 'translateX(-24px)';
          setTimeout(() => row.remove(), 350);
        }
      } else {
        Swal.fire('Error', r.msg || 'No se pudo eliminar el atleta.', 'error');
      }
    } catch (err) {
      Swal.fire('Error', 'Problema de conexión con el servidor.', 'error');
    }
  });
}
</script>

<script>
// ── Código y Link de Entrenador (Editar / Generar / Copiar) ──
window.entRefData = {
  codigo: <?= json_encode($codRefCfg, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
  link: <?= json_encode($linkRefCfg, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
};

function actualizarUiCodigoEntrenador(codigo, link) {
  window.entRefData.codigo = codigo || '';
  window.entRefData.link = link || '';
  const inpLink = document.getElementById('inputLinkEntCfg');
  const inpCod  = document.getElementById('inputCodigoEntCfg');
  if (inpLink) {
    inpLink.value = window.entRefData.link;
    inpLink.title = window.entRefData.link;
  }
  if (inpCod) inpCod.value = window.entRefData.codigo;
}

function mostrarErrorCodigoEnt(msg) {
  const errEl = document.getElementById('errCodigoEntCfg');
  if (!errEl) return;
  if (msg) {
    errEl.textContent = msg;
    errEl.style.display = 'inline-block';
  } else {
    errEl.textContent = '';
    errEl.style.display = 'none';
  }
}

function toggleEditarCodigoEnt(forzarAbrir) {
  const inp      = document.getElementById('inputCodigoEntCfg');
  const btnEdit  = document.getElementById('btnToggleEditCodigoEnt');
  const btnRight = document.getElementById('btnCopiarCodigoEnt');
  const hintEl   = document.getElementById('hintCodigoEntCfg');
  if (!inp) return;

  const estabaEditando = !inp.readOnly;
  const activar = (typeof forzarAbrir === 'boolean') ? forzarAbrir : !estabaEditando;

  inp.readOnly = !activar;
  inp.classList.toggle('is-editing', activar);
  if (hintEl) hintEl.classList.toggle('is-hidden', !activar);

  if (btnEdit) {
    btnEdit.setAttribute('aria-pressed', activar ? 'true' : 'false');
    btnEdit.className = activar ? 'xp-ref-btn xp-ref-btn-ghost' : 'xp-ref-btn xp-ref-btn-soft';
    btnEdit.innerHTML = activar
      ? '<i class="ti ti-x"></i> <span>Cancelar</span>'
      : '<i class="ti ti-pencil"></i> <span>Editar</span>';
  }

  if (btnRight) {
    btnRight.type = activar ? 'submit' : 'button';
    btnRight.className = activar ? 'xp-ref-btn xp-ref-btn-save' : 'xp-ref-btn xp-ref-btn-ghost';
    btnRight.innerHTML = activar
      ? '<i class="ti ti-check"></i> <span>Guardar</span>'
      : '<i class="ti ti-copy"></i> <span>Copiar</span>';
  }

  mostrarErrorCodigoEnt('');
  inp.value = window.entRefData.codigo || '';

  if (activar) {
    setTimeout(() => { inp.focus(); inp.select(); }, 30);
  }
}

document.getElementById('inputCodigoEntCfg')?.addEventListener('input', function() {
  mostrarErrorCodigoEnt('');
});
document.getElementById('inputCodigoEntCfg')?.addEventListener('keydown', function(e) {
  if (e.key === 'Escape' && !this.readOnly) {
    e.preventDefault();
    toggleEditarCodigoEnt(false);
  }
});

function copiarTextoEntrenador(texto, titulo) {
  const onCopied = () => {
    if (typeof Swal !== 'undefined') {
      Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: titulo, showConfirmButton: false, timer: 2200, background: '#121217', color: '#fff' });
    }
  };
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(texto).then(onCopied).catch(() => {});
  } else {
    const ta = document.createElement('textarea');
    ta.value = texto;
    document.body.appendChild(ta);
    ta.select();
    document.execCommand('copy');
    document.body.removeChild(ta);
    onCopied();
  }
}

async function enviarCambioCodigoEnt(payload, btnTrigger, toastTitulo) {
  mostrarErrorCodigoEnt('');
  if (btnTrigger) btnTrigger.disabled = true;
  try {
    const r = await fetch((window.APP || '') + '/entrenador/api-generar-codigo', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.CSRF || '' },
      body: JSON.stringify(Object.assign({ csrf_token: window.CSRF || '' }, payload || {}))
    });
    const d = await r.json();
    if (d && d.ok && d.codigo_referido) {
      actualizarUiCodigoEntrenador(d.codigo_referido, d.link_referido || '');
      toggleEditarCodigoEnt(false);
      if (typeof Swal !== 'undefined') {
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: toastTitulo, text: d.codigo_referido, showConfirmButton: false, timer: 2400, background: '#121217', color: '#fff' });
      }
      return true;
    }
    const errMsg = (d && d.msg) || 'No se pudo actualizar el código.';
    if (payload && payload.codigo) {
      mostrarErrorCodigoEnt(errMsg);
    } else if (typeof Swal !== 'undefined') {
      Swal.fire({ icon: 'error', title: 'Error', text: errMsg, background: '#121217', color: '#fff' });
    }
  } catch (err) {
    const errMsg = err.message || 'Error de conexión.';
    if (payload && payload.codigo) {
      mostrarErrorCodigoEnt(errMsg);
    } else if (typeof Swal !== 'undefined') {
      Swal.fire({ icon: 'error', title: 'Error', text: errMsg, background: '#121217', color: '#fff' });
    }
  } finally {
    if (btnTrigger) btnTrigger.disabled = false;
  }
  return false;
}

async function generarNuevoCodigoEntrenador() {
  const btn = document.getElementById('btnGenerarCodigoEnt');
  await enviarCambioCodigoEnt({}, btn, 'Nuevo link y código generados');
}

async function guardarCodigoEntrenador() {
  const inp = document.getElementById('inputCodigoEntCfg');
  if (!inp || inp.readOnly) return;
  const btn = document.getElementById('btnCopiarCodigoEnt');
  const raw = (inp.value || '').trim();
  const limpio = raw.normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/\s+/g, '-').replace(/[^A-Za-z0-9\-_]/g, '').replace(/-{2,}/g, '-').replace(/^[-_]+|[-_]+$/g, '').toUpperCase();
  if (limpio.length < 4 || limpio.length > 32) {
    mostrarErrorCodigoEnt('El código debe tener entre 4 y 32 caracteres (letras, números o guion).');
    inp.focus();
    return;
  }
  if (limpio === (window.entRefData.codigo || '').toUpperCase()) {
    toggleEditarCodigoEnt(false);
    return;
  }
  await enviarCambioCodigoEnt({ codigo: limpio }, btn, 'Código actualizado');
}

// ── Switch Activar/Desactivar Tienda + Ajustes WhatsApp ──
async function toggleActivarTienda(chk) {
  const activo = chk && chk.checked ? 1 : 0;
  const panel  = document.getElementById('panelAjustesTiendaWS');
  const desc   = document.getElementById('lblEstadoTiendaDesc');
  const inpNum = document.getElementById('whatsapp_numero');
  const inpMsg = document.getElementById('whatsapp_mensaje');
  const btnSav = document.getElementById('btnGuardarWsAjustes');

  const cfgCard = document.getElementById('cfgTiendaCard');
  const applyUiState = (isOn) => {
    if (cfgCard) cfgCard.classList.toggle('is-store-on', !!isOn);
    if (panel) panel.classList.toggle('is-hidden', !isOn);
    if (panel) panel.classList.toggle('is-open', !!isOn);
    if (desc) {
      desc.textContent = isOn
        ? 'Activada: visible para ti y todos tus atletas asignados.'
        : 'Desactivada: activa el switch para habilitar tu tienda y ajustes de WhatsApp.';
    }
    [inpNum, inpMsg, btnSav].forEach(el => {
      if (!el) return;
      el.disabled = !isOn;
      if (el !== btnSav) el.required = !!isOn;
    });
  };

  chk.disabled = true;
  try {
    const res = await fetch((window.APP || '') + '/entrenador/api-toggle-tienda', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.CSRF || '' },
      body: JSON.stringify({ csrf_token: window.CSRF || '', activo: activo })
    });
    const d = await res.json();
    if (d && d.ok) {
      applyUiState(activo === 1);
      Swal.fire({
        toast: true, position: 'top-end', icon: 'success',
        title: d.msg || (activo ? 'Tienda activada' : 'Tienda desactivada'),
        timer: 2000, showConfirmButton: false, background: '#121217', color: '#fff'
      });
    } else {
      chk.checked = !chk.checked;
      Swal.fire({ icon: 'error', title: 'Error', text: (d && d.msg) || 'No se pudo cambiar el estado.', background: '#121217', color: '#fff' });
    }
  } catch (err) {
    chk.checked = !chk.checked;
    Swal.fire({ icon: 'error', title: 'Error', text: err.message, background: '#121217', color: '#fff' });
  } finally {
    chk.disabled = false;
  }
}

function guardarAjustesWhatsApp() {
  const form = document.getElementById("formAjustesWhatsApp");
  if (!form) return;
  const fd = new FormData(form);
  const token = window.CSRF || '';
  fd.append("csrf_token", token);
  const appBase = window.APP || '';
  fetch(appBase + "/entrenador/guardar-ajustes-whatsapp", {
    method: "POST", body: fd
  })
  .then(r => r.json())
  .then(data => {
    if(data.ok) {
      const inpNum = document.getElementById('whatsapp_numero');
      if (inpNum && data.whatsapp_numero) inpNum.value = data.whatsapp_numero;
      Swal.fire({icon:"success", title:"Guardado", text:data.msg, timer:1500, showConfirmButton:false, background:'#121217', color:'#fff'});
    } else {
      Swal.fire({icon:"error", title:"Error", text:data.msg, background:'#121217', color:'#fff'});
    }
  })
  .catch(err => {
    Swal.fire({icon:"error", title:"Error", text:err.message, background:'#121217', color:'#fff'});
  });
}
</script>
<?php require __DIR__ . '/../layout/footer.php'; ?>

