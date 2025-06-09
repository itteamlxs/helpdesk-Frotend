<?php 
$titulo = 'Dashboard - Helpdesk';
$jsModules = ['dashboard'];
ob_start(); 
?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3">Dashboard</h1>
        <p class="text-muted">Bienvenido al sistema de tickets, <?= $_SESSION['usuario']['nombre'] ?></p>
    </div>
</div>

<!-- Cards de estadísticas -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="card-title text-primary">Total Tickets</h6>
                        <h2 class="card-text" id="totalTickets">-</h2>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-ticket-alt fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="card-title text-warning">Tickets Abiertos</h6>
                        <h2 class="card-text" id="ticketsAbiertos">-</h2>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-folder-open fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-danger">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="card-title text-danger">Alta Prioridad</h6>
                        <h2 class="card-text" id="ticketsUrgentes">-</h2>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="card-title text-success">Resueltos Hoy</h6>
                        <h2 class="card-text" id="ticketsResueltos">-</h2>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tickets recientes -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Tickets Recientes</h5>
                <a href="?ruta=tickets" class="btn btn-primary btn-sm">
                    <i class="fas fa-eye"></i> Ver Todos
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Estado</th>
                                <th>Prioridad</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody id="ticketsRecientes">
                            <tr>
                                <td colspan="6" class="text-center">
                                    <i class="fas fa-spinner fa-spin"></i> Cargando...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$contenido = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>