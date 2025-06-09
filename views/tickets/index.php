<?php 
$titulo = 'Gestión de Tickets';
$jsModules = ['tickets'];
ob_start(); 
?>

<!-- Page Header -->
<div class="page-header">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="page-title">Tickets</h1>
            <p class="page-subtitle">Gestiona todas las solicitudes de soporte del sistema</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i>
                Actualizar
            </button>
            <a href="?ruta=tickets-crear" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Nuevo Ticket
            </a>
        </div>
    </div>
</div>

<!-- Filters Panel -->
<div class="content-card mb-4">
    <div class="content-card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Filtros y Búsqueda</h5>
            <button class="btn btn-outline-secondary btn-sm" onclick="limpiarFiltros()">
                <i class="fas fa-times"></i>
                Limpiar Filtros
            </button>
        </div>
    </div>
    <div class="content-card-body">
        <div class="row g-3">
            <div class="col-lg-2 col-md-6">
                <label class="form-label small fw-medium">Estado</label>
                <select class="form-select form-select-sm" id="filtroEstado">
                    <option value="">Todos los estados</option>
                    <option value="abierto">Abierto</option>
                    <option value="en_proceso">En Proceso</option>
                    <option value="en_espera">En Espera</option>
                    <option value="cerrado">Cerrado</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label small fw-medium">Prioridad</label>
                <select class="form-select form-select-sm" id="filtroPrioridad">
                    <option value="">Todas las prioridades</option>
                    <option value="baja">Baja</option>
                    <option value="media">Media</option>
                    <option value="alta">Alta</option>
                    <option value="urgente">Urgente</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label small fw-medium">Búsqueda</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" class="form-control" id="busqueda" 
                           placeholder="Buscar por título, ID o descripción...">
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label small fw-medium">Ordenar por</label>
                <select class="form-select form-select-sm" id="ordenarPor">
                    <option value="fecha_desc">Más recientes</option>
                    <option value="fecha_asc">Más antiguos</option>
                    <option value="prioridad">Por prioridad</option>
                    <option value="estado">Por estado</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label small fw-medium">Ver</label>
                <select class="form-select form-select-sm" id="vistaFormato">
                    <option value="tabla">Vista Tabla</option>
                    <option value="cards">Vista Cards</option>
                </select>
            </div>
            <div class="col-lg-1 col-md-6">
                <label class="form-label small fw-medium">&nbsp;</label>
                <div class="d-flex gap-1">
                    <button class="btn btn-outline-primary btn-sm flex-fill" onclick="aplicarFiltros()" title="Aplicar filtros">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row g-3 mb-4">
    <div class="col-lg-3 col-6">
        <div class="quick-stat-card stat-primary">
            <div class="stat-icon">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" id="statTotal">0</div>
                <div class="stat-label">Total</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="quick-stat-card stat-warning">
            <div class="stat-icon">
                <i class="fas fa-folder-open"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" id="statAbiertos">0</div>
                <div class="stat-label">Abiertos</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="quick-stat-card stat-danger">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" id="statUrgentes">0</div>
                <div class="stat-label">Urgentes</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="quick-stat-card stat-success">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" id="statCerrados">0</div>
                <div class="stat-label">Cerrados</div>
            </div>
        </div>
    </div>
</div>

