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
                <!-- 🔔 SISTEMA DE NOTIFICACIONES -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle position-relative" href="#" id="notificationsDropdown" 
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <!-- Contador de notificaciones no leídas -->
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
                              id="notificationCount" style="display: none;">
                            0
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end notification-dropdown" style="width: 350px; max-height: 500px;">
                        <!-- Header del dropdown -->
                        <li class="dropdown-header d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Notificaciones</span>
                            <div>
                                <button class="btn btn-sm btn-outline-primary me-1" onclick="markAllNotificationsAsRead()" 
                                        title="Marcar todas como leídas" id="markAllBtn" style="display: none;">
                                    <i class="fas fa-check-double"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" onclick="refreshNotifications()" title="Refrescar">
                                    <i class="fas fa-sync"></i>
                                </button>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        
                        <!-- Lista de notificaciones -->
                        <div id="notificationsList" style="max-height: 300px; overflow-y: auto;">
                            <!-- Loading initial -->
                            <li class="text-center py-3" id="notificationsLoading">
                                <i class="fas fa-spinner fa-spin text-primary"></i>
                                <div class="small text-muted mt-1">Cargando notificaciones...</div>
                            </li>
                        </div>
                        
                        <!-- Footer del dropdown -->
                        <li><hr class="dropdown-divider"></li>
                        <li class="dropdown-footer text-center">
                            <a href="?ruta=notificaciones" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-list"></i> Ver todas las notificaciones
                            </a>
                        </li>
                    </ul>
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
                        
                        <li><a class="dropdown-item" href="#" onclick="mostrarConfigNotificaciones()">
                            <i class="fas fa-bell-slash"></i> Configurar Notificaciones
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

<!-- Modal de configuración de notificaciones -->
<div class="modal fade" id="notificationConfigModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-bell-slash"></i> Configurar Notificaciones
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="notificationConfigForm">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Notificaciones en el Navegador</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="navegador_tickets" name="navegador_tickets">
                            <label class="form-check-label" for="navegador_tickets">
                                <i class="fas fa-ticket-alt text-primary"></i> Tickets (nuevos, asignados, comentarios)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="navegador_sla" name="navegador_sla">
                            <label class="form-check-label" for="navegador_sla">
                                <i class="fas fa-clock text-warning"></i> Alertas SLA (próximos a vencer)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="navegador_sistema" name="navegador_sistema">
                            <label class="form-check-label" for="navegador_sistema">
                                <i class="fas fa-cog text-info"></i> Sistema (mantenimientos, actualizaciones)
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Notificaciones por Email</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="email_tickets" name="email_tickets">
                            <label class="form-check-label" for="email_tickets">
                                <i class="fas fa-envelope text-primary"></i> Tickets importantes
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="email_sla" name="email_sla">
                            <label class="form-check-label" for="email_sla">
                                <i class="fas fa-envelope text-warning"></i> Alertas SLA críticas
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="email_sistema" name="email_sistema">
                            <label class="form-check-label" for="email_sistema">
                                <i class="fas fa-envelope text-info"></i> Notificaciones del sistema
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Resúmenes</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="resumen_diario" name="resumen_diario">
                            <label class="form-check-label" for="resumen_diario">
                                <i class="fas fa-calendar-day text-success"></i> Resumen diario por email
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="resumen_semanal" name="resumen_semanal">
                            <label class="form-check-label" for="resumen_semanal">
                                <i class="fas fa-calendar-week text-success"></i> Resumen semanal por email
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Horarios</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="solo_horario_laboral" name="solo_horario_laboral">
                            <label class="form-check-label" for="solo_horario_laboral">
                                <i class="fas fa-business-time text-secondary"></i> Solo notificar en horario laboral
                            </label>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <label for="no_molestar_inicio" class="form-label small">No molestar desde:</label>
                                <input type="time" class="form-control form-control-sm" id="no_molestar_inicio" name="no_molestar_inicio" value="22:00">
                            </div>
                            <div class="col-6">
                                <label for="no_molestar_fin" class="form-label small">Hasta:</label>
                                <input type="time" class="form-control form-control-sm" id="no_molestar_fin" name="no_molestar_fin" value="08:00">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="guardarConfigNotificaciones()">
                    <i class="fas fa-save"></i> Guardar Configuración
                </button>
            </div>
        </div>
    </div>
