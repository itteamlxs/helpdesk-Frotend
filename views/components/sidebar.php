<?php 
// Obtener la ruta actual
$ruta_actual = $_GET['ruta'] ?? 'dashboard';
$usuario_rol = $_SESSION['usuario']['rol_id'] ?? 0;
$usuario_nombre = $_SESSION['usuario']['nombre'] ?? 'Usuario';
?>

<div class="app-sidebar" id="appSidebar">
    <!-- Brand/Logo Section -->
    <div class="sidebar-brand">
        <div class="brand-content">
            <div class="brand-icon">
                <i class=""></i>
            </div>
            <h4 class="brand-text">HelpDesk Pro</h4>
        </div>
        <button class="sidebar-toggle" id="sidebarToggle" type="button">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>

    <!-- Navigation Menu -->
    <ul class="sidebar-nav">
        <!-- Dashboard -->
        <li class="sidebar-nav-item">
            <a class="sidebar-nav-link <?= $ruta_actual === 'dashboard' ? 'active' : '' ?>" 
               href="?ruta=dashboard"
               data-tooltip="Dashboard">
                <i class="fas fa-tachometer-alt"></i>
                <span class="nav-text">Dashboard</span>
            </a>
        </li>
        
        <!-- Tickets -->
        <li class="sidebar-nav-item">
            <a class="sidebar-nav-link <?= str_starts_with($ruta_actual, 'tickets') ? 'active' : '' ?>" 
               href="?ruta=tickets"
               data-tooltip="Tickets">
                <i class="fas fa-ticket-alt"></i>
                <span class="nav-text">Tickets</span>
            </a>
        </li>
        
        <!-- Mis Tickets -->
        <li class="sidebar-nav-item">
            <a class="sidebar-nav-link <?= $ruta_actual === 'mis-tickets' ? 'active' : '' ?>" 
               href="?ruta=mis-tickets"
               data-tooltip="Mis Tickets">
                <i class="fas fa-user-clock"></i>
                <span class="nav-text">Mis Tickets</span>
            </a>
        </li>
        
        <!-- Separador para roles superiores -->
        <?php if ($usuario_rol >= 2): ?>
        <li class="sidebar-nav-item">
            <div class="nav-separator"></div>
        </li>
        
        <!-- Usuarios (Técnico o Admin) -->
        <li class="sidebar-nav-item">
            <a class="sidebar-nav-link <?= str_starts_with($ruta_actual, 'usuarios') ? 'active' : '' ?>" 
               href="?ruta=usuarios"
               data-tooltip="Usuarios">
                <i class="fas fa-users"></i>
                <span class="nav-text">Usuarios</span>
            </a>
        </li>
        
        <!-- Reportes (Técnico o Admin) -->
        <li class="sidebar-nav-item">
            <a class="sidebar-nav-link <?= $ruta_actual === 'reportes' ? 'active' : '' ?>" 
               href="?ruta=reportes"
               data-tooltip="Reportes">
                <i class="fas fa-chart-bar"></i>
                <span class="nav-text">Reportes</span>
            </a>
        </li>
        <?php endif; ?>
        
        <!-- Solo Admin -->
        <?php if ($usuario_rol >= 3): ?>
        <li class="sidebar-nav-item">
            <div class="nav-separator"></div>
        </li>
        
        <!-- SLA -->
        <li class="sidebar-nav-item">
            <a class="sidebar-nav-link <?= $ruta_actual === 'sla' ? 'active' : '' ?>" 
               href="?ruta=sla"
               data-tooltip="SLA">
                <i class="fas fa-clock"></i>
                <span class="nav-text">SLA</span>
            </a>
        </li>
        
        <!-- Auditoría -->
        <li class="sidebar-nav-item">
            <a class="sidebar-nav-link <?= $ruta_actual === 'audit' ? 'active' : '' ?>" 
               href="?ruta=audit"
               data-tooltip="Auditoría">
                <i class="fas fa-history"></i>
                <span class="nav-text">Auditoría</span>
            </a>
        </li>
        
        <!-- Configuración -->
        <li class="sidebar-nav-item">
            <a class="sidebar-nav-link <?= $ruta_actual === 'settings' ? 'active' : '' ?>" 
               href="?ruta=settings"
               data-tooltip="Configuración">
                <i class="fas fa-cog"></i>
                <span class="nav-text">Configuración</span>
            </a>
        </li>
        <?php endif; ?>
        
        <!-- Separador final -->
        <li class="sidebar-nav-item">
            <div class="nav-separator"></div>
        </li>
        
        <!-- Mi Perfil -->
        <li class="sidebar-nav-item">
            <a class="sidebar-nav-link <?= $ruta_actual === 'perfil' ? 'active' : '' ?>" 
               href="?ruta=perfil"
               data-tooltip="Mi Perfil">
                <i class="fas fa-user"></i>
                <span class="nav-text">Mi Perfil</span>
            </a>
        </li>
        
        <!-- Cerrar Sesión -->
        <li class="sidebar-nav-item">
            <a class="sidebar-nav-link" 
               href="?ruta=logout"
               data-tooltip="Cerrar Sesión">
                <i class="fas fa-sign-out-alt"></i>
                <span class="nav-text">Cerrar Sesión</span>
            </a>
        </li>
    </ul>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('appSidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const appLayout = document.querySelector('.app-layout');
    
    // Cargar estado del sidebar desde localStorage
    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (sidebarCollapsed) {
        sidebar.classList.add('sidebar-collapsed');
        appLayout.classList.add('sidebar-collapsed');
    }
    
    // Toggle sidebar
    sidebarToggle?.addEventListener('click', function() {
        const isCollapsed = sidebar.classList.contains('sidebar-collapsed');
        
        if (isCollapsed) {
            sidebar.classList.remove('sidebar-collapsed');
            appLayout.classList.remove('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', 'false');
        } else {
            sidebar.classList.add('sidebar-collapsed');
            appLayout.classList.add('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', 'true');
        }
    });
    
    // Tooltips para sidebar colapsado
    const navLinks = document.querySelectorAll('.sidebar-nav-link');
    let tooltip = null;
    
    navLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            if (sidebar.classList.contains('sidebar-collapsed')) {
                const tooltipText = this.getAttribute('data-tooltip');
                if (tooltipText) {
                    tooltip = document.createElement('div');
                    tooltip.className = 'sidebar-tooltip';
                    tooltip.textContent = tooltipText;
                    document.body.appendChild(tooltip);
                    
                    const rect = this.getBoundingClientRect();
                    tooltip.style.left = (rect.right + 10) + 'px';
                    tooltip.style.top = (rect.top + rect.height / 2 - tooltip.offsetHeight / 2) + 'px';
                }
            }
        });
        
        link.addEventListener('mouseleave', function() {
            if (tooltip) {
                tooltip.remove();
                tooltip = null;
            }
        });
    });
    
    // Cerrar tooltip al hacer scroll
    document.addEventListener('scroll', function() {
        if (tooltip) {
            tooltip.remove();
            tooltip = null;
        }
    });
});
</script>

<style>
/* Estilos adicionales para separadores */
.nav-separator {
    height: 1px;
    background: rgba(255, 255, 255, 0.1);
    margin: 0.5rem 1rem;
}

.app-sidebar.sidebar-collapsed .nav-separator {
    margin: 0.5rem;
}

/* Animación del toggle button */
.sidebar-toggle i {
    transition: transform 0.3s ease;
}

.app-sidebar.sidebar-collapsed .sidebar-toggle i {
    transform: rotate(180deg);
}

/* Estados de hover mejorados */
.sidebar-nav-link:hover i {
    transform: scale(1.1);
}

.sidebar-nav-link.active i {
    color: currentColor;
}

/* Responsive fixes */
@media (max-width: 1024px) {
    .app-sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .app-sidebar.sidebar-open {
        transform: translateX(0);
    }
}
</style>