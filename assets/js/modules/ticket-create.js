// /assets/js/modules/ticket-create.js

let usuarios = [];
let ticketCreado = null;

document.addEventListener('DOMContentLoaded', function() {
    configurarFormulario();
    cargarUsuarios();
    configurarValidaciones();
});

function configurarFormulario() {
    const userRole = obtenerRolUsuario();
    
    // Solo técnicos/admins pueden crear tickets para otros usuarios
    if (userRole >= 2) {
        document.getElementById('selectorCliente').style.display = 'block';
    }
    
    // Event listener del formulario
    document.getElementById('formCrearTicket').addEventListener('submit', crearTicket);
    
    // Auto-resize del textarea
    const descripcion = document.getElementById('descripcion');
    descripcion.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });
    
    // Validación en tiempo real del título
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

async function cargarUsuarios() {
    const userRole = obtenerRolUsuario();
    
    // Solo cargar usuarios si es técnico/admin
    if (userRole < 2) return;
    
    try {
        usuarios = await api.getUsuarios();
        
        const select = document.getElementById('cliente_id');
        if (select) {
            // Filtrar solo clientes (rol_id = 1) para crear tickets
            const clientes = usuarios.filter(u => u.rol_id === 1);
            
            select.innerHTML = '<option value="">Selecciona un cliente</option>' +
                clientes.map(cliente => 
                    `<option value="${cliente.id}">${cliente.nombre} (${cliente.correo})</option>`
                ).join('');
        }
        
    } catch (error) {
        console.error('Error cargando usuarios:', error);
    }
}

function configurarValidaciones() {
    // Validación de prioridad con información contextual
    document.getElementById('prioridad').addEventListener('change', function() {
        const prioridad = this.value;
        const info = {
            'urgente': '🔴 El equipo será notificado inmediatamente. Tiempo de respuesta: 1 hora',
            'alta': '🟠 Prioridad alta en la cola. Tiempo de respuesta: 4 horas',
            'media': '🟡 Prioridad normal. Tiempo de respuesta: 12 horas',
            'baja': '🟢 Se atenderá según disponibilidad. Tiempo de respuesta: 24 horas'
        };
        
        const helpText = this.parentNode.querySelector('.form-text small');
        if (prioridad && info[prioridad]) {
            helpText.innerHTML = info[prioridad] + '<br><br>' + helpText.innerHTML.split('<br><br>')[1];
        }
    });
    
    // Validación de categoría con sugerencias
    document.getElementById('categoria').addEventListener('change', function() {
        const categoria = this.value;
        const sugerencias = {
            'Hardware': 'Incluye: problemas con computadoras, monitores, teclados, ratones, etc.',
            'Software': 'Incluye: aplicaciones que no funcionan, instalaciones, licencias, etc.',
            'Redes': 'Incluye: internet lento, sin conexión, WiFi, VPN, etc.',
            'Email': 'Incluye: problemas con Outlook, Gmail, no recibe/envía emails, etc.',
            'Impresoras': 'Incluye: no imprime, atascos de papel, calidad de impresión, etc.',
            'Accesos': 'Incluye: olvidé mi contraseña, sin acceso a carpetas/sistemas, etc.',
            'Telefonia': 'Incluye: teléfono no funciona, problemas de audio en llamadas, etc.',
            'Seguridad': 'Incluye: posible virus, comportamiento extraño del sistema, etc.'
        };
        
        if (categoria && sugerencias[categoria]) {
            showAlert(sugerencias[categoria], 'info', 3000);
        }
    });
}

async function crearTicket(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const data = {};
    
    // Recopilar datos del formulario
    for (let [key, value] of formData.entries()) {
        if (value.trim()) {
            data[key] = value.trim();
        }
    }
    
    // Validaciones adicionales
    if (!validarFormulario(data)) {
        return;
    }
    
    // Preparar datos para envío
    const ticketData = {
        titulo: data.titulo,
        descripcion: data.descripcion,
        categoria: data.categoria,
        prioridad: data.prioridad
    };
    
    // Solo técnicos/admins pueden especificar cliente_id
    const userRole = obtenerRolUsuario();
    if (userRole >= 2 && data.cliente_id) {
        ticketData.cliente_id = parseInt(data.cliente_id);
    }
    
    console.log('Creando ticket:', ticketData);
    
    try {
        // Deshabilitar botón y mostrar loading
        const btnCrear = document.getElementById('btnCrearTicket');
        const textoOriginal = btnCrear.innerHTML;
        btnCrear.disabled = true;
        btnCrear.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando...';
        
        // Crear ticket vía API
        const response = await api.createTicket(ticketData);
        
        console.log('Ticket creado:', response);
        
        // Mostrar modal de confirmación
        mostrarConfirmacion(response, ticketData);
        
        // Limpiar formulario
        document.getElementById('formCrearTicket').reset();
        
        // Restaurar botón
        btnCrear.disabled = false;
        btnCrear.innerHTML = textoOriginal;
        
    } catch (error) {
        console.error('Error creando ticket:', error);
        showAlert('Error al crear el ticket: ' + error.message, 'danger');
        
        // Restaurar botón
        const btnCrear = document.getElementById('btnCrearTicket');
        btnCrear.disabled = false;
        btnCrear.innerHTML = '<i class="fas fa-save"></i> Crear Ticket';
    }
}

