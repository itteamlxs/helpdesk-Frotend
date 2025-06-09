<?php 
$titulo = 'Auditoría del Sistema';
$jsModules = ['audit'];
ob_start(); 
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3">
            <i class="fas fa-history"></i> Auditoría del Sistema
        </h1>
        <p class="text-muted">Monitorea y revisa todas las actividades del sistema de helpdesk</p>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group">
            <button class="btn btn-outline-success" onclick="exportarAuditoria()">
                <i class="fas fa-download"></i> Exportar
            </button>
            <button class="btn btn-outline-info" onclick="refrescarAuditoria()">
                <i class="fas fa-sync"></i> Refrescar
            </button>
        </div>
    </div>
</div>

<!-- Estadísticas de auditoría -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="fas fa-list fa-2x text-primary mb-2"></i>
                <h4 id="totalAcciones">-</h4>
                <small class="text-muted">Total Acciones</small>
                <div class="text-success small" id="accionesHoy">+0 hoy</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <i class="fas fa-users fa-2x text-info mb-2"></i>
                <h4 id="usuariosActivos">-</h4>
                <small class="text-muted">Usuarios Activos</small>
                <div class="text-info small" id="usuariosHoy">0 hoy</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <i class="fas fa-sign-in-alt fa-2x text-warning mb-2"></i>
                <h4 id="totalLogins">-</h4>
                <small class="text-muted">Logins Hoy</small>
                <div class="text-warning small" id="loginsRecientes">Último: -</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-danger">
            <div class="card-body text-center">
                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                <h4 id="accionesRiesgo">-</h4>
                <small class="text-muted">Acciones de Riesgo</small>
                <div class="text-danger small" id="ultimaAccionRiesgo">Última: -</div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros y controles -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-filter"></i> Filtros de Auditoría
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <label for="filtroFecha" class="form-label">Período</label>
                <select class="form-select" id="filtroFecha">
                    <option value="hoy">Hoy</option>
                    <option value="ayer">Ayer</option>
                    <option value="semana" selected>Última semana</option>
                    <option value="mes">Último mes</option>
                    <option value="personalizado">Período personalizado</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filtroUsuario" class="form-label">Usuario</label>
                <select class="form-select" id="filtroUsuario">
                    <option value="">Todos los usuarios</option>
                    <!-- Se llena dinámicamente -->
                </select>
            </div>
            <div class="col-md-3">
                <label for="filtroAccion" class="form-label">Tipo de Acción</label>
                <select class="form-select" id="filtroAccion">
                    <option value="">Todas las acciones</option>
                    <option value="login">Login/Logout</option>
                    <option value="ticket">Gestión de Tickets</option>
                    <option value="usuario">Gestión de Usuarios</option>
                    <option value="config">Configuración</option>
                    <option value="delete">Eliminaciones</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary w-100" onclick="aplicarFiltros()">
                    <i class="fas fa-search"></i> Aplicar Filtros
                </button>
            </div>
        </div>
        
        <!-- Filtros de fecha personalizada -->
        <div class="row mt-3 d-none" id="fechasPersonalizadas">
            <div class="col-md-6">
                <label for="fechaInicio" class="form-label">Fecha Inicio</label>
                <input type="date" class="form-control" id="fechaInicio">
            </div>
            <div class="col-md-6">
                <label for="fechaFin" class="form-label">Fecha Fin</label>
                <input type="date" class="form-control" id="fechaFin">
            </div>
        </div>
    </div>
</div>

