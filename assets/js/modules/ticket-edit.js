// /assets/js/modules/ticket-edit.js

let ticketActual = null;
let valoresOriginales = {};
let tecnicos = [];
let cambiosPendientes = false;

document.addEventListener('DOMContentLoaded', function() {
    const ticketId = obtenerTicketId();
    if (ticketId) {
        cargarTicket(ticketId);
    } else {
        mostrarError('ID de ticket no válido');
    }
});

function obtenerTicketId() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id');
}

async function cargarTicket(id) {
    try {
        console.log('Cargando ticket para edición:', id);
        
        // Cargar ticket y técnicos en paralelo
        const [ticket, usuarios] = await Promise.all([
            api.getTicket(id),
            api.getUsuarios()
        ]);
        
        ticketActual = ticket;
        tecnicos = usuarios.filter(u => u.rol_id >= 2); // Solo técnicos y admins
        
        console.log('Ticket cargado:', ticketActual);
        
        // Verificar permisos
        if (!verificarPermisosEdicion()) {
            mostrarError('No tienes permisos para editar este ticket', 403);
            return;
        }
        
        // Mostrar formulario
        mostrarFormulario();
        cargarDatosFormulario();
        configurarEventListeners();
        
        // Ocultar loading y mostrar form
        document.getElementById('loadingCard').style.display = 'none';
        document.getElementById('formCard').style.display = 'block';
        document.getElementById('infoCard').style.display = 'block';
        document.getElementById('historialCard').style.display = 'block';
        document.getElementById('accionesCard').style.display = 'block';
        
    } catch (error) {
        console.error('Error cargando ticket:', error);
        mostrarError(error.message);
    }
}

function verificarPermisosEdicion() {
    const userRole = obtenerRolUsuario();
    const userId = obtenerUsuarioId();
    
    // Técnicos y admins pueden editar cualquier ticket
    if (userRole >= 2) {
        return true;
    }
    
    // Clientes solo pueden editar sus propios tickets
    if (userRole === 1 && ticketActual.cliente_id == userId) {
        return true;
    }
    
    return false;
}

function mostrarFormulario() {
    const userRole = obtenerRolUsuario();
    
    // Mostrar campos de administración solo para técnicos/admins
    if (userRole >= 2) {
        document.getElementById('camposAdmin').style.display = 'block';
        cargarTecnicos();
    }
    
    // Actualizar títulos
    document.getElementById('tituloTicket').innerHTML = 
        `<i class="fas fa-edit"></i> Editando Ticket #${ticketActual.id}`;
    document.getElementById('breadcrumbTicket').innerHTML = 
        `<a href="?ruta=tickets-ver&id=${ticketActual.id}">Ticket #${ticketActual.id}</a>`;
}

function cargarDatosFormulario() {
    // Cargar datos básicos
    document.getElementById('titulo').value = ticketActual.titulo || '';
    document.getElementById('descripcion').value = ticketActual.descripcion || '';
    document.getElementById('categoria').value = ticketActual.categoria || '';
    document.getElementById('prioridad').value = ticketActual.prioridad || '';
    
    // Cargar datos de administración si están disponibles
    const userRole = obtenerRolUsuario();
    if (userRole >= 2) {
        document.getElementById('estado').value = ticketActual.estado || 'abierto';
        document.getElementById('tecnico_id').value = ticketActual.tecnico_id || '';
    }
    
    // Actualizar información lateral
    actualizarInfoLateral();
    
    // Guardar valores originales
    guardarValoresOriginales();
    
    // Actualizar estado visual
    actualizarEstadoVisual();
}

function cargarTecnicos() {
    const select = document.getElementById('tecnico_id');
    if (select && tecnicos.length > 0) {
        select.innerHTML = '<option value="">Sin asignar</option>' +
            tecnicos.map(t => `<option value="${t.id}">${t.nombre}</option>`).join('');
    }
}

function guardarValoresOriginales() {
    valoresOriginales = {
        titulo: document.getElementById('titulo').value,
        descripcion: document.getElementById('descripcion').value,
        categoria: document.getElementById('categoria').value,
        prioridad: document.getElementById('prioridad').value
    };
    
    const userRole = obtenerRolUsuario();
    if (userRole >= 2) {
        valoresOriginales.estado = document.getElementById('estado').value;
        valoresOriginales.tecnico_id = document.getElementById('tecnico_id').value;
    }
}

