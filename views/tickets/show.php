<?php 
$titulo = 'Ver Ticket';
$jsModules = ['ticket-show'];
ob_start(); 
?>

<!-- Header del ticket -->
<div class="row mb-4">
    <div class="col-md-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="?ruta=dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="?ruta=tickets">Tickets</a></li>
                <li class="breadcrumb-item active" id="breadcrumbTicket">Cargando...</li>
            </ol>
        </nav>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group" role="group">
            <a href="?ruta=tickets" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <!-- 🔐 SOLO TÉCNICOS/ADMINS PUEDEN EDITAR -->
            <button class="btn btn-outline-primary" onclick="editarTicket()" id="btnEditar" style="display: none;">
                <i class="fas fa-edit"></i> Editar
            </button>
        </div>
    </div>
</div>

<!-- Información del ticket -->
<div class="row mb-4">
    <div class="col-lg-8">
        <!-- Card principal del ticket -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0" id="ticketTitulo">
                    <i class="fas fa-ticket-alt"></i> Cargando...
                </h4>
                <div id="ticketBadges">
                    <!-- Badges de estado y prioridad -->
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>ID:</strong> <span id="ticketId">-</span><br>
                        <strong>Cliente:</strong> <span id="ticketCliente">-</span><br>
                        <strong>Técnico:</strong> <span id="ticketTecnico">Sin asignar</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Categoría:</strong> <span id="ticketCategoria">-</span><br>
                        <strong>Creado:</strong> <span id="ticketCreado">-</span><br>
                        <strong>Actualizado:</strong> <span id="ticketActualizado">-</span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <strong>Descripción:</strong>
                    <div class="border rounded p-3 bg-light mt-2" id="ticketDescripcion">
                        Cargando descripción...
                    </div>
                </div>

                <!-- 🔐 ACCIONES RÁPIDAS - SOLO TÉCNICOS/ADMINS - NUEVA UX -->
                <div class="card mt-3" id="accionesRapidas" style="display: none;">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-tools"></i> Gestión del Ticket
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Indicador de cambios pendientes -->
                        <div class="alert alert-warning d-none" id="cambiosPendientes">
                            <i class="fas fa-clock"></i> <strong>Cambios pendientes:</strong> 
                            <span id="resumenCambios"></span>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <label for="cambiarEstado" class="form-label">Estado:</label>
                                <select class="form-select" id="cambiarEstado">
                                    <option value="abierto">Abierto</option>
                                    <option value="en_proceso">En Proceso</option>
                                    <option value="en_espera">En Espera</option>
                                    <option value="cerrado">Cerrado</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="asignarTecnico" class="form-label">Técnico Asignado:</label>
                                <select class="form-select" id="asignarTecnico">
                                    <option value="">Sin asignar</option>
                                    <!-- Se llena dinámicamente -->
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button class="btn btn-success w-100" id="btnGuardarCambios" onclick="guardarCambiosTicket()" disabled>
                                    <i class="fas fa-save"></i> Guardar Cambios
                                </button>
                            </div>
                        </div>
                        
                        <!-- Información adicional -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    Los cambios se aplicarán cuando hagas clic en "Guardar Cambios"
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sidebar con información adicional -->
    <div class="col-lg-4">
        <!-- Timeline rápido -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-clock"></i> Timeline</h6>
            </div>
            <div class="card-body">
                <div id="ticketTimeline">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin"></i> Cargando...
                    </div>
                </div>
            </div>
        </div>

        <!-- SLA Info -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-tachometer-alt"></i> SLA</h6>
            </div>
            <div class="card-body">
                <div id="slaInfo">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin"></i> Cargando...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sistema de comentarios -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-comments"></i> Comentarios
                    <span class="badge bg-secondary" id="contadorComentarios">0</span>
                </h5>
                <button class="btn btn-primary btn-sm" onclick="toggleComentarioForm()">
                    <i class="fas fa-plus"></i> Agregar Comentario
                </button>
            </div>
            
            <!-- Formulario de nuevo comentario -->
            <div class="card-body border-bottom d-none" id="comentarioForm">
                <form id="formNuevoComentario">
                    <div class="mb-3">
                        <label for="contenidoComentario" class="form-label">Comentario:</label>
                        <textarea class="form-control" id="contenidoComentario" rows="4" 
                                  placeholder="Escribe tu comentario aquí..." required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <!-- 🔐 CHECKBOX INTERNO - SOLO TÉCNICOS/ADMINS -->
                            <div class="form-check" style="display: none;">
                                <input class="form-check-input" type="checkbox" id="comentarioInterno">
                                <label class="form-check-label" for="comentarioInterno">
                                    <i class="fas fa-lock"></i> Comentario interno (solo visible para técnicos)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="button" class="btn btn-outline-secondary me-2" onclick="toggleComentarioForm()">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Enviar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Lista de comentarios -->
            <div class="card-body p-0">
                <div id="listaComentarios">
                    <div class="text-center p-4">
                        <i class="fas fa-spinner fa-spin"></i> Cargando comentarios...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script para configurar permisos iniciales -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 🔐 CONFIGURAR PERMISOS SEGÚN ROL
    const userRole = window.userRole || 1;
    console.log('Configurando permisos UI - Rol:', userRole);
    
    if (userRole >= 2) { 
        // Técnico o Admin - Mostrar todas las opciones
        document.getElementById('accionesRapidas').style.display = 'block';
        document.getElementById('btnEditar').style.display = 'inline-block';
        console.log('UI configurada para técnico/admin');
    } else { 
        // Cliente - Ocultar opciones de administración
        document.getElementById('accionesRapidas').style.display = 'none';
        document.getElementById('btnEditar').style.display = 'none';
        console.log('UI configurada para cliente');
    }
});
</script>

<?php 
$contenido = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>