// /assets/js/modules/ticket-show.js

let ticketActual = null;
let comentarios = [];
let tecnicos = [];

document.addEventListener('DOMContentLoaded', function() {
    const ticketId = obtenerTicketId();
    if (ticketId) {
        cargarTicket(ticketId);
        cargarComentarios(ticketId);
        cargarTecnicos();
        configurarPermisosUI();
    } else {
        showAlert('ID de ticket no válido', 'danger');
        setTimeout(() => window.location.href = '?ruta=tickets', 2000);
    }

    // Event listeners
    document.getElementById('formNuevoComentario').addEventListener('submit', enviarComentario);
});

function obtenerTicketId() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id');
}

function configurarPermisosUI() {
    const userRole = obtenerRolUsuario();
    console.log('Configurando UI para rol:', userRole);
    
    // 🔐 PERMISOS SEGÚN ROL
    if (userRole >= 2) { 
        // Técnico o Admin - Ver todo y editar
        document.getElementById('accionesRapidas').style.display = 'block';
        document.getElementById('btnEditar').style.display = 'inline-block';
    } else { 
        // Cliente - Solo ver
        document.getElementById('accionesRapidas').style.display = 'none';
        document.getElementById('btnEditar').style.display = 'none';
    }
}

async function cargarTicket(id) {
    try {
        console.log('Cargando ticket:', id);
        ticketActual = await api.getTicket(id);
        console.log('Ticket cargado:', ticketActual);
        
        mostrarTicket(ticketActual);
        actualizarTimeline(ticketActual);
        mostrarSLA(ticketActual);
        
    } catch (error) {
        console.error('Error cargando ticket:', error);
        if (error.message.includes('sin permisos')) {
            showAlert('No tienes permisos para ver este ticket', 'danger');
            setTimeout(() => window.location.href = '?ruta=tickets', 2000);
        } else {
            showAlert('Error al cargar el ticket: ' + error.message, 'danger');
        }
    }
}

function mostrarTicket(ticket) {
    // Header y breadcrumb
    document.getElementById('breadcrumbTicket').textContent = `#${ticket.id} - ${ticket.titulo}`;
    document.getElementById('ticketTitulo').innerHTML = `
        <i class="fas fa-ticket-alt"></i> #${ticket.id} - ${ticket.titulo}
    `;
    
    // Badges
    document.getElementById('ticketBadges').innerHTML = `
        <span class="${getStatusBadge(ticket.estado)}">${ticket.estado.replace('_', ' ')}</span>
        <span class="${getPriorityBadge(ticket.prioridad)} ms-2">${ticket.prioridad}</span>
    `;
    
    // Información básica
    document.getElementById('ticketId').textContent = `#${ticket.id}`;
    document.getElementById('ticketCliente').textContent = ticket.cliente || 'N/A';
    document.getElementById('ticketTecnico').textContent = ticket.tecnico || 'Sin asignar';
    document.getElementById('ticketCategoria').textContent = ticket.categoria || 'Sin categoría';
    document.getElementById('ticketCreado').textContent = formatDate(ticket.creado_en);
    document.getElementById('ticketActualizado').textContent = formatDate(ticket.actualizado_en);
    document.getElementById('ticketDescripcion').innerHTML = 
        ticket.descripcion ? ticket.descripcion.replace(/\n/g, '<br>') : 'Sin descripción';
    
    // Estados (solo si tiene permisos)
    const userRole = obtenerRolUsuario();
    if (userRole >= 2) {
        document.getElementById('cambiarEstado').value = ticket.estado;
        document.getElementById('asignarTecnico').value = ticket.tecnico_id || '';
    }
}

async function cargarComentarios(ticketId) {
    try {
        console.log('Cargando comentarios para ticket:', ticketId);
        
        // 🔧 LLAMADA DIRECTA A LA API
        const url = `${window.BASE_PATH}/public/index.php?api=1&ruta=comments&ticket_id=${ticketId}`;
        console.log('URL comentarios:', url);
        
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Comentarios obtenidos:', data);
        
        // Verificar que sea array
        if (!Array.isArray(data)) {
            console.error('Respuesta no es array:', data);
            throw new Error('Respuesta inválida del servidor');
        }
        
        comentarios = data;
        mostrarComentarios(comentarios);
        
    } catch (error) {
        console.error('Error cargando comentarios:', error);
        if (error.message.includes('sin permisos')) {
            document.getElementById('listaComentarios').innerHTML = `
                <div class="text-center p-4 text-warning">
                    <i class="fas fa-lock"></i><br>
                    Sin permisos para ver comentarios
                </div>
            `;
        } else {
            document.getElementById('listaComentarios').innerHTML = `
                <div class="text-center p-4 text-danger">
                    <i class="fas fa-exclamation-triangle"></i><br>
                    Error al cargar comentarios: ${error.message}
                </div>
            `;
        }
    }
}

