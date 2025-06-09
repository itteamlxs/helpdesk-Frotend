<?php
// Calcular BASE_PATH dinámicamente
$basePath = str_replace('/public', '', dirname($_SERVER['SCRIPT_NAME']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?? 'Helpdesk System' ?></title>
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS Moderno -->
    <link href="<?= $basePath ?>/assets/css/modern-theme.css" rel="stylesheet">
    
    <!-- Pasar datos del usuario a JavaScript -->
    <script>
        window.BASE_PATH = '<?= $basePath ?>';
        
        <?php if (isset($_SESSION['usuario'])): ?>
            window.userId = <?= $_SESSION['usuario']['id'] ?>;
            window.userRole = <?= $_SESSION['usuario']['rol_id'] ?>;
            window.userName = '<?= addslashes($_SESSION['usuario']['nombre']) ?>';
            window.userEmail = '<?= addslashes($_SESSION['usuario']['correo'] ?? '') ?>';
            
            console.log('Usuario cargado desde sesión:', {
                id: window.userId,
                role: window.userRole,
                name: window.userName,
                email: window.userEmail
            });
        <?php else: ?>
            console.warn('No hay usuario en sesión - Redirigiendo a login');
            window.userId = null;
            window.userRole = 1;
            window.userName = 'Invitado';
            window.userEmail = '';
            
            if (window.location.search.indexOf('ruta=login') === -1) {
                window.location.href = '?ruta=login';
            }
        <?php endif; ?>
        
        console.log('BASE_PATH configurado:', window.BASE_PATH);
        console.log('Permisos de usuario:', {
            role: window.userRole,
            canEdit: window.userRole >= 2,
            canAdmin: window.userRole >= 3
        });
    </script>
</head>
<body>
    <div class="app-layout">
        <!-- Sidebar -->
        <aside class="app-sidebar" id="sidebar">
            <!-- Brand -->
            <div class="sidebar-brand">
                <div class="brand-content">
                    <i class="fas fa-headset brand-icon"></i>
                    <h4 class="brand-text">Helpdesk</h4>
                </div>
                <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <!-- Navigation -->
            <ul class="sidebar-nav">
                <li class="sidebar-nav-item">
                    <a href="?ruta=dashboard" 
                       class="sidebar-nav-link <?= ($_GET['ruta'] ?? '') === 'dashboard' ? 'active' : '' ?>"
                       data-tooltip="Dashboard">
                        <i class="fas fa-home"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                
                <li class="sidebar-nav-item">
                    <a href="?ruta=tickets" 
                       class="sidebar-nav-link <?= str_starts_with($_GET['ruta'] ?? '', 'tickets') ? 'active' : '' ?>"
                       data-tooltip="Tickets">
                        <i class="fas fa-ticket-alt"></i>
                        <span class="nav-text">Tickets</span>
                    </a>
                </li>
                
                <?php if (($_SESSION['usuario']['rol_id'] ?? 0) >= 2): ?>
                <li class="sidebar-nav-item">
                    <a href="?ruta=usuarios" 
                       class="sidebar-nav-link <?= str_starts_with($_GET['ruta'] ?? '', 'usuarios') ? 'active' : '' ?>"
                       data-tooltip="Usuarios">
                        <i class="fas fa-users"></i>
                        <span class="nav-text">Usuarios</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (($_SESSION['usuario']['rol_id'] ?? 0) >= 3): ?>
                <li class="sidebar-nav-item">
                    <a href="?ruta=sla" 
                       class="sidebar-nav-link <?= ($_GET['ruta'] ?? '') === 'sla' ? 'active' : '' ?>"
                       data-tooltip="SLA">
                        <i class="fas fa-clock"></i>
                        <span class="nav-text">SLA</span>
                    </a>
                </li>
                
                <li class="sidebar-nav-item">
                    <a href="?ruta=audit" 
                       class="sidebar-nav-link <?= ($_GET['ruta'] ?? '') === 'audit' ? 'active' : '' ?>"
                       data-tooltip="Auditoría">
                        <i class="fas fa-shield-alt"></i>
                        <span class="nav-text">Auditoría</span>
                    </a>
                </li>
                
                <li class="sidebar-nav-item">
                    <a href="?ruta=settings" 
                       class="sidebar-nav-link <?= ($_GET['ruta'] ?? '') === 'settings' ? 'active' : '' ?>"
                       data-tooltip="Configuración">
                        <i class="fas fa-cog"></i>
                        <span class="nav-text">Configuración</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </aside>
        
        <!-- Header -->
        <header class="app-header">
            <div class="header-content">
                <!-- Breadcrumb -->
                <nav class="header-breadcrumb" aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="?ruta=dashboard">
                                <i class="fas fa-home"></i>
                            </a>
                        </li>
                        <?php
                        $ruta = $_GET['ruta'] ?? 'dashboard';
                        $breadcrumbs = [
                            'dashboard' => 'Dashboard',
                            'tickets' => 'Tickets',
                            'tickets-crear' => 'Nuevo Ticket',
                            'tickets-ver' => 'Ver Ticket',
                            'tickets-editar' => 'Editar Ticket',
                            'usuarios' => 'Usuarios',
                            'sla' => 'SLA',
                            'audit' => 'Auditoría',
                            'settings' => 'Configuración'
                        ];
                        
                        if (isset($breadcrumbs[$ruta])):
                        ?>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?= $breadcrumbs[$ruta] ?>
                        </li>
                        <?php endif; ?>
                    </ol>
                </nav>
                
                <!-- Header Actions -->
                <div class="header-actions">
                    <!-- Search -->
                    <div class="header-search">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Buscar tickets, usuarios..." id="globalSearch">
                    </div>
                    
                    <!-- Notifications -->
                    <div class="header-notifications">
                        <button class="notification-btn" id="notificationsBtn" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge" id="notificationCount" style="display: none;">0</span>
                        </button>
                        
                        <div class="dropdown-menu dropdown-menu-end" style="width: 350px;">
                            <div class="p-3 border-bottom">
                                <h6 class="mb-0">Notificaciones</h6>
                            </div>
                            <div id="notificationsList" style="max-height: 300px; overflow-y: auto;">
                                <div class="p-3 text-center text-muted">
                                    <i class="fas fa-bell-slash fa-2x mb-2"></i>
                                    <div>No hay notificaciones</div>
                                </div>
                            </div>
                            <div class="p-2 border-top">
                                <a href="?ruta=notificaciones" class="btn btn-sm btn-primary w-100">
                                    Ver todas
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- User Menu -->
                    <div class="user-menu dropdown">
                        <button class="user-menu-btn" data-bs-toggle="dropdown">
                            <div class="user-avatar">
                                <?= strtoupper(substr($_SESSION['usuario']['nombre'] ?? 'U', 0, 1)) ?>
                            </div>
                            <div class="d-none d-md-block">
                                <div class="user-name">
                                    <?= $_SESSION['usuario']['nombre'] ?? 'Usuario' ?>
                                </div>
                                <div class="user-role">
                                    <?php 
                                    $rol = $_SESSION['usuario']['rol_id'] ?? 1;
                                    echo $rol == 1 ? 'Cliente' : ($rol == 2 ? 'Técnico' : 'Administrador');
                                    ?>
                                </div>
                            </div>
                            <i class="fas fa-chevron-down user-chevron"></i>
                        </button>
                        
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Mi Cuenta</h6></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Perfil</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Preferencias</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php if (($_SESSION['usuario']['rol_id'] ?? 0) >= 3): ?>
                            <li><a class="dropdown-item" href="?ruta=settings"><i class="fas fa-tools me-2"></i>Administración</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item text-danger" href="?ruta=logout"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Main Content -->
        <main class="app-main">
            <!-- Alert Container -->
            <div class="alert-container" id="alertContainer"></div>
            
            <!-- Page Content -->
            <?php 
            if (isset($contenido)) {
                echo $contenido;
            }
            ?>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- API Client y App JS -->
    <script src="<?= $basePath ?>/assets/js/api.js"></script>
    <script src="<?= $basePath ?>/assets/js/app.js"></script>
    
    <!-- Sistema de alertas y funcionalidades -->
    <script>
        // Sistema de alertas global moderno
        window.showAlert = function(message, type = 'info', duration = 5000) {
            const alertContainer = document.getElementById('alertContainer');
            const alertId = 'alert_' + Date.now();
            
            const alertHTML = `
                <div class="alert-modern alert-${type}" id="${alertId}">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-${getIconForType(type)} me-2"></i>
                        <div class="flex-grow-1">${message}</div>
                        <button type="button" class="btn-close btn-close-sm ms-2" onclick="document.getElementById('${alertId}').remove()"></button>
                    </div>
                </div>
            `;
            
            alertContainer.insertAdjacentHTML('beforeend', alertHTML);
            
            if (duration > 0) {
                setTimeout(() => {
                    const alert = document.getElementById(alertId);
                    if (alert) {
                        alert.style.transform = 'translateX(100%)';
                        alert.style.opacity = '0';
                        setTimeout(() => alert.remove(), 300);
                    }
                }, duration);
            }
        };

        function getIconForType(type) {
            const icons = {
                'success': 'check-circle',
                'danger': 'exclamation-triangle',
                'warning': 'exclamation-circle',
                'info': 'info-circle'
            };
            return icons[type] || 'info-circle';
        }
        
        // Toggle sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const layout = document.querySelector('.app-layout');
            
            sidebar.classList.toggle('sidebar-collapsed');
            layout.classList.toggle('sidebar-collapsed');
            
            // Guardar estado en localStorage
            const isCollapsed = sidebar.classList.contains('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        }
        
        // Restaurar estado del sidebar al cargar
        document.addEventListener('DOMContentLoaded', function() {
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed) {
                document.getElementById('sidebar').classList.add('sidebar-collapsed');
                document.querySelector('.app-layout').classList.add('sidebar-collapsed');
            }
            
            // Tooltips para sidebar colapsado
            const navLinks = document.querySelectorAll('.sidebar-nav-link');
            navLinks.forEach(link => {
                link.addEventListener('mouseenter', function() {
                    if (document.getElementById('sidebar').classList.contains('sidebar-collapsed')) {
                        showTooltip(this);
                    }
                });
                
                link.addEventListener('mouseleave', function() {
                    hideTooltip();
                });
            });
        });
        
        // Sistema de tooltips para sidebar colapsado
        let tooltipElement = null;
        
        function showTooltip(element) {
            const tooltip = element.getAttribute('data-tooltip');
            if (!tooltip) return;
            
            hideTooltip();
            
            tooltipElement = document.createElement('div');
            tooltipElement.className = 'sidebar-tooltip';
            tooltipElement.textContent = tooltip;
            document.body.appendChild(tooltipElement);
            
            const rect = element.getBoundingClientRect();
            tooltipElement.style.top = rect.top + 'px';
            tooltipElement.style.left = (rect.right + 10) + 'px';
        }
        
        function hideTooltip() {
            if (tooltipElement) {
                tooltipElement.remove();
                tooltipElement = null;
            }
        }
        
        // Búsqueda global
        document.getElementById('globalSearch').addEventListener('input', function(e) {
            const query = e.target.value;
            if (query.length > 2) {
                console.log('Buscando:', query);
            }
        });
        
        // Toggle sidebar en móvil
        function toggleMobileSidebar() {
            document.getElementById('sidebar').classList.toggle('sidebar-open');
        }
    </script>
    
    <!-- Módulos específicos -->
    <?php if (isset($jsModules) && is_array($jsModules)): ?>
        <?php foreach ($jsModules as $module): ?>
            <script src="<?= $basePath ?>/assets/js/modules/<?= $module ?>.js"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Debug final -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.api !== 'undefined') {
                console.log('API Client cargado correctamente');
            } else {
                console.error('API Client no cargado - revisar ruta');
            }
            
            if (window.userId && window.userRole) {
                console.log('Datos de usuario disponibles para JavaScript');
                console.log(`Rol: ${window.userRole} (${window.userRole >= 2 ? 'Técnico/Admin' : 'Cliente'})`);
            } else {
                console.warn('Datos de usuario no disponibles');
            }
            
            const scripts = Array.from(document.scripts).filter(s => s.src.includes('/assets/js/'));
            console.log('Scripts JS cargados:', scripts.map(s => s.src));
        });
    </script>
</body>
</html>