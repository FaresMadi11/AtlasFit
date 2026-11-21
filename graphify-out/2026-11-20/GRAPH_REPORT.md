# Graph Report - Xprod Fit  (2026-11-20)

## Corpus Check
- 125 files · ~323,522 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 14 file(s) not represented in the graph (top: (none) 7, .css 4, .conf 1)

## Summary
- 1444 nodes · 3237 edges · 114 communities (54 shown, 60 thin omitted)
- Extraction: 76% EXTRACTED · 24% INFERRED · 0% AMBIGUOUS · INFERRED: 763 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `54f0b05e`
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
- .substr
- enhanceSelect
- custom-date.js
- views/entrenador/metodos_pago.php
- core/helpers.php
- MetaModel
- UsuarioSistemaModel
- cfg_herramientas_ia_backup.php
- env
- cfg_paleta_colores.php
- views/cliente/modificar_rutina.php
- views/entrenador/clientes.php
- AuthController.php
- .strtoupper
- .addOrEnqueueAnAddress
- security.php
- .analizarHistorialAtleta
- footer.php
- views/layout/header.php
- registrar_cliente.php

## God Nodes (most connected - your core abstractions)
1. `getDB()` - 158 edges
2. `PHPMailer` - 131 edges
3. `csrfCheck()` - 97 edges
4. `jsonOut()` - 85 edges
5. `SMTP` - 46 edges
6. `SimpleXLSX` - 45 edges
7. `obtenerEstadoMembresiaEntrenador()` - 26 edges
8. `getJsonPayload()` - 25 edges
9. `Exception` - 24 edges
10. `UsuarioModel` - 23 edges

## Surprising Connections (you probably didn't know these)
- `entClientes()` --calls--> `getDB()`  [INFERRED]
  controllers/EntrenadorController.php → config/database.php
- `entMetodosPago()` --calls--> `getDB()`  [INFERRED]
  controllers/EntrenadorController.php → config/database.php
- `entPagosRecibidos()` --calls--> `getDB()`  [INFERRED]
  controllers/EntrenadorController.php → config/database.php
- `{closure#3}()` --calls--> `sanitizeInput()`  [INFERRED]
  controllers/AuthController.php → core/security.php
- `{closure#4}()` --calls--> `sanitizeInput()`  [INFERRED]
  controllers/AuthController.php → core/security.php

## Import Cycles
- None detected.

## Communities (114 total, 60 thin omitted)

### Community 0 - "controllers/ClienteController.php"
Cohesion: 0.07
Nodes (43): getDB(), cliApiCambiarPassword(), cliApiDiaDetalle(), cliApiEditarPerfil(), cliApiEditarPeso(), cliApiGuardarPregunta(), cliApiNuevoPeso(), cliApiPesoHistorial() (+35 more)

### Community 1 - "pwa-sync.js"
Cohesion: 0.35
Nodes (14): checkInstalledAsync(), dequeue(), desinstalarXprodApp(), enqueue(), ensurePillInDOM(), flushQueue(), getQueue(), initSW() (+6 more)

### Community 4 - "login.php"
Cohesion: 0.04
Nodes (37): boxMetaOtros, boxSubHipertrofia, btnPesoMinus, btnPesoPlus, btnRecovery, btnRulerMinus, btnRulerPlus, btnSkipGoals (+29 more)

### Community 9 - "crear_rutina.php"
Cohesion: 0.08
Nodes (24): actualizarNumerosPasosCardio(), cardioState, cargarDiasAtleta(), catalogoEjercicios, categoriasDB, consolidarPasosCardio(), EJERCICIOS_EDITAR, eliminadosEnReordenar (+16 more)

### Community 10 - "EntrenadorController.php"
Cohesion: 0.05
Nodes (38): cliModificarRutina(), cliRutinaLista(), calcularFirmaPlantilla(), entActualizarMedidas(), entApiCatalogoEditar(), entApiCatalogoList(), entApiDiaDetalle(), entApiEliminarRutina() (+30 more)

