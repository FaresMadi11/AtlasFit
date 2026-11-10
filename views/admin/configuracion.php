<?php
// views/admin/configuracion.php — Módulo 3: Configuración del Mensaje de Aviso al Pausar
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
      <h1 class="sys-top-title user-name-gradient" title="Configuración" style="font-size: 1.12rem !important; font-weight: 800; color: #fff; margin: 0; line-height: 1.25;">
        <span class="user-name-text">Configuración</span>
      </h1>
      <p style="font-size: .74rem; font-weight: 500; color: #94a3af; margin: 2px 0 0; letter-spacing: 0.2px; line-height: 1.2;">
        Mensaje de aviso al pausar y ajustes del sistema
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
    padding: 24px 26px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.42);
  }

  .sa-cfg-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 99px;
    background: rgba(168, 85, 247, 0.18);
    border: 1px solid rgba(168, 85, 247, 0.38);
    color: #e9d5ff;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.7px;
    text-transform: uppercase;
    margin-bottom: 8px;
  }

  .sa-cfg-title {
    margin: 0;
    font-size: 1.35rem;
    font-weight: 800;
    color: #ffffff;
  }

  .sa-cfg-sub {
    margin: 6px 0 0;
    font-size: 0.86rem;
    color: #94a3b8;
  }

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

  .sa-preview-box {
    margin-top: 18px;
    padding: 16px 18px;
    border-radius: 16px;
    background: rgba(239, 68, 68, 0.09);
    border: 1px solid rgba(239, 68, 68, 0.3);
    display: flex;
    align-items: flex-start;
    gap: 12px;
  }

  .sa-btn-save {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 22px;
    border-radius: 13px;
    border: none;
    background: linear-gradient(135deg, #8a2be2, #6d28d9);
    color: #ffffff;
    font-size: 0.88rem;
    font-weight: 800;
    cursor: pointer;
    box-shadow: 0 8px 22px rgba(138, 43, 226, 0.38);
    transition: all 0.2s ease;
  }
  .sa-btn-save:hover {
    transform: translateY(-1px);
    box-shadow: 0 12px 28px rgba(138, 43, 226, 0.5);
  }
</style>

<div class="sa-cfg-shell">
  <section class="sa-cfg-card">
    <div class="sa-cfg-badge">
      <i class="ti ti-settings"></i> MÓDULO DE CONFIGURACIÓN
    </div>
    <h1 class="sa-cfg-title">Configuración del Mensaje de Aviso</h1>
    <p class="sa-cfg-sub">
      Personaliza el mensaje que verá el entrenador cuando su web se encuentre pausada o en suspensión por membresía vencida.
    </p>

    <div class="sa-cfg-field">
      <label for="cfgAlcanceEntrenador">
        <i class="ti ti-users" style="color:#a855f7;"></i> Aplicar configuración a
      </label>
      <select id="cfgAlcanceEntrenador" class="sa-cfg-select" onchange="cambiarAlcanceMensaje(this.value)">
        <option value="todos" data-msg="<?= e($mensajePausaGlobal) ?>">Todos los entrenadores (Mensaje general)</option>
        <?php foreach ($entrenadores as $ent):
          $eid = (int)$ent['id'];
          $msgEnt = trim((string)($ent['membresia']['mensaje_bloqueo'] ?? ''));
          if ($msgEnt === '') $msgEnt = $mensajePausaGlobal;
        ?>
          <option value="<?= $eid ?>" data-msg="<?= e($msgEnt) ?>">
            Entrenador: <?= e($ent['nombre']) ?> (<?= e($ent['email']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Campo exacto de la Imagen 3 -->
    <div class="sa-cfg-field">
      <label for="cfgInputMensajePausa">
        <i class="ti ti-message-exclamation" style="color:#94a3b8;"></i> MENSAJE AL PAUSAR (SOLO VISIBLE AL ENTRENADOR)
      </label>
      <input type="text"
             id="cfgInputMensajePausa"
             class="sa-cfg-input"
             maxlength="255"
             value="<?= e($mensajePausaGlobal) ?>"
             placeholder="La web ha sido pausada hasta que logres pagar la membresía."
             oninput="actualizarVistaPreviaMensaje(this.value)">
    </div>

    <div class="sa-preview-box">
      <div style="width:36px;height:36px;border-radius:10px;background:rgba(239,68,68,0.18);color:#f87171;display:flex;align-items:center;justify-content:center;font-size:1.15rem;flex-shrink:0;">
        <i class="ti ti-lock-pause"></i>
      </div>
      <div style="min-width:0;">
        <div style="font-size:0.7rem;font-weight:800;text-transform:uppercase;letter-spacing:0.6px;color:#fca5a5;">
          Vista previa del aviso al entrenador
        </div>
        <div id="cfgPreviewTexto" style="font-size:0.88rem;color:#f8fafc;font-weight:600;margin-top:3px;line-height:1.4;">
          <?= e($mensajePausaGlobal) ?>
        </div>
      </div>
    </div>

    <div style="display:flex;justify-content:flex-end;margin-top:20px;">
      <button type="button" class="sa-btn-save" id="btnGuardarMsgPausa" onclick="guardarConfiguracionMensaje()">
        <i class="ti ti-device-floppy"></i> Guardar Mensaje de Aviso
      </button>
    </div>
  </section>
</div>

<script>
function cambiarAlcanceMensaje(val) {
  const sel = document.getElementById('cfgAlcanceEntrenador');
  const opt = sel?.options[sel.selectedIndex];
  const msg = opt ? (opt.getAttribute('data-msg') || '') : '';
  const inp = document.getElementById('cfgInputMensajePausa');
  if (inp) {
    inp.value = msg;
    actualizarVistaPreviaMensaje(msg);
  }
}

function actualizarVistaPreviaMensaje(val) {
  const prev = document.getElementById('cfgPreviewTexto');
  if (!prev) return;
  const txt = String(val || '').trim();
  prev.textContent = txt !== '' ? txt : 'La web ha sido pausada hasta que logres pagar la membresía.';
}

async function guardarConfiguracionMensaje() {
  const btn = document.getElementById('btnGuardarMsgPausa');
  const alcance = document.getElementById('cfgAlcanceEntrenador')?.value || 'todos';
  const mensaje = document.getElementById('cfgInputMensajePausa')?.value || '';

  if (btn) btn.disabled = true;
  try {
    const res = await fetch(APP + '/admin/api-guardar-configuracion', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify({
        csrf_token: CSRF,
        tipo: 'mensaje_pausa',
        alcance: alcance,
        mensaje_bloqueo: mensaje
      })
    });
    const d = await res.json();
    if (d.ok) {
      const sel = document.getElementById('cfgAlcanceEntrenador');
      if (sel) {
        if (alcance === 'todos') {
          Array.from(sel.options).forEach(o => o.setAttribute('data-msg', d.mensaje_bloqueo));
        } else {
          const opt = sel.options[sel.selectedIndex];
          if (opt) opt.setAttribute('data-msg', d.mensaje_bloqueo);
        }
      }
      Swal.fire({
        icon: 'success',
        title: 'Configuración guardada',
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