function configurarEventListeners() {
    // Event listener del formulario
    document.getElementById('formEditarTicket').addEventListener('submit', guardarCambios);
    
    // Detectar cambios en todos los campos
    const campos = ['titulo', 'descripcion', 'categoria', 'prioridad'];
    const userRole = obtenerRolUsuario();
    
    if (userRole >= 2) {
        campos.push('estado', 'tecnico_id');
    }
    
    campos.forEach(campo => {
        const elemento = document.getElementById(campo);
        if (elemento) {
            elemento.addEventListener('input', detectarCambios);
            elemento.addEventListener('change', detectarCambios);
        }
    });
    
    // Auto-resize del textarea
    const descripcion = document.getElementById('descripcion');
    descripcion.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });
    
    // Contador de caracteres para título
    document.getElementById('titulo').addEventListener('input', function() {
        const contador = this.value.length;
        const max = 255;
        const restante = max - contador;
        
        let texto = document.querySelector('#titulo + .form-text');
        if (restante < 50) {
            texto.textContent = `${restante} caracteres restantes`;
            texto.className = restante < 20 ? 'form-text text-warning' : 'form-text text-info';
        } else {
            texto.textContent = `Máximo ${max} caracteres`;
            texto.className = 'form-text';
        }
    });
}

function detectarCambios() {
    const camposActuales = {
        titulo: document.getElementById('titulo').value,
        descripcion: document.getElementById('descripcion').value,
        categoria: document.getElementById('categoria').value,
        prioridad: document.getElementById('prioridad').value
    };
    
    const userRole = obtenerRolUsuario();
    if (userRole >= 2) {
        camposActuales.estado = document.getElementById('estado').value;
        camposActuales.tecnico_id = document.getElementById('tecnico_id').value;
    }
    
    // Detectar qué campos han cambiado
    const cambios = [];
    for (const [campo, valor] of Object.entries(camposActuales)) {
        if (valor !== valoresOriginales[campo]) {
            cambios.push(campo);
        }
    }
    
    cambiosPendientes = cambios.length > 0;
    
    // Actualizar UI
    actualizarIndicadorCambios(cambios);
    
    // Habilitar/deshabilitar botón guardar
    const btnGuardar = document.getElementById('btnGuardarCambios');
    btnGuardar.disabled = !cambiosPendientes;
    
    if (cambiosPendientes) {
        btnGuardar.classList.remove('btn-success');
        btnGuardar.classList.add('btn-warning');
        btnGuardar.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios (' + cambios.length + ')';
    } else {
        btnGuardar.classList.remove('btn-warning');
        btnGuardar.classList.add('btn-success');
        btnGuardar.innerHTML = '<i class="fas fa-check"></i> Sin Cambios';
    }
}

function actualizarIndicadorCambios(cambios) {
    const indicador = document.getElementById('cambiosPendientes');
    const resumen = document.getElementById('resumenCambios');
    
    if (cambios.length === 0) {
        indicador.classList.add('d-none');
        return;
    }
    
    indicador.classList.remove('d-none');
    
    const nombresAmigables = {
        titulo: 'Título',
        descripcion: 'Descripción',
        categoria: 'Categoría',
        prioridad: 'Prioridad',
        estado: 'Estado',
        tecnico_id: 'Técnico Asignado'
    };
    
    const cambiosTexto = cambios.map(campo => nombresAmigables[campo] || campo);
    resumen.textContent = cambiosTexto.join(', ');
}

