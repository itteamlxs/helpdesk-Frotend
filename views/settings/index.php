<?php 
$titulo = 'Configuración del Sistema';
$jsModules = ['settings'];
ob_start(); 
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3">
            <i class="fas fa-cog"></i> Configuración del Sistema
        </h1>
        <p class="text-muted">Administra las configuraciones generales del helpdesk</p>
    </div>
    <div class="col-md-4 text-end">
        <button class="btn btn-success" onclick="guardarConfiguracion()" id="btnGuardarConfig">
            <i class="fas fa-save"></i> Guardar Cambios
        </button>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Configuración General -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-building"></i> Información de la Empresa
                </h5>
            </div>
            <div class="card-body">
                <form id="formConfiguracion">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nombre_empresa" class="form-label">
                                    <i class="fas fa-building"></i> Nombre de la Empresa *
                                </label>
                                <input type="text" class="form-control" id="nombre_empresa" name="nombre_empresa" 
                                       placeholder="Mi Empresa S.A." required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="zona_horaria" class="form-label">
                                    <i class="fas fa-clock"></i> Zona Horaria
                                </label>
                                <select class="form-select" id="zona_horaria" name="zona_horaria">
                                    <option value="UTC">UTC</option>
                                    <option value="America/Argentina/Buenos_Aires">Buenos Aires (GMT-3)</option>
                                    <option value="America/Mexico_City">México (GMT-6)</option>
                                    <option value="America/Bogota">Bogotá (GMT-5)</option>
                                    <option value="America/Lima">Lima (GMT-5)</option>
                                    <option value="America/Santiago">Santiago (GMT-3)</option>
                                    <option value="Europe/Madrid">Madrid (GMT+1)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Configuración de Tiempos -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-stopwatch"></i> Configuración de Tiempos (SLA)
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="tiempo_max_respuesta" class="form-label">
                                <i class="fas fa-reply"></i> Tiempo Máximo de Respuesta (minutos)
                            </label>
                            <input type="number" class="form-control" id="tiempo_max_respuesta" 
                                   name="tiempo_max_respuesta" min="5" max="10080" value="60">
                            <div class="form-text">Tiempo límite para primera respuesta</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="tiempo_cierre_tras_respuesta" class="form-label">
                                <i class="fas fa-times-circle"></i> Auto-cierre tras respuesta (minutos)
                            </label>
                            <input type="number" class="form-control" id="tiempo_cierre_tras_respuesta" 
                                   name="tiempo_cierre_tras_respuesta" min="60" max="43200" value="1440">
                            <div class="form-text">Tiempo para cerrar automáticamente tickets resueltos</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuración de Email -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-envelope"></i> Configuración de Email (SMTP)
                </h5>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="notificaciones_email" 
                           name="notificaciones_email" checked>
                    <label class="form-check-label" for="notificaciones_email">
                        Notificaciones Habilitadas
                    </label>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="smtp_host" class="form-label">
                                <i class="fas fa-server"></i> Servidor SMTP
                            </label>
                            <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                   placeholder="smtp.gmail.com">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="smtp_user" class="form-label">
                                <i class="fas fa-user"></i> Usuario SMTP
                            </label>
                            <input type="email" class="form-control" id="smtp_user" name="smtp_user" 
                                   placeholder="soporte@empresa.com">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="smtp_pass" class="form-label">
                                <i class="fas fa-lock"></i> Contraseña SMTP
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="smtp_pass" name="smtp_pass" 
                                       placeholder="Contraseña o App Password">
                                <button class="btn btn-outline-secondary" type="button" onclick="toggleSmtpPassword()">
                                    <i class="fas fa-eye" id="smtpPassIcon"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-primary w-100" onclick="probarEmail()">
                            <i class="fas fa-paper-plane"></i> Probar Configuración
                        </button>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Gmail:</strong> Usa una "Contraseña de aplicación" en lugar de tu contraseña normal. 
                    <a href="https://myaccount.google.com/apppasswords" target="_blank">Crear aquí</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Estado de configuración -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle"></i> Estado del Sistema
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Base de Datos:</strong>
                    <span class="badge bg-success">
                        <i class="fas fa-check"></i> Conectada
                    </span>
                </div>
                <div class="mb-2">
                    <strong>Email SMTP:</strong>
                    <span class="badge bg-warning" id="estadoSMTP">
                        <i class="fas fa-question"></i> No probado
                    </span>
                </div>
                <div class="mb-2">
                    <strong>Última configuración:</strong>
                    <span id="ultimaConfiguracion">-</span>
                </div>
                <div class="mb-0">
                    <strong>Versión del Sistema:</strong>
                    <span class="badge bg-info">v1.0.0</span>
                </div>
            </div>
        </div>

        <!-- Estadísticas rápidas -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-bar"></i> Estadísticas del Sistema
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <div class="h5 text-primary" id="totalUsuarios">-</div>
                            <small class="text-muted">Usuarios</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="h5 text-success" id="totalTickets">-</div>
                        <small class="text-muted">Tickets</small>
                    </div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <div class="h5 text-warning" id="ticketsAbiertos">-</div>
                            <small class="text-muted">Abiertos</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="h5 text-info" id="promedioRespuesta">-</div>
                        <small class="text-muted">Prom. Respuesta</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-bolt"></i> Acciones Rápidas
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary btn-sm" onclick="window.location.href='?ruta=sla'">
                        <i class="fas fa-clock"></i> Configurar SLA
                    </button>
                    <button class="btn btn-outline-info btn-sm" onclick="window.location.href='?ruta=audit'">
                        <i class="fas fa-history"></i> Ver Auditoría
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="exportarConfiguracion()">
                        <i class="fas fa-download"></i> Exportar Config
                    </button>
                    <button class="btn btn-outline-warning btn-sm" onclick="resetearConfiguracion()">
                        <i class="fas fa-undo"></i> Valores por Defecto
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de prueba de email -->
<div class="modal fade" id="pruebaEmailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-paper-plane"></i> Probar Configuración de Email
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="emailPrueba" class="form-label">Enviar email de prueba a:</label>
                    <input type="email" class="form-control" id="emailPrueba" 
                           placeholder="test@ejemplo.com" required>
                </div>
                <div id="resultadoPrueba"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="enviarEmailPrueba()">
                    <i class="fas fa-paper-plane"></i> Enviar Prueba
                </button>
            </div>
        </div>
    </div>
</div>

<?php 
$contenido = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>