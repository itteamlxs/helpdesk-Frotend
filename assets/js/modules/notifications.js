// /assets/js/modules/notifications.js - Sistema de Notificaciones Completo

let notificationsManager = {
    data: {
        notifications: [],
        config: null,
        resumen: null,
        currentPage: 1,
        itemsPerPage: 20,
        filters: {
            tipo: '',
            leida: '',
            importante: ''
        }
    },
    
    // Configuración
    settings: {
        autoRefreshInterval: 30000, // 30 segundos
        maxRetries: 3,
        retryDelay: 5000,
        autoMarkAsReadDelay: 5000
    },
    
    // Estado
    state: {
        isInitialized: false,
        refreshTimer: null,
        retryCount: 0,
        lastUpdate: null
    }
};

// ================================================================================================
// 🚀 INICIALIZACIÓN
// ================================================================================================

document.addEventListener('DOMContentLoaded', function() {
    // Solo inicializar si estamos en la página de notificaciones
    if (window.location.search.includes('ruta=notificaciones')) {
        initNotificationsPage();
    }
});

async function initNotificationsPage() {
    try {
        console.log('📱 Inicializando página de notificaciones...');
        
        notificationsManager.state.isInitialized = true;
        
        // Cargar datos iniciales
        await Promise.all([
            loadAllNotifications(),
            loadNotificationConfig(),
            loadNotificationStats()
        ]);
        
        // Configurar interfaz
        setupEventListeners();
        setupAutoRefresh();
        
        // Mostrar datos
        renderNotifications();
        updateStatsDisplay();
        
        console.log('✅ Página de notificaciones inicializada');
        
    } catch (error) {
        console.error('❌ Error inicializando notificaciones:', error);
        showError('Error al cargar sistema de notificaciones');
    }
}

// ================================================================================================
// 📥 CARGA DE DATOS
// ================================================================================================

