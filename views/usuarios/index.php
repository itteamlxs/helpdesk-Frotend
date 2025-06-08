<?php 
$titulo = 'Gestión de Usuarios';
$jsModules = ['usuarios'];
ob_start(); 
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3">
            <i class="fas fa-users"></i> Gestión de Usuarios
        </h1>
        <p class="text-muted">Administra los usuarios del sistema de helpdesk</p>
    </div>
    <div class="col-md-4 text-end">
        <button class="btn btn-primary" onclick="mostrarModalCrear()">
            <i class="fas fa-user-plus"></i> Nuevo Usuario
        </button>
    </div>
</div>

<!-- Filtros y búsqueda -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <label for="filtroRol" class="form-label">Filtrar por Rol</label>
                <select class="form-select" id="filtroRol">
                    <option value="">Todos los roles</option>
                    <option value="1">Clientes</option>
                    <option value="2">Técnicos</option>
                    <option value="3">Administradores</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="filtroEstado" class="form-label">Estado</label>
                <select class="form-select" id="filtroEstado">
                    <option value="">Todos</option>
                    <option value="1">Activos</option>
                    <option value="0">Inactivos</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="busquedaUsuario" class="form-label">Buscar Usuario</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="busquedaUsuario" 
                           placeholder="Nombre o correo...">
                    <button class="btn btn-outline-secondary" onclick="limpiarFiltros()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas rápidas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="fas fa-users fa-2x text-primary mb-2"></i>
                <h4 id="totalUsuarios">-</h4>
                <small class="text-muted">Total Usuarios</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <i class="fas fa-user fa-2x text-info mb-2"></i>
                <h4 id="totalClientes">-</h4>
                <small class="text-muted">Clientes</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <i class="fas fa-user-cog fa-2x text-warning mb-2"></i>
                <h4 id="totalTecnicos">-</h4>
                <small class="text-muted">Técnicos</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="fas fa-user-shield fa-2x text-success mb-2"></i>
                <h4 id="totalAdmins">-</h4>
                <small class="text-muted">Administradores</small>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de usuarios -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list"></i> Lista de Usuarios
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>
                            <i class="fas fa-hashtag"></i> ID
                        </th>
                        <th>
                            <i class="fas fa-user"></i> Usuario
                        </th>
                        <th>
                            <i class="fas fa-envelope"></i> Correo
                        </th>
                        <th>
                            <i class="fas fa-shield-alt"></i> Rol
                        </th>
                        <th>
                            <i class="fas fa-toggle-on"></i> Estado
                        </th>
                        <th>
                            <i class="fas fa-chart-bar"></i> Estadísticas
                        </th>
                        <th>
                            <i class="fas fa-cogs"></i> Acciones
                        </th>
                    </tr>
                </thead>
                <tbody id="usuariosTableBody">
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                            <br>Cargando usuarios...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para crear/editar usuario -->
<div class="modal fade" id="usuarioModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="usuarioModalTitle">
                    <i class="fas fa-user-plus"></i> Nuevo Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formUsuario">
                    <input type="hidden" id="usuarioId" name="id">
                    
                    <div class="row">
                        <!-- Información básica -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nombreUsuario" class="form-label">
                                    <i class="fas fa-user"></i> Nombre Completo *
                                </label>
                                <input type="text" class="form-control" id="nombreUsuario" name="nombre" 
                                       placeholder="Ej: Juan Pérez" required maxlength="100">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="correoUsuario" class="form-label">
                                    <i class="fas fa-envelope"></i> Correo Electrónico *
                                </label>
                                <input type="email" class="form-control" id="correoUsuario" name="correo" 
                                       placeholder="usuario@empresa.com" required maxlength="100">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Rol y estado -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="rolUsuario" class="form-label">
                                    <i class="fas fa-shield-alt"></i> Rol del Usuario *
                                </label>
                                <select class="form-select" id="rolUsuario" name="rol_id" required>
                                    <option value="">Selecciona un rol</option>
                                    <option value="1">👤 Cliente</option>
                                    <option value="2">🔧 Técnico</option>
                                    <option value="3">👑 Administrador</option>
                                </select>
                                <div class="form-text">
                                    <small>
                                        <strong>Cliente:</strong> Puede crear y ver sus tickets<br>
                                        <strong>Técnico:</strong> Puede gestionar todos los tickets<br>
                                        <strong>Admin:</strong> Acceso completo al sistema
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="estadoUsuario" class="form-label">
                                    <i class="fas fa-toggle-on"></i> Estado
                                </label>
                                <select class="form-select" id="estadoUsuario" name="activo">
                                    <option value="1">✅ Activo</option>
                                    <option value="0">❌ Inactivo</option>
                                </select>
                                <div class="form-text">
                                    Los usuarios inactivos no pueden acceder al sistema
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contraseña -->
                    <div class="row" id="seccionPassword">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="passwordUsuario" class="form-label">
                                    <i class="fas fa-lock"></i> Contraseña *
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="passwordUsuario" name="password" 
                                           placeholder="Mínimo 6 caracteres" minlength="6">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                        <i class="fas fa-eye" id="passwordIcon"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    <span id="passwordHelp">La contraseña debe tener al menos 6 caracteres</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="confirmarPassword" class="form-label">
                                    <i class="fas fa-lock"></i> Confirmar Contraseña *
                                </label>
                                <input type="password" class="form-control" id="confirmarPassword" name="confirmar_password" 
                                       placeholder="Repite la contraseña" minlength="6">
                                <div class="form-text">
                                    <span id="passwordMatch"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Información adicional -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Nota:</strong> Al crear el usuario, recibirá un correo con sus credenciales de acceso.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-success" onclick="guardarUsuario()" id="btnGuardarUsuario">
                    <i class="fas fa-save"></i> Guardar Usuario
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="eliminarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-danger"></i> Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de eliminar al usuario <strong id="usuarioEliminar">-</strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-warning"></i> 
                    <strong>Advertencia:</strong> Esta acción no se puede deshacer. 
                    Se eliminarán todos los tickets y comentarios asociados.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-danger" onclick="confirmarEliminacion()" id="btnConfirmarEliminar">
                    <i class="fas fa-trash"></i> Eliminar Usuario
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de detalles del usuario -->
<div class="modal fade" id="detallesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-circle"></i> Detalles del Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detallesUsuarioContent">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Cargando información...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" onclick="editarUsuarioActual()">
                    <i class="fas fa-edit"></i> Editar Usuario
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<?php 
$contenido = ob_get_clean();
include __DIR__ . '/../layouts/app.php';  // ✅ RUTA CORREGIDA
?>