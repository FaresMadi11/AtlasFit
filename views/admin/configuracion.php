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
  $xpPaletaEsAdmin      = true;
  $xpPaletaGlobalActivo = !empty($temaGlobalActivo);
  require BASE_DIR . '/views/partials/cfg_paleta_colores.php';

  $xpCatalogoEsAdmin = true;
  $xpCatalogoApiBase = 'admin';
  require BASE_DIR . '/views/partials/cfg_catalogo_ejercicios.php';

  $xpHerramientasEsAdmin = true;
  $xpHerramientasApiBase = 'admin';
  require BASE_DIR . '/views/partials/cfg_herramientas_ia_backup.php';
  ?>
</div>

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
</script>

<?php require BASE_DIR . '/views/layout/footer.php'; ?>