async function loadAllNotifications() {
    try {
        const params = new URLSearchParams({
            limit: notificationsManager.data.itemsPerPage,
            page: notificationsManager.data.currentPage
        });
        
        // Agregar filtros
        Object.entries(notificationsManager.data.filters).forEach(([key, value]) => {
            if (value) params.append(key, value);
        });
        
        const response = await fetch(`?api=1&ruta=notifications&${params}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        notificationsManager.data.notifications = data.notificaciones || [];
        notificationsManager.state.lastUpdate = new Date();
        notificationsManager.state.retryCount = 0;
        
        return data;
        
    } catch (error) {
        console.error('Error cargando notificaciones:', error);
        await handleLoadError();
        throw error;
    }
}

async function loadNotificationConfig() {
    try {
        const response = await fetch('?api=1&ruta=notifications/config', {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            notificationsManager.data.config = await response.json();
        }
        
    } catch (error) {
        console.error('Error cargando configuración:', error);
    }
}

async function loadNotificationStats() {
    try {
        const response = await fetch('?api=1&ruta=notifications/summary', {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            notificationsManager.data.resumen = data.resumen;
        }
        
    } catch (error) {
        console.error('Error cargando estadísticas:', error);
    }
}

// ================================================================================================
// 🎨 RENDERIZADO DE INTERFAZ
// ================================================================================================

function renderNotifications() {
    const container = document.getElementById('notificationsContainer');
    const notifications = notificationsManager.data.notifications;
    
    if (!container) return;
    
    if (notifications.length === 0) {
        container.innerHTML = renderEmptyState();
        return;
    }
    
    const notificationsHTML = notifications.map(notification => 
        renderNotificationCard(notification)
    ).join('');
    
    container.innerHTML = notificationsHTML;
    
    // Actualizar paginación
    renderPagination();
    
    // Actualizar contadores
    updateCounters();
}

function renderNotificationCard(notification) {
    const unreadClass = !notification.leida ? 'border-primary bg-light' : '';
    const importantClass = notification.importante ? 'border-start border-warning border-4' : '';
    const iconClass = getNotificationIconClass(notification.tipo);
    const timeAgo = formatTimeAgo(notification.creado_en);
    
    return `
        <div class="card mb-3 notification-card ${unreadClass} ${importantClass}" 
             data-notification-id="${notification.id}">
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <!-- Icono de notificación -->
                    <div class="notification-icon ${iconClass.bg} me-3">
                        <i class="${notification.icono || iconClass.icon}"></i>
                    </div>
                    
                    <!-- Contenido principal -->
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1 fw-bold">
                                    ${notification.titulo}
                                    ${notification.importante ? '<i class="fas fa-star text-warning ms-1"></i>' : ''}
                                </h6>
                                <p class="mb-2 text-muted">${notification.mensaje}</p>
                            </div>
                            
                            <!-- Acciones -->
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                        type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    ${!notification.leida ? `
                                        <li><a class="dropdown-item" href="#" onclick="markAsRead(${notification.id})">
                                            <i class="fas fa-check text-success"></i> Marcar como leída
                                        </a></li>
                                    ` : `
                                        <li><a class="dropdown-item" href="#" onclick="markAsUnread(${notification.id})">
                                            <i class="fas fa-undo text-info"></i> Marcar como no leída
                                        </a></li>
                                    `}
                                    ${notification.url ? `
                                        <li><a class="dropdown-item" href="${notification.url}">
                                            <i class="fas fa-external-link-alt text-primary"></i> Ir al enlace
                                        </a></li>
                                    ` : ''}
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteNotification(${notification.id})">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Información adicional -->
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <div class="d-flex align-items-center">
                                <span class="badge ${getTypeBadgeClass(notification.tipo)} me-2">
                                    ${notification.tipo}
                                </span>
                                
                                ${notification.ticket_id ? `
                                    <a href="?ruta=tickets-ver&id=${notification.ticket_id}" 
                                       class="badge bg-info text-decoration-none">
                                        <i class="fas fa-ticket-alt"></i> Ticket #${notification.ticket_id}
                                    </a>
                                ` : ''}
                            </div>
                            
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> ${timeAgo}
                            </small>
                        </div>
                        
                        <!-- Información del ticket si existe -->
                        ${notification.ticket_titulo ? `
                            <div class="mt-2 p-2 bg-light rounded">
                                <small class="text-muted">
                                    <strong>Ticket:</strong> ${notification.ticket_titulo}
                                    <span class="badge bg-${getTicketStatusClass(notification.ticket_estado)} ms-2">
                                        ${notification.ticket_estado}
                                    </span>
                                </small>
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;
}

function renderEmptyState() {
    return `
        <div class="text-center py-5">
            <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">No hay notificaciones</h4>
            <p class="text-muted">
                Te notificaremos cuando haya nuevas actividades en el sistema.
            </p>
            <button class="btn btn-primary" onclick="refreshNotifications()">
                <i class="fas fa-sync"></i> Refrescar
            </button>
        </div>
    `;
}

function renderPagination() {
    const container = document.getElementById('paginationContainer');
    if (!container) return;
    
    const totalNotifications = notificationsManager.data.resumen?.total_notificaciones || 0;
    const totalPages = Math.ceil(totalNotifications / notificationsManager.data.itemsPerPage);
    const currentPage = notificationsManager.data.currentPage;
    
    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<nav><ul class="pagination justify-content-center">';
    
    // Botón anterior
    html += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <button class="page-link" onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
                <i class="fas fa-chevron-left"></i>
            </button>
        </li>
    `;
    
    // Páginas
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);
    
    for (let i = startPage; i <= endPage; i++) {
        html += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <button class="page-link" onclick="changePage(${i})">${i}</button>
            </li>
        `;
    }
    
    // Botón siguiente
    html += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <button class="page-link" onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
                <i class="fas fa-chevron-right"></i>
            </button>
        </li>
    `;
    
    html += '</ul></nav>';
    container.innerHTML = html;
}

// ================================================================================================
// 🎛️ CONFIGURACIÓN DE EVENTOS
// ================================================================================================

function setupEventListeners() {
    // Filtros
    document.getElementById('filtroTipo')?.addEventListener('change', applyFilters);
    document.getElementById('filtroLeida')?.addEventListener('change', applyFilters);
    document.getElementById('filtroImportante')?.addEventListener('change', applyFilters);
    
    // Botones de acción masiva
    document.getElementById('markAllReadBtn')?.addEventListener('click', markAllAsRead);
    document.getElementById('deleteAllReadBtn')?.addEventListener('click', deleteAllRead);
    
    // Auto-refrescar
    document.getElementById('autoRefreshToggle')?.addEventListener('change', toggleAutoRefresh);
}

function setupAutoRefresh() {
    if (notificationsManager.state.refreshTimer) {
        clearInterval(notificationsManager.state.refreshTimer);
    }
    
    notificationsManager.state.refreshTimer = setInterval(() => {
        if (notificationsManager.state.isInitialized) {
            refreshNotifications(false); // Silencioso
        }
    }, notificationsManager.settings.autoRefreshInterval);
}

// ================================================================================================
// 🔄 ACCIONES DE NOTIFICACIONES
// ================================================================================================

async function markAsRead(notificationId) {
    try {
        const response = await fetch(`?api=1&ruta=notifications/mark-read&id=${notificationId}`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            // Actualizar notificación local
            const notification = notificationsManager.data.notifications.find(n => n.id === notificationId);
            if (notification) {
                notification.leida = true;
                notification.leida_en = new Date().toISOString();
            }
            
            // Re-renderizar
            renderNotifications();
            showSuccess('Notificación marcada como leída');
        }
        
    } catch (error) {
        console.error('Error marcando como leída:', error);
        showError('Error al marcar notificación');
    }
}

async function markAsUnread(notificationId) {
    try {
        // Implementar endpoint para marcar como no leída
        showInfo('Funcionalidad próximamente disponible');
        
    } catch (error) {
        console.error('Error marcando como no leída:', error);
    }
}

async function deleteNotification(notificationId) {
    if (!confirm('¿Estás seguro de eliminar esta notificación?')) {
        return;
    }
    
    try {
        const response = await fetch(`?api=1&ruta=notifications/delete&id=${notificationId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            // Remover de la lista local
            notificationsManager.data.notifications = notificationsManager.data.notifications.filter(
                n => n.id !== notificationId
            );
            
            // Re-renderizar
            renderNotifications();
            showSuccess('Notificación eliminada');
        }
        
    } catch (error) {
        console.error('Error eliminando notificación:', error);
        showError('Error al eliminar notificación');
    }
}

async function markAllAsRead() {
    if (!confirm('¿Marcar todas las notificaciones como leídas?')) {
        return;
    }
    
    try {
        const response = await fetch('?api=1&ruta=notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const result = await response.json();
            
            // Actualizar notificaciones locales
            notificationsManager.data.notifications.forEach(n => {
                n.leida = true;
                n.leida_en = new Date().toISOString();
            });
            
            renderNotifications();
            showSuccess(`${result.notificaciones_marcadas || 0} notificaciones marcadas como leídas`);
        }
        
    } catch (error) {
        console.error('Error marcando todas como leídas:', error);
        showError('Error al marcar notificaciones');
    }
}

async function deleteAllRead() {
    if (!confirm('¿Eliminar todas las notificaciones leídas? Esta acción no se puede deshacer.')) {
        return;
    }
    
    try {
        showInfo('Funcionalidad próximamente disponible');
        
    } catch (error) {
        console.error('Error eliminando leídas:', error);
    }
}

// ================================================================================================
// 🔍 FILTROS Y NAVEGACIÓN
// ================================================================================================

function applyFilters() {
    const tipo = document.getElementById('filtroTipo')?.value || '';
    const leida = document.getElementById('filtroLeida')?.value || '';
    const importante = document.getElementById('filtroImportante')?.value || '';
    
    notificationsManager.data.filters = { tipo, leida, importante };
    notificationsManager.data.currentPage = 1;
    
    loadAllNotifications().then(() => {
        renderNotifications();
    });
}

function clearFilters() {
    document.getElementById('filtroTipo').value = '';
    document.getElementById('filtroLeida').value = '';
    document.getElementById('filtroImportante').value = '';
    
    notificationsManager.data.filters = { tipo: '', leida: '', importante: '' };
    notificationsManager.data.currentPage = 1;
    
    loadAllNotifications().then(() => {
        renderNotifications();
    });
}

function changePage(page) {
    if (page < 1) return;
    
    notificationsManager.data.currentPage = page;
    
    loadAllNotifications().then(() => {
        renderNotifications();
        
        // Scroll to top
        document.querySelector('.main-content')?.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

function changeItemsPerPage() {
    const select = document.getElementById('itemsPerPageSelect');
    if (select) {
        notificationsManager.data.itemsPerPage = parseInt(select.value);
        notificationsManager.data.currentPage = 1;
        
        loadAllNotifications().then(() => {
            renderNotifications();
        });
    }
}

// ================================================================================================
// 🔧 UTILIDADES Y HELPERS
// ================================================================================================

function updateStatsDisplay() {
    const resumen = notificationsManager.data.resumen;
    if (!resumen) return;
    
    // Actualizar cards de estadísticas
    document.getElementById('totalNotifications')?.textContent = resumen.total_notificaciones || 0;
    document.getElementById('unreadNotifications')?.textContent = resumen.no_leidas || 0;
    document.getElementById('importantNotifications')?.textContent = resumen.importantes || 0;
}

function updateCounters() {
    const notifications = notificationsManager.data.notifications;
    const unread = notifications.filter(n => !n.leida).length;
    const important = notifications.filter(n => n.importante).length;
    
    document.getElementById('currentUnreadCount')?.textContent = unread;
    document.getElementById('currentImportantCount')?.textContent = important;
}

function getNotificationIconClass(tipo) {
    const classes = {
        'info': { bg: 'bg-info-subtle text-info', icon: 'fas fa-info-circle' },
        'success': { bg: 'bg-success-subtle text-success', icon: 'fas fa-check-circle' },
        'warning': { bg: 'bg-warning-subtle text-warning', icon: 'fas fa-exclamation-triangle' },
        'danger': { bg: 'bg-danger-subtle text-danger', icon: 'fas fa-exclamation-circle' },
        'ticket': { bg: 'bg-primary-subtle text-primary', icon: 'fas fa-ticket-alt' },
        'sla': { bg: 'bg-warning-subtle text-warning', icon: 'fas fa-clock' },
        'sistema': { bg: 'bg-secondary-subtle text-secondary', icon: 'fas fa-cog' }
    };
    
    return classes[tipo] || classes['info'];
}

function getTypeBadgeClass(tipo) {
    const classes = {
        'info': 'bg-info',
        'success': 'bg-success',
        'warning': 'bg-warning text-dark',
        'danger': 'bg-danger',
        'ticket': 'bg-primary',
        'sla': 'bg-warning text-dark',
        'sistema': 'bg-secondary'
    };
    
    return classes[tipo] || 'bg-secondary';
}

function getTicketStatusClass(estado) {
    const classes = {
        'abierto': 'primary',
        'en_proceso': 'warning',
        'en_espera': 'info',
        'cerrado': 'success'
    };
    
    return classes[estado] || 'secondary';
}

function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInMinutes = Math.floor((now - date) / (1000 * 60));
    
    if (diffInMinutes < 1) return 'Hace un momento';
    if (diffInMinutes < 60) return `Hace ${diffInMinutes} min`;
    
    const diffInHours = Math.floor(diffInMinutes / 60);
    if (diffInHours < 24) return `Hace ${diffInHours}h`;
    
    const diffInDays = Math.floor(diffInHours / 24);
    if (diffInDays < 7) return `Hace ${diffInDays}d`;
    
    return date.toLocaleDateString('es-ES');
}

async function refreshNotifications(showMessage = true) {
    try {
        await loadAllNotifications();
        renderNotifications();
        
        if (showMessage) {
            showSuccess('Notificaciones actualizadas');
        }
        
    } catch (error) {
        console.error('Error refrescando notificaciones:', error);
        if (showMessage) {
            showError('Error al actualizar notificaciones');
        }
    }
}

function toggleAutoRefresh() {
    const toggle = document.getElementById('autoRefreshToggle');
    
    if (toggle?.checked) {
        setupAutoRefresh();
        showInfo('Auto-actualización activada');
    } else {
        if (notificationsManager.state.refreshTimer) {
            clearInterval(notificationsManager.state.refreshTimer);
            notificationsManager.state.refreshTimer = null;
        }
        showInfo('Auto-actualización desactivada');
    }
}

async function handleLoadError() {
    notificationsManager.state.retryCount++;
    
    if (notificationsManager.state.retryCount < notificationsManager.settings.maxRetries) {
        console.log(`Reintentando cargar notificaciones (intento ${notificationsManager.state.retryCount})...`);
        
        setTimeout(() => {
            loadAllNotifications();
        }, notificationsManager.settings.retryDelay);
    } else {
        console.error('Máximo número de reintentos alcanzado');
        showError('Error persistente al cargar notificaciones. Recarga la página.');
    }
}

// Funciones de mensajes
function showSuccess(message) {
    if (typeof showAlert === 'function') {
        showAlert(message, 'success');
    }
}

function showError(message) {
    if (typeof showAlert === 'function') {
        showAlert(message, 'danger');
    }
}

function showInfo(message) {
    if (typeof showAlert === 'function') {
        showAlert(message, 'info');
    }
}

// Limpiar al salir de la página
window.addEventListener('beforeunload', function() {
    if (notificationsManager.state.refreshTimer) {
        clearInterval(notificationsManager.state.refreshTimer);
    }
});

// Hacer funciones globales para ser llamadas desde HTML
window.markAsRead = markAsRead;
window.markAsUnread = markAsUnread;
window.deleteNotification = deleteNotification;
window.markAllAsRead = markAllAsRead;
window.deleteAllRead = deleteAllRead;
window.applyFilters = applyFilters;
window.clearFilters = clearFilters;
window.changePage = changePage;
window.changeItemsPerPage = changeItemsPerPage;
window.refreshNotifications = refreshNotifications;
window.toggleAutoRefresh = toggleAutoRefresh;