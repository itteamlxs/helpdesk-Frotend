<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="?ruta=dashboard">
            <i class="fas fa-headset"></i> Helpdesk
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <!-- Notificaciones (futuro) -->
                <li class="nav-item">
                    <a class="nav-link" href="#" title="Notificaciones">
                        <i class="fas fa-bell"></i>
                        <span class="badge bg-danger badge-sm">3</span>
                    </a>
                </li>
                
                <!-- Dropdown del usuario -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i> 
                        <?= $_SESSION['usuario']['nombre'] ?? 'Usuario' ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <div class="dropdown-header">
                                <small class="text-muted">
                                    Rol: <?= ucfirst($_SESSION['usuario']['rol_nombre'] ?? 'Usuario') ?>
                                </small>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        
                        <!-- Opciones según el rol -->
                        <?php if (($_SESSION['usuario']['rol_id'] ?? 0) >= 3): ?>
                        <li><a class="dropdown-item" href="?ruta=settings">
                            <i class="fas fa-cog"></i> Configuración
                        </a></li>
                        <li><a class="dropdown-item" href="?ruta=audit">
                            <i class="fas fa-history"></i> Auditoría
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        
                        <li><a class="dropdown-item" href="#" onclick="mostrarPerfilModal()">
                            <i class="fas fa-user-edit"></i> Mi Perfil
                        </a></li>
                        
                        <li><hr class="dropdown-divider"></li>
                        
                        <!-- Opción de logout -->
                        <li><a class="dropdown-item text-danger" href="?ruta=logout" onclick="logout()">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Modal de perfil (futuro) -->
<div class="modal fade" id="perfilModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mi Perfil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Funcionalidad de perfil - próximamente</p>
            </div>
        </div>
    </div>
</div>

<script>
function mostrarPerfilModal() {
    // const modal = new bootstrap.Modal(document.getElementById('perfilModal'));
    // modal.show();
    showAlert('Funcionalidad de perfil próximamente', 'info');
}
</script>