// /assets/js/modules/tickets.js

let todosLosTickets = [];

document.addEventListener('DOMContentLoaded', function() {
    cargarTickets();
    configurarFiltros();
});

async function cargarTickets() {
    try {
        showLoading('ticketsTableBody');
        todosLosTickets = await api.getTickets();
        mostrarTickets(todosLosTickets);
    } catch (error) {
        console.error('Error cargando tickets:', error);
        document.getElementById('ticketsTableBody').innerHTML = 
            '<tr><td colspan="8" class="text-center text-danger">Error al cargar tickets</td></tr>';
        showAlert('Error al cargar tickets', 'danger');
    }
}

function mostrarTickets(tickets) {
    const tbody = document.getElementById('ticketsTableBody');
    
    if (tickets.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No se encontraron tickets</td></tr>';
        return;
    }
    
    tbody.innerHTML = tickets.map(ticket => `
        <tr>
            <td>#${ticket.id}</td>
            <td>
                <a href="?ruta=tickets-ver&id=${ticket.id}" class="text-decoration-none fw-bold">
                    ${ticket.titulo}
                </a>
                <br>
                <small class="text-muted">${ticket.categoria || 'Sin categoría'}</small>
            </td>
            <td><span class="${getStatusBadge(ticket.estado)}">${ticket.estado.replace('_', ' ')}</span></td>
            <td><span class="${getPriorityBadge(ticket.prioridad)}">${ticket.prioridad}</span></td>
            <td>${ticket.cliente || 'N/A'}</td>
            <td>${ticket.tecnico || '<span class="text-muted">Sin asignar</span>'}</td>
            <td>
                <small>${formatDate(ticket.creado_en)}</small>
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    <a href="?ruta=tickets-ver&id=${ticket.id}" class="btn btn-outline-primary btn-sm" title="Ver">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="?ruta=tickets-editar&id=${ticket.id}" class="btn btn-outline-warning btn-sm" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button class="btn btn-outline-danger btn-sm" onclick="eliminarTicket(${ticket.id})" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function configurarFiltros() {
    const filtroEstado = document.getElementById('filtroEstado');
    const filtroPrioridad = document.getElementById('filtroPrioridad');
    const busqueda = document.getElementById('busqueda');
    
    [filtroEstado, filtroPrioridad, busqueda].forEach(elemento => {
        elemento.addEventListener('change', aplicarFiltros);
        elemento.addEventListener('input', aplicarFiltros);
    });
}

function aplicarFiltros() {
    const estado = document.getElementById('filtroEstado').value;
    const prioridad = document.getElementById('filtroPrioridad').value;
    const busqueda = document.getElementById('busqueda').value.toLowerCase();
    
    let ticketsFiltrados = todosLosTickets.filter(ticket => {
        const cumpleEstado = !estado || ticket.estado === estado;
        const cumplePrioridad = !prioridad || ticket.prioridad === prioridad;
        const cumpleBusqueda = !busqueda || 
            ticket.titulo.toLowerCase().includes(busqueda) ||
            (ticket.descripcion && ticket.descripcion.toLowerCase().includes(busqueda));
        
        return cumpleEstado && cumplePrioridad && cumpleBusqueda;
    });
    
    mostrarTickets(ticketsFiltrados);
}

function limpiarFiltros() {
    document.getElementById('filtroEstado').value = '';
    document.getElementById('filtroPrioridad').value = '';
    document.getElementById('busqueda').value = '';
    mostrarTickets(todosLosTickets);
}

async function eliminarTicket(id) {
    if (!confirmDelete('¿Estás seguro de eliminar este ticket?')) {
        return;
    }
    
    try {
        await api.deleteTicket(id);
        showAlert('Ticket eliminado correctamente', 'success');
        cargarTickets(); // Recargar lista
    } catch (error) {
        console.error('Error eliminando ticket:', error);
        showAlert('Error al eliminar ticket', 'danger');
    }
}