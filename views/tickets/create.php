<?php 
$titulo = 'Crear Nuevo Ticket';
$jsModules = ['ticket-create'];
ob_start(); 
?>

<div class="row mb-4">
    <div class="col-md-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="?ruta=dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="?ruta=tickets">Tickets</a></li>
                <li class="breadcrumb-item active">Crear Ticket</li>
            </ol>
        </nav>
        <h1 class="h3">
            <i class="fas fa-plus-circle"></i> Crear Nuevo Ticket
        </h1>
        <p class="text-muted">Completa la información para crear un nuevo ticket de soporte</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="?ruta=tickets" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Tickets
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Formulario principal -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-edit"></i> Información del Ticket
                </h5>
            </div>
            <div class="card-body">
                <form id="formCrearTicket">
                    <!-- Título -->
                    <div class="mb-3">
                        <label for="titulo" class="form-label">
                            <i class="fas fa-heading"></i> Título *
                        </label>
                        <input type="text" class="form-control" id="titulo" name="titulo" 
                               placeholder="Describe brevemente el problema..." required maxlength="255">
                        <div class="form-text">Máximo 255 caracteres</div>
                    </div>

                    <!-- Descripción -->
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">
                            <i class="fas fa-align-left"></i> Descripción Detallada *
                        </label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="6" 
                                  placeholder="Describe el problema en detalle, pasos para reproducirlo, mensajes de error, etc..." required></textarea>
                        <div class="form-text">Proporciona todos los detalles posibles para facilitar la resolución</div>
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
                                    <option value="baja">🟢 Baja - No es urgente</option>
                                    <option value="media" selected>🟡 Media - Impacto moderado</option>
                                    <option value="alta">🟠 Alta - Impacto significativo</option>
                                    <option value="urgente">🔴 Urgente - Sistema crítico afectado</option>
                                </select>
                                <div class="form-text">
                                    <small>
                                        <strong>Baja:</strong> Solicitudes generales, mejoras<br>
                                        <strong>Media:</strong> Problemas que afectan el trabajo pero hay alternativas<br>
                                        <strong>Alta:</strong> Problemas que impiden el trabajo normal<br>
                                        <strong>Urgente:</strong> Sistemas críticos fuera de servicio
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Solo técnicos/admins pueden crear tickets para otros usuarios -->
                    <div class="mb-3" id="selectorCliente" style="display: none;">
                        <label for="cliente_id" class="form-label">
                            <i class="fas fa-user"></i> Cliente (Para quien es el ticket)
                        </label>
                        <select class="form-select" id="cliente_id" name="cliente_id">
                            <option value="">Selecciona un cliente</option>
                            <!-- Se llena dinámicamente -->
                        </select>
                        <div class="form-text">Solo técnicos y administradores pueden crear tickets para otros usuarios</div>
                    </div>

                    <!-- Botones -->
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='?ruta=tickets'">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnCrearTicket">
                            <i class="fas fa-save"></i> Crear Ticket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar con información y ayuda -->
    <div class="col-lg-4">
        <!-- Información de SLA -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-clock"></i> Tiempos de Respuesta (SLA)
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <div class="h6 text-danger">1h</div>
                            <small class="text-muted">Urgente</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="h6 text-warning">4h</div>
                        <small class="text-muted">Alta</small>
                    </div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <div class="h6 text-info">12h</div>
                            <small class="text-muted">Media</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="h6 text-secondary">24h</div>
                        <small class="text-muted">Baja</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Consejos -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-lightbulb"></i> Consejos para un Mejor Soporte
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i>
                        <strong>Sé específico:</strong> Incluye mensajes de error exactos
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i>
                        <strong>Pasos para reproducir:</strong> Cómo llegaste al problema
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i>
                        <strong>Contexto:</strong> ¿Cuándo empezó? ¿Cambió algo?
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i>
                        <strong>Urgencia real:</strong> Usa la prioridad adecuada
                    </li>
                </ul>
            </div>
        </div>

        <!-- Estado del usuario -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-user-circle"></i> Tu Información
                </h6>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Usuario:</strong> 
                    <span id="nombreUsuario"><?= $_SESSION['usuario']['nombre'] ?? 'Usuario' ?></span>
                </p>
                <p class="mb-2">
                    <strong>Rol:</strong> 
                    <span id="rolUsuario" class="badge bg-primary">
                        <?php 
                        $rol = $_SESSION['usuario']['rol_id'] ?? 1;
                        echo $rol == 1 ? 'Cliente' : ($rol == 2 ? 'Técnico' : 'Administrador');
                        ?>
                    </span>
                </p>
                <p class="mb-0 text-muted">
                    <small>
                        <i class="fas fa-info-circle"></i>
                        El ticket se creará a tu nombre automáticamente
                    </small>
                </p>
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
                    <i class="fas fa-check-circle text-success"></i> Ticket Creado
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Tu ticket ha sido creado exitosamente.</p>
                <div class="alert alert-info">
                    <strong>Número de Ticket:</strong> <span id="ticketNumero">#0</span><br>
                    <strong>Estado:</strong> Abierto<br>
                    <strong>Prioridad:</strong> <span id="ticketPrioridad">-</span>
                </div>
                <p class="text-muted">
                    <i class="fas fa-bell"></i> 
                    Recibirás notificaciones sobre el progreso de tu ticket.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Crear Otro Ticket
                </button>
                <button type="button" class="btn btn-primary" id="btnVerTicket">
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