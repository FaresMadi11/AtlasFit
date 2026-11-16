<?php
// views/entrenador/membresia_suspendida.php
$entIdSusp = (int)($_SESSION['user_id'] ?? 0);
$nomEntSusp = (string)($_SESSION['nombre'] ?? 'Entrenador');
$membresiaSusp = $membresia ?? obtenerEstadoMembresiaEntrenador($entIdSusp);
$msgSusp = !empty($membresiaSusp['mensaje_alerta'])
    ? (string)$membresiaSusp['mensaje_alerta']
    : 'Tu acceso al panel de entrenador ha sido pausado temporalmente hasta que logres completar el pago de tu membresía mensual.';
$fechaCorteSusp = !empty($membresiaSusp['fecha_proximo_pago'])
    ? date('d/m/Y', strtotime((string)$membresiaSusp['fecha_proximo_pago']))
    : 'Pendiente';
$fechaLimGraciaSusp = !empty($membresiaSusp['fecha_limite_gracia'])
    ? date('d/m/Y', strtotime((string)$membresiaSusp['fecha_limite_gracia']))
    : 'Vencida';
$esPausaManual = !empty($membresiaSusp['pausado_manualmente']);
?><!DOCTYPE html>
<html lang="es" data-theme="classic">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Membresía en Pausa | Atlas Fit</title>
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/vendor/tabler-icons/tabler-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      min-height: 100vh;
      font-family: 'Inter', sans-serif;
      background: radial-gradient(circle at 20% 20%, rgba(138, 43, 226, 0.16), transparent 45%),
                  radial-gradient(circle at 80% 80%, rgba(239, 68, 68, 0.12), transparent 50%),
                  #05050a;
      color: #f8fafc;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px 16px;
    }
    .susp-card {
      width: 100%;
      max-width: 480px;
      background: rgba(18, 18, 26, 0.92);
      border: 1px solid rgba(239, 68, 68, 0.35);
      border-radius: 24px;
      padding: 32px 26px;
      box-shadow: 0 24px 60px rgba(0, 0, 0, 0.65), 0 0 40px rgba(239, 68, 68, 0.12);
      text-align: center;
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
    }
    .susp-icon {
      width: 76px;
      height: 76px;
      border-radius: 22px;
      margin: 0 auto 18px;
      background: rgba(239, 68, 68, 0.15);
      border: 1.5px solid rgba(239, 68, 68, 0.45);
      color: #f87171;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.35rem;
      box-shadow: 0 0 28px rgba(239, 68, 68, 0.25);
    }
    .susp-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 5px 14px;
      border-radius: 999px;
      background: rgba(239, 68, 68, 0.16);
      border: 1px solid rgba(239, 68, 68, 0.38);
      color: #fca5a5;
      font-size: 0.74rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.7px;
      margin-bottom: 14px;
    }
    .susp-title {
      font-size: 1.45rem;
      font-weight: 800;
      color: #ffffff;
      margin-bottom: 10px;
      line-height: 1.3;
    }
    .susp-msg {
      font-size: 0.92rem;
      color: #cbd5e1;
      line-height: 1.55;
      margin-bottom: 22px;
      padding: 14px 16px;
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 16px;
    }
    .susp-meta-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      margin-bottom: 22px;
      text-align: left;
    }
    .susp-meta-box {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.07);
      border-radius: 14px;
      padding: 11px 13px;
    }
    .susp-meta-lbl {
      font-size: 0.68rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #94a3b8;
      display: block;
      margin-bottom: 3px;
    }
    .susp-meta-val {
      font-size: 0.9rem;
      font-weight: 700;
      color: #f1f5f9;
    }
    .susp-note {
      font-size: 0.78rem;
      color: #94a3b8;
      margin-bottom: 22px;
      line-height: 1.45;
    }
    .susp-actions {
      display: flex;
      gap: 10px;
      justify-content: center;
      flex-wrap: wrap;
    }
    .btn-susp {
      padding: 12px 20px;
      border-radius: 14px;
      font-size: 0.88rem;
      font-weight: 700;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 7px;
      transition: all 0.2s ease;
      cursor: pointer;
      border: none;
    }
    .btn-susp-reload {
      background: linear-gradient(135deg, #8a2be2, #6d28d9);
      color: #fff;
      box-shadow: 0 6px 20px rgba(138, 43, 226, 0.35);
    }
    .btn-susp- logout, .btn-susp-logout {
      background: rgba(255, 255, 255, 0.07);
      border: 1px solid rgba(255, 255, 255, 0.14);
      color: #e2e8f0;
    }
  </style>
</head>
<body>
  <div class="susp-card" role="alertdialog" aria-labelledby="suspTitle">
    <div class="susp-icon">
      <i class="ti ti-player-pause"></i>
    </div>
    <div class="susp-pill">
      <i class="ti ti-lock-pause"></i>
      <?= $esPausaManual ? 'Cuenta en Pausa Administrativa' : 'Membresía Vencida' ?>
    </div>
    <h1 class="susp-title" id="suspTitle">Hola, <?= e($nomEntSusp) ?>.<br>Tu panel web está en pausa</h1>
    <div class="susp-msg">
      <?= e($msgSusp) ?>
    </div>

    <div class="susp-meta-grid">
      <div class="susp-meta-box">
        <span class="susp-meta-lbl">Fecha de Corte</span>
        <span class="susp-meta-val"><?= e($fechaCorteSusp) ?></span>
      </div>
      <div class="susp-meta-box">
        <span class="susp-meta-lbl">Fin de Gracia (+7d)</span>
        <span class="susp-meta-val" style="color:#f87171;"><?= e($fechaLimGraciaSusp) ?></span>
      </div>
    </div>

    <p class="susp-note">
      <i class="ti ti-shield-check" style="color:#a855f7;"></i>
      Tus atletas, rutinas, historiales de cargas y estadísticas están 100% resguardados. En cuanto el administrador registre tu pago mensual, tu acceso se reactivará de inmediato.
    </p>

    <div class="susp-actions">
      <a href="<?= APP_URL ?>/entrenador/dashboard" class="btn-susp btn-susp-reload">
        <i class="ti ti-refresh"></i> Verificar Reactivación
      </a>
      <a href="<?= APP_URL ?>/auth/logout" class="btn-susp btn-susp-logout">
        <i class="ti ti-logout"></i> Cerrar Sesión
      </a>
    </div>
  </div>
</body>
</html>

