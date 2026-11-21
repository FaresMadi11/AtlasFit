# Graph Report - Xprod Fit  (2026-11-21)

## Corpus Check
- 125 files · ~338,524 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 14 file(s) not represented in the graph (top: (none) 7, .css 4, .conf 1)

## Summary
- 1504 nodes · 3392 edges · 107 communities (52 shown, 55 thin omitted)
- Extraction: 76% EXTRACTED · 24% INFERRED · 0% AMBIGUOUS · INFERRED: 799 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `d8ca7167`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- controllers/ClienteController.php
- pwa-sync.js
- PHPMailer
- SMTP
- login.php
- SimpleXLSX
- CatalogoService
- crear_rutina.php
- EntrenadorController.php
- csrfCheck
- views/cliente/estadisticas.php
- sw.js
- submitRegistration
- views/cliente/rutina_dia.php
- positionRegWheel
- progreso.php
- views/cliente/perfil.php
- perfil_cliente.php
- UsuarioModel
- entrenador/configuracion.php
- .analizar
- .setError
- clearRecoveryErrors
- login.php
- DocumentParser
- views/entrenador/asistencias.php
- renderizarRutinaInline
- aplicarHistorialEjercicioEnt
- assets/js/cookies.js
- xpUpdateSwipeHeight
- TradingWeightChart
- cargarPlantillaEnRutina
- addExercise
- openSheet
- abrirSheetPlantillas
- views/auth/recuperar.php
- views/entrenador/dashboard.php
- cargarDetalleDiaInline
- BackupService
- renderExerciseList
- ExcelExportService.php
- ClienteModel
- MetodoPagoModel
- actualizarBotonPlantilla
- renderModalDia
- assets/js/app.js
- SesionAsistenciaModel
- abrirModalReemplazarEjercicio
- ObjetivoModel
- PesoHistorialModel
- RutinaModel
- AdminController.php
- Product
- rutina.php
- gestion.php
- PagoModel
- admin/configuracion.php
- media_storage.php
- enhanceSelect
- custom-date.js
- views/entrenador/metodos_pago.php
- core/helpers.php
- MetaModel
- UsuarioSistemaModel
- cfg_herramientas_ia_backup.php
- cfg_paleta_colores.php
- views/cliente/modificar_rutina.php
- views/entrenador/clientes.php
- .strlen
- footer.php
- views/layout/header.php
- registrar_cliente.php

## God Nodes (most connected - your core abstractions)
1. `getDB()` - 165 edges
2. `PHPMailer` - 131 edges
3. `csrfCheck()` - 103 edges
4. `jsonOut()` - 90 edges
5. `SMTP` - 46 edges
6. `SimpleXLSX` - 45 edges
7. `getJsonPayload()` - 28 edges
8. `obtenerEstadoMembresiaEntrenador()` - 26 edges
9. `Exception` - 24 edges
10. `UsuarioModel` - 23 edges

## Surprising Connections (you probably didn't know these)
- `entMetodosPago()` --calls--> `getDB()`  [INFERRED]
  controllers/EntrenadorController.php → config/database.php
- `entPagosRecibidos()` --calls--> `getDB()`  [INFERRED]
  controllers/EntrenadorController.php → config/database.php
- `{closure#3}()` --calls--> `sanitizeInput()`  [INFERRED]
  controllers/AuthController.php → core/security.php
- `{closure#4}()` --calls--> `sanitizeInput()`  [INFERRED]
  controllers/AuthController.php → core/security.php
- `getDB()` --calls--> `env()`  [INFERRED]
  config/database.php → core/env.php

## Import Cycles
- None detected.

## Communities (107 total, 55 thin omitted)

### Community 0 - "controllers/ClienteController.php"
Cohesion: 0.07
Nodes (50): getDB(), adminApiAprobarEntrenador(), cliApiCambiarPassword(), cliApiDiaDetalle(), cliApiEditarPerfil(), cliApiEditarPeso(), cliApiGuardarPregunta(), cliApiNuevoPeso() (+42 more)

### Community 1 - "pwa-sync.js"
Cohesion: 0.35
Nodes (14): checkInstalledAsync(), dequeue(), desinstalarXprodApp(), enqueue(), ensurePillInDOM(), flushQueue(), getQueue(), initSW() (+6 more)

