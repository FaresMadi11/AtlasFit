# Graph Report - Xprod Fit  (2026-11-12)

## Corpus Check
- 124 files · ~309,689 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 13 file(s) not represented in the graph (top: (none) 7, .css 3, .conf 1)

## Summary
- 1385 nodes · 3069 edges · 106 communities (50 shown, 56 thin omitted)
- Extraction: 76% EXTRACTED · 24% INFERRED · 0% AMBIGUOUS · INFERRED: 734 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `7cdc257c`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- controllers/ClienteController.php
- pwa-sync.js
- PHPMailer
- SMTP
- login.php
- SimpleXLSX
- progreso.php
- crear_rutina.php
- EntrenadorController.php
- csrfCheck
- views/cliente/estadisticas.php
- sw.js
- submitRegistration
- views/cliente/rutina_dia.php
- positionRegWheel
- guardarMediaPermanente
- views/cliente/perfil.php
- perfil_cliente.php
- AuthController.php
- entrenador/configuracion.php
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
- seleccionarGrupo
- ObjetivoModel
- PesoHistorialModel
- RutinaModel
- AdminController.php
- Product
- rutina.php
- gestion.php
- PagoModel
- admin/configuracion.php
- enhanceSelect
- CatalogoService
- views/entrenador/metodos_pago.php
- core/helpers.php
- MetaModel
- UsuarioSistemaModel
- cfg_herramientas_ia_backup.php
- redirect
- cfg_paleta_colores.php
- views/cliente/modificar_rutina.php
- views/entrenador/clientes.php
- footer.php
- views/layout/header.php
- registrar_cliente.php

## God Nodes (most connected - your core abstractions)
1. `getDB()` - 159 edges
2. `PHPMailer` - 131 edges
3. `csrfCheck()` - 96 edges
4. `jsonOut()` - 84 edges
5. `SMTP` - 46 edges
6. `SimpleXLSX` - 45 edges
7. `obtenerEstadoMembresiaEntrenador()` - 26 edges
8. `getJsonPayload()` - 24 edges
9. `Exception` - 24 edges
10. `UsuarioModel` - 22 edges

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

## Communities (106 total, 56 thin omitted)

### Community 0 - "controllers/ClienteController.php"
Cohesion: 0.08
Nodes (39): getDB(), cliApiCambiarPassword(), cliApiDiaDetalle(), cliApiEditarPerfil(), cliApiEditarPeso(), cliApiGuardarMeta(), cliApiGuardarPregunta(), cliApiNuevoPeso() (+31 more)

### Community 1 - "pwa-sync.js"
Cohesion: 0.37
Nodes (13): checkInstalledAsync(), dequeue(), desinstalarXprodApp(), enqueue(), ensurePillInDOM(), flushQueue(), getQueue(), initSW() (+5 more)

### Community 2 - "PHPMailer"
Cohesion: 0.06
Nodes (3): sendEmail(), _sendEmailViaHttpsApi(), PHPMailer

### Community 4 - "login.php"
Cohesion: 0.04
Nodes (37): boxMetaOtros, boxSubHipertrofia, btnPesoMinus, btnPesoPlus, btnRecovery, btnRulerMinus, btnRulerPlus, btnSkipGoals (+29 more)

### Community 6 - "progreso.php"
Cohesion: 0.21
Nodes (17): asegurarEjercicioEnCatalogo(), coincideGrupoMuscularProgreso(), construirSnapshotEstadisticasOffline(), inferirEquipamientoProgreso(), inferirGrupoMuscularProgreso(), normalizarTextoProgreso(), obtenerDetalleDia(), obtenerEjerciciosProgramados() (+9 more)

### Community 9 - "crear_rutina.php"
Cohesion: 0.08
Nodes (24): actualizarNumerosPasosCardio(), cardioState, cargarDiasAtleta(), catalogoEjercicios, categoriasDB, consolidarPasosCardio(), EJERCICIOS_EDITAR, eliminadosEnReordenar (+16 more)

### Community 10 - "EntrenadorController.php"
Cohesion: 0.06
Nodes (33): calcularFirmaPlantilla(), entApiCatalogoAgregar(), entApiCatalogoBorrar(), entApiCatalogoEditar(), entApiCatalogoList(), entApiClienteMedidas(), entApiDiaDetalle(), entApiGuardarMensualidad() (+25 more)

### Community 11 - "csrfCheck"
Cohesion: 0.10
Nodes (37): adminApiCatalogoAgregar(), adminApiCatalogoBorrar(), adminApiCatalogoEditar(), adminApiLeerNotificaciones(), adminDescargarRespaldoDb(), authDoRecuperarCambiar(), cliIniciarRutina(), cliMarcarRutina() (+29 more)