### Community 11 - "csrfCheck"
Cohesion: 0.11
Nodes (34): adminApiLeerNotificaciones(), adminApiMarcarNotificacion(), authDoRecuperarCambiar(), {closure#1}(), cliApiLeerNotificaciones(), cliApiMarcarNotificacion(), cliNotificaciones(), entApiActualizarRutinaCompleta() (+26 more)

### Community 12 - "views/cliente/estadisticas.php"
Cohesion: 0.08
Nodes (34): e(), abrirHistorialPeso(), abrirModalDia(), aplicarDatosHistorialEjercicio(), asegurarPortalBody(), badgeSerieTexto(), badgeSerieTitulo(), cambiarSerie() (+26 more)

### Community 13 - "sw.js"
Cohesion: 0.23
Nodes (8): getMetaUrl(), getScopeBase(), homePathForRole(), PRECACHE_ASSETS, readOfflineMeta(), resolveOfflineHtml(), TRUSTED_CDN_HOSTS, writeOfflineMeta()

### Community 14 - "submitRegistration"
Cohesion: 0.22
Nodes (9): clearRegErrors(), normalizarCedula(), postApi(), resetEmailVerificationUI(), setBtn(), setRegError(), submitRegistration(), switchView() (+1 more)

### Community 15 - "views/cliente/rutina_dia.php"
Cohesion: 0.07
Nodes (41): abrirModalAgregarEjercicioAtleta(), abrirModalConfirmarRevertir(), abrirModalPosponerRutina(), abrirModalReemplazoCliente(), actualizarBarraCambios(), actualizarBotonesModoEdicion(), actualizarEstadoEjercicio(), actualizarProgreso() (+33 more)

### Community 16 - "positionRegWheel"
Cohesion: 0.40
Nodes (5): ensureWheelPositioned(), initRegWheel(), positionRegWheel(), syncRegWheelDate(), updateRegWheelCol()

### Community 17 - "progreso.php"
Cohesion: 0.17
Nodes (20): cliApiProgresoEjercicios(), cliApiProgresoHistorial(), cliEstadisticas(), asegurarEjercicioEnCatalogo(), coincideGrupoMuscularProgreso(), construirSnapshotEstadisticasOffline(), inferirEquipamientoProgreso(), inferirGrupoMuscularProgreso() (+12 more)

### Community 18 - "views/cliente/perfil.php"
Cohesion: 0.11
Nodes (13): copiarDatosPago(), draw(), fbCopy(), init(), mostrarToast(), open(), resetSegModal(), scrollToWheelDate() (+5 more)

### Community 19 - "perfil_cliente.php"
Cohesion: 0.07
Nodes (26): abrirModalSelectorEjercicio(), actualizarCardioSummaryInline(), actualizarNumerosPasosCardioInline(), api(), cambiarMesCalendario(), currentBarrasPesos, currentGrupoEjercicios, currentTop3Sesiones (+18 more)

### Community 20 - "UsuarioModel"
Cohesion: 0.21
Nodes (7): authApiEnviarCodigoEmail(), authDoRegistro(), _validarDominioCorreoReal(), entGuardarCliente(), googleCallback(), sanitizeEmail(), UsuarioModel

### Community 21 - "entrenador/configuracion.php"
Cohesion: 0.18
Nodes (10): actualizarUiCodigoEntrenador(), closePanel(), closePwdModal(), eliminarAtleta(), enviarCambioCodigoEnt(), generarNuevoCodigoEntrenador(), guardarCodigoEntrenador(), mostrarErrorCodigoEnt() (+2 more)

### Community 22 - ".analizar"
Cohesion: 0.22
Nodes (5): adminApiImportarHistorialAnalizar(), adminApiImportarHistorialGuardar(), entApiImportarHistorialAnalizar(), entApiImportarHistorialGuardar(), ImportadorService

### Community 26 - "login.php"
Cohesion: 0.50
Nodes (5): animate(), drawFrame(), getActiveLoginThemeColors(), initParticles(), resize()

### Community 29 - "views/entrenador/asistencias.php"
Cohesion: 0.09
Nodes (43): abrirModalEjercicioAsistencias(), abrirOpcionesEjercicioDrawer(), abrirSolicitudRutinaAtleta(), actualizarBadges(), actualizarEjercicioEnCache(), actualizarTrackCarrusel(), aplicarDatosPanelesAsistencias(), asistenciasFechaCache (+35 more)

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
Nodes (36): adminApiActualizarMembresia(), adminApiActualizarRutinaCompleta(), adminApiCatalogoAgregar(), adminApiCatalogoBorrar(), adminApiCatalogoEditar(), adminApiCatalogoList(), adminApiCrearEntrenador(), adminApiCrearRutinaPropia() (+28 more)

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

### Community 65 - ".substr"
Cohesion: 0.23
Nodes (17): cliApiSubirFoto(), xpAppBuildVersion(), xpAppCacheName(), comprimirImagenGD(), eliminarMediaPermanente(), ensureMediaTable(), esImagenAnimada(), guardarMediaPermanente() (+9 more)

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
Nodes (29): adminApiAprobarEntrenador(), adminApiGenerarCodigo(), cliApiNotificaciones(), cliApiTiendaGet(), entApiGenerarCodigo(), entApiNotificaciones(), entApiTiendaGet(), entApiTiendaSecciones() (+21 more)

### Community 72 - "cfg_herramientas_ia_backup.php"
Cohesion: 0.16
Nodes (10): cancelarYVolverACargar(), cerrarStagingModal(), confirmarGuardadoCronologia(), contarSeriesTotales(), currentCatalogoEjercicios, dropzoneIA, escapeHtml(), iniciarAnalisisCronologiaIA() (+2 more)

### Community 73 - "env"
Cohesion: 0.26
Nodes (13): authGoogleRedirect(), getGoogleRedirectUri(), googleBuildSignedState(), googleDecodeIdToken(), googleExchangeCode(), googleHttpGet(), googleHttpPost(), googleOAuthIsHttps() (+5 more)

### Community 74 - "cfg_paleta_colores.php"
Cohesion: 0.60
Nodes (4): actualizarUiAlcancePaletaAdmin(), actualizarUISelectorTema(), solicitarCambioTema(), toggleAlcancePaletaAdmin()

### Community 78 - "AuthController.php"
Cohesion: 0.18
Nodes (12): authApiValidarCodigo(), authGoogleCallback(), authLogin(), authRecuperar(), authRegistro(), authRegistroError(), {closure#3}(), {closure#4}() (+4 more)

### Community 81 - ".strtoupper"
Cohesion: 0.29
Nodes (9): authApiVerificarCodigoEmail(), authDoLogin(), authDoRecuperarIdentificar(), authDoRecuperarVerificarRespuesta(), getClientIp(), rateLimitCheck(), _rateLimitDeny(), rateLimitFail() (+1 more)

### Community 83 - "security.php"
Cohesion: 0.27
Nodes (7): authLogout(), assertOwnership(), _denyAccess(), sanitizeId(), xpClearRememberCookie(), xpRestoreRememberSession(), xpSetRememberCookie()

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
- **60 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `getDB()` connect `controllers/ClienteController.php` to `EntrenadorController.php`, `csrfCheck`, `progreso.php`, `UsuarioModel`, `.analizar`, `ClienteModel`, `MetodoPagoModel`, `SesionAsistenciaModel`, `ObjetivoModel`, `PesoHistorialModel`, `RutinaModel`, `AdminController.php`, `PagoModel`, `.substr`, `core/helpers.php`, `MetaModel`, `UsuarioSistemaModel`, `env`, `.strtoupper`, `security.php`?**
  _High betweenness centrality (0.108) - this node is a cross-community bridge._
- **Are the 156 inferred relationships involving `getDB()` (e.g. with `env()` and `adminApiActualizarMembresia()`) actually correct?**
  _`getDB()` has 156 INFERRED edges - model-reasoned connections that need verification._
- **Should `controllers/ClienteController.php` be split into smaller, more focused modules?**
  _Cohesion score 0.07477288609364081 - nodes in this community are weakly interconnected._
- **Why does `PHPMailer` connect `PHPMailer` to `.mailSend`, `SMTP`, `.preSend`, `.strlen`, `.addOrEnqueueAnAddress`, `.msgHTML`, `.setError`?**
  _High betweenness centrality (0.053) - this node is a cross-community bridge._
- **Are the 94 inferred relationships involving `csrfCheck()` (e.g. with `adminApiActualizarMembresia()` and `adminApiActualizarRutinaCompleta()`) actually correct?**
  _`csrfCheck()` has 94 INFERRED edges - model-reasoned connections that need verification._
- **Should `PHPMailer` be split into smaller, more focused modules?**
  _Cohesion score 0.06090808416389812 - nodes in this community are weakly interconnected._
- **Why does `sendEmail()` connect `.addOrEnqueueAnAddress` to `PHPMailer`, `core/helpers.php`, `env`, `UsuarioModel`, `.setError`?**
  _High betweenness centrality (0.030) - this node is a cross-community bridge._