<!-- Tabs de vistas -->
<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" id="auditTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="detalle-tab" data-bs-toggle="tab" data-bs-target="#detalle" 
                        type="button" role="tab">
                    <i class="fas fa-list"></i> Vista Detallada
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="resumen-tab" data-bs-toggle="tab" data-bs-target="#resumen" 
                        type="button" role="tab">
                    <i class="fas fa-chart-bar"></i> Resumen por Día
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="usuarios-tab" data-bs-toggle="tab" data-bs-target="#usuarios" 
                        type="button" role="tab">
                    <i class="fas fa-users"></i> Actividad por Usuario
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="ips-tab" data-bs-toggle="tab" data-bs-target="#ips" 
                        type="button" role="tab">
                    <i class="fas fa-globe"></i> Direcciones IP
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="auditTabContent">
            
            <!-- Vista Detallada -->
            <div class="tab-pane fade show active" id="detalle" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <span class="text-muted">Mostrando </span>
                        <span id="totalRegistros">0</span>
                        <span class="text-muted"> registros</span>
                    </div>
                    <div>
                        <select class="form-select form-select-sm" id="registrosPorPagina" style="width: auto;">
                            <option value="25">25 por página</option>
                            <option value="50" selected>50 por página</option>
                            <option value="100">100 por página</option>
                        </select>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <i class="fas fa-clock"></i> Fecha/Hora
                                </th>
                                <th>
                                    <i class="fas fa-user"></i> Usuario
                                </th>
                                <th>
                                    <i class="fas fa-cog"></i> Acción
                                </th>
                                <th>
                                    <i class="fas fa-globe"></i> IP
                                </th>
                                <th>
                                    <i class="fas fa-shield-alt"></i> Rol
                                </th>
                                <th>
                                    <i class="fas fa-info-circle"></i> Detalles
                                </th>
                            </tr>
                        </thead>
                        <tbody id="auditTableBody">
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                                    <br>Cargando registros de auditoría...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginación -->
                <nav aria-label="Paginación de auditoría">
                    <ul class="pagination justify-content-center" id="paginacion">
                        <!-- Se genera dinámicamente -->
                    </ul>
                </nav>
            </div>
            
            <!-- Resumen por Día -->
            <div class="tab-pane fade" id="resumen" role="tabpanel">
                <div class="row">
                    <div class="col-lg-8">
                        <canvas id="graficoActividad" width="400" height="200"></canvas>
                    </div>
                    <div class="col-lg-4">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                        <th>Usuarios</th>
                                    </tr>
                                </thead>
                                <tbody id="resumenTabla">
                                    <tr>
                                        <td colspan="3" class="text-center">
                                            <i class="fas fa-spinner fa-spin"></i> Cargando...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actividad por Usuario -->
            <div class="tab-pane fade" id="usuarios" role="tabpanel">
                <div class="row" id="actividadUsuarios">
                    <div class="col-12 text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                        <p>Cargando actividad por usuario...</p>
                    </div>
                </div>
            </div>
            
            <!-- Direcciones IP -->
            <div class="tab-pane fade" id="ips" role="tabpanel">
                <div class="row">
                    <div class="col-md-8">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Dirección IP</th>
                                        <th>Acciones</th>
                                        <th>Usuarios</th>
                                        <th>Última Actividad</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="ipsTabla">
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            <i class="fas fa-spinner fa-spin"></i> Cargando...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-shield-alt"></i> Análisis de Seguridad
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>IPs Únicas:</span>
                                        <span class="fw-bold" id="ipsUnicas">-</span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>IPs Sospechosas:</span>
                                        <span class="fw-bold text-warning" id="ipsSospechosas">-</span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Intentos Fallidos:</span>
                                        <span class="fw-bold text-danger" id="intentosFallidos">-</span>
                                    </div>
                                </div>
                                <hr>
                                <div class="text-center">
                                    <button class="btn btn-outline-warning btn-sm" onclick="analizarSeguridad()">
                                        <i class="fas fa-search"></i> Análisis Detallado
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de detalles de auditoría -->
<div class="modal fade" id="detalleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Detalles de Auditoría
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detalleContent">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Cargando detalles...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de exportación -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-download"></i> Exportar Auditoría
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="formatoExport" class="form-label">Formato de Exportación</label>
                    <select class="form-select" id="formatoExport">
                        <option value="csv">CSV (Excel compatible)</option>
                        <option value="json">JSON (datos estructurados)</option>
                        <option value="pdf">PDF (reporte formateado)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="rangoExport" class="form-label">Rango de Datos</label>
                    <select class="form-select" id="rangoExport">
                        <option value="filtro_actual">Datos del filtro actual</option>
                        <option value="todo">Toda la auditoría</option>
                        <option value="personalizado">Rango personalizado</option>
                    </select>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="incluirDetalles" checked>
                    <label class="form-check-label" for="incluirDetalles">
                        Incluir detalles completos
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-success" onclick="confirmarExportacion()">
                    <i class="fas fa-download"></i> Exportar
                </button>
            </div>
        </div>
    </div>
</div>

<?php 
$contenido = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>