### Community 4 - "login.php"
Cohesion: 0.04
Nodes (37): boxMetaOtros, boxSubHipertrofia, btnPesoMinus, btnPesoPlus, btnRecovery, btnRulerMinus, btnRulerPlus, btnSkipGoals (+29 more)

### Community 6 - "CatalogoService"
Cohesion: 0.23
Nodes (5): entApiCatalogoAgregar(), entApiCatalogoBorrar(), entApiCatalogoEditar(), entApiCatalogoList(), CatalogoService

### Community 9 - "crear_rutina.php"
Cohesion: 0.07
Nodes (27): actualizarNumerosPasosCardio(), cardioState, cargarDiasAtleta(), catalogoEjercicios, categoriasDB, consolidarPasosCardio(), CR_API_PREFIX, CR_BACK_URL (+19 more)

### Community 10 - "EntrenadorController.php"
Cohesion: 0.05
Nodes (36): calcularFirmaPlantilla(), entActualizarMedidas(), entApiEliminarPlantillaCatalogo(), entApiEliminarRutina(), entApiGuardarMensualidad(), entApiGuardarPlantillaCatalogo(), entApiGuardarRutinaComoPlantilla(), entApiGuardarTemaSistema() (+28 more)

### Community 11 - "csrfCheck"
Cohesion: 0.15
Nodes (38): adminApiActualizarMembresia(), adminApiAprobarAtleta(), adminApiCrearEntrenador(), adminApiGenerarCodigo(), adminApiGuardarConfiguracion(), adminApiMarcarNotificacion(), adminApiRevocarEntrenador(), adminApiTogglePausa() (+30 more)

### Community 12 - "views/cliente/estadisticas.php"
Cohesion: 0.08
Nodes (36): e(), abrirHistorialPeso(), abrirModalDia(), aplicarDatosHistorialEjercicio(), asegurarPortalBody(), badgeSerieTexto(), badgeSerieTitulo(), cambiarSerie() (+28 more)

### Community 13 - "sw.js"
Cohesion: 0.23
Nodes (8): getMetaUrl(), getScopeBase(), homePathForRole(), PRECACHE_ASSETS, readOfflineMeta(), resolveOfflineHtml(), TRUSTED_CDN_HOSTS, writeOfflineMeta()

### Community 14 - "submitRegistration"
Cohesion: 0.20
Nodes (10): clearRegErrors(), normalizarCedula(), postApi(), resetEmailVerificationUI(), safeAnime(), setBtn(), setRegError(), submitRegistration() (+2 more)

### Community 15 - "views/cliente/rutina_dia.php"
Cohesion: 0.07
Nodes (41): abrirModalAgregarEjercicioAtleta(), abrirModalConfirmarRevertir(), abrirModalPosponerRutina(), abrirModalReemplazoCliente(), actualizarBarraCambios(), actualizarBotonesModoEdicion(), actualizarEstadoEjercicio(), actualizarProgreso() (+33 more)

### Community 16 - "positionRegWheel"
Cohesion: 0.40
Nodes (5): ensureWheelPositioned(), initRegWheel(), positionRegWheel(), syncRegWheelDate(), updateRegWheelCol()

### Community 17 - "progreso.php"
Cohesion: 0.12
Nodes (21): adminApiDiasAtleta(), entApiDiaDetalle(), entApiDiasAtleta(), entApiProgresoEjercicios(), entApiProgresoHistorial(), entAsistencias(), entAsistenciasData(), coincideGrupoMuscularProgreso() (+13 more)

### Community 18 - "views/cliente/perfil.php"
Cohesion: 0.11
Nodes (13): copiarDatosPago(), draw(), fbCopy(), init(), mostrarToast(), open(), resetSegModal(), scrollToWheelDate() (+5 more)

### Community 19 - "perfil_cliente.php"
Cohesion: 0.07
Nodes (26): abrirModalSelectorEjercicio(), actualizarCardioSummaryInline(), actualizarNumerosPasosCardioInline(), api(), cambiarMesCalendario(), currentBarrasPesos, currentGrupoEjercicios, currentTop3Sesiones (+18 more)

### Community 20 - "UsuarioModel"
Cohesion: 0.05
Nodes (51): authApiEnviarCodigoEmail(), authApiValidarCodigo(), authApiVerificarCodigoEmail(), authDoLogin(), authDoRecuperarIdentificar(), authDoRecuperarVerificarRespuesta(), authDoRegistro(), authGoogleCallback() (+43 more)

