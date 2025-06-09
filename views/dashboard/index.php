<?php 
$titulo = 'Dashboard - Helpdesk';
$jsModules = ['dashboard'];
ob_start(); 
?>

<!-- Page Header -->
<div class="page-header">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Bienvenido al sistema de tickets, <?= $_SESSION['usuario']['nombre'] ?></p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i>
                Actualizar
            </button>
            <a href="?ruta=tickets-crear" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i>
                Nuevo Ticket
            </a>
        </div>
    </div>
</div>

<!-- Metrics Cards -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="metric-card metric-card-primary">
            <div class="metric-card-body">
                <div class="metric-header">
                    <div class="metric-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="metric-trend">
                        <span class="trend-indicator trend-up">
                            <i class="fas fa-arrow-up"></i>
                        </span>
                    </div>
                </div>
                <div class="metric-content">
                    <div class="metric-value" id="totalTickets">-</div>
                    <div class="metric-label">Total Tickets</div>
                    <div class="metric-sublabel">Todos los tickets del sistema</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="metric-card metric-card-warning">
            <div class="metric-card-body">
                <div class="metric-header">
                    <div class="metric-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div class="metric-trend">
                        <span class="trend-indicator trend-neutral">
                            <i class="fas fa-minus"></i>
                        </span>
                    </div>
                </div>
                <div class="metric-content">
                    <div class="metric-value" id="ticketsAbiertos">-</div>
                    <div class="metric-label">Tickets Abiertos</div>
                    <div class="metric-sublabel">Pendientes de atención</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="metric-card metric-card-danger">
            <div class="metric-card-body">
                <div class="metric-header">
                    <div class="metric-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="metric-trend">
                        <span class="trend-indicator trend-down">
                            <i class="fas fa-arrow-down"></i>
                        </span>
                    </div>
                </div>
                <div class="metric-content">
                    <div class="metric-value" id="ticketsUrgentes">-</div>
                    <div class="metric-label">Alta Prioridad</div>
                    <div class="metric-sublabel">Requieren atención inmediata</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="metric-card metric-card-success">
            <div class="metric-card-body">
                <div class="metric-header">
                    <div class="metric-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="metric-trend">
                        <span class="trend-indicator trend-up">
                            <i class="fas fa-arrow-up"></i>
                        </span>
                    </div>
                </div>
                <div class="metric-content">
                    <div class="metric-value" id="ticketsResueltos">-</div>
                    <div class="metric-label">Resueltos Hoy</div>
                    <div class="metric-sublabel">Tickets cerrados hoy</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="row g-4">
    <!-- Recent Tickets -->
    <div class="col-lg-8">
        <div class="content-card">
            <div class="content-card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1">Tickets Recientes</h5>
                        <p class="card-subtitle text-muted mb-0">Últimos tickets creados o actualizados</p>
                    </div>
                    <div class="card-actions">
                        <button class="btn btn-outline-secondary btn-sm me-2" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <a href="?ruta=tickets" class="btn btn-primary btn-sm">
                            Ver Todos
                        </a>
                    </div>
                </div>
            </div>
            <div class="content-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-header">
                            <tr>
                                <th class="border-0">Ticket</th>
                                <th class="border-0">Estado</th>
                                <th class="border-0">Prioridad</th>
                                <th class="border-0">Cliente</th>
                                <th class="border-0">Fecha</th>
                                <th class="border-0">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="ticketsRecientes">
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="loading-state">
                                        <div class="loading-spinner"></div>
                                        <div class="loading-text">Cargando tickets recientes...</div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions & Stats -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="content-card mb-4">
            <div class="content-card-header">
                <h5 class="card-title mb-0">Acciones Rápidas</h5>
            </div>
            <div class="content-card-body">
                <div class="quick-actions">
                    <a href="?ruta=tickets-crear" class="quick-action-item">
                        <div class="quick-action-icon bg-primary">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="quick-action-content">
                            <div class="quick-action-title">Nuevo Ticket</div>
                            <div class="quick-action-subtitle">Crear solicitud de soporte</div>
                        </div>
                        <div class="quick-action-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>
                    
                    <a href="?ruta=tickets" class="quick-action-item">
                        <div class="quick-action-icon bg-info">
                            <i class="fas fa-list"></i>
                        </div>
                        <div class="quick-action-content">
                            <div class="quick-action-title">Ver Todos los Tickets</div>
                            <div class="quick-action-subtitle">Gestionar solicitudes</div>
                        </div>
                        <div class="quick-action-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>
                    
                    <?php if (($_SESSION['usuario']['rol_id'] ?? 0) >= 2): ?>
                    <a href="?ruta=usuarios" class="quick-action-item">
                        <div class="quick-action-icon bg-success">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="quick-action-content">
                            <div class="quick-action-title">Gestionar Usuarios</div>
                            <div class="quick-action-subtitle">Administrar cuentas</div>
                        </div>
                        <div class="quick-action-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (($_SESSION['usuario']['rol_id'] ?? 0) >= 3): ?>
                    <a href="?ruta=settings" class="quick-action-item">
                        <div class="quick-action-icon bg-warning">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="quick-action-content">
                            <div class="quick-action-title">Configuración</div>
                            <div class="quick-action-subtitle">Ajustes del sistema</div>
                        </div>
                        <div class="quick-action-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- System Status -->
        <div class="content-card">
            <div class="content-card-header">
                <h5 class="card-title mb-0">Estado del Sistema</h5>
            </div>
            <div class="content-card-body">
                <div class="system-status">
                    <div class="status-item">
                        <div class="status-indicator status-online"></div>
                        <div class="status-content">
                            <div class="status-title">Sistema Operativo</div>
                            <div class="status-subtitle">Todos los servicios funcionando</div>
                        </div>
                    </div>
                    
                    <div class="status-item">
                        <div class="status-indicator status-online"></div>
                        <div class="status-content">
                            <div class="status-title">Base de Datos</div>
                            <div class="status-subtitle">Conexión estable</div>
                        </div>
                    </div>
                    
                    <div class="status-item">
                        <div class="status-indicator status-warning"></div>
                        <div class="status-content">
                            <div class="status-title">Email SMTP</div>
                            <div class="status-subtitle">Configuración pendiente</div>
                        </div>
                    </div>
                    
                    <div class="status-item">
                        <div class="status-indicator status-online"></div>
                        <div class="status-content">
                            <div class="status-title">Espacio en Disco</div>
                            <div class="status-subtitle">78% utilizado</div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3 pt-3 border-top">
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>
                        Última verificación: hace 2 minutos
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS específico para Dashboard -->
<style>
/* Metric Cards */
.metric-card {
    background: var(--content-white);
    border-radius: 0.75rem;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--content-border);
    overflow: hidden;
    transition: all 0.2s ease;
    position: relative;
}

