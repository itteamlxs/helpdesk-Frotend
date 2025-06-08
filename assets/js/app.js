// /assets/js/app.js - Funciones globales

// Logout function mejorada
window.logout = async function() {
    if (confirm('¿Estás seguro de cerrar sesión?')) {
        // Redirigir a la página de logout
        window.location.href = '?ruta=logout';
    }
};

// Función alternativa para logout directo (sin confirmación)
window.logoutDirect = async function() {
    window.location.href = '?ruta=logout';
};

// Función para logout de emergencia (si la API falla)
window.logoutEmergency = function() {
    // Limpiar almacenamiento local
    if (typeof Storage !== "undefined") {
        sessionStorage.clear();
        localStorage.clear();
    }
    
    // Redirigir al login
    window.location.href = '?ruta=login';
};

// Utilidades globales
window.formatDate = function(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

window.getPriorityBadge = function(prioridad) {
    const badges = {
        'baja': 'badge bg-secondary',
        'media': 'badge bg-info',
        'alta': 'badge bg-warning',
        'urgente': 'badge bg-danger'
    };
    return badges[prioridad] || 'badge bg-secondary';
};

window.getStatusBadge = function(estado) {
    const badges = {
        'abierto': 'badge bg-primary',
        'en_proceso': 'badge bg-warning',
        'en_espera': 'badge bg-info',
        'cerrado': 'badge bg-success'
    };
    return badges[estado] || 'badge bg-secondary';
};

// Loading spinner
window.showLoading = function(element) {
    if (typeof element === 'string') {
        element = document.getElementById(element);
    }
    if (element) {
        element.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
    }
};

// Confirmación de eliminación
window.confirmDelete = function(message = '¿Estás seguro de eliminar este elemento?') {
    return confirm(message);
};

// Detectar si la sesión expiró (opcional)
window.checkSession = async function() {
    try {
        const response = await fetch('?api=1&ruta=csrf-token', {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        if (response.status === 401) {
            showAlert('Tu sesión ha expirado', 'warning');
            setTimeout(() => {
                window.location.href = '?ruta=login';
            }, 2000);
        }
    } catch (error) {
        // Ignorar errores de conexión
    }
};

// Verificar sesión cada 10 minutos (opcional)
// setInterval(checkSession, 10 * 60 * 1000);