<?php
// views/admin/gestion.php — Módulo 2: Gestión de Entrenadores, Membresías y Atletas Asignados
require BASE_DIR . '/views/layout/header.php';

$avatarPalette = ['#7c3aed','#ec4899','#f97316','#10b981','#3b82f6','#ef4444','#8b5cf6','#06b6d4','#f59e0b','#6366f1'];
$unread_hdr = function_exists('contarNotificacionesNoLeidas')
    ? contarNotificacionesNoLeidas((int)($_SESSION['user_id'] ?? 0), 'admin')
    : 0;
?>

<!-- ── HEADER COMPACTO (ESTILO MIS ATLETAS) ── -->
<div class="sys-top-header premium-header">
  <div class="user-info-group">
    <div class="rl-header-icon" style="width: 42px; height: 42px; border-radius: 12px; background: linear-gradient(135deg, var(--accent-primary, #8a2be2), var(--accent-hover, #9d4edd)); display: grid; place-items: center; color: #fff; flex-shrink: 0; box-shadow: 0 4px 14px rgba(138, 43, 226, 0.35); font-size: 1.25rem;">
      <i class="ti ti-users-group"></i>
    </div>
    <div class="user-text-wrap">
      <h1 class="sys-top-title user-name-gradient" title="Gestión de Entrenadores" style="font-size: 1.12rem !important; font-weight: 800; color: var(--text, #fff); margin: 0; line-height: 1.25; display:flex; align-items:center; gap:8px;">
        <span class="user-name-text">Entrenadores</span>
        <span class="cl-count-badge" id="saTotalCountBadge" style="font-size: 0.72rem; padding: 2px 8px; border-radius: 10px; background: rgba(138,43,226,0.25); color: #d8b4fe; border: 1px solid rgba(138,43,226,0.4);"><?= count($entrenadores) ?></span>
      </h1>
      <p style="font-size: .74rem; font-weight: 500; color: #94a3af; margin: 2px 0 0; letter-spacing: 0.2px; line-height: 1.2;">
        Control de membresías, códigos y atletas por entrenador
      </p>
    </div>
  </div>

  <div class="header-actions">
    <div id="xpPwaSyncPill" class="xp-pwa-header-pill online" title="Conectado a la red">
      <span class="xp-status-dot"></span><span class="xp-status-text">Online</span>
    </div>
    <div class="header-action-buttons">
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

<div class="sa-shell">
  <?php if (!empty($solicitudesEntrenadores)): ?>
  <!-- ── ALERTA DE SOLICITUDES DE NUEVOS ENTRENADORES ── -->
  <section id="saSolicitudesEntrenadoresPanel" class="glass-surface" style="padding: 18px; border-radius: 18px; border: 1px solid rgba(251,191,36,0.35); background: linear-gradient(145deg, rgba(251,191,36,0.08), rgba(15,15,24,0.85));" aria-label="Solicitudes pendientes de entrenadores">
    <h2 style="font-size: 1.05rem; font-weight: 800; color: #facc15; margin: 0 0 14px; display: flex; align-items: center; gap: 8px;">
      <i class="ti ti-user-plus"></i>
      <span>Nuevas Solicitudes de Entrenadores (<span id="saSolCount"><?= count($solicitudesEntrenadores) ?></span>)</span>
    </h2>
    <div id="saSolicitudesList" style="display: flex; flex-direction: column; gap: 10px;">
      <?php foreach ($solicitudesEntrenadores as $sol):
        $solUid   = (int)$sol['usuario_id'];
        $solFecha = !empty($sol['creado_en']) ? date('d/m/Y h:i A', strtotime((string)$sol['creado_en'])) : '';
      ?>
      <div class="sa-solicitud-card" id="saSolicitudEnt-<?= $solUid ?>" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; background: rgba(0,0,0,0.28); padding: 14px 16px; border-radius: 14px; border: 1px solid rgba(255,255,255,0.08); transition: opacity 0.25s ease, transform 0.25s ease;">
        <div style="min-width: 0; flex: 1;">
          <div style="font-weight: 700; color: #fff; font-size: 0.96rem; margin-bottom: 2px;"><?= e($sol['nombre']) ?></div>
          <div style="color: rgba(255,255,255,0.62); font-size: 0.82rem; display: flex; align-items: center; flex-wrap: wrap; gap: 10px;">
            <span><i class="ti ti-mail"></i> <?= e($sol['email']) ?></span>
            <?php if ($solFecha !== ''): ?>
              <span style="color: rgba(255,255,255,0.45);"><i class="ti ti-clock"></i> <?= e($solFecha) ?></span>
            <?php endif; ?>
          </div>
        </div>
        <div style="display: flex; gap: 8px; flex-shrink: 0;">
          <button type="button" class="btn-xp" style="padding: 8px 14px; font-size: 0.84rem; font-weight: 700; background: rgba(16,185,129,0.16); color: #34d399; border: 1px solid rgba(16,185,129,0.38); border-radius: 10px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;" onclick="gestionarSolicitudEntrenador(<?= $solUid ?>, 'aprobar', this)">
            <i class="ti ti-check"></i> Aceptar
          </button>
          <button type="button" class="btn-xp" style="padding: 8px 14px; font-size: 0.84rem; font-weight: 700; background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.35); border-radius: 10px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;" onclick="gestionarSolicitudEntrenador(<?= $solUid ?>, 'rechazar', this)">
            <i class="ti ti-x"></i> Rechazar
          </button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- ── 1. ACCESOS DIRECTOS COMPACTOS (FILTROS POR ESTADO) ── -->
  <section class="sa-shortcuts-grid" aria-label="Filtrar entrenadores por estado de membresía">
    <button type="button" class="sa-shortcut-btn is-ok" data-filter="al_dia" onclick="filtrarPorEstadoShortcut('al_dia', this)">
      <div class="sa-sc-icon"><i class="ti ti-circle-check"></i></div>
      <div class="sa-sc-info">
        <span class="sa-sc-val"><?= (int)$kpiActivos ?></span>
        <span class="sa-sc-lbl">Membresías al día</span>
      </div>
    </button>

    <button type="button" class="sa-shortcut-btn is-warn-purple" data-filter="aviso" onclick="filtrarPorEstadoShortcut('aviso', this)">
      <div class="sa-sc-icon"><i class="ti ti-bell-ringing"></i></div>
      <div class="sa-sc-info">
        <span class="sa-sc-val"><?= (int)$kpiPorVencer ?></span>
        <span class="sa-sc-lbl">En semana de aviso</span>
      </div>
    </button>

    <button type="button" class="sa-shortcut-btn is-grace" data-filter="prorroga" onclick="filtrarPorEstadoShortcut('prorroga', this)">
      <div class="sa-sc-icon"><i class="ti ti-clock-exclamation"></i></div>
      <div class="sa-sc-info">
        <span class="sa-sc-val"><?= (int)$kpiEnGracia ?></span>
        <span class="sa-sc-lbl">En prórroga (+7d)</span>
      </div>
    </button>

    <button type="button" class="sa-shortcut-btn is-danger" data-filter="suspendido" onclick="filtrarPorEstadoShortcut('suspendido', this)">
      <div class="sa-sc-icon"><i class="ti ti-lock"></i></div>
      <div class="sa-sc-info">
        <span class="sa-sc-val"><?= (int)$kpiSuspendidos ?></span>
        <span class="sa-sc-lbl">Web en suspensión</span>
      </div>
    </button>
  </section>

  <!-- ── 2. BUSCADOR DEBAJO DE LOS ACCESOS DIRECTOS ── -->
  <section class="sa-toolbar-row">
    <div class="cl-search-wrap" id="saSearchWrap" style="flex:1; min-width:0; width:100%;">
      <span class="cl-search-icon">
        <i class="ti ti-search" style="font-size:1rem;"></i>
      </span>
      <input type="search"
             class="cl-search-input"
             id="saSearchInput"
             placeholder="Buscar entrenador por nombre, correo o código..."
             autocomplete="off"
             oninput="aplicarFiltrosEntrenadores()">
      <button type="button"
              class="cl-search-clear"
              id="saSearchClear"
              onclick="limpiarBusquedaEntrenadores()"
              style="display:none;"
              aria-label="Limpiar búsqueda">
        <i class="ti ti-x"></i>
      </button>
    </div>
  </section>

  <!-- Indicador de filtro activo -->
  <div id="saActiveFilterBanner" style="display:none; align-items:center; justify-content:space-between; gap:10px; padding:7px 12px; border-radius:11px; background:rgba(138,43,226,0.12); border:1px solid rgba(168,85,247,0.35); font-size:0.76rem; color:#e9d5ff; font-weight:700;">
    <span id="saActiveFilterText">Filtrando entrenadores</span>
    <button type="button" onclick="resetFiltroEstado()" style="background:none; border:none; color:#c084fc; font-size:0.75rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:4px;">
      <i class="ti ti-x"></i> Ver todos
    </button>
  </div>

  <!-- ── 3. PERFILES DE ENTRENADORES (RECTÁNGULOS IGUAL QUE MIS ATLETAS EN PC Y MÓVIL) ── -->
  <section id="entrenadoresSection">
    <div class="atletas-grid" id="saTrainersGrid">
      <?php foreach ($entrenadores as $idx => $ent):
        $eid       = (int)$ent['id'];
        $mem       = $ent['membresia'];
        $est       = (string)($mem['estado_calculado'] ?? 'activa');
        $bloq      = !empty($mem['bloqueado']);
        $codigo    = (string)($mem['codigo_referido'] ?? '');
        $avatarCol = $avatarPalette[$idx % count($avatarPalette)];

        $partsNom  = explode(' ', trim((string)$ent['nombre']));
        $firstName = $partsNom[0] ?? '';
        $lastName  = $partsNom[1] ?? '';
        $shortName = trim($firstName . ' ' . $lastName) ?: (string)$ent['nombre'];
        $initial   = strtoupper(mb_substr($firstName ?: (string)$ent['nombre'], 0, 1));

        if ($bloq || $est === 'suspendida' || $est === 'pausada_manual') {
            $filterGroup = 'suspendido';
            $badgeText   = 'Suspendido';
            $badgeClass  = 'is-suspendido';
            $badgeIcon   = 'ti-lock';
        } elseif ($est === 'en_gracia' || $est === 'gracia') {
            $filterGroup = 'prorroga';
            $badgeText   = 'Prórroga';
            $badgeClass  = 'is-prorroga';
            $badgeIcon   = 'ti-clock-exclamation';
        } elseif ($est === 'recordatorio') {
            $filterGroup = 'aviso';
            $badgeText   = 'Aviso';
            $badgeClass  = 'is-aviso';
            $badgeIcon   = 'ti-bell-ringing';
        } else {
            $filterGroup = 'al_dia';
            $badgeText   = 'Al día';
            $badgeClass  = 'is-aldia';
            $badgeIcon   = 'ti-circle-check';
        }

        $totalAtl = (int)$ent['total_atletas'];
        $microTxt = $totalAtl . ' ' . ($totalAtl === 1 ? 'atleta' : 'atletas') . ' • ' . ($ent['email'] ?? 'Sin correo');
      ?>
      <div class="ig-card glass-surface sa-trainer-card <?= $bloq ? 'is-suspended' : ($filterGroup === 'prorroga' ? 'is-grace' : '') ?>"
           id="trainerCard_<?= $eid ?>"
           role="button"
           tabindex="0"
           data-status="<?= e($filterGroup) ?>"
           data-search="<?= e(mb_strtolower($ent['nombre'] . ' ' . $ent['email'] . ' ' . $codigo)) ?>"
           onclick="abrirModalFichaEntrenador(<?= $eid ?>)"
           onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();abrirModalFichaEntrenador(<?= $eid ?>);}">

        <div class="ig-avatar" style="background:<?= e($avatarCol) ?>;"><?= e($initial) ?></div>

        <div class="ig-info">
          <div class="ig-name" title="<?= e($ent['nombre']) ?>"><?= e($shortName) ?></div>
          <div class="ig-micro" title="<?= e($microTxt) ?>"><?= e($microTxt) ?></div>
        </div>

        <span class="sa-status-tag <?= e($badgeClass) ?>" title="Estado de membresía: <?= e($badgeText) ?>">
          <i class="ti <?= e($badgeIcon) ?>"></i>
          <span><?= e($badgeText) ?></span>
        </span>

        <div class="ig-btn-wrap"><span class="ig-btn">Ver Perfil</span></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Estado vacío cuando un filtro no tiene coincidencias -->
    <div id="saEmptyFilterState" style="display:none; text-align:center; padding:38px 20px; border-radius:18px; background:rgba(255,255,255,0.02); border:1px dashed rgba(255,255,255,0.1); margin-top:12px;">
      <i class="ti ti-user-search" style="font-size:2rem; color:#94a3b8; display:block; margin-bottom:8px;"></i>
      <p style="margin:0; font-size:0.9rem; font-weight:700; color:#e2e8f0;">No se encontraron entrenadores con ese filtro</p>
      <button type="button" onclick="limpiarBusquedaEntrenadores(); resetFiltroEstado();" style="margin-top:10px; padding:7px 14px; border-radius:10px; background:rgba(138,43,226,0.2); border:1px solid rgba(168,85,247,0.4); color:#e9d5ff; font-size:0.78rem; font-weight:700; cursor:pointer;">
        Mostrar todos los entrenadores
      </button>
    </div>
  </section>