function mostrarComentarios(comentarios) {
    const container = document.getElementById('listaComentarios');
    
    console.log('Mostrando comentarios:', comentarios);
    console.log('Container encontrado:', container);
    
    if (!container) {
        console.error('Container listaComentarios no encontrado');
        return;
    }
    
    // Actualizar contador
    document.getElementById('contadorComentarios').textContent = comentarios.length;
    
    if (comentarios.length === 0) {
        container.innerHTML = `
            <div class="text-center p-4 text-muted">
                <i class="fas fa-comment-slash fa-2x mb-2"></i><br>
                No hay comentarios aún
            </div>
        `;
        return;
    }
    
    const userRole = obtenerRolUsuario();
    console.log('Rol usuario:', userRole);
    
    const comentariosHTML = comentarios.map((comentario, index) => {
        console.log(`Procesando comentario ${index}:`, comentario);
        
        const esInterno = !!comentario.interno;
        const borderClass = esInterno ? 'border-warning' : 'border-light';
        const bgClass = esInterno ? 'bg-warning bg-opacity-10' : '';
        
        return `
            <div class="border-bottom ${borderClass} ${bgClass} p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <strong>${comentario.autor || 'Usuario'}</strong>
                        ${esInterno ? '<span class="badge bg-warning text-dark ms-2"><i class="fas fa-lock"></i> Interno</span>' : ''}
                    </div>
                    <small class="text-muted">${formatDate(comentario.creado_en)}</small>
                </div>
                <div class="comment-content">
                    ${(comentario.contenido || '').replace(/\n/g, '<br>')}
                </div>
            </div>
        `;
    });
    
    console.log('HTML generado:', comentariosHTML);
    
    if (comentariosHTML.length === 0) {
        container.innerHTML = `
            <div class="text-center p-4 text-muted">
                <i class="fas fa-eye-slash fa-2x mb-2"></i><br>
                No hay comentarios visibles
            </div>
        `;
    } else {
        container.innerHTML = comentariosHTML.join('');
    }
}

async function cargarTecnicos() {
    try {
        const usuarios = await api.getUsuarios();
        tecnicos = usuarios.filter(u => u.rol_id >= 2); // Técnicos y admins
        
        const select = document.getElementById('asignarTecnico');
        if (select) {
            select.innerHTML = '<option value="">Sin asignar</option>' +
                tecnicos.map(t => `<option value="${t.id}">${t.nombre}</option>`).join('');
                
            // Seleccionar técnico actual si existe
            if (ticketActual && ticketActual.tecnico_id) {
                select.value = ticketActual.tecnico_id;
            }
        }
        
    } catch (error) {
        console.error('Error cargando técnicos:', error);
    }
}

function actualizarTimeline(ticket) {
    const timeline = document.getElementById('ticketTimeline');
    const ahora = new Date();
    const creado = new Date(ticket.creado_en);
    const tiempoTranscurrido = Math.floor((ahora - creado) / (1000 * 60 * 60)); // horas
    
    timeline.innerHTML = `
        <div class="timeline-item mb-2">
            <i class="fas fa-plus-circle text-primary"></i>
            <span class="ms-2">Ticket creado</span>
            <br><small class="text-muted ms-3">${formatDate(ticket.creado_en)}</small>
        </div>
        <div class="timeline-item mb-2">
            <i class="fas fa-clock text-info"></i>
            <span class="ms-2">Tiempo transcurrido</span>
            <br><small class="text-muted ms-3">${tiempoTranscurrido}h</small>
        </div>
        <div class="timeline-item">
            <i class="fas fa-${ticket.estado === 'cerrado' ? 'check-circle text-success' : 'hourglass-half text-warning'}"></i>
            <span class="ms-2">Estado: ${ticket.estado.replace('_', ' ')}</span>
            <br><small class="text-muted ms-3">${formatDate(ticket.actualizado_en)}</small>
        </div>
    `;
}

function mostrarSLA(ticket) {
    const slaContainer = document.getElementById('slaInfo');
    
    // Información básica de SLA según prioridad
    const tiemposSLA = {
        'urgente': { respuesta: 1, resolucion: 4 },
        'alta': { respuesta: 4, resolucion: 12 },
        'media': { respuesta: 12, resolucion: 24 },
        'baja': { respuesta: 24, resolucion: 48 }
    };
    
    const sla = tiemposSLA[ticket.prioridad] || tiemposSLA['media'];
    
    slaContainer.innerHTML = `
        <div class="row text-center">
            <div class="col-6">
                <div class="border-end">
                    <div class="h5 text-primary">${sla.respuesta}h</div>
                    <small class="text-muted">Tiempo respuesta</small>
                </div>
            </div>
            <div class="col-6">
                <div class="h5 text-success">${sla.resolucion}h</div>
                <small class="text-muted">Tiempo resolución</small>
            </div>
        </div>
        <hr>
        <div class="text-center">
            <span class="badge ${ticket.estado === 'cerrado' ? 'bg-success' : 'bg-warning'}">
                ${ticket.estado === 'cerrado' ? 'Resuelto' : 'En progreso'}
            </span>
        </div>
    `;
}

