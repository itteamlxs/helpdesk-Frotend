<?php 
$titulo = 'Editar Ticket';
$jsModules = ['ticket-edit'];
ob_start(); 
?>

<div class="row mb-4">
    <div class="col-md-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="?ruta=dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="?ruta=tickets">Tickets</a></li>
                <li class="breadcrumb-item" id="breadcrumbTicket">Cargando...</li>
                <li class="breadcrumb-item active">Editar</li>
            </ol>
        </nav>
        <h1 class="h3" id="tituloTicket">
            <i class="fas fa-edit"></i> Editando Ticket...
        </h1>
        <p class="text-muted">Modifica la información del ticket de soporte</p>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group">
            <button class="btn btn-outline-secondary" onclick="volverAlTicket()">
                <i class="fas fa-eye"></i> Ver Ticket
            </button>
            <a href="?ruta=tickets" class="btn btn-outline-secondary">
                <i class="fas fa-list"></i> Lista Tickets
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Loading inicial -->
        <div class="card" id="loadingCard">
            <div class="card-body text-center py-5">
                <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                <h5>Cargando información del ticket...</h5>
            </div>
        </div>

        <!-- Formulario de edición -->
        <div class="card" id="formCard" style="display: none;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-edit"></i> Editar Información
                </h5>
                <div id="estadoTicket">
                    <!-- Badges de estado -->
                </div>
            </div>
            <div class="card-body">
                <form id="formEditarTicket">
                    <!-- Título -->
                    <div class="mb-3">
                        <label for="titulo" class="form-label">
                            <i class="fas fa-heading"></i> Título *
                        </label>
                        <input type="text" class="form-control" id="titulo" name="titulo" 
                               placeholder="Título del ticket..." required maxlength="255">
                        <div class="form-text">Máximo 255 caracteres</div>
                    </div>

                    <!-- Descripción -->
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">
                            <i class="fas fa-align-left"></i> Descripción Detallada *
                        </label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="6" 
                                  placeholder="Describe el problema en detalle..." required></textarea>
                    </div>

                    <div class="row">
                        <!-- Categoría -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="categoria" class="form-label">
                                    <i class="fas fa-tags"></i> Categoría *
                                </label>
                                <select class="form-select" id="categoria" name="categoria" required>
                                    <option value="">Selecciona una categoría</option>
                                    <option value="Hardware">🖥️ Hardware</option>
                                    <option value="Software">💻 Software</option>
                                    <option value="Redes">🌐 Redes e Internet</option>
                                    <option value="Email">📧 Email y Comunicaciones</option>
                                    <option value="Impresoras">🖨️ Impresoras</option>
                                    <option value="Accesos">🔐 Accesos y Permisos</option>
                                    <option value="Telefonia">📞 Telefonía</option>
                                    <option value="Seguridad">🛡️ Seguridad</option>
                                    <option value="Otros">📋 Otros</option>
                                </select>
                            </div>
                        </div>

                        <!-- Prioridad -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="prioridad" class="form-label">
                                    <i class="fas fa-exclamation-triangle"></i> Prioridad *
                                </label>
                                <select class="form-select" id="prioridad" name="prioridad" required>
                                    <option value="">Selecciona la prioridad</option>
                                    <option value="baja">🟢 Baja</option>
                                    <option value="media">🟡 Media</option>
                                    <option value="alta">🟠 Alta</option>
                                    <option value="urgente">🔴 Urgente</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Campos adicionales para técnicos/admins -->
                    <div id="camposAdmin" style="display: none;">
                        <hr>
                        <h6 class="text-muted mb-3">
                            <i class="fas fa-tools"></i> Gestión Técnica
                        </h6>
                        
                        <div class="row">
                            <!-- Estado -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="estado" class="form-label">
                                        <i class="fas fa-flag"></i> Estado
                                    </label>
                                    <select class="form-select" id="estado" name="estado">
                                        <option value="abierto">🔵 Abierto</option>
                                        <option value="en_proceso">🟡 En Proceso</option>
                                        <option value="en_espera">🟠 En Espera</option>
                                        <option value="cerrado">🟢 Cerrado</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Técnico asignado -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tecnico_id" class="form-label">
                                        <i class="fas fa-user-cog"></i> Técnico Asignado
                                    </label>
                                    <select class="form-select" id="tecnico_id" name="tecnico_id">
                                        <option value="">Sin asignar</option>
                                        <!-- Se llena dinámicamente -->
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Indicador de cambios -->
                    <div class="alert alert-warning d-none" id="cambiosPendientes">
                        <i class="fas fa-clock"></i> <strong>Cambios pendientes:</strong>
                        <span id="resumenCambios"></span>
                    </div>

                    <!-- Botones -->
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" onclick="volverAlTicket()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <div>
                            <button type="button" class="btn btn-outline-info me-2" onclick="resetearFormulario()">
                                <i class="fas fa-undo"></i> Resetear
                            </button>
                            <button type="submit" class="btn btn-success" id="btnGuardarCambios" disabled>
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Card de error -->
        <div class="card d-none" id="errorCard">
            <div class="card-body text-center py-5">
                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-3"></i>
                <h5>Error al cargar el ticket</h5>
                <p class="text-muted" id="mensajeError">No se pudo cargar la información del ticket.</p>
                <button class="btn btn-primary" onclick="window.location.reload()">
                    <i class="fas fa-refresh"></i> Reintentar
                </button>
            </div>
        </div>
    </div>

    <!-- Sidebar con información -->
    <div class="col-lg-4">
        <!-- Información del ticket -->
        <div class="card mb-3" id="infoCard" style="display: none;">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle"></i> Información del Ticket
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>ID:</strong> <span id="ticketId">#-</span>
                </div>
                <div class="mb-2">
                    <strong>Cliente:</strong> <span id="ticketCliente">-</span>
                </div>
                <div class="mb-2">
                    <strong>Creado:</strong> <span id="ticketCreado">-</span>
                </div>
                <div class="mb-2">
                    <strong>Última actualización:</strong> <span id="ticketActualizado">-</span>
                </div>
                <div class="mb-0">
                    <strong>Comentarios:</strong> <span id="totalComentarios">0</span>
                </div>
            </div>
        </div>

        <!-- Historial de cambios (simulado) -->
        <div class="card mb-3" id="historialCard" style="display: none;">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-history"></i> Historial Reciente
                </h6>
            </div>
            <div class="card-body">
                <div id="historialCambios">
                    <div class="d-flex align-items-start mb-2">
                        <i class="fas fa-plus-circle text-primary me-2 mt-1"></i>
                        <div>
                            <small class="text-muted">Ticket creado</small><br>
                            <small id="fechaCreacion">-</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="card" id="accionesCard" style="display: none;">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-bolt"></i> Acciones Rápidas
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary btn-sm" onclick="volverAlTicket()">
                        <i class="fas fa-eye"></i> Ver Ticket Completo
                    </button>
                    <button class="btn btn-outline-info btn-sm" onclick="window.location.href='?ruta=tickets'">
                        <i class="fas fa-list"></i> Lista de Tickets
                    </button>
                    <button class="btn btn-outline-success btn-sm" id="btnVistaPrevia" onclick="mostrarVistaPrevia()">
                        <i class="fas fa-search"></i> Vista Previa
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de vista previa -->
<div class="modal fade" id="vistaPreviaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-search"></i> Vista Previa de Cambios
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">Valores Actuales</h6>
                        <div id="valoresActuales"></div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Valores Nuevos</h6>
                        <div id="valoresNuevos"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cerrar
                </button>
                <button type="button" class="btn btn-success" onclick="guardarCambiosDesdeModal()">
                    <i class="fas fa-save"></i> Confirmar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación -->
<div class="modal fade" id="confirmacionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle text-success"></i> Cambios Guardados
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Los cambios han sido guardados exitosamente.</p>
                <div class="alert alert-success" id="resumenCambiosGuardados">
                    <!-- Se llena dinámicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Continuar Editando
                </button>
                <button type="button" class="btn btn-primary" onclick="volverAlTicket()">
                    <i class="fas fa-eye"></i> Ver Ticket
                </button>
            </div>
        </div>
    </div>
</div>

<?php 
$contenido = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>