</div>

<!-- ══════════════════════════════════════════════════════════════════
     MODAL FICHA DE GESTIÓN DEL ENTRENADOR (SE ABRE AL TOCAR SU PERFIL)
══════════════════════════════════════════════════════════════════ -->
<div class="sa-modal-backdrop" id="modalFichaEntrenador" role="dialog" aria-modal="true" aria-label="Gestión del entrenador" onclick="if(event.target===this)cerrarModalFichaEntrenador()">
  <div class="sa-modal sa-modal-ficha">
    <?php foreach ($entrenadores as $idx => $ent):
      $eid       = (int)$ent['id'];
      $mem       = $ent['membresia'];
      $est       = (string)($mem['estado_calculado'] ?? 'activa');
      $bloq      = !empty($mem['bloqueado']);
      $codigo    = (string)($mem['codigo_referido'] ?? '');
      $avatarCol = $avatarPalette[$idx % count($avatarPalette)];

      $partsNom  = explode(' ', trim((string)$ent['nombre']));
      $firstName = $partsNom[0] ?? '';
      $lastName  = $partsNom[1] ?? '';
      $shortName = trim($firstName . ' ' . $lastName) ?: (string)$ent['nombre'];
      $initial   = strtoupper(mb_substr($firstName ?: (string)$ent['nombre'], 0, 1));

      if ($bloq || $est === 'suspendida' || $est === 'pausada_manual') {
          $badgeText  = 'Suspendido';
          $badgeClass = 'is-suspendido';
          $badgeIcon  = 'ti-lock';
      } elseif ($est === 'en_gracia' || $est === 'gracia') {
          $badgeText  = 'Prórroga';
          $badgeClass = 'is-prorroga';
          $badgeIcon  = 'ti-clock-exclamation';
      } elseif ($est === 'recordatorio') {
          $badgeText  = 'Aviso';
          $badgeClass = 'is-aviso';
          $badgeIcon  = 'ti-bell-ringing';
      } else {
          $badgeText  = 'Al día';
          $badgeClass = 'is-aldia';
          $badgeIcon  = 'ti-circle-check';
      }

      $fechaPagoIso  = (string)($mem['fecha_proximo_pago'] ?? date('Y-m-d'));
      $fechaCorteIso = (string)($mem['fecha_limite_gracia'] ?? date('Y-m-d', strtotime('+7 days', strtotime($fechaPagoIso))));
      $fechaAvisoFmt = (string)($mem['fecha_aviso_fmt'] ?? date('d/m/Y', strtotime('-7 days', strtotime($fechaPagoIso))));
    ?>
    <div class="sa-ficha-pane" id="fichaEntrenadorPane_<?= $eid ?>" style="display:none; flex-direction:column; gap:13px;">
      <!-- Cabecera de Perfil en el Modal + Etiqueta de Estado + Botón Cerrar -->
      <div class="sa-coach-profile-row">
        <div class="sa-ficha-avatar" style="background:<?= e($avatarCol) ?>;"><?= e($initial) ?></div>
        <div class="sa-ficha-info">
          <div class="sa-ficha-name" title="<?= e($ent['nombre']) ?>"><?= e($shortName) ?></div>
          <div class="sa-ficha-email" title="<?= e($ent['email']) ?>"><?= e($ent['email']) ?></div>
        </div>
        <span class="sa-status-tag <?= e($badgeClass) ?>">
          <i class="ti <?= e($badgeIcon) ?>"></i>
          <span><?= e($badgeText) ?></span>
        </span>
        <button type="button" onclick="cerrarModalFichaEntrenador()" class="sa-modal-close" aria-label="Cerrar ficha">
          <i class="ti ti-x"></i>
        </button>
      </div>

      <!-- Botón de acceso directo "Atletas Asignados [N]" -->
      <div class="sa-meta-bar">
        <button type="button"
                class="sa-btn-athletes-shortcut"
                onclick="abrirModalAtletasEntrenador(<?= $eid ?>)">
          <i class="ti ti-users"></i>
          <span>Atletas Asignados</span>
          <span class="sa-athlete-count-box"><?= (int)$ent['total_atletas'] ?></span>
        </button>
      </div>

      <!-- Código y Link de Invitación -->
      <div class="sa-ref-box">
        <div class="sa-ref-head">
          <span><i class="ti ti-link" style="color:#a855f7;"></i> Código & Link</span>
          <span class="sa-ref-sub">Auto-asigna atletas</span>
        </div>
        <div class="sa-ref-row">
          <input type="text"
                 class="sa-code-input"
                 id="inpCodigo_<?= $eid ?>"
                 data-saved-code="<?= e($codigo) ?>"
                 value="<?= e($codigo) ?>"
                 maxlength="32"
                 aria-label="Código de entrenador">
          <div class="sa-ref-btns">
            <button type="button" class="sa-mini-btn" onclick="regenerarCodigoAdmin(<?= $eid ?>)" title="Generar nuevo código único">
              <i class="ti ti-refresh"></i>
            </button>
            <button type="button" class="sa-mini-btn" onclick="copiarCodigoEntrenador(<?= $eid ?>)" title="Copiar código">
              <i class="ti ti-copy"></i> <span>Código</span>
            </button>
            <button type="button" class="sa-mini-btn" onclick="copiarLinkEntrenador(<?= $eid ?>)" title="Copiar link de registro directo">
              <i class="ti ti-share"></i> <span>Link</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Hilera Horizontal de 3 Secciones de Fecha: AVISO | PAGO | CORTE (100% Seleccionables) -->
      <div class="sa-dates-trio" aria-label="Fechas de Aviso, Pago y Corte">
        <!-- 1. Izquierda: AVISO (-7 días calculado automáticamente) -->
        <div class="sa-date-cell is-aviso-cell">
          <span class="sa-date-tag"><i class="ti ti-bell"></i> Aviso</span>
          <div class="sa-date-readonly" id="txtAviso_<?= $eid ?>" title="Se avisa 7 días antes del pago">
            <?= e($fechaAvisoFmt) ?>
          </div>
        </div>

        <!-- 2. Centro: PAGO (Seleccionable con un toque en toda la celda) -->
        <div class="sa-date-cell is-pago-cell is-clickable-date" onclick="abrirDatePicker(document.getElementById('inpFecha_<?= $eid ?>'))">
          <label class="sa-date-tag" for="inpFecha_<?= $eid ?>"><i class="ti ti-calendar-event"></i> Pago</label>
          <input type="date"
                 class="sa-date-input"
                 id="inpFecha_<?= $eid ?>"
                 value="<?= e($fechaPagoIso) ?>"
                 onclick="event.stopPropagation(); abrirDatePicker(this)"
                 onchange="recalcularFechasEntrenador(<?= $eid ?>, this.value)">
        </div>

        <!-- 3. Derecha: CORTE (Calculado +7 días por defecto y editable con un toque en toda la celda) -->
        <div class="sa-date-cell is-corte-cell is-clickable-date" onclick="abrirDatePicker(document.getElementById('inpCorte_<?= $eid ?>'))">
          <label class="sa-date-tag" for="inpCorte_<?= $eid ?>"><i class="ti ti-calendar-off"></i> Corte</label>
          <input type="date"
                 class="sa-date-input"
                 id="inpCorte_<?= $eid ?>"
                 value="<?= e($fechaCorteIso) ?>"
                 onclick="event.stopPropagation(); abrirDatePicker(this)">
        </div>
      </div>

      <!-- Pie de Acciones -->
      <div class="sa-card-footer">
        <button type="button" class="sa-btn sa-btn-primary" style="flex:1;" onclick="guardarMembresia(<?= $eid ?>)">
          <i class="ti ti-device-floppy"></i> Guardar
        </button>
        <?php if ($bloq): ?>
          <button type="button" class="sa-btn sa-btn-success" style="flex:1;" onclick="togglePausaEntrenador(<?= $eid ?>, 0)">
            <i class="ti ti-player-play"></i> Reactivar Web
          </button>
        <?php else: ?>
          <button type="button" class="sa-btn sa-btn-danger" style="flex:1;" onclick="togglePausaEntrenador(<?= $eid ?>, 1)">
            <i class="ti ti-player-pause"></i> Pausar Web
          </button>
        <?php endif; ?>
        <?php if (count($entrenadores) > 1): ?>
          <button type="button" class="sa-btn sa-btn-ghost sa-btn-icon-only" onclick="revocarEntrenador(<?= $eid ?>, '<?= e(addslashes((string)$ent['nombre'])) ?>')" title="Revocar rol de entrenador">
            <i class="ti ti-user-minus"></i>
          </button>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════
     MODAL DE ATLETAS ASIGNADOS AL ENTRENADOR (ESTILO MIS ATLETAS PC/MÓVIL)
