<?php
/**
 * views/partials/cfg_catalogo_ejercicios.php
 * Partial compartido para el Catálogo de Ejercicios Drill-Down (Entrenador & Super Admin).
 * Params esperados:
 *  - $xpCatalogoEsAdmin (bool)
 *  - $xpCatalogoApiBase ('admin'|'entrenador')
 *  - $catalogoPorGrupo (array)
 *  - $csrf (string)
 */
$xpCatalogoEsAdmin = !empty($xpCatalogoEsAdmin);
$xpCatalogoApiBase = (string)($xpCatalogoApiBase ?? ($xpCatalogoEsAdmin ? 'admin' : 'entrenador'));
$catalogoPorGrupo  = $catalogoPorGrupo ?? [];
$csrf              = (string)($csrf ?? ($_SESSION['csrf_token'] ?? ''));
?>
<style>
/* ── UI-UX-Pro-Max: Drill-Down Card ── */
.drill-card {
  background: rgba(9, 9, 13, 0.79);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 20px;
  overflow: hidden;
  min-height: 600px;
  position: relative;
  display: flex;
  flex-direction: column;
  margin-bottom: 30px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.drill-level {
  display: none;
  flex-direction: column;
  height: 100%;
  animation: drillFadeIn 0.3s cubic-bezier(0.32, 0.72, 0, 1);
}
.drill-level.active {
  display: flex;
}

@keyframes drillFadeIn {
  from { opacity: 0; transform: translateX(20px); }
  to { opacity: 1; transform: translateX(0); }
}

.drill-header {
  padding: 20px 24px;
  border-bottom: 1px solid rgba(255,255,255,0.05);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  background: rgba(255,255,255,0.02);
  position: sticky;
  top: 0;
  z-index: 10;
  flex-wrap: wrap;
}
.drill-header-left {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 0;
}
.drill-header-title {
  font-size: 1.15rem;
  font-weight: 700;
  color: #fff;
  margin: 0;
}
.drill-btn-back {
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.1);
  color: var(--text);
  width: 36px; height: 36px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
}
.drill-btn-back:hover {
  background: rgba(255,255,255,0.1); color: #fff;
}

.drill-body {
  padding: 16px;
  flex: 1;
  overflow-y: auto;
}
@media (max-width: 480px) {
  .drill-body { padding: 12px; }
}
.drill-body::-webkit-scrollbar { width: 6px; }
.drill-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }

.cat-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
}
@media (min-width: 600px) {
  .cat-grid {
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 12px;
  }
}

.btn-categoria {
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 14px;
  padding: 10px 12px;
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  transition: all 0.2s;
  min-height: 52px;
  box-sizing: border-box;
  user-select: none;
  -webkit-tap-highlight-color: transparent;
}
.btn-categoria * {
  pointer-events: none;
}
.btn-categoria:hover {
  background: rgba(139,92,246,0.12);
  border-color: rgba(139,92,246,0.35);
  transform: translateY(-2px);
}
.btn-categoria:active { transform: scale(0.98); }
.btn-categoria i {
  font-size: 1.35rem;
  color: var(--accent);
  flex-shrink: 0;
}
.btn-categoria span {
  font-weight: 600;
  color: #fff;
  font-size: 0.88rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.drill-search {
  width: 100%;
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.05);
  border-radius: 14px;
  padding: 12px 16px 12px 46px;
  color: var(--text);
  font-size: 0.95rem;
  outline: none;
  transition: border-color 0.2s;
}
.drill-search:focus { border-color: var(--accent); }

/* Lista de ejercicios + scroll interno para categorías numerosas */
.ej-list {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
}
@media (min-width: 600px) {
  .ej-list {
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 12px;
  }
}
#nivel2List.ej-list,
#drillSearchResults.ej-list {
  max-height: clamp(290px, 46vh, 450px);
  overflow-y: auto;
  overflow-x: hidden;
  padding: 2px 6px 6px 2px;
  align-content: start;
  overscroll-behavior: contain;
  scrollbar-width: thin;
  scrollbar-color: rgba(138, 43, 226, 0.5) rgba(255, 255, 255, 0.03);
}
#nivel2List.ej-list::-webkit-scrollbar,
#drillSearchResults.ej-list::-webkit-scrollbar {
  width: 6px;
}
#nivel2List.ej-list::-webkit-scrollbar-track,
#drillSearchResults.ej-list::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.03);
  border-radius: 999px;
}
#nivel2List.ej-list::-webkit-scrollbar-thumb,
#drillSearchResults.ej-list::-webkit-scrollbar-thumb {
  background: rgba(138, 43, 226, 0.45);
  border-radius: 999px;
}
#nivel2List.ej-list::-webkit-scrollbar-thumb:hover,
#drillSearchResults.ej-list::-webkit-scrollbar-thumb:hover {
  background: rgba(168, 85, 247, 0.72);
}

.ej-card {
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 14px;
  padding: 10px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  min-height: 105px;
  box-sizing: border-box;
  position: relative;
}
.ej-card:hover {
  background: rgba(255,255,255,0.06);
  border-color: rgba(139,92,246,0.4);
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0,0,0,0.3);
}
.ej-card:active { transform: scale(0.98); }

.ej-card-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 6px;
}
.ej-thumb {
  width: 38px; height: 38px;
  border-radius: 10px;
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.08);
  display: flex; align-items: center; justify-content: center;
  overflow: hidden;
  flex-shrink: 0;
  position: relative;
}
.ej-thumb img { width: 100%; height: 100%; object-fit: cover; }
.ej-thumb i { font-size: 1.25rem; color: var(--accent); }

.ej-edit-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 0.72rem;
  font-weight: 600;
  padding: 4px 8px;
  border-radius: 8px;
  background: rgba(139,92,246,0.12);
  color: var(--accent-light, #a78bfa);
  border: 1px solid rgba(139,92,246,0.25);
  transition: all 0.2s;
  flex-shrink: 0;
}
.ej-card:hover .ej-edit-badge {
  background: var(--accent);
  color: #fff;
  border-color: var(--accent);
}

.ej-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
  min-width: 0;
}
.ej-name {
  font-weight: 600;
  color: #fff;
  font-size: 0.85rem;
  line-height: 1.25;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  min-height: 2.1em;
  word-break: break-word;
}
.ej-badges { display: flex; gap: 4px; flex-wrap: wrap; }
.ej-badge {
  font-size: 0.65rem;
  padding: 2px 5px;
  border-radius: 4px;
  background: rgba(255,255,255,0.06);
  color: var(--muted);
  display: inline-flex;
  align-items: center;
  gap: 3px;
}
.ej-badge.is-own {
  background: rgba(56, 189, 248, 0.14);
  color: #7dd3fc;
}

