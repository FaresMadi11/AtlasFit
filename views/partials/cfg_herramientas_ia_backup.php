<?php
/**
 * views/partials/cfg_herramientas_ia_backup.php
 * Partial compartido para Importador IA + Copia de Seguridad (+ Accesos rápidos de pago para Entrenador).
 * Params esperados:
 *  - $xpHerramientasEsAdmin (bool)
 *  - $xpHerramientasApiBase ('admin'|'entrenador')
 *  - $atletasList (array)
 *  - $dbName (string)
 *  - $totalTablas (int)
 *  - $totalSeries (int)
 *  - $csrf (string)
 */
$xpHerramientasEsAdmin = !empty($xpHerramientasEsAdmin);
$xpHerramientasApiBase = (string)($xpHerramientasApiBase ?? ($xpHerramientasEsAdmin ? 'admin' : 'entrenador'));
$atletasList           = $atletasList ?? [];
$dbName                = (string)($dbName ?? 'xprod_fit');
$totalTablas           = (int)($totalTablas ?? 0);
$totalSeries           = (int)($totalSeries ?? 0);
$csrf                  = (string)($csrf ?? ($_SESSION['csrf_token'] ?? ''));
?>
<style>
/* ── Accesos Rápidos / Herramientas Bento ── */
.cfg-quick-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  max-width: 520px;
  margin: 0 auto 24px;
}
.cfg-quick-grid.cfg-quick-grid-admin {
  max-width: 100%;
  margin: 0;
}
.cfg-quick-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 16px 10px;
  min-height: 112px;
  width: 100%;
  border-radius: 18px;
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.10);
  color: var(--text, #f8fafc);
  text-decoration: none;
  font-family: inherit;
  font-weight: 700;
  font-size: 0.84rem;
  text-align: center;
  transition: all .22s cubic-bezier(.4,0,.2,1);
  cursor: pointer;
}
.cfg-quick-card:hover {
  background: rgba(255,255,255,0.09);
  border-color: rgba(168, 85, 247, 0.35);
  transform: translateY(-2px);
  box-shadow: 0 10px 30px rgba(0,0,0,0.35);
  color: #fff;
}
.cfg-quick-icon {
  width: 48px; height: 48px; border-radius: 14px;
  display: grid; place-items: center;
  font-size: 1.4rem; color: #fff; flex-shrink: 0;
}

/* ── Modales de Herramientas Bento ── */
.xp-cfg-modal {
  position: fixed;
  inset: 0;
  z-index: 99980;
  background: rgba(4, 5, 10, 0.78);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  display: none;
  align-items: center;
  justify-content: center;
  padding: 16px;
}
.xp-cfg-modal.is-open {
  display: flex;
}
.xp-cfg-modal-dialog {
  position: relative;
  width: 100%;
  max-width: 640px;
  max-height: 90vh;
  overflow-y: auto;
  border-radius: 22px;
  animation: xpCfgModalIn 0.18s cubic-bezier(0.2, 0.8, 0.2, 1);
}
@keyframes xpCfgModalIn {
  from { opacity: 0; transform: translateY(10px) scale(0.98); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}
.xp-cfg-modal-close {
  position: absolute;
  top: 14px;
  right: 14px;
  z-index: 5;
  width: 34px;
  height: 34px;
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.14);
  background: rgba(255,255,255,0.07);
  color: #e2e8f0;
  display: grid;
  place-items: center;
  font-size: 1.05rem;
  cursor: pointer;
  transition: all 0.18s ease;
}
.xp-cfg-modal-close:hover {
  background: rgba(239, 68, 68, 0.22);
  border-color: rgba(239, 68, 68, 0.45);
  color: #fca5a5;
}