══════════════════════════════════════════════════════════════════ -->
<div class="sa-modal-backdrop sa-modal-backdrop-top" id="modalAtletasEntrenador" role="dialog" aria-modal="true" aria-labelledby="modalAtlCoachTitle" onclick="if(event.target===this)cerrarModalAtletasEntrenador()">
  <div class="sa-modal sa-modal-atletas">
    <div class="sa-modal-header">
      <div style="display:flex; align-items:center; gap:10px; min-width:0;">
        <div style="width:36px; height:36px; border-radius:10px; background:rgba(138,43,226,0.2); color:#d8b4fe; display:grid; place-items:center; font-size:1.1rem; flex-shrink:0;">
          <i class="ti ti-users"></i>
        </div>
        <div style="min-width:0;">
          <h3 id="modalAtlCoachTitle" style="margin:0; font-size:1rem; font-weight:800; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
            Atletas Registrados
          </h3>
          <p id="modalAtlCoachSub" style="margin:2px 0 0; font-size:0.74rem; color:#94a3b8;">
            Listado de atletas asignados
          </p>
        </div>
      </div>
      <button type="button" onclick="cerrarModalAtletasEntrenador()" class="sa-modal-close" aria-label="Cerrar modal">
        <i class="ti ti-x"></i>
      </button>
    </div>

    <div id="modalAtlCoachBody" class="sa-modal-atletas-body">
      <!-- Renderizado dinámico estilo Mis Atletas (.modal-atletas-grid > .modal-ig-card) -->
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════
     MODAL CREAR / DESIGNAR ENTRENADOR