</div>

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

<!-- CSS personalizado para notificaciones -->
<style>
.notification-dropdown {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border-radius: 10px;
}

.notification-item {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    transition: background-color 0.2s;
    text-decoration: none;
    color: inherit;
    display: block;
}

.notification-item:hover {
    background-color: #f8f9fa;
    color: inherit;
    text-decoration: none;
}

.notification-item.unread {
    background-color: #e3f2fd;
    border-left: 4px solid #2196f3;
}

.notification-item.important {
    border-left: 4px solid #f44336;
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    margin-right: 0.75rem;
    flex-shrink: 0;
}

.notification-icon.tipo-info { background-color: #e3f2fd; color: #1976d2; }
.notification-icon.tipo-success { background-color: #e8f5e8; color: #2e7d32; }
.notification-icon.tipo-warning { background-color: #fff3e0; color: #f57c00; }
.notification-icon.tipo-danger { background-color: #ffebee; color: #d32f2f; }
.notification-icon.tipo-ticket { background-color: #f3e5f5; color: #7b1fa2; }
.notification-icon.tipo-sla { background-color: #fff8e1; color: #f9a825; }
.notification-icon.tipo-sistema { background-color: #f0f4c3; color: #689f38; }

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-title {
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.notification-message {
    font-size: 0.8rem;
    color: #6c757d;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: 0.25rem;
}

.notification-time {
    font-size: 0.75rem;
    color: #adb5bd;
}

.notification-actions {
    opacity: 0;
    transition: opacity 0.2s;
}

.notification-item:hover .notification-actions {
    opacity: 1;
}

#notificationCount {
    font-size: 0.7rem;
    min-width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.dropdown-footer {
    padding: 0.5rem 1rem;
    background-color: #f8f9fa;
    border-bottom-left-radius: 10px;
    border-bottom-right-radius: 10px;
}

.empty-notifications {
    padding: 2rem 1rem;
    text-align: center;
    color: #6c757d;
}

.empty-notifications i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    color: #dee2e6;
}
</style>

<script>
// ================================================================================================
// 🔔 SISTEMA DE NOTIFICACIONES - NAVBAR
// ================================================================================================

// Variables globales para notificaciones
let notificationsData = {
    resumen: null,
    preview: [],
    config: null,
    lastUpdate: null
};

// Inicializar sistema de notificaciones cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔔 Inicializando sistema de notificaciones...');
    
    // Cargar notificaciones iniciales
    loadNotifications();
    
    // Configurar actualización automática cada 30 segundos
    setInterval(loadNotifications, 30000);
    
    // Cargar configuración de notificaciones
    loadNotificationConfig();
    
    console.log('✅ Sistema de notificaciones inicializado');
});

// ================================================================================================
// 📥 CARGAR NOTIFICACIONES
// ================================================================================================

async function loadNotifications() {
    try {
        // Usar endpoint optimizado para resumen
        const response = await fetch('?api=1&ruta=notifications/summary', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        notificationsData = { ...notificationsData, ...data };
        
        updateNotificationUI();
        
    } catch (error) {
        console.error('Error cargando notificaciones:', error);
        handleNotificationError();
    }
}

function updateNotificationUI() {
    const { resumen, preview } = notificationsData;
    
    if (!resumen) return;
    
    // Actualizar contador en la campanita
    updateNotificationBadge(resumen.no_leidas);
    
    // Actualizar lista de preview
    updateNotificationsList(preview);
    
    // Mostrar/ocultar botón "marcar todas como leídas"
    toggleMarkAllButton(resumen.no_leidas > 0);
}

function updateNotificationBadge(count) {
    const badge = document.getElementById('notificationCount');
    
    if (count > 0) {
        badge.textContent = count > 99 ? '99+' : count;
        badge.style.display = 'flex';
        
        // Animar si hay notificaciones nuevas
        if (notificationsData.lastUpdate && count > (notificationsData.lastCount || 0)) {
            badge.style.animation = 'none';
            setTimeout(() => {
                badge.style.animation = 'pulse 2s infinite';
            }, 10);
        }
        
        notificationsData.lastCount = count;
    } else {
        badge.style.display = 'none';
    }
}

function updateNotificationsList(notifications) {
    const container = document.getElementById('notificationsList');
    const loading = document.getElementById('notificationsLoading');
    
    // Ocultar loading
    if (loading) {
        loading.style.display = 'none';
    }
    
    if (!notifications || notifications.length === 0) {
        container.innerHTML = `
            <li class="empty-notifications">
                <i class="fas fa-bell-slash"></i>
                <div>No tienes notificaciones</div>
                <small class="text-muted">Te notificaremos cuando haya nuevas actividades</small>
            </li>
        `;
        return;
    }
    
    container.innerHTML = notifications.map(notification => {
        const unreadClass = !notification.leida ? 'unread' : '';
        const importantClass = notification.importante ? 'important' : '';
        const iconClass = `notification-icon tipo-${notification.tipo}`;
        
        return `
            <li>
                <a href="#" class="notification-item ${unreadClass} ${importantClass}" 
                   onclick="handleNotificationClick(${notification.id}, '${notification.url || ''}'); return false;">
                    <div class="d-flex align-items-start">
                        <div class="${iconClass}">
                            <i class="${notification.icono}"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">${notification.titulo}</div>
                            <div class="notification-message">${notification.mensaje}</div>
                            <div class="notification-time">
                                ${notification.tiempo_transcurrido}
                                ${notification.importante ? '<i class="fas fa-star text-warning ms-1"></i>' : ''}
                            </div>
                        </div>
                        <div class="notification-actions ms-2">
                            <button class="btn btn-sm btn-outline-secondary" 
                                    onclick="markNotificationAsRead(${notification.id}); event.stopPropagation();" 
                                    title="Marcar como leída">
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </div>
                </a>
            </li>
        `;
    }).join('');
}

function toggleMarkAllButton(show) {
    const button = document.getElementById('markAllBtn');
    if (button) {
        button.style.display = show ? 'inline-block' : 'none';
    }
}

// ================================================================================================
// 🎬 ACCIONES DE NOTIFICACIONES
// ================================================================================================

async function handleNotificationClick(notificationId, url) {
    try {
        // Marcar como leída
        await markNotificationAsRead(notificationId);
        
        // Navegar si hay URL
        if (url) {
            if (url.startsWith('http') || url.startsWith('/')) {
                window.location.href = url;
            } else {
                window.location.href = '?' + url;
            }
        }
        
        // Cerrar dropdown
        const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('notificationsDropdown'));
        if (dropdown) {
            dropdown.hide();
        }
        
    } catch (error) {
        console.error('Error manejando click de notificación:', error);
    }
}

async function markNotificationAsRead(notificationId) {
    try {
        const response = await fetch(`?api=1&ruta=notifications/mark-read&id=${notificationId}`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        if (response.ok) {
            // Recargar notificaciones para actualizar UI
            loadNotifications();
        }
        
    } catch (error) {
        console.error('Error marcando notificación como leída:', error);
    }
}

async function markAllNotificationsAsRead() {
    try {
        const response = await fetch('?api=1&ruta=notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        if (response.ok) {
            // Recargar notificaciones
            loadNotifications();
            
            // Mostrar mensaje
            if (typeof showAlert === 'function') {
                showAlert('Todas las notificaciones marcadas como leídas', 'success');
            }
        }
        
    } catch (error) {
        console.error('Error marcando todas como leídas:', error);
    }
}

function refreshNotifications() {
    // Mostrar loading
    const loading = document.getElementById('notificationsLoading');
    if (loading) {
        loading.style.display = 'block';
    }
    
    // Recargar
    loadNotifications();
}

// ================================================================================================
// ⚙️ CONFIGURACIÓN DE NOTIFICACIONES
// ================================================================================================

async function loadNotificationConfig() {
    try {
        const response = await fetch('?api=1&ruta=notifications/config', {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const config = await response.json();
            notificationsData.config = config;
        }
        
    } catch (error) {
        console.error('Error cargando configuración:', error);
    }
}

function mostrarConfigNotificaciones() {
    const modal = new bootstrap.Modal(document.getElementById('notificationConfigModal'));
    
    // Llenar formulario con configuración actual
    if (notificationsData.config) {
        const config = notificationsData.config;
        
        // Checkboxes
        ['navegador_tickets', 'navegador_sla', 'navegador_sistema',
         'email_tickets', 'email_sla', 'email_sistema',
         'resumen_diario', 'resumen_semanal', 'solo_horario_laboral'].forEach(field => {
            const checkbox = document.getElementById(field);
            if (checkbox) {
                checkbox.checked = config[field] || false;
            }
        });
        
        // Horarios
        if (config.no_molestar_inicio) {
            document.getElementById('no_molestar_inicio').value = config.no_molestar_inicio;
        }
        if (config.no_molestar_fin) {
            document.getElementById('no_molestar_fin').value = config.no_molestar_fin;
        }
    }
    
    modal.show();
}

async function guardarConfigNotificaciones() {
    try {
        const form = document.getElementById('notificationConfigForm');
        const formData = new FormData(form);
        
        const config = {};
        
        // Recopilar checkboxes
        ['navegador_tickets', 'navegador_sla', 'navegador_sistema',
         'email_tickets', 'email_sla', 'email_sistema',
         'resumen_diario', 'resumen_semanal', 'solo_horario_laboral'].forEach(field => {
            const checkbox = document.getElementById(field);
            config[field] = checkbox ? checkbox.checked : false;
        });
        
        // Recopilar horarios
        config.no_molestar_inicio = document.getElementById('no_molestar_inicio').value;
        config.no_molestar_fin = document.getElementById('no_molestar_fin').value;
        
        const response = await fetch('?api=1&ruta=notifications/config', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(config)
        });
        
        if (response.ok) {
            // Cerrar modal
            bootstrap.Modal.getInstance(document.getElementById('notificationConfigModal')).hide();
            
            // Actualizar configuración local
            notificationsData.config = { ...notificationsData.config, ...config };
            
            // Mostrar mensaje
            if (typeof showAlert === 'function') {
                showAlert('Configuración de notificaciones guardada', 'success');
            }
        } else {
            throw new Error('Error al guardar configuración');
        }
        
    } catch (error) {
        console.error('Error guardando configuración:', error);
        if (typeof showAlert === 'function') {
            showAlert('Error al guardar configuración', 'danger');
        }
    }
}

// ================================================================================================
// 🔧 UTILIDADES
// ================================================================================================

function handleNotificationError() {
    const container = document.getElementById('notificationsList');
    const loading = document.getElementById('notificationsLoading');
    
    if (loading) {
        loading.style.display = 'none';
    }
    
    container.innerHTML = `
        <li class="text-center py-3 text-danger">
            <i class="fas fa-exclamation-triangle"></i>
            <div class="small">Error al cargar notificaciones</div>
            <button class="btn btn-sm btn-outline-primary mt-2" onclick="loadNotifications()">
                <i class="fas fa-retry"></i> Reintentar
            </button>
        </li>
    `;
}

function mostrarPerfilModal() {
    // const modal = new bootstrap.Modal(document.getElementById('perfilModal'));
    // modal.show();
    if (typeof showAlert === 'function') {
        showAlert('Funcionalidad de perfil próximamente', 'info');
    }
}

// Hacer funciones globales
window.markAllNotificationsAsRead = markAllNotificationsAsRead;
window.refreshNotifications = refreshNotifications;
window.mostrarConfigNotificaciones = mostrarConfigNotificaciones;
window.guardarConfigNotificaciones = guardarConfigNotificaciones;
window.mostrarPerfilModal = mostrarPerfilModal;
window.handleNotificationClick = handleNotificationClick;
window.markNotificationAsRead = markNotificationAsRead;
</script>