async function cambiarEstadoTicket() {
    const nuevoEstado = document.getElementById('cambiarEstado').value;
    if (!ticketActual || nuevoEstado === ticketActual.estado) return;
    
    const userRole = obtenerRolUsuario();
    if (userRole < 2) {
        showAlert('Sin permisos para cambiar estado', 'warning');
        document.getElementById('cambiarEstado').value = ticketActual.estado;
        return;
    }
    
    try {
        await api.updateTicket(ticketActual.id, { estado: nuevoEstado });
        ticketActual.estado = nuevoEstado;
        mostrarTicket(ticketActual);
        showAlert(`Estado cambiado a: ${nuevoEstado.replace('_', ' ')}`, 'success');
        
        // Recargar para actualizar timeline
        setTimeout(() => window.location.reload(), 1000);
        
    } catch (error) {
        console.error('Error cambiando estado:', error);
        showAlert('Error al cambiar estado: ' + error.message, 'danger');
        document.getElementById('cambiarEstado').value = ticketActual.estado; // Revertir
    }
}

async function asignarTecnicoTicket() {
    const nuevoTecnicoId = document.getElementById('asignarTecnico').value;
    if (!ticketActual) return;
    
    const userRole = obtenerRolUsuario();
    if (userRole < 2) {
        showAlert('Sin permisos para asignar técnico', 'warning');
        return;
    }
    
    try {
        await api.updateTicket(ticketActual.id, { 
            tecnico_id: nuevoTecnicoId || null 
        });
        
        const tecnico = tecnicos.find(t => t.id == nuevoTecnicoId);
        const nombreTecnico = tecnico ? tecnico.nombre : 'Sin asignar';
        
        document.getElementById('ticketTecnico').textContent = nombreTecnico;
        showAlert(`Técnico asignado: ${nombreTecnico}`, 'success');
        
    } catch (error) {
        console.error('Error asignando técnico:', error);
        showAlert('Error al asignar técnico: ' + error.message, 'danger');
    }
}

function toggleComentarioForm() {
    const form = document.getElementById('comentarioForm');
    form.classList.toggle('d-none');
    
    if (!form.classList.contains('d-none')) {
        // 🔐 MOSTRAR/OCULTAR CHECKBOX INTERNO SEGÚN ROL
        const checkboxContainer = document.querySelector('.form-check');
        const userRole = obtenerRolUsuario();
        
        console.log('Toggle comentario form - Rol usuario:', userRole);
        
        if (userRole >= 2) { // Técnico o Admin
            checkboxContainer.style.display = 'block';
            console.log('Checkbox interno visible para técnico/admin');
        } else { // Cliente
            checkboxContainer.style.display = 'none';
            // Asegurar que esté desmarcado
            document.getElementById('comentarioInterno').checked = false;
            console.log('Checkbox interno oculto para cliente');
        }
        
        document.getElementById('contenidoComentario').focus();
    }
}

async function enviarComentario(event) {
    event.preventDefault();
    
    const contenido = document.getElementById('contenidoComentario').value.trim();
    const interno = document.getElementById('comentarioInterno').checked;
    
    if (!contenido) {
        showAlert('El comentario no puede estar vacío', 'warning');
        return;
    }
    
    console.log('Enviando comentario:', {
        ticketId: ticketActual.id,
        contenido: contenido,
        interno: interno
    });
    
    try {
        const data = {
            contenido: contenido,
            interno: interno
            // No enviar usuario_id - se toma de la sesión en el backend (más seguro)
        };
        
        const response = await api.createComentario(ticketActual.id, data);
        
        showAlert('Comentario agregado correctamente', 'success');
        
        // Limpiar formulario
        document.getElementById('contenidoComentario').value = '';
        document.getElementById('comentarioInterno').checked = false;
        toggleComentarioForm();
        
        // Recargar comentarios
        cargarComentarios(ticketActual.id);
        
    } catch (error) {
        console.error('Error enviando comentario:', error);
        if (error.message.includes('sin permisos')) {
            showAlert('No tienes permisos para comentar en este ticket', 'warning');
        } else {
            showAlert('Error al enviar comentario: ' + error.message, 'danger');
        }
    }
}

function editarTicket() {
    const userRole = obtenerRolUsuario();
    if (userRole < 2) {
        showAlert('Sin permisos para editar tickets', 'warning');
        return;
    }
    
    if (ticketActual) {
        window.location.href = `?ruta=tickets-editar&id=${ticketActual.id}`;
    }
}

// Utilidades
function obtenerRolUsuario() {
    // Verificar que window.userRole esté definido
    if (typeof window.userRole !== 'undefined') {
        console.log('Rol de window.userRole:', window.userRole);
        return window.userRole;
    }
    
    console.warn('window.userRole no definido, usando rol por defecto');
    return 1; // Por defecto cliente
}

function obtenerUsuarioId() {
    // Verificar que window.userId esté definido
    if (typeof window.userId !== 'undefined') {
        return window.userId;
    }
    
    console.warn('window.userId no definido');
    return 1; // Por defecto usuario 1
}

// Utilidades de formato
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