══════════════════════════════════════════════════════════════════ -->
<div class="sa-modal-backdrop sa-modal-backdrop-top" id="modalEntrenador" role="dialog" aria-modal="true" aria-labelledby="modalEntTitle" onclick="if(event.target===this)cerrarModalEntrenador()">
  <div class="sa-modal">
    <div class="sa-modal-header" style="margin-bottom:14px;">
      <h3 id="modalEntTitle" style="margin:0; font-size:1.05rem; font-weight:800; color:#fff; display:flex; align-items:center; gap:8px;">
        <i class="ti ti-user-check" style="color:#a855f7;"></i> Designar / Crear Entrenador
      </h3>
      <button type="button" onclick="cerrarModalEntrenador()" class="sa-modal-close" aria-label="Cerrar modal">
        <i class="ti ti-x"></i>
      </button>
    </div>

    <div class="sa-tabs">
      <button type="button" class="sa-tab-btn active" id="tabBtnNuevo" onclick="cambiarModoModal('nuevo')">
        Crear Cuenta Nueva
      </button>
      <button type="button" class="sa-tab-btn" id="tabBtnPromover" onclick="cambiarModoModal('promover')">
        Designar Usuario Existente
      </button>
    </div>

    <form id="formCrearEntrenador" autocomplete="off" onsubmit="submitCrearEntrenador(event)">
      <input type="hidden" id="modoEntrenador" value="nuevo">

      <div id="paneNuevoEntrenador" style="display:flex; flex-direction:column; gap:11px;">
        <div class="sa-field">
          <label for="newEntNombre">Nombre completo</label>
          <input type="text" id="newEntNombre" placeholder="Ej: Carlos Mendoza" autocomplete="off">
        </div>
        <div class="sa-field">
          <label for="newEntEmail">Correo electrónico</label>
          <input type="email" id="newEntEmail" placeholder="coach@correo.com" autocomplete="off">
        </div>
        <div class="sa-field">
          <label for="newEntPass">Contraseña de acceso</label>
          <input type="password" id="newEntPass" placeholder="Mínimo 6 caracteres" autocomplete="new-password">
        </div>
      </div>

      <div id="panePromoverEntrenador" style="display:none; flex-direction:column; gap:11px;">
        <div class="sa-field">
          <label for="promoverUsuarioId">Seleccionar usuario registrado</label>
          <select id="promoverUsuarioId">
            <option value="">-- Selecciona un usuario --</option>
            <?php foreach ($atletas as $atl): ?>
              <option value="<?= (int)$atl['usuario_id'] ?>">
                <?= e($atl['nombre']) ?> (<?= e($atl['email']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div style="display:grid; grid-template-columns:1fr 110px; gap:10px; margin-top:11px;">
        <div class="sa-field">
          <label for="newEntCodigo">Código de Entrenador</label>
          <input type="text" id="newEntCodigo" placeholder="Ej: CARLOS-FIT" style="text-transform:uppercase;" autocomplete="off">
        </div>
        <div class="sa-field">
          <label for="newEntMonto">Mensualidad ($)</label>
          <input type="number" step="0.01" min="0" id="newEntMonto" value="25.00">
        </div>
      </div>

      <div class="sa-field" style="margin-top:11px;" onclick="abrirDatePicker(document.getElementById('newEntFechaPago'))">
        <label for="newEntFechaPago">Fecha de próximo pago mensual</label>
        <input type="date" id="newEntFechaPago" value="<?= date('Y-m-d', strtotime('+1 month')) ?>" onclick="event.stopPropagation(); abrirDatePicker(this)" style="cursor:pointer; color-scheme:dark;" required>
      </div>

      <div class="sa-modal-actions">
        <button type="button" class="sa-btn sa-btn-ghost" onclick="cerrarModalEntrenador()">Cancelar</button>
        <button type="submit" class="sa-btn sa-btn-primary" id="btnSubmitEntrenador">
          <i class="ti ti-check"></i> Guardar Entrenador
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════
     FAB – ACCESO RÁPIDO (CONTIENE EL BOTÓN DESIGNAR / CREAR ENTRENADOR)
══════════════════════════════════════════════════════════════════ -->
<div class="fab-wrapper" id="fabWrapper">
  <div class="fab-options" id="fabOptions" aria-hidden="true">
    <button type="button"
            class="fab-option"
            id="fabOptCrearEntrenador"
            style="--opt-color:#8B5CF6; background:none; border:none; cursor:pointer; width:100%; text-align:left;"
            onclick="cerrarFab(); abrirModalEntrenador();"
            title="Designar / Crear Entrenador">
      <i class="ti ti-user-plus"></i>
      <span class="fab-label">Designar / Crear Entrenador</span>
    </button>
  </div>

  <button type="button" class="fab-main" id="fabMain" onclick="toggleFab()" aria-label="Acciones de entrenador">
    <i class="ti ti-plus" id="fabIcon"></i>
  </button>
</div>

<style>
  .sa-shell,
  .sa-shell * {
    box-sizing: border-box;
  }

  .sa-shell {
    width: 100%;
    max-width: 1180px;
    margin: 0 auto;
    padding: 4px 0 90px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    overflow-x: hidden;
  }

  /* ── 1. ACCESOS DIRECTOS COMPACTOS (FILTROS) ── */
  .sa-shortcuts-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 10px;
    width: 100%;
  }

  @media (max-width: 767px) {
    .sa-shortcuts-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 8px;
    }
  }

  .sa-shortcut-btn {
    background: rgba(18, 18, 28, 0.86);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 14px;
    padding: 10px 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    text-align: left;
    transition: all 0.18s ease;
    min-width: 0;
    width: 100%;
  }

  .sa-shortcut-btn:hover {
    transform: translateY(-1px);
    border-color: rgba(255, 255, 255, 0.2);
    background: rgba(26, 25, 40, 0.92);
  }

  .sa-shortcut-btn.active.is-ok {
    border-color: #4ade80;
    background: rgba(34, 197, 94, 0.12);
    box-shadow: 0 4px 16px rgba(34, 197, 94, 0.18);
  }
  .sa-shortcut-btn.active.is-warn-purple {
    border-color: #c084fc;
    background: rgba(168, 85, 247, 0.15);
    box-shadow: 0 4px 16px rgba(168, 85, 247, 0.22);
  }
  .sa-shortcut-btn.active.is-grace {
    border-color: #fbbf24;
    background: rgba(245, 158, 11, 0.14);
    box-shadow: 0 4px 16px rgba(245, 158, 11, 0.2);
  }
  .sa-shortcut-btn.active.is-danger {
    border-color: #f87171;
    background: rgba(239, 68, 68, 0.14);
    box-shadow: 0 4px 16px rgba(239, 68, 68, 0.2);
  }

  .sa-sc-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
  }
  .sa-shortcut-btn.is-ok .sa-sc-icon {
    background: rgba(34, 197, 94, 0.16);
    color: #4ade80;
  }
  .sa-shortcut-btn.is-warn-purple .sa-sc-icon {
    background: rgba(168, 85, 247, 0.18);
    color: #c084fc;
  }
  .sa-shortcut-btn.is-grace .sa-sc-icon {
    background: rgba(245, 158, 11, 0.18);
    color: #fbbf24;
  }
  .sa-shortcut-btn.is-danger .sa-sc-icon {
    background: rgba(239, 68, 68, 0.18);
    color: #f87171;
  }

  .sa-sc-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
  }

  .sa-sc-val {
    font-size: 1.1rem;
    font-weight: 800;
    color: #ffffff;
    line-height: 1.1;
  }

  .sa-sc-lbl {
    font-size: 0.7rem;
    color: #94a3b8;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: 1px;
  }

  /* ── 2. BARRA BUSCADOR ── */
  .sa-toolbar-row {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
  }

  .cl-search-wrap {
    position: relative;
    display: flex;
    align-items: center;
    background: rgba(18, 18, 28, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.14);
    border-radius: 12px;
    height: 42px;
    overflow: hidden;
    transition: border-color 0.18s ease;
  }
  .cl-search-wrap:focus-within {
    border-color: #a855f7;
  }
  .cl-search-icon {
    display: flex;
    align-items: center;
    padding: 0 10px 0 13px;
    color: #94a3b8;
    pointer-events: none;
  }
  .cl-search-input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    color: #ffffff;
    font-size: 0.85rem;
    padding: 8px 6px 8px 0;
    min-width: 0;
  }
  .cl-search-input::placeholder {
    color: #64748b;
  }
  .cl-search-clear {
    width: 28px;
    height: 28px;
    margin-right: 6px;
    border-radius: 8px;
    border: none;
    background: rgba(255, 255, 255, 0.07);
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
  }

  /* ══════════════════════════════════════════════════════════════════
     3. PERFILES DE ENTRENADORES (IDÉNTICO A MIS ATLETAS EN PC Y MÓVIL)
  ══════════════════════════════════════════════════════════════════ */
  .atletas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
    gap: 14px;
    padding: 4px 0 20px;
  }

  .ig-card {
    position: relative;
    text-decoration: none;
    color: var(--text, #fff);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 24px 14px 18px;
    transition: transform 0.2s, border-color 0.2s;
    cursor: pointer;
    min-width: 0;
  }
  .ig-card:hover {
    transform: translateY(-2px);
    border-color: rgba(168, 85, 247, 0.45);
  }
  .ig-card.is-suspended {
    border-color: rgba(239, 68, 68, 0.38);
  }
  .ig-card.is-grace {
    border-color: rgba(245, 158, 11, 0.38);
  }
  .ig-card.filter-hidden {
    display: none !important;
  }

  .ig-avatar {
    width: 58px;
    height: 58px;
    border-radius: 8px;
    display: grid;
    place-items: center;
    font-size: 1.35rem;
    font-weight: 800;
    color: #fff;
    flex-shrink: 0;
    box-shadow: none !important;
  }

  .ig-info {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 3px;
    width: 100%;
    min-width: 0;
  }

  .ig-name {
    font-size: 0.85rem;
    font-weight: 700;
    color: #fff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
  }

  .ig-micro {
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.42);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
  }

  .ig-btn-wrap {
    width: 100%;
    margin-top: 2px;
  }
  .ig-btn {
    display: block;
    width: 100%;
    text-align: center;
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--accent, #a855f7);
    padding: 6px 0;
    border-radius: 8px;
    background: rgba(139, 92, 246, 0.1);
    border: 1px solid rgba(139, 92, 246, 0.25);
    transition: background 0.15s;
  }
  .ig-card:hover .ig-btn {
    background: rgba(139, 92, 246, 0.2);
  }

  /* Etiqueta discreta de estado (Al día, Aviso, Prórroga, Suspendido) */
  .sa-status-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    border-radius: 99px;
    font-size: 0.66rem;
    font-weight: 800;
    letter-spacing: 0.3px;
    flex-shrink: 0;
    white-space: nowrap;
  }
  .sa-status-tag.is-aldia {
    background: rgba(34, 197, 94, 0.14);
    color: #4ade80;
    border: 1px solid rgba(34, 197, 94, 0.32);
  }
  .sa-status-tag.is-aviso {
    background: rgba(168, 85, 247, 0.16);
    color: #c084fc;
    border: 1px solid rgba(168, 85, 247, 0.36);
  }
  .sa-status-tag.is-prorroga {
    background: rgba(245, 158, 11, 0.16);
    color: #fbbf24;
    border: 1px solid rgba(245, 158, 11, 0.38);
  }
  .sa-status-tag.is-suspendido {
    background: rgba(239, 68, 68, 0.16);
    color: #f87171;
    border: 1px solid rgba(239, 68, 68, 0.38);
  }

  /* MÓVIL — Rectángulos horizontales exactos como "Mis Atletas" */
  @media (max-width: 767px) {
    .atletas-grid {
      display: flex;
      flex-direction: column;
      gap: 12px;
      padding: 0 0 70px 0;
    }

    .ig-card {
      flex-direction: row;
      align-items: center;
      padding: 14px 18px;
      gap: 14px;
      transform: none !important;
    }
    .ig-card:active {
      transform: scale(0.98) !important;
    }

    .ig-avatar {
      width: 54px;
      height: 54px;
      font-size: 1.3rem;
      flex-shrink: 0;
      box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.08) !important;
    }

    .ig-info {
      align-items: flex-start;
      text-align: left;
      flex: 1;
      min-width: 0;
      overflow: hidden;
      gap: 2px;
    }
    .ig-name {
      font-size: 0.98rem;
      font-weight: 600;
      color: #fff;
    }
    .ig-micro {
      font-size: 0.82rem;
      color: rgba(255, 255, 255, 0.42);
    }

    .ig-btn-wrap {
      display: none;
    }
  }

  /* ══════════════════════════════════════════════════════════════════
     FICHA MODAL DEL ENTRENADOR (CONTROLES COMPLETOS Y FECHAS SELECCIONABLES)
  ══════════════════════════════════════════════════════════════════ */
  .sa-modal-ficha {
    max-width: 460px;
    padding: 18px;
  }

  .sa-coach-profile-row {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    min-width: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.07);
  }

  .sa-ficha-avatar {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    font-size: 1.15rem;
    font-weight: 800;
    color: #ffffff;
    flex-shrink: 0;
  }

  .sa-ficha-info {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-width: 0;
    overflow: hidden;
    gap: 1px;
  }

  .sa-ficha-name {
    font-size: 0.95rem;
    font-weight: 800;
    color: #ffffff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .sa-ficha-email {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.45);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .sa-meta-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    flex-wrap: wrap;
    width: 100%;
  }

  .sa-btn-athletes-shortcut {
    width: 100%;
    max-width: 100%;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 11px;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #e2e8f0;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.16s ease;
  }
  .sa-btn-athletes-shortcut:hover {
    background: rgba(138, 43, 226, 0.15);
    border-color: rgba(168, 85, 247, 0.45);
    color: #ffffff;
  }
  .sa-btn-athletes-shortcut i {
    color: #a855f7;
    font-size: 0.95rem;
  }

  .sa-athlete-count-box {
    margin-left: auto;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 22px;
    height: 20px;
    padding: 0 6px;
    border-radius: 6px;
    background: rgba(138, 43, 226, 0.24);
    border: 1px solid rgba(168, 85, 247, 0.5);
    color: #e9d5ff;
    font-size: 0.72rem;
    font-weight: 800;
    line-height: 1;
  }

  .sa-ref-box {
    background: rgba(255, 255, 255, 0.025);
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 12px;
    padding: 9px 11px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    width: 100%;
    min-width: 0;
  }

  .sa-ref-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    font-size: 0.66rem;
    color: #94a3b8;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
  }
  .sa-ref-sub {
    color: #c084fc;
    font-size: 0.64rem;
  }

  .sa-ref-row {
    display: flex;
    align-items: center;
    gap: 6px;
    width: 100%;
    min-width: 0;
  }

  .sa-code-input {
    flex: 1;
    min-width: 0;
    background: rgba(0, 0, 0, 0.35);
    border: 1px solid rgba(168, 85, 247, 0.32);
    border-radius: 9px;
    color: #e9d5ff;
    font-family: monospace;
    font-size: 0.8rem;
    font-weight: 800;
    padding: 6px 9px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    outline: none;
  }

  .sa-ref-btns {
    display: flex;
    align-items: center;
    gap: 5px;
    flex-shrink: 0;
  }

  .sa-mini-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 9px;
    border-radius: 9px;
    font-size: 0.72rem;
    font-weight: 700;
    background: rgba(138, 43, 226, 0.18);
    color: #d8b4fe;
    border: 1px solid rgba(138, 43, 226, 0.35);
    cursor: pointer;
    transition: all 0.15s ease;
    white-space: nowrap;
  }
  .sa-mini-btn:hover {
    background: rgba(138, 43, 226, 0.32);
    color: #ffffff;
  }

  /* ── HILERA HORIZONTAL DE 3 SECCIONES DE FECHA: AVISO | PAGO | CORTE (100% SELECCIONABLES) ── */
  .sa-dates-trio {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 6px;
    width: 100%;
    min-width: 0;
  }

  .sa-date-cell {
    background: rgba(13, 14, 23, 0.85);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 11px;
    padding: 6px 5px;
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-width: 0;
    position: relative;
    transition: border-color 0.15s ease, background 0.15s ease;
  }
  .sa-date-cell.is-clickable-date {
    cursor: pointer;
  }
  .sa-date-cell.is-clickable-date:hover {
    border-color: rgba(168, 85, 247, 0.55);
    background: rgba(25, 26, 42, 0.95);
  }
  .sa-date-cell.is-aviso-cell {
    border-color: rgba(168, 85, 247, 0.22);
    background: rgba(168, 85, 247, 0.04);
  }
  .sa-date-cell.is-pago-cell {
    border-color: rgba(255, 255, 255, 0.16);
  }
  .sa-date-cell.is-corte-cell {
    border-color: rgba(245, 158, 11, 0.28);
    background: rgba(245, 158, 11, 0.04);
  }

  .sa-date-tag {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.62rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: #94a3b8;
    margin: 0;
    white-space: nowrap;
    cursor: inherit;
  }
  .is-aviso-cell .sa-date-tag { color: #c084fc; }
  .is-pago-cell .sa-date-tag  { color: #e2e8f0; }
  .is-corte-cell .sa-date-tag { color: #fbbf24; }

  .sa-date-readonly {
    font-size: 0.7rem;
    font-weight: 700;
    color: #cbd5e1;
    line-height: 1.35;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding: 2px 0;
  }

  .sa-date-input {
    width: 100%;
    min-width: 0;
    background: transparent;
    border: none;
    color: #ffffff;
    color-scheme: dark;
    font-size: 0.68rem;
    letter-spacing: -0.25px;
    font-weight: 700;
    padding: 2px 0;
    outline: none;
    font-family: inherit;
    cursor: pointer;
  }
  .sa-date-input::-webkit-calendar-picker-indicator {
    cursor: pointer;
    opacity: 0.85;
    margin-left: 0;
    padding: 0;
    width: 13px;
    height: 13px;
  }

  /* Botones inferiores de la tarjeta */
  .sa-card-footer {
    display: flex;
    align-items: center;
    gap: 7px;
    padding-top: 6px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    width: 100%;
  }

  .sa-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px 11px;
    border-radius: 11px;
    font-size: 0.78rem;
    font-weight: 700;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all 0.18s ease;
    text-decoration: none;
    white-space: nowrap;
    min-width: 0;
  }

  .sa-btn-primary {
    background: linear-gradient(135deg, #8a2be2, #6d28d9);
    color: #ffffff;
    box-shadow: 0 6px 16px rgba(138, 43, 226, 0.3);
  }
  .sa-btn-primary:hover {
    transform: translateY(-1px);
  }

  .sa-btn-danger {
    background: rgba(239, 68, 68, 0.13);
    color: #fca5a5;
    border-color: rgba(239, 68, 68, 0.32);
  }
  .sa-btn-danger:hover {
    background: rgba(239, 68, 68, 0.22);
    color: #ffffff;
  }

  .sa-btn-success {
    background: rgba(34, 197, 94, 0.13);
    color: #86efac;
    border-color: rgba(34, 197, 94, 0.32);
  }
  .sa-btn-success:hover {
    background: rgba(34, 197, 94, 0.22);
    color: #ffffff;
  }

  .sa-btn-ghost {
    background: rgba(255, 255, 255, 0.05);
    color: #e2e8f0;
    border-color: rgba(255, 255, 255, 0.1);
  }
  .sa-btn-icon-only {
    padding: 8px 10px;
    color: #f87171;
    flex-shrink: 0;
  }

  /* ── MODALES (MONTADOS EN BODY PARA EVITAR BUGS DE SCROLL Y TRANSFORM) ── */
  .sa-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(2, 4, 10, 0.82);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 9500;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
    overflow-y: auto;
    overscroll-behavior: contain;
  }
  .sa-modal-backdrop-top {
    z-index: 9600;
  }
  .sa-modal-backdrop.active {
    display: flex;
  }

  .sa-modal {
    width: 100%;
    max-width: 500px;
    background: #13131f;
    border: 1px solid rgba(168, 85, 247, 0.35);
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.65);
    max-height: calc(100dvh - 32px);
    overflow-y: auto;
    overflow-x: hidden;
    overscroll-behavior: contain;
    display: flex;
    flex-direction: column;
    margin: auto;
    box-sizing: border-box;
  }

  .sa-modal * {
    box-sizing: border-box;
  }

  .sa-modal-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 16px;
    padding-top: 14px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    width: 100%;
    flex-shrink: 0;
  }
  .sa-modal-actions .sa-btn {
    flex: 1;
    height: 40px;
  }

  .sa-modal-atletas {
    max-width: 680px;
  }

  .sa-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding-bottom: 12px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.07);
  }

  .sa-modal-close {
    width: 32px;
    height: 32px;
    border-radius: 9px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
  }
  .sa-modal-close:hover {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.1);
  }

  .sa-modal-atletas-body {
    overflow-y: auto;
    padding-top: 14px;
  }

  /* Grid de Atletas dentro del Modal: idéntico a views/entrenador/clientes.php */
  .modal-atletas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 12px;
  }

  .modal-ig-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    padding: 18px 12px 14px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    text-align: center;
    min-width: 0;
  }

  .modal-ig-card .ig-avatar {
    width: 54px;
    height: 54px;
    border-radius: 10px;
    font-size: 1.25rem;
  }

  .modal-ig-card .ig-info {
    align-items: center;
    text-align: center;
    width: 100%;
  }

  @media (max-width: 767px) {
    .modal-atletas-grid {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    .modal-ig-card {
      flex-direction: row;
      align-items: center;
      padding: 12px 14px;
      gap: 14px;
      text-align: left;
    }
    .modal-ig-card .ig-avatar {
      width: 52px;
      height: 52px;
    }
    .modal-ig-card .ig-info {
      align-items: flex-start;
      text-align: left;
    }
  }

  .sa-tabs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
    background: rgba(255, 255, 255, 0.04);
    padding: 5px;
    border-radius: 12px;
    margin-bottom: 14px;
  }
  .sa-tab-btn {
    padding: 8px 10px;
    border-radius: 9px;
    border: none;
    background: transparent;
    color: #94a3b8;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
  }
  .sa-tab-btn.active {
    background: #8a2be2;
    color: #ffffff;
  }

  .sa-field label {
    display: block;
    font-size: 0.7rem;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 4px;
  }
  .sa-field input,
  .sa-field select {
    width: 100%;
    background: #0f101a;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 11px;
    color: #f8fafc;
    font-size: 0.84rem;
    font-weight: 600;
    padding: 9px 11px;
    outline: none;
  }
  .sa-field input:focus,
  .sa-field select:focus {
    border-color: #a855f7;
  }
  /* Evitar que el autocompletado del navegador ponga fondo blanco y oculte las letras blancas */
  .sa-field input:-webkit-autofill,
  .sa-field input:-webkit-autofill:hover,
  .sa-field input:-webkit-autofill:focus,
  .sa-field input:-webkit-autofill:active,
  .sa-code-input:-webkit-autofill,
  .sa-code-input:-webkit-autofill:hover,
  .sa-code-input:-webkit-autofill:focus {
    -webkit-box-shadow: 0 0 0 1000px #0f101a inset !important;
    -webkit-text-fill-color: #f8fafc !important;
    caret-color: #f8fafc !important;
    transition: background-color 9999s ease-in-out 0s;
  }

  /* ══════════════════════════════════════════════════════════════════
     FAB – BOTÓN FLOTANTE (IDÉNTICO A DASHBOARD DEL ENTRENADOR)
  ══════════════════════════════════════════════════════════════════ */
  .fab-wrapper {
    position: fixed;
    bottom: 80px;
    right: 20px;
    z-index: 2500;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 12px;
  }

  .fab-main {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    border: none;
    background: #8B5CF6;
    color: #FFFFFF;
    font-size: 1.5rem;
    display: grid;
    place-items: center;
    cursor: pointer;
    box-shadow: 0 8px 16px rgba(139, 92, 246, 0.4) !important;
    transition: transform .25s cubic-bezier(.4,0,.2,1),
                background .2s, box-shadow .2s, opacity .2s;
  }
  .fab-main:hover,
  .fab-main:active {
    transform: scale(1.05) translateY(-2px);
    background: #7c3aed;
    box-shadow: 0 12px 24px rgba(139, 92, 246, 0.5) !important;
  }
  .fab-main.is-open {
    transform: rotate(45deg) scale(1.05);
    background: #7c3aed;
  }

  #fabIcon {
    transition: transform .25s cubic-bezier(.4,0,.2,1);
  }

  .fab-options {
    display: flex;
    flex-direction: column;
    gap: 4px;
    background: var(--bg2, #13131f);
    border: 1px solid var(--border, rgba(255,255,255,0.12));
    border-radius: 16px;
    padding: 8px;
    pointer-events: none;
    opacity: 0;
    transform: translateY(20px) scale(0.95);
    transform-origin: bottom right;
    transition: opacity .3s cubic-bezier(0.175, 0.885, 0.32, 1.275),
                transform .3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.55);
    margin-bottom: 4px;
  }

  .fab-option {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    padding: 8px 16px 8px 8px;
    border-radius: 10px;
    transition: background .2s ease;
    pointer-events: none;
  }
  .fab-option .fab-label {
    color: #e2e8f0;
    font-size: .85rem;
    font-weight: 600;
    white-space: nowrap;
  }
  .fab-option i {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(139, 92, 246, 0.16);
    color: var(--opt-color, #a855f7);
    display: grid;
    place-items: center;
    font-size: 1.1rem;
    transition: background .2s ease, transform .2s ease;
    flex-shrink: 0;
  }
  .fab-option:hover {
    background: rgba(255, 255, 255, 0.06);
  }
  .fab-option:hover i {
    background: rgba(139, 92, 246, 0.28);
    transform: scale(1.08);
  }

  .fab-options.is-open {
    pointer-events: all;
    opacity: 1;
    transform: translateY(0) scale(1);
  }
  .fab-options.is-open .fab-option {
    pointer-events: all;
  }
</style>

<script>
// Mapa en memoria de los atletas por entrenador para el modal instantáneo
const SA_ENTRENADORES_ATLETAS = <?= json_encode(
    array_reduce($entrenadores, function ($acc, $ent) {
        $acc[(int)$ent['id']] = [
            'nombre'  => (string)$ent['nombre'],
            'atletas' => array_map(function ($a) {
                return [
                    'cliente_id' => (int)($a['cliente_id'] ?? 0),
                    'nombre'     => (string)($a['nombre'] ?? ''),
                    'email'      => (string)($a['email'] ?? ''),
                    'peso_kg'    => isset($a['peso_kg']) && $a['peso_kg'] !== null ? (float)$a['peso_kg'] : null,
                ];
            }, $ent['atletas_lista'] ?? []),
        ];
        return $acc;
    }, []),
    JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
) ?>;

const SA_AVATAR_COLORS = ['#7c3aed','#ec4899','#f97316','#10b981','#3b82f6','#ef4444','#8b5cf6','#06b6d4','#f59e0b','#6366f1'];

let saActiveStatusFilter = '';
let fabOpen = false;

// Mover el FAB y los modales al body para evitar que el 'transform' de .xp-main rompa position:fixed y el scroll
document.addEventListener('DOMContentLoaded', () => {
  ['fabWrapper', 'modalFichaEntrenador', 'modalAtletasEntrenador', 'modalEntrenador'].forEach(id => {
    const el = document.getElementById(id);
    if (el) document.body.appendChild(el);
  });
});

function sincronizarEstadoModales() {
  const hayModalAbierto = !!document.querySelector('.sa-modal-backdrop.active');
  document.body.style.overflow = hayModalAbierto ? 'hidden' : '';
  document.documentElement.style.overflow = hayModalAbierto ? 'hidden' : '';
  const fab = document.getElementById('fabWrapper');
  if (fab) {
    fab.style.display = hayModalAbierto ? 'none' : 'flex';
  }
}

function toggleFab() {
  fabOpen = !fabOpen;
  const btn  = document.getElementById('fabMain');
  const opts = document.getElementById('fabOptions');
  if (btn)  btn.classList.toggle('is-open', fabOpen);
  if (opts) {
    opts.classList.toggle('is-open', fabOpen);
    opts.setAttribute('aria-hidden', fabOpen ? 'false' : 'true');
  }
}

function cerrarFab() {
  fabOpen = false;
  document.getElementById('fabMain')?.classList.remove('is-open');
  const opts = document.getElementById('fabOptions');
  if (opts) {
    opts.classList.remove('is-open');
    opts.setAttribute('aria-hidden', 'true');
  }
}

document.addEventListener('click', function(e) {
  if (fabOpen && !e.target.closest('#fabWrapper')) {
    cerrarFab();
  }
});

// ── ABRIR CALENDARIO MINIMALISTA BENTO AL TOCAR CUALQUIER CAMPO DE FECHA ──
function abrirDatePicker(inp) {
  if (!inp) return;
  if (window.XpDatePicker && typeof window.XpDatePicker.open === 'function') {
    window.XpDatePicker.open(inp);
    return;
  }
  inp.focus();
  if (typeof inp.showPicker === 'function') {
    try { inp.showPicker(); } catch (e) {}
  }
}

// ── ABRIR / CERRAR MODAL DE FICHA DE GESTIÓN DEL ENTRENADOR ──
function abrirModalFichaEntrenador(eid) {
  document.querySelectorAll('.sa-ficha-pane').forEach(p => {
    p.style.display = 'none';
  });
  const pane = document.getElementById('fichaEntrenadorPane_' + eid);
  if (pane) {
    pane.style.display = 'flex';
    document.getElementById('modalFichaEntrenador')?.classList.add('active');
    sincronizarEstadoModales();
  }
}

function cerrarModalFichaEntrenador() {
  document.getElementById('modalFichaEntrenador')?.classList.remove('active');
  sincronizarEstadoModales();
}

function saEscHtml(str) {
  return String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

// ── 1. FILTRADO POR ACCESOS DIRECTOS + BUSCADOR ──
function filtrarPorEstadoShortcut(statusKey, btnEl) {
  if (saActiveStatusFilter === statusKey) {
    resetFiltroEstado();
    return;
  }
  saActiveStatusFilter = statusKey;
  document.querySelectorAll('.sa-shortcut-btn').forEach(b => b.classList.remove('active'));
  if (btnEl) btnEl.classList.add('active');

  const labelsMap = {
    'al_dia': 'Membresías al día',
    'aviso': 'En semana de aviso (-7 días)',
    'prorroga': 'En prórroga (+7 días gracia)',
    'suspendido': 'Web en suspensión / pausa'
  };
  const banner = document.getElementById('saActiveFilterBanner');
  const txt = document.getElementById('saActiveFilterText');
  if (banner && txt) {
    txt.innerHTML = `<i class="ti ti-filter"></i> Mostrando: <strong>${labelsMap[statusKey] || statusKey}</strong>`;
    banner.style.display = 'flex';
  }
  aplicarFiltrosEntrenadores();
}

function resetFiltroEstado() {
  saActiveStatusFilter = '';
  document.querySelectorAll('.sa-shortcut-btn').forEach(b => b.classList.remove('active'));
  const banner = document.getElementById('saActiveFilterBanner');
  if (banner) banner.style.display = 'none';
  aplicarFiltrosEntrenadores();
}

function aplicarFiltrosEntrenadores() {
  const inp = document.getElementById('saSearchInput');
  const clearBtn = document.getElementById('saSearchClear');
  const q = String(inp?.value || '').toLowerCase().trim();
  if (clearBtn) clearBtn.style.display = q ? 'flex' : 'none';

  let visibles = 0;
  document.querySelectorAll('#saTrainersGrid .sa-trainer-card').forEach(card => {
    const st = card.getAttribute('data-status') || '';
    const haystack = card.getAttribute('data-search') || '';
    const matchStatus = !saActiveStatusFilter || st === saActiveStatusFilter;
    const matchSearch = !q || haystack.includes(q);
    const show = matchStatus && matchSearch;
    card.classList.toggle('filter-hidden', !show);
    if (show) visibles++;
  });

  const emptyEl = document.getElementById('saEmptyFilterState');
  if (emptyEl) {
    emptyEl.style.display = visibles === 0 ? 'block' : 'none';
  }
}

function limpiarBusquedaEntrenadores() {
  const inp = document.getElementById('saSearchInput');
  if (inp) {
    inp.value = '';
    aplicarFiltrosEntrenadores();
  }
}

// ── 2. CÁLCULO DINÁMICO DE AVISO (-7d) Y CORTE (+7d) AL CAMBIAR PAGO ──
function recalcularFechasEntrenador(eid, nuevaFechaPagoIso) {
  if (!nuevaFechaPagoIso) return;
  const baseDate = new Date(nuevaFechaPagoIso + 'T12:00:00');
  if (isNaN(baseDate.getTime())) return;

  // Aviso = 7 días antes
  const avisoDate = new Date(baseDate.getTime());
  avisoDate.setDate(avisoDate.getDate() - 7);
  const ddA = String(avisoDate.getDate()).padStart(2, '0');
  const mmA = String(avisoDate.getMonth() + 1).padStart(2, '0');
  const yyA = avisoDate.getFullYear();
  const txtAviso = document.getElementById('txtAviso_' + eid);
  if (txtAviso) {
    txtAviso.textContent = `${ddA}/${mmA}/${yyA}`;
  }

  // Corte = 7 días después por defecto (pero sigue siendo editable manualmente)
  const corteDate = new Date(baseDate.getTime());
  corteDate.setDate(corteDate.getDate() + 7);
  const ddC = String(corteDate.getDate()).padStart(2, '0');
  const mmC = String(corteDate.getMonth() + 1).padStart(2, '0');
  const yyC = corteDate.getFullYear();
  const inpCorte = document.getElementById('inpCorte_' + eid);
  if (inpCorte) {
    inpCorte.value = `${yyC}-${mmC}-${ddC}`;
  }
}

// ── 3. MODAL DE ATLETAS ASIGNADOS AL ENTRENADOR (SIN "ACTIVO" NI SELECT DE COACH) ──
function abrirModalAtletasEntrenador(eid) {
  const info = SA_ENTRENADORES_ATLETAS[eid] || { nombre: 'Entrenador', atletas: [] };
  const titleEl = document.getElementById('modalAtlCoachTitle');
  const subEl   = document.getElementById('modalAtlCoachSub');
  const bodyEl  = document.getElementById('modalAtlCoachBody');

  if (titleEl) titleEl.textContent = `Atletas de ${info.nombre}`;
  if (subEl)   subEl.textContent   = `${info.atletas.length} ${info.atletas.length === 1 ? 'atleta registrado' : 'atletas registrados'}`;

  if (bodyEl) {
    if (!info.atletas || info.atletas.length === 0) {
      bodyEl.innerHTML = `
        <div style="text-align:center; padding:32px 16px; color:#94a3b8;">
          <i class="ti ti-users" style="font-size:2.2rem; opacity:0.45; display:block; margin-bottom:8px;"></i>
          <p style="margin:0; font-size:0.88rem;">Este entrenador aún no tiene atletas registrados.</p>
        </div>`;
    } else {
      let html = `<div class="modal-atletas-grid">`;
      info.atletas.forEach((atl, i) => {
        const parts = String(atl.nombre || '').trim().split(/\s+/);
        const shortName = parts.slice(0, 2).join(' ') || atl.nombre;
        const initial = String(parts[0] || 'A').charAt(0).toUpperCase();
        const color = SA_AVATAR_COLORS[i % SA_AVATAR_COLORS.length];
        const micro = (atl.peso_kg ? `${atl.peso_kg} kg • ` : '') + (atl.email || 'Sin correo');

        html += `
          <div class="modal-ig-card glass-surface">
            <div class="ig-avatar" style="background:${color};">${saEscHtml(initial)}</div>
            <div class="ig-info">
              <div class="ig-name" title="${saEscHtml(atl.nombre)}">${saEscHtml(shortName)}</div>
              <div class="ig-micro" title="${saEscHtml(micro)}">${saEscHtml(micro)}</div>
            </div>
          </div>`;
      });
      html += `</div>`;
      bodyEl.innerHTML = html;
    }
  }

  document.getElementById('modalAtletasEntrenador')?.classList.add('active');
  sincronizarEstadoModales();
}

function cerrarModalAtletasEntrenador() {
  document.getElementById('modalAtletasEntrenador')?.classList.remove('active');
  sincronizarEstadoModales();
}

// ── 4. CÓDIGO, LINK Y GUARDADO DE MEMBRESÍA ──
function sanitizarCodigoJs(raw) {
  return String(raw || '').trim().toUpperCase().replace(/[^A-Z0-9\-_]/g, '');
}

function construirUrlReferido(codigo) {
  const base = String(APP || window.location.origin).replace(/\/+$/, '').replace(/\s/g, '%20');
  return `${base}/auth/registro?ref=${encodeURIComponent(codigo)}`;
}

async function sincronizarCodigoSiCambio(eid) {
  const inp = document.getElementById('inpCodigo_' + eid);
  if (!inp) return '';
  const code = sanitizarCodigoJs(inp.value);
  inp.value = code;
  const saved = sanitizarCodigoJs(inp.getAttribute('data-saved-code') || '');
  if (code && code !== saved) {
    try {
      const fecha = document.getElementById('inpFecha_' + eid)?.value || '';
      const corte = document.getElementById('inpCorte_' + eid)?.value || '';
      const res = await fetch(APP + '/admin/api-actualizar-membresia', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({
          csrf_token: CSRF,
          entrenador_id: eid,
          codigo_referido: code,
          fecha_proximo_pago: fecha,
          fecha_corte: corte
        })
      });
      const d = await res.json();
      if (d && d.ok && d.membresia && d.membresia.codigo_referido) {
        inp.value = d.membresia.codigo_referido;
        inp.setAttribute('data-saved-code', d.membresia.codigo_referido);
        return d.membresia.codigo_referido;
      }
    } catch (_) {}
  }
  return code;
}

async function copiarTextoPortapapeles(texto) {
  if (navigator.clipboard && navigator.clipboard.writeText) {
    await navigator.clipboard.writeText(texto);
    return;
  }
  const ta = document.createElement('textarea');
  ta.value = texto;
  ta.style.position = 'fixed';
  ta.style.opacity = '0';
  document.body.appendChild(ta);
  ta.select();
  document.execCommand('copy');
  document.body.removeChild(ta);
}

async function regenerarCodigoAdmin(eid) {
  try {
    const res = await fetch(APP + '/admin/api-generar-codigo', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify({ csrf_token: CSRF, entrenador_id: eid })
    });
    const d = await res.json();
    if (d && d.ok && d.codigo_referido) {
      const inp = document.getElementById('inpCodigo_' + eid);
      if (inp) {
        inp.value = d.codigo_referido;
        inp.setAttribute('data-saved-code', d.codigo_referido);
      }
      Swal.fire({
        toast: true, position: 'top-end', icon: 'success',
        title: 'Código regenerado: ' + d.codigo_referido,
        timer: 2000, showConfirmButton: false, background: '#12131f', color: '#fff'
      });
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: (d && d.msg) || 'No se pudo generar.', background: '#12131f', color: '#fff' });
    }
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: err.message, background: '#12131f', color: '#fff' });
  }
}

