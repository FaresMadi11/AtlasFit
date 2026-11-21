# Graph Report - Xprod Fit  (2026-11-20)

## Corpus Check
- 125 files · ~329,295 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 14 file(s) not represented in the graph (top: (none) 7, .css 4, .conf 1)

## Summary
- 1469 nodes · 3305 edges · 106 communities (51 shown, 55 thin omitted)
- Extraction: 77% EXTRACTED · 23% INFERRED · 0% AMBIGUOUS · INFERRED: 772 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `c7abcbb5`
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
- clearRecoveryErrors
- login.php
- DocumentParser
- views/entrenador/asistencias.php
- renderizarRutinaInline
- aplicarHistorialEjercicioEnt
- assets/js/cookies.js
- xpUpdateSwipeHeight
- TradingWeightChart
- .mailSend
- closeSheet
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
- .addOrEnqueueAnAddress
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
- footer.php
- views/layout/header.php
- registrar_cliente.php

## God Nodes (most connected - your core abstractions)
1. `getDB()` - 157 edges
2. `PHPMailer` - 131 edges
3. `csrfCheck()` - 97 edges
4. `jsonOut()` - 86 edges
5. `SMTP` - 46 edges
6. `SimpleXLSX` - 45 edges
7. `obtenerEstadoMembresiaEntrenador()` - 26 edges
8. `getJsonPayload()` - 25 edges
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

## Communities (106 total, 55 thin omitted)

### Community 0 - "controllers/ClienteController.php"
Cohesion: 0.07
Nodes (45): getDB(), cliApiCambiarPassword(), cliApiDiaDetalle(), cliApiEditarPerfil(), cliApiEditarPeso(), cliApiGuardarMeta(), cliApiGuardarPregunta(), cliApiNuevoPeso() (+37 more)

### Community 1 - "pwa-sync.js"
Cohesion: 0.35
Nodes (14): checkInstalledAsync(), dequeue(), desinstalarXprodApp(), enqueue(), ensurePillInDOM(), flushQueue(), getQueue(), initSW() (+6 more)

### Community 4 - "login.php"
Cohesion: 0.04
Nodes (37): boxMetaOtros, boxSubHipertrofia, btnPesoMinus, btnPesoPlus, btnRecovery, btnRulerMinus, btnRulerPlus, btnSkipGoals (+29 more)

### Community 5 - "SimpleXLSX"
Cohesion: 0.06
Nodes (20): adminSanitizarCodigoReferido(), cliApiSubirFoto(), generarCodigoEntrenadorUnico(), xpAppBuildVersion(), xpAppCacheName(), comprimirImagenGD(), eliminarMediaPermanente(), ensureMediaTable() (+12 more)

### Community 6 - "CatalogoService"
Cohesion: 0.21
Nodes (10): adminApiActualizarRutinaCompleta(), adminApiCrearRutinaPropia(), entApiActualizarRutinaCompleta(), entApiCatalogoList(), entApiRutinaEjercicio(), CatalogoService, asegurarEjercicioEnCatalogo(), construirSnapshotEstadisticasOffline() (+2 more)

### Community 9 - "crear_rutina.php"
Cohesion: 0.08
Nodes (24): actualizarNumerosPasosCardio(), cardioState, cargarDiasAtleta(), catalogoEjercicios, categoriasDB, consolidarPasosCardio(), EJERCICIOS_EDITAR, eliminadosEnReordenar (+16 more)

