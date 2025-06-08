<div class="sidebar-sticky">
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?= ($_GET['ruta'] ?? '') === 'dashboard' ? 'active' : '' ?>" 
               href="?ruta=dashboard">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?= str_starts_with($_GET['ruta'] ?? '', 'tickets') ? 'active' : '' ?>" 
               href="?ruta=tickets">
                <i class="fas fa-ticket-alt"></i> Tickets
            </a>
        </li>
        
        <?php if (($_SESSION['usuario']['rol_id'] ?? 0) >= 2): // Técnico o Admin ?>
        <li class="nav-item">
            <a class="nav-link <?= str_starts_with($_GET['ruta'] ?? '', 'usuarios') ? 'active' : '' ?>" 
               href="?ruta=usuarios">
                <i class="fas fa-users"></i> Usuarios
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (($_SESSION['usuario']['rol_id'] ?? 0) >= 3): // Solo Admin ?>
        <li class="nav-item">
            <a class="nav-link <?= ($_GET['ruta'] ?? '') === 'sla' ? 'active' : '' ?>" 
               href="?ruta=sla">
                <i class="fas fa-clock"></i> SLA
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?= ($_GET['ruta'] ?? '') === 'audit' ? 'active' : '' ?>" 
               href="?ruta=audit">
                <i class="fas fa-history"></i> Auditoría
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?= ($_GET['ruta'] ?? '') === 'settings' ? 'active' : '' ?>" 
               href="?ruta=settings">
                <i class="fas fa-cog"></i> Configuración
            </a>
        </li>
        <?php endif; ?>
    </ul>
</div>