async function copiarCodigoEntrenador(eid) {
  const code = await sincronizarCodigoSiCambio(eid);
  if (!code) return;
  await copiarTextoPortapapeles(code);
  Swal.fire({
    icon: 'success',
    title: 'Código copiado',
    text: `Código "${code}" copiado al portapapeles.`,
    timer: 1500,
    showConfirmButton: false,
    background: '#12131f',
    color: '#fff'
  });
}

async function copiarLinkEntrenador(eid) {
  const code = await sincronizarCodigoSiCambio(eid);
  if (!code) return;
  const link = construirUrlReferido(code);
  await copiarTextoPortapapeles(link);
  Swal.fire({
    icon: 'success',
    title: 'Link de invitación copiado',
    html: `<div style="font-size:0.82rem;color:#cbd5e1;word-break:break-all;background:rgba(255,255,255,0.05);padding:8px 10px;border-radius:10px;margin-top:6px;">${link}</div>`,
    timer: 2000,
    showConfirmButton: false,
    background: '#12131f',
    color: '#fff'
  });
}

async function guardarMembresia(eid) {
  const codigo = document.getElementById('inpCodigo_' + eid)?.value || '';
  const fecha  = document.getElementById('inpFecha_' + eid)?.value || '';
  const corte  = document.getElementById('inpCorte_' + eid)?.value || '';

  try {
    const res = await fetch(APP + '/admin/api-actualizar-membresia', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify({
        csrf_token: CSRF,
        entrenador_id: eid,
        codigo_referido: codigo,
        fecha_proximo_pago: fecha,
        fecha_corte: corte
      })
    });
    const data = await res.json();
    if (data.ok) {
      Swal.fire({
        icon: 'success',
        title: 'Guardado',
        text: data.msg,
        timer: 1400,
        showConfirmButton: false,
        background: '#12131f',
        color: '#fff'
      }).then(() => window.location.reload());
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: data.msg, background: '#12131f', color: '#fff' });
    }
  } catch (e) {
    Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo conectar con el servidor.', background: '#12131f', color: '#fff' });
  }
}