<!-- Tickets Table/Cards Container -->
<div class="content-card">
    <div class="content-card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-1">Lista de Tickets</h5>
                <p class="card-subtitle text-muted mb-0">
                    <span id="totalResultados">0</span> tickets encontrados
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="btn-group btn-group-sm" role="group">
                    <input type="radio" class="btn-check" name="vistaOptions" id="vistaTabla" autocomplete="off" checked>
                    <label class="btn btn-outline-secondary" for="vistaTabla">
                        <i class="fas fa-table"></i>
                    </label>
                    <input type="radio" class="btn-check" name="vistaOptions" id="vistaCards" autocomplete="off">
                    <label class="btn btn-outline-secondary" for="vistaCards">
                        <i class="fas fa-th-large"></i>
                    </label>
                </div>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-download me-2"></i>Exportar CSV</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-file-pdf me-2"></i>Exportar PDF</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-print me-2"></i>Imprimir Lista</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Table View -->
    <div class="table-container" id="tablaContainer">
        <div class="table-responsive">
            <table class="table table-hover tickets-table mb-0">
                <thead class="table-header">
                    <tr>
                        <th class="border-0">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                            </div>
                        </th>
                        <th class="border-0 sortable" data-sort="id">
                            ID
                            <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th class="border-0 sortable" data-sort="titulo">
                            Título
                            <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th class="border-0 sortable" data-sort="estado">
                            Estado
                            <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th class="border-0 sortable" data-sort="prioridad">
                            Prioridad
                            <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th class="border-0 sortable" data-sort="cliente">
                            Cliente
                            <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th class="border-0 sortable" data-sort="tecnico">
                            Técnico
                            <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th class="border-0 sortable" data-sort="fecha">
                            Fecha
                            <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th class="border-0">Acciones</th>
                    </tr>
                </thead>
                <tbody id="ticketsTableBody">
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="loading-state">
                                <div class="loading-spinner"></div>
                                <div class="loading-text">Cargando tickets...</div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Cards View -->
    <div class="cards-container d-none" id="cardsContainer">
        <div class="row g-3 p-3" id="ticketsCardsBody">
            <!-- Cards se generan dinámicamente -->
        </div>
    </div>
    
    <!-- Pagination -->
    <div class="content-card-footer">
        <div class="d-flex justify-content-between align-items-center">
            <div class="pagination-info">
                <small class="text-muted">
                    Mostrando <span id="paginaInicio">1</span> - <span id="paginaFin">10</span> 
                    de <span id="totalPaginas">100</span> tickets
                </small>
            </div>
            <nav aria-label="Paginación de tickets">
                <ul class="pagination pagination-sm mb-0" id="paginationContainer">
                    <!-- Pagination se genera dinámicamente -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Bulk Actions Modal -->
<div class="modal fade" id="bulkActionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Acciones en Lote</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Selecciona la acción a realizar en <span id="selectedCount">0</span> tickets:</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary" onclick="bulkAction('assign')">
                        <i class="fas fa-user-plus me-2"></i>Asignar Técnico
                    </button>
                    <button class="btn btn-outline-warning" onclick="bulkAction('priority')">
                        <i class="fas fa-exclamation-triangle me-2"></i>Cambiar Prioridad
                    </button>
                    <button class="btn btn-outline-info" onclick="bulkAction('status')">
                        <i class="fas fa-flag me-2"></i>Cambiar Estado
                    </button>
                    <button class="btn btn-outline-danger" onclick="bulkAction('delete')">
                        <i class="fas fa-trash me-2"></i>Eliminar Tickets
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- CSS específico para Lista de Tickets -->
<style>
/* Quick Stats Cards */
.quick-stat-card {
    background: var(--content-white);
    border-radius: 0.5rem;
    padding: 1rem;
    border: 1px solid var(--content-border);
    display: flex;
    align-items: center;
    gap: 0.75rem;
    transition: all 0.2s ease;
}

.quick-stat-card:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
    flex-shrink: 0;
}

.stat-primary .stat-icon { background: rgba(59, 130, 246, 0.1); color: var(--primary); }
.stat-warning .stat-icon { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
.stat-danger .stat-icon { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
.stat-success .stat-icon { background: rgba(16, 185, 129, 0.1); color: var(--success); }

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--content-text);
    line-height: 1;
}

.stat-label {
    font-size: 0.75rem;
    color: var(--content-text-light);
    font-weight: 500;
}

/* Table Styles */
.tickets-table {
    --bs-table-hover-bg: rgba(59, 130, 246, 0.02);
}

.table-header th {
    font-weight: 600;
    color: var(--content-text);
    font-size: 0.875rem;
    padding: 1rem 0.75rem;
    background: var(--content-bg);
    position: relative;
}

.sortable {
    cursor: pointer;
    user-select: none;
    transition: all 0.2s ease;
}

.sortable:hover {
    background: rgba(59, 130, 246, 0.05);
}

.sort-icon {
    font-size: 0.75rem;
    color: var(--content-text-light);
    margin-left: 0.25rem;
    transition: all 0.2s ease;
}