### Community 12 - "views/cliente/estadisticas.php"
Cohesion: 0.08
Nodes (34): e(), abrirHistorialPeso(), abrirModalDia(), aplicarDatosHistorialEjercicio(), asegurarPortalBody(), badgeSerieTexto(), badgeSerieTitulo(), cambiarSerie() (+26 more)

### Community 14 - "submitRegistration"
Cohesion: 0.25
Nodes (8): clearRegErrors(), normalizarCedula(), postApi(), resetEmailVerificationUI(), setBtn(), setRegError(), submitRegistration(), switchView()

### Community 15 - "views/cliente/rutina_dia.php"
Cohesion: 0.07
Nodes (41): abrirModalAgregarEjercicioAtleta(), abrirModalConfirmarRevertir(), abrirModalPosponerRutina(), abrirModalReemplazoCliente(), actualizarBarraCambios(), actualizarBotonesModoEdicion(), actualizarEstadoEjercicio(), actualizarProgreso() (+33 more)

### Community 16 - "positionRegWheel"
Cohesion: 0.40
Nodes (5): ensureWheelPositioned(), initRegWheel(), positionRegWheel(), syncRegWheelDate(), updateRegWheelCol()

### Community 17 - "guardarMediaPermanente"
Cohesion: 0.28
Nodes (11): cliApiSubirFoto(), cliGuardarPago(), entApiTiendaAgregar(), entApiTiendaBorrar(), entApiTiendaEditar(), comprimirImagenGD(), eliminarMediaPermanente(), ensureMediaTable() (+3 more)

### Community 18 - "views/cliente/perfil.php"
Cohesion: 0.11
Nodes (13): copiarDatosPago(), draw(), fbCopy(), init(), mostrarToast(), open(), resetSegModal(), scrollToWheelDate() (+5 more)

### Community 19 - "perfil_cliente.php"
Cohesion: 0.07
Nodes (26): abrirModalSelectorEjercicio(), actualizarCardioSummaryInline(), actualizarNumerosPasosCardioInline(), api(), cambiarMesCalendario(), currentBarrasPesos, currentGrupoEjercicios, currentTop3Sesiones (+18 more)

### Community 20 - "AuthController.php"
Cohesion: 0.06
Nodes (46): authApiEnviarCodigoEmail(), authApiValidarCodigo(), authApiVerificarCodigoEmail(), authDoLogin(), authDoRecuperarIdentificar(), authDoRecuperarVerificarRespuesta(), authDoRegistro(), authGoogleCallback() (+38 more)

### Community 21 - "entrenador/configuracion.php"
Cohesion: 0.18
Nodes (10): actualizarUiCodigoEntrenador(), closePanel(), closePwdModal(), eliminarAtleta(), enviarCambioCodigoEnt(), generarNuevoCodigoEntrenador(), guardarCodigoEntrenador(), mostrarErrorCodigoEnt() (+2 more)

### Community 26 - "login.php"
Cohesion: 0.50
Nodes (5): animate(), drawFrame(), getActiveLoginThemeColors(), initParticles(), resize()

### Community 28 - "DocumentParser"
Cohesion: 0.11
Nodes (4): adminApiImportarHistorialAnalizar(), DocumentParser, GeminiService, ImportadorService

### Community 29 - "views/entrenador/asistencias.php"
Cohesion: 0.09
Nodes (42): abrirModalEjercicioAsistencias(), abrirOpcionesEjercicioDrawer(), abrirSolicitudRutinaAtleta(), actualizarBadges(), actualizarEjercicioEnCache(), actualizarTrackCarrusel(), aplicarDatosPanelesAsistencias(), asistenciasFechaCache (+34 more)

### Community 30 - "renderizarRutinaInline"
Cohesion: 0.17
Nodes (16): abrirMenuOpcionesEjercicio(), abrirModalAgregarEjercicio(), abrirModalReemplazarEjercicio(), activarModoEdicionRutina(), agregarPasoCardioInline(), agregarSerieEdicion(), cambiarImplementoEdicion(), cambiarUnidadEdicion() (+8 more)

### Community 31 - "aplicarHistorialEjercicioEnt"
Cohesion: 0.22
Nodes (11): renderDiasMiniBarPhp(), aplicarHistorialEjercicioEnt(), cambiarSerie(), cerrarModalSelectorEjercicio(), renderBotonesGrupoEnt(), renderChartEjercicio(), renderPodioTop3(), renderSummary() (+3 more)

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
Cohesion: 0.40
Nodes (6): abrirModalDia(), asegurarPortalBody(), badgeSerieTexto(), badgeSerieTitulo(), formatoFechaLarga(), renderModalDia()

### Community 50 - "assets/js/app.js"
Cohesion: 0.19
Nodes (9): clienteForm, generarHtmlSkeletonModulo(), loginForm, medidasForm, mostrarSkeletonTransicion(), obtenerModuloDestino(), regForm, scheduleSkeletonRemoval() (+1 more)

