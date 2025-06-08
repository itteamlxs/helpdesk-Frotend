// /assets/js/modules/dashboard.js (CON MÁS DEBUG)

document.addEventListener('DOMContentLoaded', async function() {
    console.log('Dashboard cargado, iniciando...'); // Debug
    await cargarEstadisticas();
    await cargarTicketsRecientes();
});

async function cargarEstadisticas() {
    try {
        console.log('Cargando estadísticas...'); // Debug
        const tickets = await api.getTickets();
        console.log('Tickets obtenidos:', tickets); // Debug
        
        // Verificar que tickets es array
        if (!Array.isArray(tickets)) {
            console.error('Tickets no es un array:', tickets);
            throw new Error('Datos de tickets inválidos');
        }
        
        // Calcular estadísticas
        const total = tickets.length;
        const abiertos = tickets.filter(t => t.estado === 'abierto').length;
        const urgentes = tickets.filter(t => t.prioridad === 'urgente' || t.prioridad === 'alta').length;
        
        // Tickets resueltos hoy
        const hoy = new Date().toISOString().split('T')[0];
        const resueltosHoy = tickets.filter(t => 
            t.estado === 'cerrado' && 
            t.actualizado_en && 
            t.actualizado_en.startsWith(hoy)
        ).length;
        
        console.log('Estadísticas calculadas:', { total, abiertos, urgentes, resueltosHoy }); // Debug
        
        // Actualizar UI
        const totalElement = document.getElementById('totalTickets');
        const abiertosElement = document.getElementById('ticketsAbiertos');
        const urgentesElement = document.getElementById('ticketsUrgentes');
        const resueltosElement = document.getElementById('ticketsResueltos');
        
        if (totalElement) totalElement.textContent = total;
        if (abiertosElement) abiertosElement.textContent = abiertos;
        if (urgentesElement) urgentesElement.textContent = urgentes;
        if (resueltosElement) resueltosElement.textContent = resueltosHoy;
        
    } catch (error) {
        console.error('Error cargando estadísticas:', error);
        // Mostrar error en las cards
        ['totalTickets', 'ticketsAbiertos', 'ticketsUrgentes', 'ticketsResueltos'].forEach(id => {
            const element = document.getElementById(id);
            if (element) element.textContent = 'Error';
        });
        
        if (typeof showAlert === 'function') {
            showAlert('Error al cargar estadísticas: ' + error.message, 'danger');
        }
    }
}

async function cargarTicketsRecientes() {
    try {
        console.log('Cargando tickets recientes...'); // Debug
        const tickets = await api.getTickets();
        const tbody = document.getElementById('ticketsRecientes');
        
        if (!tbody) {
            console.error('Elemento ticketsRecientes no encontrado');
            return;
        }
        
        // Verificar que tickets es array
        if (!Array.isArray(tickets)) {
            console.error('Tickets no es un array:', tickets);
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error: Datos inválidos</td></tr>';
            return;
        }
        
        // Ordenar por fecha (más recientes primero) y tomar solo 5
        const ticketsRecientes = tickets
            .sort((a, b) => new Date(b.creado_en) - new Date(a.creado_en))
            .slice(0, 5);
        
        console.log('Tickets recientes:', ticketsRecientes); // Debug
        
        if (ticketsRecientes.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No hay tickets disponibles</td></tr>';
            return;
        }
        
        tbody.innerHTML = ticketsRecientes.map(ticket => `
            <tr>
                <td>#${ticket.id}</td>
                <td>
                    <a href="?ruta=tickets-ver&id=${ticket.id}" class="text-decoration-none">
                        ${ticket.titulo || 'Sin título'}
                    </a>
                </td>
                <td><span class="${getStatusBadge(ticket.estado)}">${ticket.estado || 'Sin estado'}</span></td>
                <td><span class="${getPriorityBadge(ticket.prioridad)}">${ticket.prioridad || 'Sin prioridad'}</span></td>
                <td>${ticket.cliente || 'N/A'}</td>
                <td>${formatDate(ticket.creado_en)}</td>
            </tr>
        `).join('');
        
    } catch (error) {
        console.error('Error cargando tickets recientes:', error);
        const tbody = document.getElementById('ticketsRecientes');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Error al cargar tickets: ${error.message}</td></tr>`;
        }
    }
}

// Funciones de utilidad (en caso de que no estén en app.js)
function getStatusBadge(estado) {
    const badges = {
        'abierto': 'badge bg-primary',
        'en_proceso': 'badge bg-warning',
        'en_espera': 'badge bg-info',
        'cerrado': 'badge bg-success'
    };
    return badges[estado] || 'badge bg-secondary';
}

function getPriorityBadge(prioridad) {
    const badges = {
        'baja': 'badge bg-secondary',
        'media': 'badge bg-info',
        'alta': 'badge bg-warning',
        'urgente': 'badge bg-danger'
    };
    return badges[prioridad] || 'badge bg-secondary';
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}