async function guardarCambios(event) {
    event.preventDefault();
    
    if (!cambiosPendientes) {
        showAlert('No hay cambios para guardar', 'info');
        return;
    }
    
    // Validar formulario
    if (!validarFormulario()) {
        return;
    }
    
    // Preparar datos solo con los campos cambiados
    const cambios = {};
    const camposActuales = {
        titulo: document.getElementById('titulo').value.trim(),
        descripcion: document.getElementById('descripcion').value.trim(),
        categoria: document.getElementById('categoria').value,
        prioridad: document.getElementById('prioridad').value
    };
    
    const userRole = obtenerRolUsuario();
    if (userRole >= 2) {
        camposActuales.estado = document.getElementById('estado').value;
        camposActuales.tecnico_id = document.getElementById('tecnico_id').value || null;
    }
    
    // Solo incluir campos que han cambiado
    for (const [campo, valor] of Object.entries(camposActuales)) {
        if (valor !== valoresOriginales[campo]) {
            cambios[campo] = valor;
        }
    }
    
    console.log('Guardando cambios:', cambios);
    
    try {
        // Deshabilitar botón y mostrar loading
        const btnGuardar = document.getElementById('btnGuardarCambios');
        const textoOriginal = btnGuardar.innerHTML;
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        
        // Guardar cambios
        await api.updateTicket(ticketActual.id, cambios);
        
        // Actualizar ticket local
        Object.assign(ticketActual, cambios);
        
        // Actualizar valores originales
        guardarValoresOriginales();
        
        // Resetear indicadores
        cambiosPendientes = false;
        detectarCambios();
        
        // Actualizar info lateral
        actualizarInfoLateral();
        actualizarEstadoVisual();
        
        // Mostrar confirmación
        mostrarConfirmacion(cambios);
        
        // Restaurar botón
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = textoOriginal;
        
    } catch (error) {
        console.error('Error guardando cambios:', error);
        showAlert('Error al guardar cambios: ' + error.message, 'danger');
        
        // Restaurar botón
        const btnGuardar = document.getElementById('btnGuardarCambios');
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
    }
}

function validarFormulario() {
    const errores = [];
    
    const titulo = document.getElementById('titulo').value.trim();
    const descripcion = document.getElementById('descripcion').value.trim();
    const categoria = document.getElementById('categoria').value;
    const prioridad = document.getElementById('prioridad').value;
    
    if (!titulo || titulo.length < 5) {
        errores.push('El título debe tener al menos 5 caracteres');
    }
    
    if (titulo.length > 255) {
        errores.push('El título no puede exceder 255 caracteres');
    }
    
    if (!descripcion || descripcion.length < 10) {
        errores.push('La descripción debe tener al menos 10 caracteres');
    }
    
    if (!categoria) {
        errores.push('Debes seleccionar una categoría');
    }
    
    if (!prioridad) {
        errores.push('Debes seleccionar una prioridad');
    }
    
    if (errores.length > 0) {
        showAlert('Por favor corrige los siguientes errores:<br>• ' + errores.join('<br>• '), 'warning');
        return false;
    }
    
    return true;
}

function mostrarConfirmacion(cambios) {
    const cambiosTexto = [];
    
    if (cambios.titulo) cambiosTexto.push('Título actualizado');
    if (cambios.descripcion) cambiosTexto.push('Descripción actualizada');
    if (cambios.categoria) cambiosTexto.push(`Categoría: ${cambios.categoria}`);
    if (cambios.prioridad) cambiosTexto.push(`Prioridad: ${cambios.prioridad}`);
    if (cambios.estado) cambiosTexto.push(`Estado: ${cambios.estado.replace('_', ' ')}`);
    if (cambios.tecnico_id !== undefined) {
        const tecnico = tecnicos.find(t => t.id == cambios.tecnico_id);
        cambiosTexto.push(`Técnico: ${tecnico ? tecnico.nombre : 'Sin asignar'}`);
    }
    
    document.getElementById('resumenCambiosGuardados').innerHTML = 
        '<strong>Cambios aplicados:</strong><br>• ' + cambiosTexto.join('<br>• ');
    
    const modal = new bootstrap.Modal(document.getElementById('confirmacionModal'));
    modal.show();
}