### Community 21 - "entrenador/configuracion.php"
Cohesion: 0.20
Nodes (14): actualizarUiCodigoEntrenador(), closePanel(), closePwdModal(), eliminarAtleta(), enviarCambioCodigoEnt(), escCfgHtml(), generarNuevoCodigoEntrenador(), guardarCodigoEntrenador() (+6 more)

### Community 22 - ".analizar"
Cohesion: 0.22
Nodes (5): adminApiImportarHistorialAnalizar(), adminApiImportarHistorialGuardar(), entApiImportarHistorialAnalizar(), entApiImportarHistorialGuardar(), ImportadorService

### Community 26 - "login.php"
Cohesion: 0.50
Nodes (5): animate(), drawFrame(), getActiveLoginThemeColors(), initParticles(), resize()

### Community 29 - "views/entrenador/asistencias.php"
Cohesion: 0.09
Nodes (44): abrirModalEjercicioAsistencias(), abrirOpcionesEjercicioDrawer(), abrirSolicitudRutinaAtleta(), actualizarBadges(), actualizarEjercicioEnCache(), actualizarSerieCacheLocalEntrenador(), actualizarTrackCarrusel(), aplicarDatosPanelesAsistencias() (+36 more)

### Community 30 - "renderizarRutinaInline"
Cohesion: 0.17
Nodes (16): activarModoEdicionRutina(), agregarSerieEdicion(), aplicarTipoSerieInline(), cambiarImplementoEdicion(), cambiarUnidadEdicion(), cerrarSheetPerfil(), eliminarSerieInline(), getExerciseThumbUrl() (+8 more)

### Community 31 - "aplicarHistorialEjercicioEnt"
Cohesion: 0.15
Nodes (15): renderDiasMiniBarPhp(), aplicarHistorialEjercicioEnt(), cambiarSerie(), cambiarTren(), cerrarModalSelectorEjercicio(), initGraficaPeso(), initStatsDashboard(), renderBotonesGrupoEnt() (+7 more)

### Community 32 - "assets/js/cookies.js"
Cohesion: 0.30
Nodes (12): applyConsent(), buildBanner(), buildModal(), getDefaultPreferences(), hideBanner(), init(), loadAnalyticsScripts(), loadConsent() (+4 more)

### Community 33 - "xpUpdateSwipeHeight"
Cohesion: 0.25
Nodes (11): activarModoEditarMedicionActual(), activarModoNuevaMedicion(), cancelarEdicionInline(), enviarGuardarMedidas(), guardarMedicionInline(), renderHistorialPills(), renderMedidasParaFecha(), seleccionarFechaHistorial() (+3 more)

### Community 36 - "cargarPlantillaEnRutina"
Cohesion: 0.50
Nodes (5): actualizarCardioSummary(), cargarPlantillaEnRutina(), onCustomCardioMin(), setCardioMinutos(), toggleCardioPanel()

### Community 37 - "addExercise"
Cohesion: 0.31
Nodes (10): actualizarHintMancuerna(), addExercise(), addSet(), cambiarImplemento(), cambiarUnidad(), cerrarWheelPicker(), copiarValoresATodasLasSeries(), initWheelEvents() (+2 more)

### Community 38 - "openSheet"
Cohesion: 0.19
Nodes (14): abrirWheelPicker(), actualizarWheelActivo(), catsDrillBack(), closeSheet(), confirmSelection(), deleteSerie(), initSortableCompacto(), openCategorySheet() (+6 more)

### Community 39 - "abrirSheetPlantillas"
Cohesion: 0.25
Nodes (8): abrirSheetPlantillas(), agregarPasoCardio(), cargarPasosCardio(), eliminarPlantillaGuardada(), escapeHtmlAttr(), escapeJs(), handlePlantillaClick(), renderPlantillasList()

### Community 40 - "views/auth/recuperar.php"
Cohesion: 0.40
Nodes (8): animateTransition(), procesarPaso1(), procesarPaso2(), procesarPaso3(), procesarPaso4(), setButtonLoading(), showAlert(), volverPaso()

### Community 41 - "views/entrenador/dashboard.php"
Cohesion: 0.14
Nodes (4): cerrarModalProducto(), entEsc(), entRenderMiRutina(), guardarProducto()