function validarFormulario(data) {
    const errores = [];
    
    // Validar título
    if (!data.titulo || data.titulo.length < 5) {
        errores.push('El título debe tener al menos 5 caracteres');
    }
    
    if (data.titulo && data.titulo.length > 255) {
        errores.push('El título no puede exceder 255 caracteres');
    }
    
    // Validar descripción
    if (!data.descripcion || data.descripcion.length < 20) {
        errores.push('La descripción debe tener al menos 20 caracteres para una mejor resolución');
    }
    
    // Validar categoría
    if (!data.categoria) {
        errores.push('Debes seleccionar una categoría');
    }
    
    // Validar prioridad
    if (!data.prioridad) {
        errores.push('Debes seleccionar una prioridad');
    }
    
    // Mostrar errores si los hay
    if (errores.length > 0) {
        showAlert('Por favor corrige los siguientes errores:<br>• ' + errores.join('<br>• '), 'warning');
        return false;
    }
    
    return true;
}

function mostrarConfirmacion(response, ticketData) {
    // Guardar datos del ticket creado
    ticketCreado = {
        id: response.ticket_id,
        ...ticketData
    };
    
    // Actualizar modal con información
    document.getElementById('ticketNumero').textContent = `#${response.ticket_id}`;
    document.getElementById('ticketPrioridad').textContent = ticketData.prioridad;
    
    // Configurar botón "Ver Ticket"
    document.getElementById('btnVerTicket').onclick = function() {
        window.location.href = `?ruta=tickets-ver&id=${response.ticket_id}`;
    };
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('confirmacionModal'));
    modal.show();
    
    // Auto-redirigir después de 10 segundos si no hace nada
    setTimeout(() => {
        if (modal._isShown) {
            modal.hide();
            showAlert('Ticket creado correctamente. Puedes verlo en la lista de tickets.', 'success');
        }
    }, 10000);
}

// Función para obtener estadísticas rápidas (llamada desde el sidebar)
async function cargarEstadisticasRapidas() {
    try {
        const tickets = await api.getTickets();
        
        // Estadísticas del usuario actual
        const misTickets = tickets.length;
        const ticketsAbiertos = tickets.filter(t => t.estado === 'abierto').length;
        const ticketsUrgentes = tickets.filter(t => t.prioridad === 'urgente' || t.prioridad === 'alta').length;
        
        // Mostrar en el sidebar (si existe)
        const estadisticas = document.getElementById('estadisticasUsuario');
        if (estadisticas) {
            estadisticas.innerHTML = `
                <div class="row text-center">
                    <div class="col-4">
                        <div class="h6 text-primary">${misTickets}</div>
                        <small>Total</small>
                    </div>
                    <div class="col-4">
                        <div class="h6 text-warning">${ticketsAbiertos}</div>
                        <small>Abiertos</small>
                    </div>
                    <div class="col-4">
                        <div class="h6 text-danger">${ticketsUrgentes}</div>
                        <small>Urgentes</small>
                    </div>
                </div>
            `;
        }
        
    } catch (error) {
        console.error('Error cargando estadísticas:', error);
    }
}

// Función para autocompletar basado en historial (futuro)
function configurarAutocompletado() {
    // Esta función se puede expandir para sugerir títulos/descripciones
    // basados en tickets previos del usuario
    
    const titulo = document.getElementById('titulo');
    const descripcion = document.getElementById('descripcion');
    
    // Sugerencias comunes basadas en categoría
    document.getElementById('categoria').addEventListener('change', function() {
        const categoria = this.value;
        const sugerenciasTitulos = {
            'Hardware': ['Computadora no enciende', 'Monitor sin imagen', 'Teclado no funciona'],
            'Software': ['Aplicación se cierra inesperadamente', 'No puedo instalar programa', 'Error de licencia'],
            'Redes': ['Internet muy lento', 'No hay conexión WiFi', 'No puedo acceder a carpetas compartidas'],
            'Email': ['No recibo emails', 'Error al enviar correos', 'Problemas con Outlook'],
            'Impresoras': ['Impresora no responde', 'Calidad de impresión deficiente', 'Atasco de papel']
        };
        
        if (sugerenciasTitulos[categoria] && titulo.value === '') {
            titulo.placeholder = sugerenciasTitulos[categoria][0] + '...';
        }
    });
}

// Utilidades
function obtenerRolUsuario() {
    return window.userRole || 1;
}

function obtenerUsuarioId() {
    return window.userId || 1;
}

// Llamar configuraciones adicionales
setTimeout(() => {
    configurarAutocompletado();
    cargarEstadisticasRapidas();
}, 500);