function mostrarVistaPrevia() {
    if (!cambiosPendientes) {
        showAlert('No hay cambios para previsualizar', 'info');
        return;
    }
    
    // Comparar valores actuales vs originales
    const actuales = document.getElementById('valoresActuales');
    const nuevos = document.getElementById('valoresNuevos');
    
    const campos = [
        { id: 'titulo', nombre: 'Título' },
        { id: 'descripcion', nombre: 'Descripción' },
        { id: 'categoria', nombre: 'Categoría' },
        { id: 'prioridad', nombre: 'Prioridad' }
    ];
    
    const userRole = obtenerRolUsuario();
    if (userRole >= 2) {
        campos.push(
            { id: 'estado', nombre: 'Estado' },
            { id: 'tecnico_id', nombre: 'Técnico' }
        );
    }
    
    let htmlActuales = '';
    let htmlNuevos = '';
    
    campos.forEach(campo => {
        const valorOriginal = valoresOriginales[campo.id];
        const valorNuevo = document.getElementById(campo.id).value;
        
        const claseResultado = valorOriginal !== valorNuevo ? 'text-warning' : '';
        
        htmlActuales += `<div class="mb-2 ${claseResultado}"><strong>${campo.nombre}:</strong><br>${valorOriginal || '<em>Vacío</em>'}</div>`;
        htmlNuevos += `<div class="mb-2 ${claseResultado}"><strong>${campo.nombre}:</strong><br>${valorNuevo || '<em>Vacío</em>'}</div>`;
    });
    
    actuales.innerHTML = htmlActuales;
    nuevos.innerHTML = htmlNuevos;
    
    const modal = new bootstrap.Modal(document.getElementById('vistaPreviaModal'));
    modal.show();
}

function guardarCambiosDesdeModal() {
    // Cerrar modal de vista previa
    bootstrap.Modal.getInstance(document.getElementById('vistaPreviaModal')).hide();
    
    // Disparar guardado
    document.getElementById('formEditarTicket').dispatchEvent(new Event('submit'));
}

function resetearFormulario() {
    if (!confirm('¿Estás seguro de descartar todos los cambios?')) {
        return;
    }
    
    cargarDatosFormulario();
    cambiosPendientes = false;
    detectarCambios();
    
    showAlert('Formulario restablecido a los valores originales', 'info');
}

function actualizarInfoLateral() {
    document.getElementById('ticketId').textContent = `#${ticketActual.id}`;
    document.getElementById('ticketCliente').textContent = ticketActual.cliente || 'N/A';
    document.getElementById('ticketCreado').textContent = formatDate(ticketActual.creado_en);
    document.getElementById('ticketActualizado').textContent = formatDate(ticketActual.actualizado_en);
    document.getElementById('fechaCreacion').textContent = formatDate(ticketActual.creado_en);
    
    // Cargar número de comentarios (simulado)
    if (ticketActual.comentarios_count !== undefined) {
        document.getElementById('totalComentarios').textContent = ticketActual.comentarios_count;
    }
}

function actualizarEstadoVisual() {
    const estadoBadges = document.getElementById('estadoTicket');
    if (estadoBadges) {
        estadoBadges.innerHTML = `
            <span class="${getStatusBadge(ticketActual.estado)}">${ticketActual.estado.replace('_', ' ')}</span>
            <span class="${getPriorityBadge(ticketActual.prioridad)} ms-2">${ticketActual.prioridad}</span>
        `;
    }
}

function volverAlTicket() {
    if (cambiosPendientes) {
        if (confirm('Tienes cambios sin guardar. ¿Estás seguro de salir?')) {
            window.location.href = `?ruta=tickets-ver&id=${ticketActual.id}`;
        }
    } else {
        window.location.href = `?ruta=tickets-ver&id=${ticketActual.id}`;
    }
}

function mostrarError(mensaje, codigo = 500) {
    document.getElementById('loadingCard').style.display = 'none';
    document.getElementById('formCard').style.display = 'none';
    document.getElementById('errorCard').classList.remove('d-none');
    document.getElementById('mensajeError').textContent = mensaje;
    
    if (codigo === 403) {
        setTimeout(() => {
            window.location.href = `?ruta=tickets-ver&id=${obtenerTicketId()}`;
        }, 3000);
    }
}

// Utilidades
function obtenerRolUsuario() {
    return window.userRole || 1;
}

function obtenerUsuarioId() {
    return window.userId || 1;
}

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

// Prevenir pérdida de datos al salir
window.addEventListener('beforeunload', function(e) {
    if (cambiosPendientes) {
        e.preventDefault();
        e.returnValue = '';
        return '';
    }
});