async function togglePausaEntrenador(eid, pausar) {
  const confirm = await Swal.fire({
    icon: pausar ? 'warning' : 'question',
    title: pausar ? '¿Pausar la web de este entrenador?' : '¿Reactivar acceso web?',
    text: pausar
      ? 'El entrenador verá la pantalla de membresía en pausa hasta que la reactives o pague su mensualidad.'
      : 'El entrenador recuperará inmediatamente el acceso a su panel.',
    showCancelButton: true,
    confirmButtonColor: pausar ? '#ef4444' : '#22c55e',
    confirmButtonText: pausar ? 'Sí, pausar web' : 'Sí, reactivar',
    cancelButtonText: 'Cancelar',
    background: '#12131f',
    color: '#fff'
  });
  if (!confirm.isConfirmed) return;

  const res = await fetch(APP + '/admin/api-toggle-pausa', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
    body: JSON.stringify({
      csrf_token: CSRF,
      entrenador_id: eid,
      pausar: pausar
    })
  });
  const data = await res.json();
  if (data.ok) {
    window.location.reload();
  } else {
    Swal.fire({ icon: 'error', title: 'Error', text: data.msg, background: '#12131f', color: '#fff' });
  }
}

async function revocarEntrenador(eid, nombre) {
  const confirm = await Swal.fire({
    icon: 'warning',
    title: `¿Revocar rol de entrenador a ${nombre}?`,
    text: 'Sus atletas actuales serán reasignados automáticamente a otro entrenador activo.',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    confirmButtonText: 'Sí, revocar',
    cancelButtonText: 'Cancelar',
    background: '#12131f',
    color: '#fff'
  });
  if (!confirm.isConfirmed) return;

  const res = await fetch(APP + '/admin/api-revocar-entrenador', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
    body: JSON.stringify({ csrf_token: CSRF, entrenador_id: eid })
  });
  const data = await res.json();
  if (data.ok) {
    window.location.reload();
  } else {
    Swal.fire({ icon: 'error', title: 'Error', text: data.msg, background: '#12131f', color: '#fff' });
  }
}