### Community 42 - "cargarDetalleDiaInline"
Cohesion: 0.25
Nodes (8): cancelarModoEdicionRutina(), cargarDetalleDiaInline(), consolidarPasosCardioInline(), generarSkeletonRutinaInline(), guardarCambiosRutinaEdicion(), inicializarRutinaInline(), renderizarBarraDias(), seleccionarDiaRutina()

### Community 44 - "renderExerciseList"
Cohesion: 0.33
Nodes (7): catsDrillInto(), catsFilterExercises(), getCanonicalMuscleGroup(), getExercisesForCategory(), getExerciseThumbUrl(), renderExerciseList(), toggleExerciseSelection()

### Community 47 - "MetodoPagoModel"
Cohesion: 0.27
Nodes (3): entEliminarMetodo(), entGuardarMetodo(), MetodoPagoModel

### Community 48 - "actualizarBotonPlantilla"
Cohesion: 0.40
Nodes (6): actualizarBotonPlantilla(), cancelarReordenarCompacto(), deleteExercise(), guardarNuevoOrdenCompacto(), initDragHandlers(), updateCanvasHeader()

### Community 49 - "renderModalDia"
Cohesion: 0.22
Nodes (11): abrirMenuOpcionesEjercicio(), abrirModalDia(), agregarPasoCardioInline(), asegurarPortalBody(), badgeSerieTexto(), badgeSerieTitulo(), cargarPasosCardioInline(), eliminarEjercicioEdicion() (+3 more)

### Community 50 - "assets/js/app.js"
Cohesion: 0.19
Nodes (9): clienteForm, generarHtmlSkeletonModulo(), loginForm, medidasForm, mostrarSkeletonTransicion(), obtenerModuloDestino(), regForm, scheduleSkeletonRemoval() (+1 more)

### Community 52 - "abrirModalReemplazarEjercicio"
Cohesion: 0.31
Nodes (9): abrirModalAgregarEjercicio(), abrirModalReemplazarEjercicio(), abrirSelectorTipoSerieInline(), abrirSheetPerfil(), obtenerCatalogoEjercicios(), pfBuildCategoryGrid(), pfCatsDrillBack(), pfCatsDrillInto() (+1 more)

### Community 58 - "AdminController.php"
Cohesion: 0.13
Nodes (27): adminApiActualizarRutinaCompleta(), adminApiCatalogoAgregar(), adminApiCatalogoBorrar(), adminApiCatalogoEditar(), adminApiCatalogoList(), adminApiCrearRutinaPropia(), adminApiDiaDetalle(), adminApiEliminarPlantillaCatalogo() (+19 more)

### Community 60 - "Product"
Cohesion: 0.18
Nodes (10): Brand Commitments, Capabilities and Constraints, Evidence on Hand, Operating Context, Platform, Positioning, Product, Product Principles (+2 more)

### Community 61 - "rutina.php"
Cohesion: 0.15
Nodes (20): SA_CATALOGO, SA_DIAS_META, SA_PLANTILLAS, saAgregarEjercicioBorrador(), saAgregarSerieBorrador(), saCargarDia(), saCargarPlantillaEnBorrador(), saConfirmarEliminarRutina() (+12 more)

### Community 62 - "gestion.php"
Cohesion: 0.08
Nodes (23): abrirModalAtletasEntrenador(), abrirModalEntrenador(), abrirModalFichaEntrenador(), aplicarFiltrosEntrenadores(), cerrarModalAtletasEntrenador(), cerrarModalEntrenador(), cerrarModalFichaEntrenador(), construirUrlReferido() (+15 more)

### Community 64 - "admin/configuracion.php"
Cohesion: 0.21
Nodes (12): actualizarVistaPreviaMensaje(), cambiarAlcanceMensaje(), cambiarTipoMensaje(), closePanel(), closePwdModal(), DEFAULT_MSGS, eliminarAtleta(), escCfgHtml() (+4 more)

### Community 65 - "media_storage.php"
Cohesion: 0.29
Nodes (15): cliApiSubirFoto(), comprimirImagenGD(), eliminarMediaPermanente(), ensureMediaTable(), esImagenAnimada(), guardarMediaPermanente(), intentarServirMediaRespaldado(), purgarComprobantesVencidos() (+7 more)