.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.metric-card-primary { border-left: 4px solid var(--primary); }
.metric-card-warning { border-left: 4px solid var(--warning); }
.metric-card-danger { border-left: 4px solid var(--danger); }
.metric-card-success { border-left: 4px solid var(--success); }

.metric-card-body {
    padding: 1.5rem;
}

.metric-header {
    display: flex;
    justify-content: between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.metric-icon {
    width: 48px;
    height: 48px;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    margin-right: auto;
}

.metric-card-primary .metric-icon { background: rgba(59, 130, 246, 0.1); color: var(--primary); }
.metric-card-warning .metric-icon { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
.metric-card-danger .metric-icon { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
.metric-card-success .metric-icon { background: rgba(16, 185, 129, 0.1); color: var(--success); }

.metric-trend {
    margin-left: 1rem;
}

.trend-indicator {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    font-size: 0.75rem;
}

.trend-up { background: rgba(16, 185, 129, 0.1); color: var(--success); }
.trend-down { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
.trend-neutral { background: rgba(107, 114, 128, 0.1); color: var(--content-text-light); }

.metric-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--content-text);
    line-height: 1;
    margin-bottom: 0.5rem;
}

.metric-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--content-text);
    margin-bottom: 0.25rem;
}

.metric-sublabel {
    font-size: 0.75rem;
    color: var(--content-text-light);
}

/* Table Styles */
.table-header th {
    font-weight: 600;
    color: var(--content-text);
    font-size: 0.875rem;
    padding: 1rem;
    background: var(--content-bg);
}

.table tbody tr {
    border-bottom: 1px solid var(--content-border);
}

.table tbody tr:hover {
    background: rgba(59, 130, 246, 0.02);
}

.table tbody td {
    padding: 1rem;
    vertical-align: middle;
}

/* Quick Actions */
.quick-actions {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.quick-action-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-radius: 0.5rem;
    text-decoration: none;
    color: var(--content-text);
    background: var(--content-bg);
    border: 1px solid transparent;
    transition: all 0.2s ease;
}

.quick-action-item:hover {
    background: var(--content-white);
    border-color: var(--content-border);
    color: var(--content-text);
    transform: translateX(4px);
    box-shadow: var(--shadow-sm);
}

.quick-action-icon {
    width: 40px;
    height: 40px;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin-right: 1rem;
    flex-shrink: 0;
}

.quick-action-content {
    flex: 1;
}

.quick-action-title {
    font-weight: 500;
    font-size: 0.875rem;
    margin-bottom: 0.125rem;
}

.quick-action-subtitle {
    font-size: 0.75rem;
    color: var(--content-text-light);
}

.quick-action-arrow {
    color: var(--content-text-light);
    font-size: 0.75rem;
    margin-left: 0.5rem;
}

/* System Status */
.system-status {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.status-item {
    display: flex;
    align-items: center;
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 0.75rem;
    flex-shrink: 0;
}

.status-online { background: var(--success); }
.status-warning { background: var(--warning); }
.status-offline { background: var(--danger); }

.status-content {
    flex: 1;
}

.status-title {
    font-weight: 500;
    font-size: 0.875rem;
    margin-bottom: 0.125rem;
}

.status-subtitle {
    font-size: 0.75rem;
    color: var(--content-text-light);
}

/* Loading State */
.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.loading-spinner {
    width: 32px;
    height: 32px;
    border: 3px solid var(--content-border);
    border-top: 3px solid var(--primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-text {
    color: var(--content-text-light);
    font-size: 0.875rem;
}

/* Card Actions */
.card-actions {
    display: flex;
    align-items: center;
}

.card-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--content-text);
}

.card-subtitle {
    font-size: 0.875rem;
}

/* Status Badges */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-badge-open { background: rgba(59, 130, 246, 0.1); color: var(--primary); }
.status-badge-progress { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
.status-badge-waiting { background: rgba(6, 182, 212, 0.1); color: var(--info); }
.status-badge-closed { background: rgba(16, 185, 129, 0.1); color: var(--success); }

.priority-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.priority-badge-low { background: rgba(107, 114, 128, 0.1); color: var(--content-text-light); }
.priority-badge-medium { background: rgba(6, 182, 212, 0.1); color: var(--info); }
.priority-badge-high { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
.priority-badge-urgent { background: rgba(239, 68, 68, 0.1); color: var(--danger); }

/* Responsive */
@media (max-width: 768px) {
    .metric-value {
        font-size: 1.5rem;
    }
    
    .metric-card-body {
        padding: 1rem;
    }
    
    .quick-action-item {
        padding: 0.75rem;
    }
    
    .content-card-body {
        padding: 1rem;
    }
}
</style>

<?php 
$contenido = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>