.btn-drill {
  background: var(--accent-primary, #8a2be2);
  color: var(--btn-text, #fff);
  border: none;
  padding: 14px 24px;
  border-radius: 14px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  transition: all 0.2s;
  width: 100%;
}
.btn-drill i { color: inherit; }
.btn-drill:hover { background: var(--accent-hover, #9d4edd); color: var(--btn-text, #fff); transform: translateY(-1px); }

.drill-dropzone {
  border: 2px dashed rgba(255,255,255,0.15); border-radius: 16px; padding: 32px 24px;
  text-align: center; background: rgba(255,255,255,0.02); cursor: pointer;
  transition: all 0.2s ease;
}
.drill-dropzone:hover { background: rgba(139,92,246,0.05); border-color: var(--accent); }

.drill-media-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  margin-top: 8px;
}
.drill-media-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px dashed rgba(139, 92, 246, 0.35);
  border-radius: 14px;
  padding: 14px 10px;
  text-align: center;
  position: relative;
  cursor: pointer;
  transition: all 0.2s ease;
  min-height: 120px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
.drill-media-card:hover {
  border-color: var(--accent);
  background: rgba(139, 92, 246, 0.06);
}
.drill-media-card .dm-title {
  font-size: 0.84rem;
  font-weight: 700;
  color: #fff;
  margin-bottom: 2px;
  display: flex;
  align-items: center;
  gap: 4px;
}
.drill-media-card .dm-sub {
  font-size: 0.72rem;
  color: var(--muted);
}
.drill-media-preview-wrap {
  position: relative;
  width: 100%;
  margin-top: 8px;
  border-radius: 10px;
  overflow: hidden;
}
.drill-media-img {
  width: 100%;
  height: 95px;
  object-fit: cover;
  border-radius: 10px;
  display: block;
  border: 1px solid rgba(255,255,255,0.08);
}
.drill-btn-del-img {
  position: absolute;
  top: 6px;
  right: 6px;
  background: rgba(239, 68, 68, 0.9);
  color: #fff;
  border: none;
  border-radius: 8px;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: transform 0.15s ease, background 0.15s ease;
  box-shadow: 0 2px 8px rgba(0,0,0,0.4);
  z-index: 5;
}
.drill-btn-del-img:hover {
  background: #dc2626;
  transform: scale(1.08);
}

.drill-form-tabs {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
  background: rgba(255,255,255,0.03);
  padding: 4px;
  border-radius: 14px;
  margin: 18px 0 16px;
  border: 1px solid rgba(255,255,255,0.06);
}
.drill-tab-btn {
  padding: 10px 14px;
  border-radius: 10px;
  border: none;
  background: transparent;
  color: #cbd5e1;
  font-size: 0.88rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
}
.drill-tab-btn:hover:not(.active) {
  color: #ffffff;
  background: rgba(255, 255, 255, 0.06);
}
.drill-tab-btn.active {
  background: linear-gradient(135deg, #8a2be2, #9d4edd);
  color: #ffffff !important;
  font-weight: 800;
  box-shadow: 0 4px 14px rgba(138, 43, 226, 0.45);
}
.paso-cfg-item {
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 14px;
  padding: 12px;
  margin-bottom: 10px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.paso-cfg-top {
  display: flex;
  align-items: center;
  gap: 8px;
}
.paso-cfg-media-preview {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(0,0,0,0.3);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 10px;
  padding: 6px 10px;
  margin-top: 4px;
}

.paso-item { display: flex; gap: 10px; align-items: flex-start; background: rgba(255,255,255,0.02); padding: 12px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); margin-bottom: 8px; }
.paso-num { width: 24px; height: 24px; border-radius: 50%; background: var(--accent); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 700; flex-shrink: 0; }
.paso-input { flex: 1; background: transparent; border: none; color: var(--text); outline: none; resize: none; font-family: inherit; font-size: 0.9rem; line-height: 1.4; }
.paso-del { background: none; border: none; color: #f87171; cursor: pointer; padding: 4px; border-radius: 6px; }
.paso-del:hover { background: rgba(248,113,113,0.1); }

.expand-anim {
  animation: expandCard 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  transform-origin: center top;
}
@keyframes expandCard {
  0% { transform: scale(0.95) translateY(10px); opacity: 0; }
  100% { transform: scale(1) translateY(0); opacity: 1; }
}

.expand-anim .ej-card,
.expand-anim .ejercicio-item {
  opacity: 0;
  animation: fadeInUp 0.4s ease forwards;
}
.expand-anim .ejercicio-item:nth-child(1), .expand-anim .ej-card:nth-child(1) { animation-delay: 0.2s; }
.expand-anim .ejercicio-item:nth-child(2), .expand-anim .ej-card:nth-child(2) { animation-delay: 0.25s; }
.expand-anim .ejercicio-item:nth-child(3), .expand-anim .ej-card:nth-child(3) { animation-delay: 0.3s; }
.expand-anim .ejercicio-item:nth-child(4), .expand-anim .ej-card:nth-child(4) { animation-delay: 0.35s; }
.expand-anim .ejercicio-item:nth-child(5), .expand-anim .ej-card:nth-child(5) { animation-delay: 0.40s; }
.expand-anim .ejercicio-item:nth-child(n+6), .expand-anim .ej-card:nth-child(n+6) { animation-delay: 0.45s; }

@keyframes fadeInUp {
  to { opacity: 1; transform: translateY(0); }
}
</style>

<!-- ── Catálogo de Ejercicios Drill-Down ── -->
<div class="drill-card" id="drillCard">
  <!-- HEADER GLOBAL -->
  <div class="drill-header">
    <div class="drill-header-left">
      <button type="button" class="drill-btn-back" id="globalBtnBack" style="display:none;"><i class="ti ti-arrow-left"></i></button>
      <h3 class="drill-header-title" id="globalHeaderTitle"><i class="ti ti-notebook" style="color:var(--accent);"></i> Catálogo de Ejercicios</h3>
    </div>
  </div>

  <div class="drill-body">
    <!-- SEARCH GLOBAL -->
    <div id="globalSearchContainer" style="position:relative; margin-bottom: 24px;">
      <i class="ti ti-search" style="position:absolute; left:16px; top:50%; transform:translateY(-50%); color:var(--muted); font-size:1.2rem;"></i>
      <input type="text" id="drillSearch" oninput="buscarGlobalCMS()" class="drill-search" placeholder="Buscar cualquier ejercicio...">
    </div>

    <!-- RESULTADOS BUSQUEDA GLOBALES -->
    <div id="drillSearchResults" class="ej-list" style="display:none; margin-bottom: 24px;"></div>

    <!-- VIEWS CONTAINER -->
    <div id="drillViewsContainer">
      <!-- VISTA 1: Categorías (Raíz) -->
      <div id="drillView1" class="drill-view" style="display:block;">
        <div id="drillCategoriesGrid">
          <!-- Upper Body -->
          <h4 style="color:#fff; margin-bottom:12px; font-size:1rem;"><i class="ti ti-barbell" style="color:var(--accent);"></i> Upper Body</h4>
          <div class="cat-grid" style="margin-bottom: 24px;">
            <div class="btn-categoria" onclick="abrirCategoria('Pecho')"><i class="ti ti-heart"></i><span>Pecho</span></div>
            <div class="btn-categoria" onclick="abrirCategoria('Espalda')"><i class="ti ti-arrow-autofit-up"></i><span>Espalda</span></div>
            <div class="btn-categoria" onclick="abrirCategoria('Hombros')"><i class="ti ti-arrow-up-circle"></i><span>Hombros</span></div>
            <div class="btn-categoria" onclick="abrirCategoria('Bíceps')"><i class="ti ti-arm"></i><span>Bíceps</span></div>
            <div class="btn-categoria" onclick="abrirCategoria('Tríceps')"><i class="ti ti-wave-sine"></i><span>Tríceps</span></div>
            <div class="btn-categoria" onclick="abrirCategoria('Trapecio')"><i class="ti ti-triangle"></i><span>Trapecio</span></div>
            <div class="btn-categoria" onclick="abrirCategoria('Antebrazos')"><i class="ti ti-hand-finger"></i><span>Antebrazos</span></div>
            <div class="btn-categoria" onclick="abrirCategoria('Cuello')"><i class="ti ti-man"></i><span>Cuello</span></div>
          </div>

          <!-- Lower Body -->
          <h4 style="color:#fff; margin-bottom:12px; font-size:1rem;"><i class="ti ti-run" style="color:var(--accent);"></i> Lower Body</h4>
          <div class="cat-grid" style="margin-bottom: 24px;">
            <div class="btn-categoria" onclick="abrirCategoria('Cuádriceps')"><i class="ti ti-run"></i><span>Cuádriceps</span></div>
            <div class="btn-categoria" onclick="abrirCategoria('Femoral')"><i class="ti ti-run"></i><span>Femoral</span></div>
            <div class="btn-categoria" onclick="abrirCategoria('Glúteos')"><i class="ti ti-user"></i><span>Glúteos</span></div>
            <div class="btn-categoria" onclick="abrirCategoria('Gemelos')"><i class="ti ti-anchor"></i><span>Gemelos</span></div>
            <div class="btn-categoria" onclick="abrirCategoria('Abductores')"><i class="ti ti-layout-sidebar"></i><span>Abductores</span></div>
            <div class="btn-categoria" onclick="abrirCategoria('Aductores')"><i class="ti ti-layout-sidebar-right"></i><span>Aductores</span></div>
          </div>

          <!-- Core / Otros -->
          <h4 style="color:#fff; margin-bottom:12px; font-size:1rem;"><i class="ti ti-flame" style="color:var(--accent);"></i> Core & Otros</h4>
          <div class="cat-grid">
            <div class="btn-categoria" onclick="abrirCategoria('Abdomen')"><i class="ti ti-layout-bottombar"></i><span>Abdomen</span></div>
            <div class="btn-categoria" onclick="abrirCategoria('Full Body')"><i class="ti ti-body-scan"></i><span>Full Body</span></div>
            <div class="btn-categoria" onclick="abrirCategoria('Cardio')"><i class="ti ti-heart-rate-monitor"></i><span>Cardio</span></div>
            <div class="btn-categoria" onclick="abrirCategoria('Otro')"><i class="ti ti-dots"></i><span>Otros / Sin Grupo</span></div>
          </div>
        </div>
      </div>

      <!-- VISTA 2: Lista por Categoría -->
      <div id="drillView2" class="drill-view" style="display: none;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
          <h4 id="nivel2Title" style="color:#fff; margin:0; font-size:1.05rem; text-transform:capitalize;">Categoría</h4>
          <span id="nivel2Count" style="font-size:0.75rem; color:var(--muted); font-weight:600;"></span>
        </div>
        <div id="nivel2List" class="ej-list">
          <!-- Renderizado por JS -->
        </div>
        <div style="margin-top: 20px;">
          <button type="button" class="btn-drill" onclick="abrirFormulario('NUEVO_EN_CAT')">
            <i class="ti ti-plus"></i> Agregar Ejercicio a esta Categoría
          </button>
        </div>
      </div>

      <!-- VISTA 3: Formulario CRUD -->
      <div id="drillView3" class="drill-view" style="display: none;">
        <h4 id="nivel3Title" style="color:#fff; margin-bottom:16px; font-size:1.1rem;">Agregar Ejercicio</h4>
        <form id="formEjercicio" onsubmit="guardarEjercicio(event)" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
          <input type="hidden" id="ej_id" name="id" value="">
          <input type="hidden" id="eliminar_portada_input" name="eliminar_portada" value="0">
          <input type="hidden" id="eliminar_demo_input" name="eliminar_demo" value="0">
          <input type="hidden" id="ej_indicaciones_input" name="indicaciones" value="">
          <input type="hidden" id="ej_cfg_maquina_input" name="configuracion_maquina" value="">
          <textarea id="ej_desc_legacy" name="descripcion" style="display:none;"></textarea>

          <div class="sys-input-group">
            <label for="ej_nombre">Nombre del ejercicio <span style="color:var(--accent)">*</span></label>
            <input type="text" id="ej_nombre" name="nombre" class="sys-input" required placeholder="Ej: Press de banca con mancuernas">
          </div>

          <div class="sys-input-group">
            <label for="ej_grupo">Grupo muscular</label>
            <div style="position:relative;">
              <select id="ej_grupo" name="grupo_muscular" class="sys-input">
                <option value="">-- Sin grupo --</option>
                <optgroup label="Upper Body">
                  <option value="Pecho">Pecho</option>
                  <option value="Espalda">Espalda</option>
                  <option value="Hombros">Hombros</option>
                  <option value="Bíceps">Bíceps</option>
                  <option value="Tríceps">Tríceps</option>
                  <option value="Trapecio">Trapecio</option>
                  <option value="Antebrazos">Antebrazos</option>
                  <option value="Cuello">Cuello</option>
                </optgroup>
                <optgroup label="Lower Body">
                  <option value="Cuádriceps">Cuádriceps</option>
                  <option value="Femoral">Femoral</option>
                  <option value="Glúteos">Glúteos</option>
                  <option value="Gemelos">Gemelos</option>
                  <option value="Abductores">Abductores</option>
                  <option value="Aductores">Aductores</option>
                </optgroup>
                <optgroup label="Core & Otros">
                  <option value="Abdomen">Abdomen</option>
                  <option value="Full Body">Full Body</option>
                  <option value="Cardio">Cardio</option>
                  <option value="Otro">Otro</option>
                </optgroup>
              </select>
            </div>
          </div>

          <!-- Carga Doble de Imágenes (Portada y Demostración) -->
          <div class="sys-input-group">
            <label>Imágenes del Ejercicio (Portada y Demostración)</label>
            <div class="drill-media-grid">
              <div class="drill-media-card" id="cardPortada">
                <div class="dm-title"><i class="ti ti-photo" style="color:var(--accent);"></i> Portada</div>
                <div class="dm-sub">Imagen principal</div>
                <div id="dropzonePortada" onclick="document.getElementById('ej_imagen_portada').click()" style="margin-top:6px; cursor:pointer;">
                  <i class="ti ti-cloud-upload" style="font-size:1.8rem; color:var(--accent-light);"></i>
                  <div style="font-size:0.75rem; color:var(--text); font-weight:600; margin-top:4px;">Subir Portada</div>
                </div>
                <input type="file" id="ej_imagen_portada" name="media_portada" accept="image/*" style="display:none" onchange="handlePortadaSelect(this)">
                <div class="drill-media-preview-wrap" id="previewWrapPortada" style="display:none;">
                  <img src="" id="imgPreviewPortada" class="drill-media-img" alt="Portada">
                  <button type="button" class="drill-btn-del-img" onclick="eliminarPortada(event)" title="Eliminar imagen de portada">
                    <i class="ti ti-trash"></i>
                  </button>
                </div>
              </div>

              <div class="drill-media-card" id="cardDemo">
                <div class="dm-title"><i class="ti ti-player-play" style="color:#38bdf8;"></i> Demostración</div>
                <div class="dm-sub">Foto o ejecución</div>
                <div id="dropzoneDemo" onclick="document.getElementById('ej_imagen_demo').click()" style="margin-top:6px; cursor:pointer;">
                  <i class="ti ti-cloud-upload" style="font-size:1.8rem; color:#38bdf8;"></i>
                  <div style="font-size:0.75rem; color:var(--text); font-weight:600; margin-top:4px;">Subir Demostración</div>
                </div>
                <input type="file" id="ej_imagen_demo" name="media_demo" accept="image/*,video/mp4" style="display:none" onchange="handleDemoSelect(this)">
                <div class="drill-media-preview-wrap" id="previewWrapDemo" style="display:none;">
                  <img src="" id="imgPreviewDemo" class="drill-media-img" alt="Demostración">
                  <button type="button" class="drill-btn-del-img" onclick="eliminarDemo(event)" title="Eliminar demostración">
                    <i class="ti ti-trash"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="drill-form-tabs" role="tablist">
            <button type="button" class="drill-tab-btn active" id="tabFormIndicaciones" onclick="setDrillFormTab('indicaciones')" role="tab">
              <i class="ti ti-list-check"></i> Indicaciones
            </button>
            <button type="button" class="drill-tab-btn" id="tabFormConfig" onclick="setDrillFormTab('configuracion')" role="tab">
              <i class="ti ti-settings"></i> Configuración
            </button>
          </div>

          <div id="panelFormIndicaciones" class="drill-form-panel">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
              <label style="font-size:0.88rem; font-weight:700; color:#fff; margin:0;">
                <i class="ti ti-activity" style="color:var(--accent);"></i> Pasos de Ejecución Biomecánica
              </label>
              <span style="font-size:0.75rem; color:var(--muted);">¿Cómo realizar el ejercicio?</span>
            </div>
            <div id="pasosContainer" style="display:flex; flex-direction:column;"></div>
            <button type="button" onclick="agregarPaso()" style="margin-top:10px; background:rgba(255,255,255,0.03); border:1px dashed rgba(255,255,255,0.2); color:var(--text); padding:11px; border-radius:12px; font-size:0.88rem; font-weight:600; display:flex; align-items:center; justify-content:center; gap:6px; cursor:pointer; width:100%; transition:all 0.2s;">
              <i class="ti ti-plus"></i> Añadir paso de ejecución
            </button>
          </div>

          <div id="panelFormConfig" class="drill-form-panel" style="display:none;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; margin-top:6px;">
              <label style="font-size:0.88rem; font-weight:700; color:#fff; margin:0;">
                <i class="ti ti-tool" style="color:#38bdf8;"></i> Pasos para Configurar la Máquina
              </label>
              <span style="font-size:0.75rem; color:var(--muted);">Ajustes de asiento, polea, etc.</span>
            </div>
            <div id="configPasosContainer" style="display:flex; flex-direction:column;"></div>
            <button type="button" onclick="agregarPasoConfig()" style="margin-top:10px; background:rgba(56,189,248,0.06); border:1px dashed rgba(56,189,248,0.3); color:#38bdf8; padding:11px; border-radius:12px; font-size:0.88rem; font-weight:600; display:flex; align-items:center; justify-content:center; gap:6px; cursor:pointer; width:100%; transition:all 0.2s;">
              <i class="ti ti-plus"></i> Añadir paso de ajuste de máquina
            </button>
          </div>

          <div style="margin-top:28px; padding-bottom: 24px; display:flex; flex-direction:column; gap:12px;">
            <button type="submit" class="btn-drill" id="btnGuardarEj">
              <i class="ti ti-device-floppy"></i> Guardar Ejercicio
            </button>
            <button type="button" onclick="volverNivel(activeNivelAnt)" class="btn-xp btn-secondary" style="width:100%; padding:14px; border-radius:14px; font-size:1rem; font-weight:600; display:flex; align-items:center; justify-content:center; gap:8px;">
              <i class="ti ti-x"></i> Cancelar
            </button>
            <div id="ejDeleteWrap" style="display:none;">
              <button type="button" onclick="borrarEjercicioDesdeForm()" style="background:rgba(239,68,68,.1); color:#f87171; border:1px solid rgba(239,68,68,.2); display:flex; align-items:center; justify-content:center; gap:8px; padding:14px; border-radius:14px; font-size:1rem; width:100%; cursor:pointer; transition:all 0.2s;">
                <i class="ti ti-trash"></i> Eliminar Ejercicio
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
window.XP_CAT_ES_ADMIN  = <?= $xpCatalogoEsAdmin ? 'true' : 'false' ?>;
window.XP_CAT_API_BASE  = <?= json_encode($xpCatalogoApiBase, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
window.catalogoCompleto = <?= json_encode($catalogoPorGrupo, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
window.activeCategoria  = null;
window.activeNivelAnt   = 1;

function xpCatEsc(str) {
  return String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

window.irNivel = function(nivel) {
  document.querySelectorAll('.drill-view').forEach(el => el.style.display = 'none');
  const target = document.getElementById('drillView' + nivel);
  if (target) {
    target.style.display = 'block';
    target.classList.remove('expand-anim');
    void target.offsetWidth;
    target.classList.add('expand-anim');
  }

  const backBtn = document.getElementById('globalBtnBack');
  const searchBar = document.getElementById('globalSearchContainer');
  const mainTitle = document.getElementById('globalHeaderTitle');

  if (nivel === 1) {
    if (backBtn) backBtn.style.display = 'none';
    if (searchBar) searchBar.style.display = 'block';
    if (mainTitle) mainTitle.innerHTML = `<i class="ti ti-notebook" style="color:var(--accent);"></i> Catálogo de Ejercicios`;
    const sInput = document.getElementById('drillSearch');
    const qs = sInput ? sInput.value.trim() : '';
    if (qs.length < 2) {
      const catGrid = document.getElementById('drillCategoriesGrid');
      if (catGrid) catGrid.style.display = 'block';
      const sRes = document.getElementById('drillSearchResults');
      if (sRes) sRes.style.display = 'none';
    }
  } else if (nivel === 2) {
    if (backBtn) {
      backBtn.style.display = 'flex';
      backBtn.onclick = () => window.volverNivel(1);
    }
    if (searchBar) searchBar.style.display = 'block';
    if (mainTitle) mainTitle.innerHTML = `<i class="ti ti-list" style="color:var(--accent);"></i> Ejercicios`;
    const catGrid = document.getElementById('drillCategoriesGrid');
    if (catGrid) catGrid.style.display = 'none';
    const sRes = document.getElementById('drillSearchResults');
    if (sRes) sRes.style.display = 'none';
  } else if (nivel === 3) {
    if (backBtn) {
      backBtn.style.display = 'flex';
      backBtn.onclick = () => window.volverNivel(window.activeNivelAnt);
    }
    if (searchBar) searchBar.style.display = 'none';
    if (mainTitle) mainTitle.innerHTML = `<i class="ti ti-pencil" style="color:var(--accent);"></i> Modo Edición`;
  }
};

window.volverNivel = function(nivel) {
  window.irNivel(nivel);
};

window.getCanonicalMuscleGroup = function(str) {
  if (!str) return 'otro';
  const s = String(str).toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
  if (s.includes('pech') || s.includes('pector')) return 'pecho';
  if (s.includes('espalda') || s.includes('dorsal')) return 'espalda';
  if (s.includes('hombr') || s.includes('deltoid')) return 'hombros';
  if (s.includes('bicep')) return 'biceps';
  if (s.includes('tricep')) return 'triceps';
  if (s.includes('trapeci')) return 'trapecio';
  if (s.includes('antebraz')) return 'antebrazos';
  if (s.includes('cuello')) return 'cuello';
  if (s.includes('cuadicep') || s.includes('cuadricep')) return 'cuadriceps';
  if (s.includes('femor') || s.includes('isquio')) return 'femoral';
  if (s.includes('glute')) return 'gluteos';
  if (s.includes('gemel') || s.includes('pantorrill')) return 'gemelos';
  if (s.includes('abduct')) return 'abductores';
  if (s.includes('aduct')) return 'aductores';
  if (s.includes('abdom') || s.includes('core')) return 'abdomen';
  if (s.includes('cardio') || s.includes('aerob')) return 'cardio';
  if (s.includes('full') || s.includes('cuerpo') || s.includes('entero')) return 'full_body';
  return 'otro';
};

window.abrirCategoria = function(catId) {
  window.activeCategoria = catId;
  const isOtro = window.getCanonicalMuscleGroup(catId) === 'otro';
  const dispName = isOtro ? 'Otros / Sin Grupo' : catId;
  const titleEl = document.getElementById('nivel2Title');
  if (titleEl) titleEl.textContent = dispName;

  const listCont = document.getElementById('nivel2List');
  if (!listCont) return;
  listCont.innerHTML = '';

  let ejerciciosCat = [];
  const targetCanon = window.getCanonicalMuscleGroup(catId);
  const catData = window.catalogoCompleto || {};

  for (const group in catData) {
    const gCanon = window.getCanonicalMuscleGroup(group);
    if (gCanon === targetCanon) {
      ejerciciosCat = ejerciciosCat.concat(catData[group] || []);
    }
  }

  const seenIds = new Set();
  ejerciciosCat = ejerciciosCat.filter(e => {
    if (seenIds.has(e.id)) return false;
    seenIds.add(e.id);
    return true;
  });

  const cntEl = document.getElementById('nivel2Count');
  if (cntEl) cntEl.textContent = `${ejerciciosCat.length} ${ejerciciosCat.length === 1 ? 'ejercicio' : 'ejercicios'}`;

  if (ejerciciosCat.length === 0) {
    listCont.innerHTML = `
      <div style="grid-column: 1 / -1; text-align:center; padding:30px 0; color:#6b7280;">
        <i class="ti ti-mood-empty" style="font-size:2.5rem; display:block; margin-bottom:12px; opacity:.4;"></i>
        <p>No hay ejercicios para esta categoría.</p>
      </div>`;
  } else {
    ejerciciosCat.forEach(ej => listCont.appendChild(window.crearCardEjercicio(ej, 2)));
    listCont.scrollTop = 0;
  }

  window.irNivel(2);
};

window.crearCardEjercicio = function(ej, nivelOrigen) {
  const card = document.createElement('div');
  card.className = 'ej-card';
  card.setAttribute('role', 'button');
  card.setAttribute('tabindex', '0');

  const appBase = window.APP || '';
  const safeName = xpCatEsc(ej.nombre || 'Ejercicio');
  const imgHtml = ej.imagen_path
    ? `<img src="${appBase}/${xpCatEsc(ej.imagen_path)}" loading="lazy" alt="${safeName}">`
    : `<i class="ti ti-barbell"></i>`;

  let badges = '';
  if (!window.XP_CAT_ES_ADMIN && Number(ej.entrenador_id || 0) > 0) {
    badges += `<span class="ej-badge is-own"><i class="ti ti-user-check"></i> ${ej.origen_global_id ? 'Personalizado' : 'Tuyo'}</span>`;
  }
  if (ej.imagen_path || ej.video_path) badges += `<span class="ej-badge"><i class="ti ti-photo"></i> Media</span>`;
  if (ej.video_url) badges += `<span class="ej-badge"><i class="ti ti-brand-youtube"></i> Video</span>`;

  card.innerHTML = `
    <div class="ej-card-top">
      <div class="ej-thumb">${imgHtml}</div>
      <span class="ej-edit-badge"><i class="ti ti-pencil"></i> Editar</span>
    </div>
    <div class="ej-info">
      <div class="ej-name" title="${safeName}">${safeName}</div>
      ${badges ? `<div class="ej-badges">${badges}</div>` : ''}
    </div>
  `;
  card.onclick = () => window.editarEjercicio(ej.id, nivelOrigen);
  card.onkeydown = (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      window.editarEjercicio(ej.id, nivelOrigen);
    }
  };
  return card;
};

window.buscarGlobalCMS = function() {
  const sInput = document.getElementById('drillSearch');
  const q = sInput ? sInput.value.toLowerCase().trim() : '';
  const grid = document.getElementById('drillCategoriesGrid');
  const res = document.getElementById('drillSearchResults');

  if (q.length < 2) {
    if (grid) grid.style.display = 'block';
    if (res) res.style.display = 'none';
    return;
  }

  if (grid) grid.style.display = 'none';
  if (res) {
    res.style.display = 'grid';
    res.innerHTML = '';
  }

  let found = 0;
  const catData = window.catalogoCompleto || {};
  for (const group in catData) {
    (catData[group] || []).forEach(ej => {
      if (ej.nombre && ej.nombre.toLowerCase().includes(q)) {
        if (res) res.appendChild(window.crearCardEjercicio(ej, 1));
        found++;
      }
    });
  }
  if (found === 0 && res) {
    res.innerHTML = `<div style="grid-column: 1 / -1; text-align:center; padding:20px; color:var(--muted);">No se encontraron ejercicios.</div>`;
  }
};

window.currentFormTab = "indicaciones";

window.setDrillFormTab = function(tab) {
  window.currentFormTab = tab;
  const btnInd = document.getElementById("tabFormIndicaciones");
  const btnCfg = document.getElementById("tabFormConfig");
  const panInd = document.getElementById("panelFormIndicaciones");
  const panCfg = document.getElementById("panelFormConfig");

  if (tab === "configuracion") {
    if (btnCfg) btnCfg.classList.add("active");
    if (btnInd) btnInd.classList.remove("active");
    if (panCfg) panCfg.style.display = "block";
    if (panInd) panInd.style.display = "none";
  } else {
    if (btnInd) btnInd.classList.add("active");
    if (btnCfg) btnCfg.classList.remove("active");
    if (panInd) panInd.style.display = "block";
    if (panCfg) panCfg.style.display = "none";
  }
};

window.handlePortadaSelect = function(input) {
  const file = input.files[0];
  const dropzone = document.getElementById("dropzonePortada");
  const wrap = document.getElementById("previewWrapPortada");
  const img = document.getElementById("imgPreviewPortada");
  const delInput = document.getElementById("eliminar_portada_input");
  if (delInput) delInput.value = "0";

  if (file) {
    const reader = new FileReader();
    reader.onload = (e) => {
      if (img) img.src = e.target.result;
      if (wrap) wrap.style.display = "block";
      if (dropzone) dropzone.style.display = "none";
    };
    reader.readAsDataURL(file);
  }
};

window.eliminarPortada = function(e) {
  if (e) e.stopPropagation();
  const input = document.getElementById("ej_imagen_portada");
  if (input) input.value = "";
  const dropzone = document.getElementById("dropzonePortada");
  const wrap = document.getElementById("previewWrapPortada");
  const img = document.getElementById("imgPreviewPortada");
  const delInput = document.getElementById("eliminar_portada_input");
  if (delInput) delInput.value = "1";
  if (img) img.src = "";
  if (wrap) wrap.style.display = "none";
  if (dropzone) dropzone.style.display = "block";
};

window.handleDemoSelect = function(input) {
  const file = input.files[0];
  const dropzone = document.getElementById("dropzoneDemo");
  const wrap = document.getElementById("previewWrapDemo");
  const img = document.getElementById("imgPreviewDemo");
  const delInput = document.getElementById("eliminar_demo_input");
  if (delInput) delInput.value = "0";

  if (file) {
    const reader = new FileReader();
    reader.onload = (e) => {
      if (img) img.src = e.target.result;
      if (wrap) wrap.style.display = "block";
      if (dropzone) dropzone.style.display = "none";
    };
    reader.readAsDataURL(file);
  }
};

window.eliminarDemo = function(e) {
  if (e) e.stopPropagation();
  const input = document.getElementById("ej_imagen_demo");
  if (input) input.value = "";
  const dropzone = document.getElementById("dropzoneDemo");
  const wrap = document.getElementById("previewWrapDemo");
  const img = document.getElementById("imgPreviewDemo");
  const delInput = document.getElementById("eliminar_demo_input");
  if (delInput) delInput.value = "1";
  if (img) img.src = "";
  if (wrap) wrap.style.display = "none";
  if (dropzone) dropzone.style.display = "block";
};

window.agregarPaso = function(texto = "") {
  const cont = document.getElementById("pasosContainer");
  if (!cont) return;
  const count = cont.children.length + 1;
  const div = document.createElement("div");
  div.className = "paso-item";
  div.innerHTML = `
    <div class="paso-num">${count}</div>
    <textarea class="paso-input" rows="2" placeholder="Describe cómo ejecutar este paso...">${xpCatEsc(texto)}</textarea>
    <button type="button" class="paso-del" onclick="this.parentElement.remove(); window.reindexarPasos();" title="Eliminar paso"><i class="ti ti-trash"></i></button>
  `;
  cont.appendChild(div);
  window.reindexarPasos();
};

window.reindexarPasos = function() {
  const cont = document.getElementById("pasosContainer");
  if (!cont) return;
  Array.from(cont.children).forEach((item, index) => {
    const num = item.querySelector(".paso-num");
    if (num) num.textContent = index + 1;
  });
};

window.cfgStepsFiles = [];

window.agregarPasoConfig = function(texto = "", mediaUrl = null, tipoMedia = null) {
  const cont = document.getElementById("configPasosContainer");
  if (!cont) return;
  const index = cont.children.length;
  const numPaso = index + 1;

  const div = document.createElement("div");
  div.className = "paso-cfg-item";
  div.dataset.index = index;

  const appBase = window.APP || "";
  let mediaPreviewHtml = "";
  if (mediaUrl) {
    const safeUrl = xpCatEsc(mediaUrl);
    const fullMedia = mediaUrl.startsWith("http") ? safeUrl : `${appBase}/${safeUrl}`;
    const isVideo = tipoMedia === "video" || mediaUrl.toLowerCase().endsWith(".mp4");
    const mediaEl = isVideo
      ? `<video src="${fullMedia}" style="width:48px;height:48px;border-radius:6px;object-fit:cover;" muted></video>`
      : `<img src="${fullMedia}" style="width:48px;height:48px;border-radius:6px;object-fit:cover;">`;

    mediaPreviewHtml = `
      <div class="paso-cfg-media-preview" id="cfgMediaPreview_${index}">
        ${mediaEl}
        <span style="font-size:0.75rem;color:#cbd5e1;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
          <i class="ti ${isVideo ? 'ti-video' : 'ti-photo'}"></i> Archivo adjunto
        </span>
        <button type="button" onclick="eliminarCfgStepMedia(${index})" style="background:none;border:none;color:#f87171;cursor:pointer;padding:4px;" title="Quitar archivo">
          <i class="ti ti-x"></i>
        </button>
      </div>
    `;
  }

  div.innerHTML = `
    <div class="paso-cfg-top">
      <div class="paso-num" style="background:#0ea5e9;">${numPaso}</div>
      <span style="font-size:0.8rem;font-weight:700;color:#94a3b8;flex:1;">Ajuste de máquina</span>
      <button type="button" class="paso-del" onclick="this.closest('.paso-cfg-item').remove(); window.reindexarPasosConfig();" title="Eliminar paso">
        <i class="ti ti-trash"></i>
      </button>
    </div>
    <textarea class="paso-cfg-input paso-input" rows="2" placeholder="Ej: Ajustar la altura del asiento a nivel 4 y colocar la clavija en el respaldo...">${xpCatEsc(texto)}</textarea>

    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:4px;">
      <button type="button" onclick="document.getElementById('cfg_file_${index}').click()" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.12);color:#cbd5e1;padding:6px 10px;border-radius:8px;font-size:0.78rem;font-weight:600;display:flex;align-items:center;gap:6px;cursor:pointer;">
        <i class="ti ti-camera-plus" style="color:#38bdf8;"></i> Adjuntar Foto o Video
      </button>
      <input type="file" id="cfg_file_${index}" name="cfg_step_file_${index}" accept="image/*,video/mp4" style="display:none;" onchange="handleCfgStepMediaSelect(this, ${index})">
      <input type="hidden" id="cfg_existing_media_${index}" value="${xpCatEsc(mediaUrl || '')}">
      <input type="hidden" id="cfg_del_media_${index}" value="0">
    </div>

    <div id="cfgMediaContainer_${index}">
      ${mediaPreviewHtml}
    </div>
  `;

  cont.appendChild(div);
  window.reindexarPasosConfig();
};

window.reindexarPasosConfig = function() {
  const cont = document.getElementById("configPasosContainer");
  if (!cont) return;
  Array.from(cont.children).forEach((item, index) => {
    item.dataset.index = index;
    const num = item.querySelector(".paso-num");
    if (num) num.textContent = index + 1;
    const fileInp = item.querySelector('input[type="file"]');
    if (fileInp) {
      fileInp.id = `cfg_file_${index}`;
      fileInp.name = `cfg_step_file_${index}`;
    }
  });
};

window.handleCfgStepMediaSelect = function(input, index) {
  const file = input.files[0];
  const container = document.getElementById(`cfgMediaContainer_${index}`);
  const delInput = document.getElementById(`cfg_del_media_${index}`);
  if (delInput) delInput.value = "0";
  if (!container || !file) return;

  const isVideo = file.type.startsWith("video/") || file.name.endsWith(".mp4");
  const url = URL.createObjectURL(file);
  const mediaEl = isVideo
    ? `<video src="${url}" style="width:48px;height:48px;border-radius:6px;object-fit:cover;" muted></video>`
    : `<img src="${url}" style="width:48px;height:48px;border-radius:6px;object-fit:cover;">`;

  container.innerHTML = `
    <div class="paso-cfg-media-preview" id="cfgMediaPreview_${index}">
      ${mediaEl}
      <span style="font-size:0.75rem;color:#cbd5e1;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
        <i class="ti ${isVideo ? 'ti-video' : 'ti-photo'}"></i> ${xpCatEsc(file.name)}
      </span>
      <button type="button" onclick="eliminarCfgStepMedia(${index})" style="background:none;border:none;color:#f87171;cursor:pointer;padding:4px;" title="Quitar archivo">
        <i class="ti ti-x"></i>
      </button>
    </div>
  `;
};

window.eliminarCfgStepMedia = function(index) {
  const container = document.getElementById(`cfgMediaContainer_${index}`);
  const fileInp = document.getElementById(`cfg_file_${index}`);
  const existingInp = document.getElementById(`cfg_existing_media_${index}`);
  const delInput = document.getElementById(`cfg_del_media_${index}`);

  if (fileInp) fileInp.value = "";
  if (existingInp) existingInp.value = "";
  if (delInput) delInput.value = "1";
  if (container) container.innerHTML = "";
};

window.abrirFormulario = function(modo) {
  const form = document.getElementById("formEjercicio");
  if (form) form.reset();
  const ejId = document.getElementById("ej_id");
  if (ejId) ejId.value = "";

  window.eliminarPortada();
  window.eliminarDemo();
  document.getElementById("eliminar_portada_input").value = "0";
  document.getElementById("eliminar_demo_input").value = "0";

  const pCont = document.getElementById("pasosContainer");
  if (pCont) pCont.innerHTML = "";
  window.agregarPaso();

  const cfgCont = document.getElementById("configPasosContainer");
  if (cfgCont) cfgCont.innerHTML = "";

  const delWrap = document.getElementById("ejDeleteWrap");
  if (delWrap) delWrap.style.display = "none";

  const select = document.getElementById("ej_grupo");
  if (select) {
    select.value = "";
    if (modo === "NUEVO_EN_CAT" && window.activeCategoria) {
      const activeCanon = window.getCanonicalMuscleGroup(window.activeCategoria);
      for (let i = 0; i < select.options.length; i++) {
        if (window.getCanonicalMuscleGroup(select.options[i].value) === activeCanon) {
          select.selectedIndex = i;
          break;
        }
      }
      window.activeNivelAnt = 2;
    } else {
      window.activeNivelAnt = 1;
    }
  }

  window.setDrillFormTab("indicaciones");
  const title3 = document.getElementById("nivel3Title");
  if (title3) title3.textContent = "Nuevo Ejercicio";
  window.irNivel(3);
};

window.findEjById = function(id) {
  const catData = window.catalogoCompleto || {};
  for (const group in catData) {
    const ej = (catData[group] || []).find(e => e.id == id);
    if (ej) return ej;
  }
  return null;
};

window.editarEjercicio = function(id, nivelOrigen) {
  const ej = window.findEjById(id);
  if (!ej) {
    console.warn("Ejercicio no encontrado con ID:", id);
    return;
  }

  const form = document.getElementById("formEjercicio");
  if (form) form.reset();
  const ejId = document.getElementById("ej_id");
  if (ejId) ejId.value = ej.id;
  const ejNom = document.getElementById("ej_nombre");
  if (ejNom) ejNom.value = ej.nombre || "";

  const dropzonePortada = document.getElementById("dropzonePortada");
  const wrapPortada = document.getElementById("previewWrapPortada");
  const imgPortada = document.getElementById("imgPreviewPortada");
  const delPortada = document.getElementById("eliminar_portada_input");
  if (delPortada) delPortada.value = "0";

  const appBase = window.APP || "";
  if (ej.imagen_path) {
    if (imgPortada) imgPortada.src = `${appBase}/${ej.imagen_path}`;
    if (wrapPortada) wrapPortada.style.display = "block";
    if (dropzonePortada) dropzonePortada.style.display = "none";
  } else {
    if (imgPortada) imgPortada.src = "";
    if (wrapPortada) wrapPortada.style.display = "none";
    if (dropzonePortada) dropzonePortada.style.display = "block";
  }

  const dropzoneDemo = document.getElementById("dropzoneDemo");
  const wrapDemo = document.getElementById("previewWrapDemo");
  const imgDemo = document.getElementById("imgPreviewDemo");
  const delDemo = document.getElementById("eliminar_demo_input");
  if (delDemo) delDemo.value = "0";

  if (ej.imagen_demo_path) {
    if (imgDemo) imgDemo.src = `${appBase}/${ej.imagen_demo_path}`;
    if (wrapDemo) wrapDemo.style.display = "block";
    if (dropzoneDemo) dropzoneDemo.style.display = "none";
  } else {
    if (imgDemo) imgDemo.src = "";
    if (wrapDemo) wrapDemo.style.display = "none";
    if (dropzoneDemo) dropzoneDemo.style.display = "block";
  }

  const select = document.getElementById("ej_grupo");
  if (select) {
    select.value = "";
    const grp = ej.categoria_nombre || ej.grupo_muscular;
    if (grp) {
      const ejCanon = window.getCanonicalMuscleGroup(grp);
      for (let i = 0; i < select.options.length; i++) {
        if (window.getCanonicalMuscleGroup(select.options[i].value) === ejCanon) {
          select.selectedIndex = i;
          break;
        }
      }
    }
  }

  const selImp = document.getElementById("ej_implemento");
  if (selImp) selImp.value = ej.tipo_implemento || "barra";
  const selUni = document.getElementById("ej_unidad");
  if (selUni) selUni.value = ej.unidad_carga || "kg";
  const ejVid = document.getElementById("ej_video_url");
  if (ejVid) ejVid.value = ej.video_url || "";

  const pCont = document.getElementById("pasosContainer");
  if (pCont) {
    pCont.innerHTML = "";
    const textoIndicaciones = ej.indicaciones || ej.descripcion || "";
    if (textoIndicaciones) {
      const lineas = textoIndicaciones.split("\n");
      lineas.forEach(l => {
        const trim = l.replace(/^(Paso \d+:|-)\s*/i, "").trim();
        if (trim) window.agregarPaso(trim);
      });
    }
    if (pCont.children.length === 0) window.agregarPaso();
  }

  const cfgCont = document.getElementById("configPasosContainer");
  if (cfgCont) {
    cfgCont.innerHTML = "";
    let pasosCfg = [];
    if (ej.configuracion_maquina) {
      try {
        pasosCfg = (typeof ej.configuracion_maquina === "string")
          ? JSON.parse(ej.configuracion_maquina)
          : ej.configuracion_maquina;
      } catch (err) {
        console.warn("Error parsing configuracion_maquina:", err);
      }
    }
    if (Array.isArray(pasosCfg) && pasosCfg.length > 0) {
      pasosCfg.forEach(p => {
        window.agregarPasoConfig(p.texto || "", p.media || null, p.tipo_media || null);
      });
    }
  }

  const title3 = document.getElementById("nivel3Title");
  if (title3) title3.textContent = `Editar: ${ej.nombre}`;

  const delWrap = document.getElementById("ejDeleteWrap");
  if (delWrap) {
    delWrap.style.display = "block";
    delWrap.dataset.ejId = ej.id;
    delWrap.dataset.ejNombre = ej.nombre;
  }

  window.setDrillFormTab("indicaciones");
  window.activeNivelAnt = nivelOrigen;
  window.irNivel(3);
};

window.consolidarPasos = function() {
  const inputs = document.querySelectorAll("#pasosContainer .paso-input");
  let lineas = [];
  inputs.forEach((inp, idx) => {
    const val = inp.value.trim();
    if (val) lineas.push(`Paso ${idx + 1}: ${val}`);
  });
  const legacy = document.getElementById("ej_desc_legacy");
  if (legacy) legacy.value = lineas.join("\n");
  const indInput = document.getElementById("ej_indicaciones_input");
  if (indInput) indInput.value = lineas.join("\n");

  const cfgItems = document.querySelectorAll("#configPasosContainer .paso-cfg-item");
  const pasosCfg = [];
  cfgItems.forEach((item, idx) => {
    const txt = (item.querySelector(".paso-cfg-input")?.value || "").trim();
    const existingMedia = item.querySelector(`input[id^="cfg_existing_media_"]`)?.value || null;
    const delMedia = item.querySelector(`input[id^="cfg_del_media_"]`)?.value === "1";
    pasosCfg.push({
      paso: idx + 1,
      texto: txt,
      media: delMedia ? null : existingMedia,
      eliminar_media: delMedia ? 1 : 0
    });
  });
  const cfgInput = document.getElementById("ej_cfg_maquina_input");
  if (cfgInput) cfgInput.value = JSON.stringify(pasosCfg);
};

window.guardarEjercicio = async function(e) {
  e.preventDefault();
  window.consolidarPasos();
  const form = document.getElementById("formEjercicio");
  const fd = new FormData(form);
  const token = fd.get("csrf_token") || window.CSRF || "";
  if (!fd.get("csrf_token")) {
    fd.append("csrf_token", token);
  }
  const id = fd.get("id");
  const appBase = window.APP || "";
  const apiBase = window.XP_CAT_API_BASE || "entrenador";
  const url = id ? (`${appBase}/${apiBase}/api-catalogo-editar`) : (`${appBase}/${apiBase}/api-catalogo-agregar`);
  const btn = document.getElementById("btnGuardarEj");

  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite"></i> Guardando...';
  }

  try {
    const r = await fetch(url, { method: 'POST', body: fd });
    const data = await r.json();
    if (data.ok) {
      Swal.fire({
        icon: 'success',
        title: '¡Guardado!',
        text: data.msg,
        background: '#0f1219',
        color: '#fff',
        timer: 1400,
        showConfirmButton: false
      }).then(() => location.reload());
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: data.msg, background: '#0f1219', color: '#fff' });
    }
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'Error de conexión', background: '#0f1219', color: '#fff' });
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = '<i class="ti ti-device-floppy"></i> Guardar Ejercicio';
    }
  }
};

window.borrarEjercicioDesdeForm = async function() {
  const delWrap = document.getElementById('ejDeleteWrap');
  if (!delWrap) return;
  const id = delWrap.dataset.ejId;
  const nombre = delWrap.dataset.ejNombre;
  if (!id) return;

  const avisoAlcance = window.XP_CAT_ES_ADMIN
    ? 'Se eliminará permanentemente para toda la plataforma.'
    : 'Solo se ocultará/eliminará de tu catálogo y el de tus atletas.';

  const res = await Swal.fire({
    title: '¿Eliminar ejercicio?',
    html: `<b>${xpCatEsc(nombre)}</b><br><span style="font-size:0.86rem;color:#94a3b8;">${avisoAlcance}</span>`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#ef4444',
    background: '#0f1219',
    color: '#fff'
  });
  if (!res.isConfirmed) return;

  const appBase = window.APP || '';
  const apiBase = window.XP_CAT_API_BASE || 'entrenador';
  const token = window.CSRF || '';
  const fd = new FormData();
  fd.append('id', id);
  fd.append('csrf_token', token);

  try {
    const r = await fetch(`${appBase}/${apiBase}/api-catalogo-borrar`, { method: 'POST', body: fd });
    const d = await r.json();
    if (d.ok) {
      Swal.fire({
        icon: 'success',
        title: 'Eliminado',
        text: d.msg,
        background: '#0f1219',
        color: '#fff',
        timer: 1200,
        showConfirmButton: false
      }).then(() => location.reload());
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: d.msg, background: '#0f1219', color: '#fff' });
    }
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: err.message, background: '#0f1219', color: '#fff' });
  }
};
</script>