### Community 66 - "enhanceSelect"
Cohesion: 0.35
Nodes (12): boot(), buildDropdownNode(), createSvg(), enhanceAll(), enhanceSelect(), close(), open(), positionDropdown() (+4 more)

### Community 67 - "custom-date.js"
Cohesion: 0.33
Nodes (14): close(), createSvg(), enhanceOne(), ensurePopover(), formatDisplay(), formatIso(), initAll(), open() (+6 more)

### Community 68 - "views/entrenador/metodos_pago.php"
Cohesion: 0.53
Nodes (4): editMethod(), resetForm(), seleccionarBancoSelect(), toggleFields()

### Community 69 - "core/helpers.php"
Cohesion: 0.08
Nodes (34): adminApiLeerNotificaciones(), adminApiNotificaciones(), {closure#1}(), cliApiLeerNotificaciones(), cliApiNotificaciones(), cliNotificaciones(), cliRutinaLista(), entApiGenerarCodigo() (+26 more)

### Community 72 - "cfg_herramientas_ia_backup.php"
Cohesion: 0.16
Nodes (10): cancelarYVolverACargar(), cerrarStagingModal(), confirmarGuardadoCronologia(), contarSeriesTotales(), currentCatalogoEjercicios, dropzoneIA, escapeHtml(), iniciarAnalisisCronologiaIA() (+2 more)

### Community 74 - "cfg_paleta_colores.php"
Cohesion: 0.43
Nodes (5): actualizarUiAlcancePaletaAdmin(), actualizarUISelectorTema(), purgarHtmlCachePorCambioTema(), solicitarCambioTema(), toggleAlcancePaletaAdmin()

### Community 93 - "footer.php"
Cohesion: 0.40
Nodes (3): closeLegalModal(), guardarPreferenciasCookies(), LEGAL_CONTENTS

### Community 94 - "views/layout/header.php"
Cohesion: 0.32
Nodes (11): aprobarEntrenadorBtn(), closeXpNotifDropdown(), toggleXpNotifDropdown(), xpClickNotifItem(), xpEscapeHtml(), xpFetchNotificaciones(), xpMarcarTodoLeido(), xpNotifsCache (+3 more)

### Community 170 - "registrar_cliente.php"
Cohesion: 0.22
Nodes (11): actualizarAltura(), actualizarPeso(), ajustarAltura(), ajustarPeso(), clearErrors(), setError(), submitAtletaForm(), switchStep() (+3 more)

## Knowledge Gaps
- **55 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `getDB()` connect `controllers/ClienteController.php` to `CatalogoService`, `EntrenadorController.php`, `csrfCheck`, `progreso.php`, `UsuarioModel`, `.analizar`, `BackupService`, `ClienteModel`, `MetodoPagoModel`, `SesionAsistenciaModel`, `ObjetivoModel`, `PesoHistorialModel`, `RutinaModel`, `AdminController.php`, `PagoModel`, `media_storage.php`, `core/helpers.php`, `MetaModel`, `UsuarioSistemaModel`?**
  _High betweenness centrality (0.103) - this node is a cross-community bridge._
- **Are the 163 inferred relationships involving `getDB()` (e.g. with `env()` and `adminApiActualizarMembresia()`) actually correct?**
  _`getDB()` has 163 INFERRED edges - model-reasoned connections that need verification._
- **Should `controllers/ClienteController.php` be split into smaller, more focused modules?**
  _Cohesion score 0.06775956284153005 - nodes in this community are weakly interconnected._
- **Why does `PHPMailer` connect `PHPMailer` to `.mailSend`, `SMTP`, `.preSend`, `.strlen`, `.setError`?**
  _High betweenness centrality (0.041) - this node is a cross-community bridge._
- **Are the 100 inferred relationships involving `csrfCheck()` (e.g. with `adminApiActualizarMembresia()` and `adminApiActualizarRutinaCompleta()`) actually correct?**
  _`csrfCheck()` has 100 INFERRED edges - model-reasoned connections that need verification._
- **Should `PHPMailer` be split into smaller, more focused modules?**
  _Cohesion score 0.05723905723905724 - nodes in this community are weakly interconnected._
- **Why does `SMTP` connect `SMTP` to `SMTP.php`, `.mailSend`?**
  _High betweenness centrality (0.025) - this node is a cross-community bridge._