### Community 52 - "seleccionarGrupo"
Cohesion: 0.50
Nodes (4): cambiarTren(), initGraficaPeso(), initStatsDashboard(), seleccionarGrupo()

### Community 58 - "AdminController.php"
Cohesion: 0.18
Nodes (26): adminApiActualizarMembresia(), adminApiActualizarRutinaCompleta(), adminApiCrearEntrenador(), adminApiCrearRutinaPropia(), adminApiDiaDetalle(), adminApiEliminarRutina(), adminApiGenerarCodigo(), adminApiGuardarConfiguracion() (+18 more)

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

### Community 68 - "views/entrenador/metodos_pago.php"
Cohesion: 0.60
Nodes (3): editMethod(), resetForm(), toggleFields()

### Community 69 - "core/helpers.php"
Cohesion: 0.10
Nodes (33): adminApiNotificaciones(), cliApiLeerNotificaciones(), cliApiMarcarNotificacion(), cliApiNotificaciones(), cliNotificaciones(), entApiGenerarCodigo(), entApiLeerNotificaciones(), entApiMarcarNotificacion() (+25 more)

### Community 72 - "cfg_herramientas_ia_backup.php"
Cohesion: 0.16
Nodes (10): cancelarYVolverACargar(), cerrarStagingModal(), confirmarGuardadoCronologia(), contarSeriesTotales(), currentCatalogoEjercicios, dropzoneIA, escapeHtml(), iniciarAnalisisCronologiaIA() (+2 more)

### Community 73 - "redirect"
Cohesion: 0.22
Nodes (9): {closure#1}(), cliRutinaLista(), entAsignarRutina(), entEstadisticasCliente(), entPerfilCliente(), entRevisarModificacion(), redirect(), requireLogin() (+1 more)

### Community 74 - "cfg_paleta_colores.php"
Cohesion: 0.60
Nodes (4): actualizarUiAlcancePaletaAdmin(), actualizarUISelectorTema(), solicitarCambioTema(), toggleAlcancePaletaAdmin()

### Community 93 - "footer.php"
Cohesion: 0.40
Nodes (3): closeLegalModal(), guardarPreferenciasCookies(), LEGAL_CONTENTS

### Community 94 - "views/layout/header.php"
Cohesion: 0.35
Nodes (10): closeXpNotifDropdown(), toggleXpNotifDropdown(), xpClickNotifItem(), xpEscapeHtml(), xpFetchNotificaciones(), xpMarcarTodoLeido(), xpNotifsCache, xpPositionDropdown() (+2 more)

### Community 170 - "registrar_cliente.php"
Cohesion: 0.22
Nodes (11): actualizarAltura(), actualizarPeso(), ajustarAltura(), ajustarPeso(), clearErrors(), setError(), submitAtletaForm(), switchStep() (+3 more)

## Knowledge Gaps
- **56 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `getDB()` connect `controllers/ClienteController.php` to `CatalogoService`, `core/helpers.php`, `MetaModel`, `UsuarioSistemaModel`, `redirect`, `EntrenadorController.php`, `csrfCheck`, `ClienteModel`, `MetodoPagoModel`, `guardarMediaPermanente`, `SesionAsistenciaModel`, `AuthController.php`, `ObjetivoModel`, `PesoHistorialModel`, `RutinaModel`, `AdminController.php`, `DocumentParser`, `PagoModel`?**
  _High betweenness centrality (0.110) - this node is a cross-community bridge._
- **Are the 157 inferred relationships involving `getDB()` (e.g. with `env()` and `adminApiActualizarMembresia()`) actually correct?**
  _`getDB()` has 157 INFERRED edges - model-reasoned connections that need verification._
- **Should `controllers/ClienteController.php` be split into smaller, more focused modules?**
  _Cohesion score 0.0783673469387755 - nodes in this community are weakly interconnected._
- **Why does `PHPMailer` connect `PHPMailer` to `.mailSend`, `SMTP`, `.strlen`, `.preSend`, `.setError`?**
  _High betweenness centrality (0.057) - this node is a cross-community bridge._
- **Are the 93 inferred relationships involving `csrfCheck()` (e.g. with `adminApiActualizarMembresia()` and `adminApiActualizarRutinaCompleta()`) actually correct?**
  _`csrfCheck()` has 93 INFERRED edges - model-reasoned connections that need verification._
- **Should `PHPMailer` be split into smaller, more focused modules?**
  _Cohesion score 0.055218855218855216 - nodes in this community are weakly interconnected._
- **Why does `sendEmail()` connect `PHPMailer` to `.setError`, `SMTP`, `AuthController.php`, `core/helpers.php`?**
  _High betweenness centrality (0.033) - this node is a cross-community bridge._