/* ── Estilos Bento UI para el Importador Inteligente IA ── */
.xp-ia-import-card {
  background: rgba(18, 18, 23, 0.94);
  border: 1px solid rgba(138, 43, 226, 0.28);
  border-radius: 20px;
  padding: 24px;
  position: relative;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
}
.xp-ia-import-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 2px;
  background: linear-gradient(90deg, #8a2be2, #4f46e5, #06b6d4);
}
.xp-ia-card-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 18px;
}
.xp-ia-icon-box {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  background: linear-gradient(135deg, #8a2be2, #4f46e5);
  display: grid;
  place-items: center;
  color: #fff;
  font-size: 1.45rem;
  box-shadow: 0 6px 18px rgba(138, 43, 226, 0.35);
  flex-shrink: 0;
}
.xp-ia-title-box { flex: 1; }
.xp-ia-badge-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 2px 10px;
  border-radius: 999px;
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  background: rgba(138, 43, 226, 0.18);
  color: #c4a3ff;
  border: 1px solid rgba(138, 43, 226, 0.3);
}
.xp-ia-label {
  display: block;
  font-size: 0.8rem;
  font-weight: 700;
  color: #e2e8f0;
  margin-bottom: 8px;
}
.xp-ia-select {
  width: 100%;
  background: rgba(10, 10, 14, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 12px;
  padding: 12px 14px;
  color: #fff;
  font-size: 0.88rem;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.xp-ia-select:focus {
  border-color: var(--accent-primary, #8a2be2);
  box-shadow: 0 0 0 3px rgba(138, 43, 226, 0.25);
}
.xp-dropzone {
  background: rgba(10, 10, 14, 0.65);
  border: 2px dashed rgba(138, 43, 226, 0.4);
  border-radius: 14px;
  padding: 16px 20px;
  cursor: pointer;
  transition: all 0.25s ease;
  min-height: 80px;
  display: flex;
  align-items: center;
}
.xp-dropzone:hover, .xp-dropzone.dragover {
  border-color: #a855f7;
  background: rgba(138, 43, 226, 0.08);
  transform: translateY(-1px);
}
.xp-dropzone-inner {
  display: flex;
  align-items: center;
  gap: 14px;
  width: 100%;
}
.xp-dropzone-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.05);
  display: grid;
  place-items: center;
  font-size: 1.4rem;
  color: #c4a3ff;
  flex-shrink: 0;
}
.xp-dropzone-text { display: flex; flex-direction: column; }
.xp-dropzone-text strong { font-size: 0.85rem; color: #fff; font-weight: 700; }
.xp-dropzone-text span { font-size: 0.74rem; color: var(--muted); margin-top: 2px; }
.xp-ia-submit-btn {
  background: linear-gradient(135deg, var(--accent-primary, #8a2be2), #4f46e5);
  border: none;
  border-radius: 12px;
  padding: 11px 22px;
  color: #fff;
  font-size: 0.88rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  box-shadow: 0 4px 16px rgba(138, 43, 226, 0.35);
  transition: transform 0.18s, box-shadow 0.18s, opacity 0.18s;
}
.xp-ia-submit-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 22px rgba(138, 43, 226, 0.5); }
.xp-ia-submit-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

/* ── Modal de Staging / Mesa de Verificación ── */
body.xp-modal-active, html.xp-modal-active { overflow: hidden !important; height: 100% !important; }
.xp-staging-backdrop {
  position: fixed;
  inset: 0;
  width: 100vw;
  height: 100vh;
  height: 100dvh;
  background: rgba(5, 5, 8, 0.88);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  z-index: 100000 !important;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 12px;
}
.xp-staging-dialog {
  background: #111117;
  border: 1px solid rgba(138, 43, 226, 0.35);
  border-radius: 20px;
  width: 100%;
  max-width: 880px;
  height: calc(100dvh - 24px);
  max-height: calc(100dvh - 24px);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.85);
  animation: xpModalIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  touch-action: pan-y !important;
}
@keyframes xpModalIn {
  from { opacity: 0; transform: scale(0.96) translateY(12px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}
.xp-staging-header {
  padding: 14px 20px;
  background: #16161f;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-shrink: 0;
}
.xp-staging-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 0.68rem;
  font-weight: 700;
  color: #34d399;
  background: rgba(52, 211, 153, 0.12);
  padding: 2px 8px;
  border-radius: 6px;
  text-transform: uppercase;
}
.xp-staging-close {
  background: rgba(255, 255, 255, 0.05);
  border: none;
  color: var(--muted);
  width: 34px;
  height: 34px;
  border-radius: 10px;
  display: grid;
  place-items: center;
  font-size: 1.25rem;
  cursor: pointer;
  transition: all 0.18s ease;
}
.xp-staging-close:hover { color: #fff; background: rgba(239, 68, 68, 0.25); transform: rotate(90deg); }
.xp-staging-summary-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 8px;
  padding: 10px 18px;
  background: rgba(0, 0, 0, 0.3);
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  flex-shrink: 0;
}
@media (max-width: 680px) {
  .xp-staging-summary-grid {
    grid-template-columns: repeat(2, 1fr);
    padding: 8px 12px;
    gap: 6px;
  }
}
.xp-sum-item {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.03);
  padding: 7px 10px;
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.05);
}
.xp-sum-item i { font-size: 1.2rem; flex-shrink: 0; }
.xp-sum-val {
  display: block;
  font-size: 0.92rem;
  font-weight: 800;
  color: #fff;
  line-height: 1.1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.xp-sum-lbl { font-size: 0.68rem; color: var(--muted); font-weight: 500; }
.xp-staging-accordion-trigger {
  padding: 10px 18px;
  background: rgba(138, 43, 226, 0.1);
  border: none;
  border-bottom: 1px solid rgba(138, 43, 226, 0.2);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
  width: 100%;
  font-size: 0.84rem;
  font-weight: 700;
  transition: background 0.2s;
  flex-shrink: 0;
}
.xp-staging-accordion-trigger:hover { background: rgba(138, 43, 226, 0.18); }
.xp-acc-trigger-left { display: flex; align-items: center; gap: 8px; }
.xp-acc-icon { transition: transform 0.25s ease; font-size: 1.1rem; }
.xp-acc-icon.is-collapsed { transform: rotate(-90deg); }
.xp-staging-body {
  padding: 16px 18px;
  overflow-y: auto !important;
  overflow-x: hidden !important;
  -webkit-overflow-scrolling: touch !important;
  touch-action: pan-y !important;
  overscroll-behavior: contain !important;
  flex: 1 1 auto;
  min-height: 0;
  display: block !important;
  scrollbar-width: thin;
  scrollbar-color: rgba(138, 43, 226, 0.45) rgba(255, 255, 255, 0.02);
}
.xp-staging-body::-webkit-scrollbar { width: 7px; }
.xp-staging-body::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.02); border-radius: 4px; }
.xp-staging-body::-webkit-scrollbar-thumb { background: rgba(138, 43, 226, 0.4); border-radius: 4px; }
.xp-staging-body::-webkit-scrollbar-thumb:hover { background: rgba(138, 43, 226, 0.7); }
.xp-session-card {
  display: block !important;
  width: 100% !important;
  box-sizing: border-box !important;
  background: rgba(22, 22, 28, 0.9);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 14px;
  margin-bottom: 12px;
  overflow: hidden;
  flex-shrink: 0 !important;
  transition: all 0.2s ease;
}
.xp-session-card:hover { border-color: rgba(138, 43, 226, 0.45); }
.xp-session-card.is-open { border-color: rgba(138, 43, 226, 0.65); box-shadow: 0 4px 20px rgba(138, 43, 226, 0.15); }
.xp-session-head {
  padding: 14px 18px !important;
  min-height: 52px !important;
  box-sizing: border-box !important;
  background: rgba(255, 255, 255, 0.035);
  display: flex !important;
  align-items: center !important;
  justify-content: space-between !important;
  border-bottom: 1px solid transparent;
  cursor: pointer;
  user-select: none;
  transition: background 0.18s;
}
.xp-session-head:hover { background: rgba(255, 255, 255, 0.06); }
.xp-session-card.is-open .xp-session-head {
  background: rgba(138, 43, 226, 0.12);
  border-bottom-color: rgba(255, 255, 255, 0.08);
}
.xp-session-date { font-weight: 800; font-size: 0.88rem; color: #c4a3ff; display: inline-flex; align-items: center; gap: 8px; }
.xp-session-title { font-size: 0.84rem; font-weight: 600; color: #e2e8f0; display: inline-flex; align-items: center; gap: 6px; }
.xp-session-exercises {
  padding: 16px 18px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  background: rgba(12, 12, 18, 0.75);
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}
.xp-mini-btn {
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 8px;
  color: #e2e8f0;
  padding: 5px 12px;
  font-size: 0.74rem;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.18s ease;
  user-select: none;
}
.xp-mini-btn:hover { background: rgba(138, 43, 226, 0.25); border-color: rgba(138, 43, 226, 0.55); color: #fff; transform: translateY(-1px); }
.xp-ej-item { background: rgba(255, 255, 255, 0.035); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 12px; padding: 14px 18px; }
.xp-ej-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; flex-wrap: wrap; gap: 6px; }
.xp-ej-name { font-size: 0.88rem; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px; }
.xp-ej-badge-match { font-size: 0.7rem; padding: 3px 9px; border-radius: 7px; font-weight: 700; background: rgba(52, 211, 153, 0.15); color: #34d399; }
.xp-ej-badge-custom { font-size: 0.7rem; padding: 3px 9px; border-radius: 7px; font-weight: 700; background: rgba(251, 191, 36, 0.15); color: #fbbf24; }
.xp-series-pills { display: flex; flex-wrap: wrap; gap: 8px; }
.xp-serie-pill {
  background: rgba(138, 43, 226, 0.12);
  border: 1px solid rgba(138, 43, 226, 0.28);
  border-radius: 8px;
  padding: 6px 12px;
  font-size: 0.78rem;
  color: #f1f5f9;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}
.xp-staging-footer {
  padding: 12px 18px;
  background: #16161f;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  flex-shrink: 0;
  z-index: 10;
}
.xp-staging-btn-cancel {
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #e2e8f0;
  padding: 10px 16px;
  border-radius: 12px;
  font-size: 0.84rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  transition: all 0.18s;
  flex: 0 1 auto;
}
.xp-staging-btn-cancel:hover { background: rgba(239, 68, 68, 0.15); border-color: rgba(239, 68, 68, 0.4); color: #fca5a5; }
.xp-staging-btn-confirm {
  background: linear-gradient(135deg, var(--accent-primary, #8a2be2), #4f46e5);
  border: none;
  color: #fff;
  padding: 11px 20px;
  border-radius: 12px;
  font-size: 0.86rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(138, 43, 226, 0.35);
  transition: all 0.18s;
  flex: 1 1 auto;
  justify-content: center;
}
.xp-staging-btn-confirm:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(138, 43, 226, 0.5); }
@media (max-width: 480px) {
  .xp-staging-footer { padding: 10px 12px; }
  .xp-staging-btn-cancel span, .xp-staging-btn-confirm span { font-size: 0.8rem; }
}

/* ── Bento UI: Tarjeta de Copia de Seguridad de Base de Datos ── */
.xp-backup-card {
  background: rgba(18, 18, 23, 0.94);
  border: 1px solid rgba(16, 185, 129, 0.28);
  border-radius: 20px;
  padding: 24px;
  position: relative;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
}
.xp-backup-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 2px;
  background: linear-gradient(90deg, #10b981, #06b6d4, #8a2be2);
}
.xp-backup-icon-box {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  background: linear-gradient(135deg, #10b981, #059669);
  display: grid;
  place-items: center;
  color: #fff;
  font-size: 1.45rem;
  box-shadow: 0 6px 18px rgba(16, 185, 129, 0.35);
  flex-shrink: 0;
}
.xp-backup-metrics-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
  margin-top: 14px;
}
.xp-backup-metric {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  gap: 7px;
  min-height: 112px;
  aspect-ratio: 1 / 0.88;
  background: rgba(16, 185, 129, 0.06);
  border: 1px solid rgba(16, 185, 129, 0.22);
  border-radius: 16px;
  padding: 12px 10px;
  transition: all 0.2s ease;
}
.xp-backup-metric:hover { background: rgba(16, 185, 129, 0.1); border-color: rgba(16, 185, 129, 0.35); }
.xp-backup-metric-icon {
  width: 36px;
  height: 36px;
  border-radius: 11px;
  background: rgba(16, 185, 129, 0.14);
  color: #34d399;
  display: grid;
  place-items: center;
  font-size: 1.15rem;
  flex-shrink: 0;
}
.xp-backup-metric-data { display: flex; flex-direction: column; align-items: center; gap: 2px; min-width: 0; width: 100%; }
.xp-backup-metric-label { font-size: 0.63rem; text-transform: uppercase; letter-spacing: 0.45px; color: #94a3b8; font-weight: 700; line-height: 1.2; }
.xp-backup-metric-val { font-size: 0.79rem; color: #f1f5f9; font-weight: 800; line-height: 1.25; max-width: 100%; word-break: break-word; }
.xp-backup-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, #10b981, #059669);
  color: #fff;
  border: none;
  border-radius: 12px;
  padding: 11px 22px;
  font-size: 0.88rem;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
  transition: all 0.2s ease;
}
.xp-backup-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45); }
.xp-backup-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
</style>

<!-- ── Accesos Rápidos y Herramientas Bento ── -->
<div class="cfg-quick-grid<?= $xpHerramientasEsAdmin ? ' cfg-quick-grid-admin' : '' ?>">
  <?php if (!$xpHerramientasEsAdmin): ?>
    <a href="<?= APP_URL ?>/entrenador/pagos-recibidos" class="cfg-quick-card">
      <div class="cfg-quick-icon" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
        <i class="ti ti-credit-card"></i>
      </div>
      <span>Pagos Pendientes</span>
    </a>
    <a href="<?= APP_URL ?>/entrenador/metodos-pago" class="cfg-quick-card">
      <div class="cfg-quick-icon" style="background: linear-gradient(135deg, #0ea5e9, #06b6d4);">
        <i class="ti ti-building-bank"></i>
      </div>
      <span>Metodos de Pago</span>
    </a>
  <?php endif; ?>
  <button type="button" class="cfg-quick-card" onclick="xpAbrirConfigModal('modalImportadorIA')" aria-haspopup="dialog">
    <div class="cfg-quick-icon" style="background: linear-gradient(135deg, #8a2be2, #c084fc);">
      <i class="ti ti-sparkles"></i>
    </div>
    <span>Importador IA</span>
  </button>
  <button type="button" class="cfg-quick-card" onclick="xpAbrirConfigModal('modalCopiaSeguridad')" aria-haspopup="dialog">
    <div class="cfg-quick-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
      <i class="ti ti-database-export"></i>
    </div>
    <span>Copia de Seguridad</span>
  </button>
</div>

<!-- ── Modal: Importador Inteligente de Cronología Deportiva (Gemini AI) ── -->
<div id="modalImportadorIA" class="xp-cfg-modal" aria-hidden="true" role="dialog" aria-modal="true" onclick="if(event.target===this)xpCerrarConfigModal('modalImportadorIA')">
  <div class="xp-cfg-modal-dialog">
    <button type="button" class="xp-cfg-modal-close" onclick="xpCerrarConfigModal('modalImportadorIA')" aria-label="Cerrar modal">
      <i class="ti ti-x"></i>
    </button>
    <div class="card-bento xp-ia-import-card" style="margin:0;">
      <div class="xp-ia-card-header">
        <div class="xp-ia-icon-box">
          <i class="ti ti-sparkles"></i>
        </div>
        <div class="xp-ia-title-box">
          <h3 style="margin: 0; font-size: 1.12rem; font-weight: 800; color: #fff; padding-right: 28px;">
            Importador Inteligente de Historial Deportivo
          </h3>
        </div>
      </div>

      <form id="formImportadorHistorial" class="xp-ia-form" onsubmit="event.preventDefault(); iniciarAnalisisCronologiaIA();">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <div class="row g-3" style="margin-top: 10px;">
          <div class="col-12">
            <label for="xpImportAtletaSelect" class="xp-ia-label">
              <i class="ti ti-user"></i> 1. Selecciona el Atleta de Destino
            </label>
            <select id="xpImportAtletaSelect" name="cliente_id" class="xp-ia-select" required>
              <option value="">-- Elige un atleta registrado --</option>
              <?php foreach ($atletasList as $atl): ?>
                <option value="<?= (int)$atl['cliente_id'] ?>" data-nombre="<?= e($atl['nombre']) ?>">
                  <?= e($atl['nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-12">
            <label class="xp-ia-label">
              <i class="ti ti-file-upload"></i> 2. Documento con el Registro (.xlsx, .docx, .pdf, .csv)
            </label>
            <div class="xp-dropzone" id="xpImportDropzone" onclick="document.getElementById('xpImportFileInput').click();">
              <input type="file" id="xpImportFileInput" name="archivo" accept=".xlsx,.xls,.docx,.pdf,.csv,.txt" style="display:none;" onchange="handleImportFileSelect(this);">
              <div class="xp-dropzone-inner" id="xpDropzoneContent">
                <div class="xp-dropzone-icon">
                  <i class="ti ti-cloud-upload"></i>
                </div>
                <div class="xp-dropzone-text">
                  <strong id="xpDropzoneTitle">Arrastra tu archivo aquí o haz clic para seleccionarlo</strong>
                  <span id="xpDropzoneSubtitle">Soporta tablas en Excel (.xlsx), rutinas en Word (.docx) o documentos en PDF</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="xp-ia-actions" style="margin-top: 18px; display: flex; align-items: center; justify-content: flex-end; flex-wrap: wrap; gap: 12px;">
          <button type="submit" id="btnIniciarAnalisisIA" class="xp-ia-submit-btn">
            <i class="ti ti-sparkles"></i> <span>Analizar con Gemini AI</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ── Modal: Copia de Seguridad de la Base de Datos ── -->
<div id="modalCopiaSeguridad" class="xp-cfg-modal" aria-hidden="true" role="dialog" aria-modal="true" onclick="if(event.target===this)xpCerrarConfigModal('modalCopiaSeguridad')">
  <div class="xp-cfg-modal-dialog">
    <button type="button" class="xp-cfg-modal-close" onclick="xpCerrarConfigModal('modalCopiaSeguridad')" aria-label="Cerrar modal">
      <i class="ti ti-x"></i>
    </button>
    <div class="card-bento xp-backup-card" style="margin:0;">
      <div class="xp-ia-card-header" style="margin-bottom: 14px;">
        <div class="xp-backup-icon-box">
          <i class="ti ti-database-export"></i>
        </div>
        <div class="xp-ia-title-box" style="padding-right: 26px;">
          <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <h3 style="margin: 0; font-size: 1.08rem; font-weight: 800; color: #fff;">
              Copia de Seguridad de la Base de Datos
            </h3>
            <span class="xp-ia-badge-pill" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border-color: rgba(16, 185, 129, 0.3);">
              <i class="ti ti-shield-check"></i> SQL Comprimido (.sql.gz)
            </span>
          </div>
        </div>
      </div>

      <div class="xp-backup-metrics-grid">
        <div class="xp-backup-metric">
          <div class="xp-backup-metric-icon"><i class="ti ti-database"></i></div>
          <div class="xp-backup-metric-data">
            <span class="xp-backup-metric-label">Base de Datos</span>
            <strong class="xp-backup-metric-val"><?= e($dbName) ?></strong>
          </div>
        </div>
        <div class="xp-backup-metric">
          <div class="xp-backup-metric-icon"><i class="ti ti-table"></i></div>
          <div class="xp-backup-metric-data">
            <span class="xp-backup-metric-label">Tablas Registradas</span>
            <strong class="xp-backup-metric-val"><?= $totalTablas ?> tablas</strong>
          </div>
        </div>
        <div class="xp-backup-metric">
          <div class="xp-backup-metric-icon"><i class="ti ti-barbell"></i></div>
          <div class="xp-backup-metric-data">
            <span class="xp-backup-metric-label">Series y Registros</span>
            <strong class="xp-backup-metric-val"><?= number_format($totalSeries) ?> series</strong>
          </div>
        </div>
        <div class="xp-backup-metric">
          <div class="xp-backup-metric-icon"><i class="ti ti-file-zip"></i></div>
          <div class="xp-backup-metric-data">
            <span class="xp-backup-metric-label">Compresión</span>
            <strong class="xp-backup-metric-val">GZIP Stream (.sql.gz)</strong>
          </div>
        </div>
      </div>

      <div class="xp-backup-actions" style="margin-top: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 16px;">
        <div style="font-size: 0.78rem; color: #94a3b8; display: flex; align-items: center; gap: 6px;">
          <i class="ti ti-info-circle" style="color: #60a5fa; font-size: 1rem;"></i>
          <span>El archivo incluye sentencias completas listas para restaurar vía phpMyAdmin o consola MySQL.</span>
        </div>
        <button type="button" id="btnDescargarBackupDb" class="xp-backup-btn" onclick="descargarCopiaSeguridadDb()">
          <i class="ti ti-download"></i> <span>Descargar Copia de Seguridad</span>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ── Modal Staging / Mesa de Verificación de Cronología (Gemini AI) ── -->
<div id="xpStagingModal" class="xp-staging-backdrop" style="display: none;">
  <div class="xp-staging-dialog" role="dialog" aria-modal="true" aria-labelledby="stagingAtletaNombre">
    <div class="xp-staging-header">
      <div class="xp-staging-head-left">
        <div class="xp-staging-badge">
          <i class="ti ti-eye-check"></i> Mesa de Verificación
        </div>
        <h3 id="stagingAtletaNombre" style="margin: 4px 0 0; font-size: 1.15rem; font-weight: 800; color: #fff;">
          Cronología Detectada
        </h3>
        <p id="stagingArchivoInfo" style="margin: 2px 0 0; font-size: 0.74rem; color: var(--muted);"></p>
      </div>
      <button type="button" class="xp-staging-close" onclick="cancelarYVolverACargar();" title="Cancelar y cambiar archivo" aria-label="Cerrar modal">
        <i class="ti ti-x"></i>
      </button>
    </div>

    <div class="xp-staging-summary-grid">
      <div class="xp-sum-item">
        <i class="ti ti-calendar-event" style="color: #60a5fa;"></i>
        <div>
          <span class="xp-sum-val" id="sumSesionesCount">0</span>
          <span class="xp-sum-lbl">Sesiones</span>
        </div>
      </div>
      <div class="xp-sum-item">
        <i class="ti ti-barbell" style="color: #c084fc;"></i>
        <div>
          <span class="xp-sum-val" id="sumSeriesCount">0</span>
          <span class="xp-sum-lbl">Series</span>
        </div>
      </div>
      <div class="xp-sum-item">
        <i class="ti ti-calendar-stats" style="color: #34d399;"></i>
        <div>
          <span class="xp-sum-val" id="sumRangoFechas">--</span>
          <span class="xp-sum-lbl">Rango</span>
        </div>
      </div>
      <div class="xp-sum-item">
        <i class="ti ti-scale" style="color: #fbbf24;"></i>
        <div>
          <span class="xp-sum-val" id="sumPesosCount">0</span>
          <span class="xp-sum-lbl">Pesos</span>
        </div>
      </div>
    </div>

    <button type="button" class="xp-staging-accordion-trigger" id="btnToggleDesplegable" onclick="toggleDesplegableCronologia();">
      <div class="xp-acc-trigger-left">
        <i class="ti ti-chevron-down xp-acc-icon" id="accChevronIcon"></i>
        <span>Detalle de Sesiones y Ejercicios (<span id="txtSesionesBadge">0</span>)</span>
      </div>
      <span id="txtToggleHint" style="font-size:0.72rem;color:var(--muted);font-weight:500;">Toca para ocultar</span>
    </button>

    <div id="stagingSubBarControls" style="display:flex; justify-content:space-between; align-items:center; padding: 8px 18px; background: rgba(0,0,0,0.35); border-bottom: 1px solid rgba(255,255,255,0.06); font-size: 0.75rem; flex-shrink: 0;">
      <span style="color:var(--muted); display:inline-flex; align-items:center; gap:6px;">
        <i class="ti ti-calendar" style="color:#c4a3ff;"></i> <span>Sesiones cronológicas</span>
      </span>
      <div style="display:flex; gap:8px; align-items:center;">
        <button type="button" class="xp-mini-btn" onclick="expandirTodasLasSesiones(true)" title="Ver todas las series de todas las sesiones">
          <i class="ti ti-arrows-maximize"></i> Expandir
        </button>
        <button type="button" class="xp-mini-btn" onclick="expandirTodasLasSesiones(false)" title="Contraer vista de series">
          <i class="ti ti-arrows-minimize"></i> Contraer
        </button>
      </div>
    </div>

    <div class="xp-staging-body" id="stagingSessionsContainer"></div>

    <div class="xp-staging-footer">
      <button type="button" class="xp-staging-btn-cancel" onclick="cancelarYVolverACargar();" title="Descartar y volver a cargar otro archivo">
        <i class="ti ti-x"></i> <span>Cancelar</span>
      </button>
      <button type="button" id="btnConfirmarPersistencia" class="xp-staging-btn-confirm" onclick="confirmarGuardadoCronologia();">
        <i class="ti ti-device-floppy"></i> <span>Inyectar al Historial y Calendario</span>
      </button>
    </div>
  </div>
</div>

<script>
window.XP_TOOLS_ES_ADMIN = <?= $xpHerramientasEsAdmin ? 'true' : 'false' ?>;
window.XP_TOOLS_API_BASE = <?= json_encode($xpHerramientasApiBase, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

document.addEventListener('DOMContentLoaded', () => {
  ['modalImportadorIA', 'modalCopiaSeguridad', 'xpStagingModal'].forEach(id => {
    const el = document.getElementById(id);
    if (el && el.parentNode !== document.body) {
      document.body.appendChild(el);
    }
  });
});

function xpAbrirConfigModal(id) {
  const modal = document.getElementById(id);
  if (!modal) return;
  modal.classList.add('is-open');
  modal.setAttribute('aria-hidden', 'false');
  document.body.style.overflow = 'hidden';
}

function xpCerrarConfigModal(id) {
  const modal = document.getElementById(id);
  if (!modal) return;
  modal.classList.remove('is-open');
  modal.setAttribute('aria-hidden', 'true');
  document.body.style.overflow = '';
}

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    xpCerrarConfigModal('modalImportadorIA');
    xpCerrarConfigModal('modalCopiaSeguridad');
  }
});

let currentImportStaging = null;
let currentCatalogoEjercicios = [];

function handleImportFileSelect(input) {
  if (input.files && input.files[0]) {
    const file = input.files[0];
    const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
    document.getElementById('xpDropzoneTitle').textContent = file.name;
    document.getElementById('xpDropzoneSubtitle').innerHTML = `<span style="color:#34d399;"><i class="ti ti-check"></i> Archivo listo (${sizeMb} MB)</span>`;
    document.getElementById('xpImportDropzone').style.borderColor = '#34d399';
  }
}

const dropzoneIA = document.getElementById('xpImportDropzone');
if (dropzoneIA) {
  ['dragenter', 'dragover'].forEach(evt => {
    dropzoneIA.addEventListener(evt, e => {
      e.preventDefault();
      dropzoneIA.classList.add('dragover');
    });
  });
  ['dragleave', 'drop'].forEach(evt => {
    dropzoneIA.addEventListener(evt, e => {
      e.preventDefault();
      dropzoneIA.classList.remove('dragover');
    });
  });
  dropzoneIA.addEventListener('drop', e => {
    if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
      const fileInput = document.getElementById('xpImportFileInput');
      fileInput.files = e.dataTransfer.files;
      handleImportFileSelect(fileInput);
    }
  });
}

async function iniciarAnalisisCronologiaIA() {
  const atletaSelect = document.getElementById('xpImportAtletaSelect');
  const fileInput = document.getElementById('xpImportFileInput');

  if (!atletaSelect || !atletaSelect.value) {
    Swal.fire({
      icon: 'warning',
      title: 'Selecciona un Atleta',
      text: 'Debes elegir a qué atleta pertenecen los entrenamientos a importar.',
      confirmButtonColor: '#8a2be2'
    });
    return;
  }

  if (!fileInput || !fileInput.files || !fileInput.files[0]) {
    Swal.fire({
      icon: 'warning',
      title: 'Selecciona un Archivo',
      text: 'Adjunta un archivo de Excel, Word o PDF para que la IA lo analice.',
      confirmButtonColor: '#8a2be2'
    });
    return;
  }

  const formData = new FormData(document.getElementById('formImportadorHistorial'));
  const btn = document.getElementById('btnIniciarAnalisisIA');
  btn.disabled = true;
  btn.innerHTML = `<i class="ti ti-loader xp-spin"></i> <span>Analizando con IA...</span>`;

  Swal.fire({
    title: 'Procesando con Gemini AI',
    html: `
      <div style="font-size:0.88rem;color:#cbd5e1;line-height:1.5;">
        <p>Extrayendo texto y tablas del documento...</p>
        <p style="color:#c4a3ff;margin-top:6px;">Reconstruyendo semanas, ejercicios, repeticiones y cargas cronológicas.</p>
      </div>
    `,
    allowOutsideClick: false,
    allowEscapeKey: false,
    didOpen: () => { Swal.showLoading(); }
  });

  try {
    const res = await fetch(`${window.APP || ''}/${window.XP_TOOLS_API_BASE}/api-importar-historial-analizar`, {
      method: 'POST',
      body: formData
    });
    const rawText = await res.text();
    let data;
    try {
      data = JSON.parse(rawText);
    } catch (parseErr) {
      console.error('Respuesta cruda del servidor:', rawText);
      const cleanSnippet = rawText.replace(/<[^>]*>?/gm, ' ').replace(/\s+/g, ' ').trim();
      throw new Error(cleanSnippet.slice(0, 220) || 'El servidor tardó demasiado en responder o devolvió un formato no válido.');
    }

    if (!data.ok) {
      throw new Error(data.msg || data.error || 'Error desconocido al analizar.');
    }

    Swal.close();
    currentImportStaging = data.cronologia;
    currentImportStaging.cliente_id = atletaSelect.value;
    currentCatalogoEjercicios = data.catalogo || [];
    renderStagingModal(data);

  } catch (err) {
    console.error('Error análisis IA:', err);
    Swal.fire({
      icon: 'error',
      title: 'No se pudo analizar el archivo',
      text: err.message || 'Error de conexión con el servidor o la IA.',
      confirmButtonColor: '#8a2be2'
    });
  } finally {
    btn.disabled = false;
    btn.innerHTML = `<i class="ti ti-sparkles"></i> <span>Analizar con Gemini AI</span>`;
  }
}

function renderStagingModal(data) {
  const cron = data.cronologia || {};
  const resumen = cron.resumen || {};
  const sesiones = cron.sesiones || [];
  const medidas = cron.medidas_corporales || [];

  document.getElementById('stagingAtletaNombre').textContent = `Cronología de ${data.atleta.nombre}`;
  document.getElementById('stagingArchivoInfo').textContent = `Archivo: ${data.archivo.nombre} (${(data.archivo.tamano / 1024).toFixed(1)} KB)`;

  const totalSes = resumen.total_sesiones || sesiones.length;
  document.getElementById('sumSesionesCount').textContent = totalSes;
  document.getElementById('sumSeriesCount').textContent = resumen.total_series || contarSeriesTotales(sesiones);
  document.getElementById('sumRangoFechas').textContent = resumen.rango_fechas || (sesiones.length ? `${sesiones[0].fecha} al ${sesiones[sesiones.length-1].fecha}` : '--');
  document.getElementById('sumPesosCount').textContent = medidas.length;
  document.getElementById('txtSesionesBadge').textContent = `${totalSes} sesiones`;

  const container = document.getElementById('stagingSessionsContainer');
  container.innerHTML = '';

  if (sesiones.length === 0) {
    container.innerHTML = `
      <div style="text-align:center;padding:30px 16px;color:var(--muted);">
        <i class="ti ti-alert-circle" style="font-size:2.5rem;opacity:0.4;display:block;margin-bottom:8px;"></i>
        <p>No se identificaron sesiones de entrenamiento legibles en el documento.</p>
      </div>
    `;
  } else {
    sesiones.forEach((s, sIdx) => {
      const card = document.createElement('div');
      card.className = 'xp-session-card';

      let ejHtml = '';
      (s.ejercicios || []).forEach(ej => {
        const isMatched = !!ej.catalogo_id;
        const badge = isMatched
          ? `<span class="xp-ej-badge-match"><i class="ti ti-check"></i> ${escapeHtml(ej.nombre_oficial || ej.nombre_detectado)}</span>`
          : `<span class="xp-ej-badge-custom"><i class="ti ti-sparkles"></i> ${escapeHtml(ej.nombre_detectado)}</span>`;

        let seriesPills = '';
        (ej.series || []).forEach(sr => {
          let uLabel = 'kg';
          const srUni = (sr.unidad || ej.unidad_carga || 'kg').toLowerCase();
          if (srUni === 'lbs' || srUni === 'lb') uLabel = 'lbs';
          else if (srUni === 'placas') uLabel = (sr.peso == 1 ? 'placa' : 'placas');
          else if (srUni === 'ninguna') uLabel = '';
          const pesoTxt = uLabel ? `${sr.peso} ${uLabel}` : (sr.peso > 0 ? `${sr.peso}` : 'Sin peso');
          seriesPills += `<span class="xp-serie-pill">S${sr.numero_serie}: ${sr.reps}r @ ${pesoTxt}</span>`;
        });

        ejHtml += `
          <div class="xp-ej-item">
            <div class="xp-ej-head">
              <span class="xp-ej-name"><i class="ti ti-barbell" style="color:#c4a3ff;"></i> ${escapeHtml(ej.nombre_detectado)}</span>
              <div style="display:flex;align-items:center;gap:6px;">
                <span class="xp-ej-unit-tag" style="font-size:0.65rem;background:rgba(56,189,248,0.15);color:#38bdf8;padding:2px 6px;border-radius:4px;font-weight:700;text-transform:uppercase;">${escapeHtml(ej.unidad_carga || 'kg')}</span>
                ${badge}
              </div>
            </div>
            <div class="xp-series-pills">
              ${seriesPills || '<span style="font-size:0.72rem;color:var(--muted);">Sin series registradas</span>'}
            </div>
          </div>
        `;
      });

      card.innerHTML = `
        <div class="xp-session-head" onclick="toggleDetalleSesionIndividual(${sIdx})">
          <span class="xp-session-date">
            <i class="ti ti-calendar"></i> ${escapeHtml(s.fecha)}
            <span style="font-size:0.75rem;opacity:0.75;font-weight:500;">(${escapeHtml(s.dia_nombre || 'Día')})</span>
          </span>
          <div style="display:flex;align-items:center;gap:10px;">
            <span class="xp-session-title">${escapeHtml(s.rutina_nombre || 'Entrenamiento')}</span>
            <span style="font-size:0.7rem;background:rgba(255,255,255,0.07);padding:2px 8px;border-radius:6px;color:#94a3b8;font-weight:600;">${(s.ejercicios || []).length} ej.</span>
            <i class="ti ti-chevron-down" id="sesChevron_${sIdx}" style="font-size:0.9rem;color:#c4a3ff;transition:transform .2s;transform:rotate(-90deg);"></i>
          </div>
        </div>
        <div class="xp-session-exercises" id="sesBody_${sIdx}" style="display:none;">
          ${ejHtml}
        </div>
      `;
      container.appendChild(card);
    });
  }

  if (medidas.length > 0) {
    const medCard = document.createElement('div');
    medCard.className = 'xp-session-card';
    medCard.style.borderColor = 'rgba(251, 191, 36, 0.4)';
    let medListHtml = '';
    medidas.forEach(m => {
      medListHtml += `<span class="xp-serie-pill" style="border-color:rgba(251,191,36,0.3);"><i class="ti ti-scale"></i> ${escapeHtml(m.fecha)}: <strong>${escapeHtml(m.peso_kg)} kg</strong></span>`;
    });
    medCard.innerHTML = `
      <div class="xp-session-head" style="background:rgba(251,191,36,0.08);" onclick="togglePesosCorporales()">
        <span class="xp-session-date" style="color:#fbbf24;"><i class="ti ti-scale"></i> Pesos Corporales Detectados</span>
        <div style="display:flex;align-items:center;gap:10px;">
          <span class="xp-session-title" style="color:#fde68a;">${medidas.length} registros</span>
          <i class="ti ti-chevron-down" id="pesosChevron" style="font-size:0.9rem;color:#fbbf24;transition:transform .2s;transform:rotate(-90deg);"></i>
        </div>
      </div>
      <div class="xp-session-exercises" id="pesosBody" style="display:none;">
        <div class="xp-series-pills">${medListHtml}</div>
      </div>
    `;
    container.insertBefore(medCard, container.firstChild);
  }

  const modal = document.getElementById('xpStagingModal');
  if (modal && modal.parentElement !== document.body) {
    document.body.appendChild(modal);
  }

  const hint = document.getElementById('txtToggleHint');
  if (hint) hint.textContent = 'Toca para ocultar';
  const accIcon = document.getElementById('accChevronIcon');
  if (accIcon) accIcon.classList.remove('is-collapsed');
  container.style.display = 'block';

  document.body.classList.add('xp-modal-active');
  document.documentElement.classList.add('xp-modal-active');
  modal.style.display = 'flex';
}

function toggleDesplegableCronologia() {
  const container = document.getElementById('stagingSessionsContainer');
  const subBar = document.getElementById('stagingSubBarControls');
  const icon = document.getElementById('accChevronIcon');
  const hint = document.getElementById('txtToggleHint');
  if (container.style.display === 'none') {
    container.style.display = 'block';
    if (subBar) subBar.style.display = 'flex';
    if (icon) icon.classList.remove('is-collapsed');
    if (hint) hint.textContent = 'Toca para ocultar';
  } else {
    container.style.display = 'none';
    if (subBar) subBar.style.display = 'none';
    if (icon) icon.classList.add('is-collapsed');
    if (hint) hint.textContent = 'Toca para desplegar';
  }
}

function toggleDetalleSesionIndividual(idx) {
  const body = document.getElementById(`sesBody_${idx}`);
  const chevron = document.getElementById(`sesChevron_${idx}`);
  const card = body ? body.closest('.xp-session-card') : null;
  if (!body) return;
  if (body.style.display === 'none') {
    body.style.display = 'flex';
    if (chevron) chevron.style.transform = 'rotate(0deg)';
    if (card) card.classList.add('is-open');
  } else {
    body.style.display = 'none';
    if (chevron) chevron.style.transform = 'rotate(-90deg)';
    if (card) card.classList.remove('is-open');
  }
}

function togglePesosCorporales() {
  const body = document.getElementById('pesosBody');
  const chevron = document.getElementById('pesosChevron');
  const card = body ? body.closest('.xp-session-card') : null;
  if (!body) return;
  if (body.style.display === 'none') {
    body.style.display = 'flex';
    if (chevron) chevron.style.transform = 'rotate(0deg)';
    if (card) card.classList.add('is-open');
  } else {
    body.style.display = 'none';
    if (chevron) chevron.style.transform = 'rotate(-90deg)';
    if (card) card.classList.remove('is-open');
  }
}

function expandirTodasLasSesiones(abrir) {
  document.querySelectorAll('[id^="sesBody_"]').forEach(el => {
    el.style.display = abrir ? 'flex' : 'none';
    const card = el.closest('.xp-session-card');
    if (card) card.classList.toggle('is-open', !!abrir);
  });
  document.querySelectorAll('[id^="sesChevron_"]').forEach(ch => {
    ch.style.transform = abrir ? 'rotate(0deg)' : 'rotate(-90deg)';
  });
  const pesos = document.getElementById('pesosBody');
  const pesosCh = document.getElementById('pesosChevron');
  if (pesos) {
    pesos.style.display = abrir ? 'flex' : 'none';
    if (pesosCh) pesosCh.style.transform = abrir ? 'rotate(0deg)' : 'rotate(-90deg)';
    const pesosCard = pesos.closest('.xp-session-card');
    if (pesosCard) pesosCard.classList.toggle('is-open', !!abrir);
  }
}

function contarSeriesTotales(sesiones) {
  let count = 0;
  (sesiones || []).forEach(s => {
    (s.ejercicios || []).forEach(ej => {
      count += (ej.series || []).length;
    });
  });
  return count;
}

function cerrarStagingModal() {
  document.getElementById('xpStagingModal').style.display = 'none';
  document.body.classList.remove('xp-modal-active');
  document.documentElement.classList.remove('xp-modal-active');
}

function cancelarYVolverACargar() {
  cerrarStagingModal();
  currentImportStaging = null;
  const form = document.getElementById('formImportadorHistorial');
  if (form) form.reset();
  const fileInput = document.getElementById('xpImportFileInput');
  if (fileInput) fileInput.value = '';
  const titleEl = document.getElementById('xpDropzoneTitle');
  const subEl = document.getElementById('xpDropzoneSubtitle');
  const dz = document.getElementById('xpImportDropzone');
  if (titleEl) titleEl.textContent = 'Arrastra tu archivo aquí o haz clic para seleccionarlo';
  if (subEl) subEl.textContent = 'Soporta tablas en Excel (.xlsx), rutinas en Word (.docx) o documentos en PDF';
  if (dz) dz.style.borderColor = 'rgba(138, 43, 226, 0.4)';

  if (typeof Swal !== 'undefined') {
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'info',
      title: 'Carga cancelada. Puedes seleccionar otro archivo.',
      showConfirmButton: false,
      timer: 2500,
      timerProgressBar: true
    });
  }
}

async function confirmarGuardadoCronologia() {
  if (!currentImportStaging) return;

  const btn = document.getElementById('btnConfirmarPersistencia');
  btn.disabled = true;
  btn.innerHTML = `<i class="ti ti-loader xp-spin"></i> <span>Guardando en BD...</span>`;

  const csrfToken = window.CSRF || <?= json_encode($csrf, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

  try {
    const payload = {
      cliente_id: currentImportStaging.cliente_id,
      sesiones: currentImportStaging.sesiones || [],
      medidas_corporales: currentImportStaging.medidas_corporales || [],
      csrf_token: csrfToken
    };

    const res = await fetch(`${window.APP || ''}/${window.XP_TOOLS_API_BASE}/api-importar-historial-guardar`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken
      },
      body: JSON.stringify(payload)
    });

    const rawText = await res.text();
    let data;
    try {
      data = JSON.parse(rawText);
    } catch (parseErr) {
      console.error('Respuesta no JSON del servidor al guardar:', rawText);
      const cleanSnippet = rawText.replace(/<[^>]*>?/gm, ' ').replace(/\s+/g, ' ').trim();
      throw new Error(cleanSnippet.slice(0, 240) || 'El servidor tardó demasiado o devolvió un formato no válido.');
    }
    if (!data.ok) {
      throw new Error(data.msg || data.error || 'Error al persistir cronología.');
    }

    const clienteIdGuardado = currentImportStaging.cliente_id;
    cerrarStagingModal();
    xpCerrarConfigModal('modalImportadorIA');

    Swal.fire({
      icon: 'success',
      title: '¡Cronología Consolidada!',
      html: `
        <div style="font-size:0.9rem;color:#cbd5e1;line-height:1.5;">
          <p>${escapeHtml(data.msg)}</p>
          <p style="margin-top:8px;color:#c4a3ff;">El calendario y las gráficas del atleta han sido actualizados con los nuevos datos históricos.</p>
        </div>
      `,
      showCancelButton: !window.XP_TOOLS_ES_ADMIN,
      confirmButtonText: window.XP_TOOLS_ES_ADMIN ? 'Aceptar' : '<i class="ti ti-user"></i> Ver Perfil del Atleta',
      cancelButtonText: 'Aceptar',
      confirmButtonColor: '#8a2be2'
    }).then(r => {
      if (r.isConfirmed && !window.XP_TOOLS_ES_ADMIN && clienteIdGuardado) {
        window.location.href = `${window.APP || ''}/entrenador/perfil-cliente?id=${encodeURIComponent(clienteIdGuardado)}`;
      }
    });

    document.getElementById('formImportadorHistorial').reset();
    document.getElementById('xpDropzoneTitle').textContent = 'Arrastra tu archivo aquí o haz clic para seleccionarlo';
    document.getElementById('xpDropzoneSubtitle').textContent = 'Soporta tablas en Excel (.xlsx), rutinas en Word (.docx) o documentos en PDF';
    document.getElementById('xpImportDropzone').style.borderColor = 'rgba(138, 43, 226, 0.4)';
    currentImportStaging = null;

  } catch (err) {
    console.error('Error guardando cronología:', err);
    Swal.fire({
      icon: 'error',
      title: 'Error al Guardar',
      text: err.message || 'No se pudo guardar la información en la base de datos.',
      confirmButtonColor: '#8a2be2'
    });
  } finally {
    btn.disabled = false;
    btn.innerHTML = `<i class="ti ti-device-floppy"></i> <span>Inyectar al Historial y Calendario</span>`;
  }
}

function escapeHtml(str) {
  if (!str) return '';
  const div = document.createElement('div');
  div.textContent = String(str);
  return div.innerHTML;
}

async function descargarCopiaSeguridadDb() {
  const btn = document.getElementById('btnDescargarBackupDb');

  const confirmResult = await Swal.fire({
    title: '¿Generar Copia de Seguridad?',
    text: 'Se creará y descargará un archivo comprimido (.sql.gz) con la base de datos completa de Atlas Fit (atletas, rutinas, series y finanzas).',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: '<i class="ti ti-download"></i> Descargar Respaldo',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#10b981',
    cancelButtonColor: '#64748b',
    background: '#121217',
    color: '#f8fafc',
    customClass: { popup: 'xp-swal-bento' }
  });

  if (!confirmResult.isConfirmed) return;

  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="ti ti-loader-2 ti-spin"></i> <span>Generando Respaldo...</span>';
  }

  Swal.fire({
    title: 'Generando Copia de Seguridad',
    html: '<div style="display:flex; flex-direction:column; align-items:center; gap:12px; margin-top:10px;">' +
          '<div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;"></div>' +
          '<p style="color:#cbd5e1; font-size:0.88rem; margin:0;">Volcando tablas y comprimiendo datos en tiempo real...</p>' +
          '</div>',
    allowOutsideClick: false,
    allowEscapeKey: false,
    showConfirmButton: false,
    background: '#121217',
    color: '#f8fafc',
    customClass: { popup: 'xp-swal-bento' }
  });

  try {
    const csrfToken = window.CSRF
      || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      || document.querySelector('input[name="csrf_token"]')?.value
      || <?= json_encode($csrf, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    const res = await fetch(`${window.APP || ''}/${window.XP_TOOLS_API_BASE}/descargar-respaldo-db`, {
      method: 'POST',
      headers: {
        'X-CSRF-Token': csrfToken,
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: 'csrf_token=' + encodeURIComponent(csrfToken)
    });

    if (!res.ok) {
      let errMsg = 'Error (' + res.status + '): No se pudo procesar la solicitud en el servidor.';
      if (res.status === 404) {
        errMsg = 'Error 404 (No Encontrado): El servidor web no encontró la ruta.';
      } else {
        try {
          const errJson = await res.json();
          if (errJson.msg) errMsg = errJson.msg;
        } catch (_) {}
      }
      throw new Error(errMsg);
    }

    const blob = await res.blob();
    let filename = 'xprod_fit_backup_' + new Date().toISOString().slice(0, 10) + '.sql.gz';
    const disp = res.headers.get('Content-Disposition');
    if (disp && disp.indexOf('filename=') !== -1) {
      const match = disp.match(/filename="?([^";]+)"?/);
      if (match && match[1]) filename = match[1].trim();
    }

    const downloadUrl = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = downloadUrl;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(downloadUrl);
    a.remove();

    Swal.fire({
      icon: 'success',
      title: '¡Respaldo Descargado!',
      text: 'El archivo ' + filename + ' se ha generado y descargado correctamente.',
      confirmButtonColor: '#10b981',
      background: '#121217',
      color: '#f8fafc',
      customClass: { popup: 'xp-swal-bento' }
    });

  } catch (err) {
    console.error('Error descarga backup:', err);
    Swal.fire({
      icon: 'error',
      title: 'No se pudo descargar',
      text: err.message || 'Ocurrió un error inesperado al procesar la solicitud.',
      confirmButtonColor: '#ef4444',
      background: '#121217',
      color: '#f8fafc',
      customClass: { popup: 'xp-swal-bento' }
    });
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = '<i class="ti ti-download"></i> <span>Descargar Copia de Seguridad</span>';
    }
  }
}
</script>