.sortable.sort-asc .sort-icon {
    color: var(--primary);
    transform: rotate(180deg);
}

.sortable.sort-desc .sort-icon {
    color: var(--primary);
}

.tickets-table tbody tr {
    border-bottom: 1px solid var(--content-border);
}

.tickets-table tbody td {
    padding: 0.875rem 0.75rem;
    vertical-align: middle;
    font-size: 0.875rem;
}

/* Ticket Cards */
.ticket-card {
    background: var(--content-white);
    border: 1px solid var(--content-border);
    border-radius: 0.75rem;
    padding: 1.25rem;
    transition: all 0.2s ease;
    cursor: pointer;
}

.ticket-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-color: var(--primary);
}

.ticket-card-header {
    display: flex;
    justify-content: between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}

.ticket-id {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--content-text-light);
    margin-bottom: 0.25rem;
}

.ticket-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--content-text);
    margin-bottom: 0.5rem;
    line-height: 1.3;
}

.ticket-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
    margin-bottom: 0.75rem;
}

.ticket-footer {
    display: flex;
    justify-content: between;
    align-items: center;
    padding-top: 0.75rem;
    border-top: 1px solid var(--content-border);
}

.ticket-client {
    font-size: 0.875rem;
    color: var(--content-text-light);
}

.ticket-date {
    font-size: 0.75rem;
    color: var(--content-text-light);
}

/* Status and Priority Badges */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: capitalize;
}

.status-badge-abierto { background: rgba(59, 130, 246, 0.1); color: var(--primary); }
.status-badge-en_proceso { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
.status-badge-en_espera { background: rgba(6, 182, 212, 0.1); color: var(--info); }
.status-badge-cerrado { background: rgba(16, 185, 129, 0.1); color: var(--success); }

.priority-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: capitalize;
}

.priority-badge-baja { background: rgba(107, 114, 128, 0.1); color: var(--content-text-light); }
.priority-badge-media { background: rgba(6, 182, 212, 0.1); color: var(--info); }
.priority-badge-alta { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
.priority-badge-urgente { background: rgba(239, 68, 68, 0.1); color: var(--danger); }

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 0.25rem;
}

.action-btn {
    width: 32px;
    height: 32px;
    border-radius: 0.375rem;
    border: 1px solid var(--content-border);
    background: var(--content-white);
    color: var(--content-text-light);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.action-btn:hover {
    background: var(--content-bg);
    color: var(--content-text);
    border-color: var(--content-text-light);
}

.action-btn-primary:hover { background: var(--primary); color: white; border-color: var(--primary); }
.action-btn-warning:hover { background: var(--warning); color: white; border-color: var(--warning); }
.action-btn-danger:hover { background: var(--danger); color: white; border-color: var(--danger); }

/* Loading States */
.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    padding: 2rem;
}

.loading-spinner {
    width: 32px;
    height: 32px;
    border: 3px solid var(--content-border);
    border-top: 3px solid var(--primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.loading-text {
    color: var(--content-text-light);
    font-size: 0.875rem;
}

/* Empty States */
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
}

.empty-state-icon {
    font-size: 3rem;
    color: var(--content-text-light);
    margin-bottom: 1rem;
}

.empty-state-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--content-text);
    margin-bottom: 0.5rem;
}

.empty-state-subtitle {
    color: var(--content-text-light);
    margin-bottom: 1.5rem;
}

/* Pagination */
.content-card-footer {
    padding: 1rem 1.5rem;
    background: var(--content-bg);
    border-top: 1px solid var(--content-border);
}

.pagination-sm .page-link {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

/* Responsive */
@media (max-width: 768px) {
    .stat-value {
        font-size: 1.25rem;
    }
    
    .quick-stat-card {
        padding: 0.75rem;
    }
    
    .ticket-card {
        padding: 1rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .tickets-table {
        font-size: 0.8rem;
    }
    
    .tickets-table th,
    .tickets-table td {
        padding: 0.5rem 0.25rem;
    }
}

@media (max-width: 576px) {
    .table-responsive {
        border: none;
    }
    
    .tickets-table th:nth-child(n+6),
    .tickets-table td:nth-child(n+6) {
        display: none;
    }
}
</style>

<?php 
$contenido = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>