### Community 10 - "EntrenadorController.php"
Cohesion: 0.05
Nodes (42): {closure#1}(), cliRutinaLista(), calcularFirmaPlantilla(), entApiCambiarGeneroCliente(), entApiClienteMedidas(), entApiEliminarPlantillaCatalogo(), entApiEliminarRutina(), entApiGuardarClienteMedidas() (+34 more)

### Community 11 - "csrfCheck"
Cohesion: 0.12
Nodes (31): adminApiLeerNotificaciones(), authDoRecuperarCambiar(), cliApiLeerNotificaciones(), cliGuardarModificacionDirecta(), cliSolicitarModificacionAtleta(), entActualizarMedidas(), entApiAprobarAtleta(), entApiCatalogoAgregar() (+23 more)

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
Cohesion: 0.19
Nodes (13): entApiDiaDetalle(), coincideGrupoMuscularProgreso(), normalizarTextoProgreso(), obtenerDetalleDia(), obtenerEjerciciosProgramados(), obtenerEstadoDiasSemanaAtleta(), obtenerEstadoDiasSemanaAtletasBatch(), obtenerFechasEntrenadasMes() (+5 more)

### Community 18 - "views/cliente/perfil.php"
Cohesion: 0.11
Nodes (13): copiarDatosPago(), draw(), fbCopy(), init(), mostrarToast(), open(), resetSegModal(), scrollToWheelDate() (+5 more)

### Community 19 - "perfil_cliente.php"
Cohesion: 0.07
Nodes (26): abrirModalSelectorEjercicio(), actualizarCardioSummaryInline(), actualizarNumerosPasosCardioInline(), api(), cambiarMesCalendario(), currentBarrasPesos, currentGrupoEjercicios, currentTop3Sesiones (+18 more)

### Community 20 - "UsuarioModel"
Cohesion: 0.05
Nodes (52): authApiEnviarCodigoEmail(), authApiValidarCodigo(), authApiVerificarCodigoEmail(), authDoLogin(), authDoRecuperarIdentificar(), authDoRecuperarVerificarRespuesta(), authDoRegistro(), authGoogleCallback() (+44 more)

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

### Community 36 - "closeSheet"
Cohesion: 0.22
Nodes (10): actualizarCardioSummary(), cargarPlantillaEnRutina(), closeSheet(), deleteSerie(), initSortableCompacto(), onCustomCardioMin(), setCardioMinutos(), setSerieType() (+2 more)

### Community 37 - "addExercise"
Cohesion: 0.31
Nodes (10): actualizarHintMancuerna(), addExercise(), addSet(), cambiarImplemento(), cambiarUnidad(), cerrarWheelPicker(), copiarValoresATodasLasSeries(), initWheelEvents() (+2 more)

### Community 38 - "openSheet"
Cohesion: 0.31
Nodes (9): abrirWheelPicker(), actualizarWheelActivo(), catsDrillBack(), confirmSelection(), openCategorySheet(), openExOptions(), openSheet(), replaceExercise() (+1 more)

### Community 39 - "abrirSheetPlantillas"
Cohesion: 0.25
Nodes (8): abrirSheetPlantillas(), agregarPasoCardio(), cargarPasosCardio(), eliminarPlantillaGuardada(), escapeHtmlAttr(), escapeJs(), handlePlantillaClick(), renderPlantillasList()

### Community 40 - "views/auth/recuperar.php"
Cohesion: 0.40
Nodes (8): animateTransition(), procesarPaso1(), procesarPaso2(), procesarPaso3(), procesarPaso4(), setButtonLoading(), showAlert(), volverPaso()

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
Cohesion: 0.12
Nodes (34): adminApiActualizarMembresia(), adminApiCatalogoAgregar(), adminApiCatalogoBorrar(), adminApiCatalogoEditar(), adminApiCatalogoList(), adminApiCrearEntrenador(), adminApiDiaDetalle(), adminApiEliminarRutina() (+26 more)

### Community 60 - "Product"
Cohesion: 0.18
Nodes (10): Brand Commitments, Capabilities and Constraints, Evidence on Hand, Operating Context, Platform, Positioning, Product, Product Principles (+2 more)

### Community 61 - "rutina.php"
Cohesion: 0.16
Nodes (22): SA_CATALOGO, SA_DIAS_META, SA_PLANTILLAS, saAbrirCrearRutina(), saActivarModoEdicion(), saAgregarEjercicioBorrador(), saAgregarSerieBorrador(), saCargarDia() (+14 more)

### Community 62 - "gestion.php"
Cohesion: 0.09
Nodes (20): abrirModalAtletasEntrenador(), abrirModalEntrenador(), abrirModalFichaEntrenador(), aplicarFiltrosEntrenadores(), cerrarModalAtletasEntrenador(), cerrarModalEntrenador(), cerrarModalFichaEntrenador(), construirUrlReferido() (+12 more)

### Community 64 - "admin/configuracion.php"
Cohesion: 0.47
Nodes (4): actualizarVistaPreviaMensaje(), cambiarAlcanceMensaje(), cambiarTipoMensaje(), DEFAULT_MSGS

### Community 66 - "enhanceSelect"
Cohesion: 0.35
Nodes (12): boot(), buildDropdownNode(), createSvg(), enhanceAll(), enhanceSelect(), close(), open(), positionDropdown() (+4 more)

### Community 67 - "custom-date.js"
Cohesion: 0.33
Nodes (14): close(), createSvg(), enhanceOne(), ensurePopover(), formatDisplay(), formatIso(), initAll(), open() (+6 more)

### Community 68 - "views/entrenador/metodos_pago.php"
Cohesion: 0.60
Nodes (3): editMethod(), resetForm(), toggleFields()

### Community 69 - "core/helpers.php"
Cohesion: 0.12
Nodes (23): adminApiAprobarEntrenador(), adminApiMarcarNotificacion(), adminApiNotificaciones(), cliApiMarcarNotificacion(), cliApiNotificaciones(), cliNotificaciones(), entApiMarcarNotificacion(), entApiNotificaciones() (+15 more)

### Community 72 - "cfg_herramientas_ia_backup.php"
Cohesion: 0.16
Nodes (10): cancelarYVolverACargar(), cerrarStagingModal(), confirmarGuardadoCronologia(), contarSeriesTotales(), currentCatalogoEjercicios, dropzoneIA, escapeHtml(), iniciarAnalisisCronologiaIA() (+2 more)

### Community 74 - "cfg_paleta_colores.php"
Cohesion: 0.60
Nodes (4): actualizarUiAlcancePaletaAdmin(), actualizarUISelectorTema(), solicitarCambioTema(), toggleAlcancePaletaAdmin()

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

- **Why does `getDB()` connect `controllers/ClienteController.php` to `core/helpers.php`, `CatalogoService`, `SimpleXLSX`, `MetaModel`, `UsuarioSistemaModel`, `EntrenadorController.php`, `csrfCheck`, `ClienteModel`, `MetodoPagoModel`, `progreso.php`, `SesionAsistenciaModel`, `UsuarioModel`, `.analizar`, `ObjetivoModel`, `PesoHistorialModel`, `RutinaModel`, `AdminController.php`, `PagoModel`?**
  _High betweenness centrality (0.105) - this node is a cross-community bridge._
- **Are the 155 inferred relationships involving `getDB()` (e.g. with `env()` and `adminApiActualizarMembresia()`) actually correct?**
  _`getDB()` has 155 INFERRED edges - model-reasoned connections that need verification._
- **Should `controllers/ClienteController.php` be split into smaller, more focused modules?**
  _Cohesion score 0.06948051948051948 - nodes in this community are weakly interconnected._
- **Why does `PHPMailer` connect `PHPMailer` to `.addOrEnqueueAnAddress`, `.mailSend`, `SMTP`, `SimpleXLSX`, `.preSend`, `.createBody`, `.setError`?**
  _High betweenness centrality (0.053) - this node is a cross-community bridge._
- **Are the 94 inferred relationships involving `csrfCheck()` (e.g. with `adminApiActualizarMembresia()` and `adminApiActualizarRutinaCompleta()`) actually correct?**
  _`csrfCheck()` has 94 INFERRED edges - model-reasoned connections that need verification._
- **Should `PHPMailer` be split into smaller, more focused modules?**
  _Cohesion score 0.06341463414634146 - nodes in this community are weakly interconnected._
- **Why does `sendEmail()` connect `.addOrEnqueueAnAddress` to `.setError`, `PHPMailer`, `UsuarioModel`, `core/helpers.php`?**
  _High betweenness centrality (0.034) - this node is a cross-community bridge._