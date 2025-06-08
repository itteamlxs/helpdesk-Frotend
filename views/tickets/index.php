<?php 
$titulo = 'Gestión de Tickets';
$jsModules = ['tickets'];
ob_start(); 
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3">Gestión de Tickets</h1>
        <p class="text-muted">Administra todos los tickets del sistema</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="?ruta=tickets-crear" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Ticket
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <label for="filtroEstado" class="form-label">Estado</label>
                <select class="form-select" id="filtroEstado">
                    <option value="">Todos</option>
                    <option value="abierto">Abierto</option>
                    <option value="en_proceso">En Proceso</option>
                    <option value="en_espera">En Espera</option>
                    <option value="cerrado">Cerrado</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filtroPrioridad" class="form-label">Prioridad</label>
                <select class="form-select" id="filtroPrioridad">
                    <option value="">Todas</option>
                    <option value="baja">Baja</option>
                    <option value="media">Media</option>
                    <option value="alta">Alta</option>
                    <option value="urgente">Urgente</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="busqueda" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="busqueda" placeholder="Título o descripción...">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-outline-secondary w-100" onclick="limpiarFiltros()">
                    <i class="fas fa-times"></i> Limpiar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de tickets -->
<div class="card">
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
                        <th>Técnico</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="ticketsTableBody">
                    <tr>
                        <td colspan="8" class="text-center">
                            <i class="fas fa-spinner fa-spin"></i> Cargando tickets...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
$contenido = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>