function abrirModalEntrenador() {
  const n = document.getElementById('newEntNombre');
  const e = document.getElementById('newEntEmail');
  const p = document.getElementById('newEntPass');
  const c = document.getElementById('newEntCodigo');
  if (n) n.value = '';
  if (e) e.value = '';
  if (p) p.value = '';
  if (c) c.value = '';
  document.getElementById('modalEntrenador')?.classList.add('active');
  sincronizarEstadoModales();
}
function cerrarModalEntrenador() {
  document.getElementById('modalEntrenador')?.classList.remove('active');
  sincronizarEstadoModales();
}
function cambiarModoModal(modo) {
  document.getElementById('modoEntrenador').value = modo;
  document.getElementById('tabBtnNuevo').classList.toggle('active', modo === 'nuevo');
  document.getElementById('tabBtnPromover').classList.toggle('active', modo === 'promover');
  document.getElementById('paneNuevoEntrenador').style.display = modo === 'nuevo' ? 'flex' : 'none';
  document.getElementById('panePromoverEntrenador').style.display = modo === 'promover' ? 'flex' : 'none';
}

async function submitCrearEntrenador(ev) {
  ev.preventDefault();
  const btn = document.getElementById('btnSubmitEntrenador');
  btn.disabled = true;

  const modo = document.getElementById('modoEntrenador').value;
  const payload = {
    csrf_token: CSRF,
    modo: modo,
    nombre: document.getElementById('newEntNombre').value,
    email: document.getElementById('newEntEmail').value,
    password: document.getElementById('newEntPass').value,
    usuario_id: document.getElementById('promoverUsuarioId').value,
    codigo_referido: document.getElementById('newEntCodigo').value,
    monto_mensualidad: document.getElementById('newEntMonto').value,
    fecha_proximo_pago: document.getElementById('newEntFechaPago').value
  };

  try {
    const res = await fetch(APP + '/admin/api-crear-entrenador', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.ok) {
      Swal.fire({
        icon: 'success',
        title: '¡Entrenador habilitado!',
        text: data.msg,
        background: '#12131f',
        color: '#fff'
      }).then(() => window.location.reload());
    } else {
      Swal.fire({ icon: 'error', title: 'Atención', text: data.msg, background: '#12131f', color: '#fff' });
      btn.disabled = false;
    }
  } catch (e) {
    Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión.', background: '#12131f', color: '#fff' });
    btn.disabled = false;
  }
}

async function gestionarSolicitudEntrenador(usuarioId, accion, btnEl) {
  const card = document.getElementById('saSolicitudEnt-' + usuarioId);
  const btns = card ? card.querySelectorAll('button') : (btnEl ? [btnEl] : []);
  btns.forEach(b => { b.disabled = true; b.style.opacity = '0.6'; });

  try {
    const res = await fetch(APP + '/admin/api-aprobar-entrenador', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify({ csrf_token: CSRF, usuario_id: usuarioId, accion: accion })
    });
    const data = await res.json();
    if (data.ok) {
      if (card) {
        card.style.opacity = '0';
        card.style.transform = 'translateX(-16px)';
        setTimeout(() => {
          card.remove();
          const list = document.getElementById('saSolicitudesList');
          const countEl = document.getElementById('saSolCount');
          if (list && countEl) countEl.textContent = String(list.children.length);
          if (list && list.children.length === 0) {
            document.getElementById('saSolicitudesEntrenadoresPanel')?.remove();
          }
        }, 260);
      }
      if (typeof xpFetchNotificaciones === 'function') xpFetchNotificaciones(true);
      await Swal.fire({
        icon: 'success',
        title: accion === 'aprobar' ? '¡Entrenador aprobado!' : 'Solicitud rechazada',
        text: data.msg || 'Acción procesada correctamente.',
        background: '#12131f',
        color: '#fff',
        confirmButtonColor: '#8a2be2',
        timer: accion === 'aprobar' ? 2200 : 1800,
        showConfirmButton: accion === 'aprobar'
      });
      if (accion === 'aprobar') {
        window.location.reload();
      }
    } else {
      Swal.fire({ icon: 'error', title: 'Atención', text: data.msg || 'No se pudo procesar la solicitud.', background: '#12131f', color: '#fff' });
      btns.forEach(b => { b.disabled = false; b.style.opacity = ''; });
    }
  } catch (err) {
    console.error(err);
    Swal.fire({ icon: 'error', title: 'Error', text: 'Error de red o conexión.', background: '#12131f', color: '#fff' });
    btns.forEach(b => { b.disabled = false; b.style.opacity = ''; });
  }
}
</script>

<?php require BASE_DIR . '/views/layout/footer.php'; ?>
