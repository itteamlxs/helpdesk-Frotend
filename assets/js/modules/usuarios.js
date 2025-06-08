// /assets/js/modules/usuarios.js - VERSIÓN COMPLETA

let todosLosUsuarios = [];
let usuarioSeleccionado = null;
let modoEdicion = false;

document.addEventListener('DOMContentLoaded', function() {
    verificarPermisos();
    cargarUsuarios();
    configurarFiltros();
    configurarValidaciones();
    configurarEventListeners();
});

function verificarPermisos() {
    const userRole = obtenerRolUsuario();
    
    // Solo técnicos y admins pueden gestionar usuarios
    if (userRole < 2) {
        showAlert('No tienes permisos para gestionar usuarios', 'warning');
        setTimeout(() => {
            window.location.href = '?ruta=dashboard';
        }, 2000);
        return false;
    }
    return true;
}

async function cargarUsuarios() {
    try {
        showLoading('usuariosTableBody');
        todosLosUsuarios = await api.getUsuarios();
        console.log('Usuarios cargados:', todosLosUsuarios);
        
        mostrarUsuarios(todosLosUsuarios);
        actualizarEstadisticas();
        
    } catch (error) {
        console.error('Error cargando usuarios:', error);
        document.getElementById('usuariosTableBody').innerHTML = 
            '<tr><td colspan="7" class="text-center text-danger">Error al cargar usuarios</td></tr>';
        showAlert('Error al cargar usuarios: ' + error.message, 'danger');
    }
}

function mostrarUsuarios(usuarios) {
    const tbody = document.getElementById('usuariosTableBody');
    
    if (usuarios.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No se encontraron usuarios</td></tr>';
        return;
    }
    
    tbody.innerHTML = usuarios.map(usuario => {
        const rolInfo = obtenerInfoRol(usuario.rol_id);
        const estadoBadge = usuario.activo ? 
            '<span class="badge bg-success">Activo</span>' : 
            '<span class="badge bg-danger">Inactivo</span>';
        
        // Estadísticas simuladas (se pueden cargar desde la API)
        const stats = obtenerEstadisticasUsuario(usuario.id);
        
        return `
            <tr>
                <td><strong>#${usuario.id}</strong></td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar-circle me-2">
                            ${usuario.nombre.charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <strong>${usuario.nombre}</strong>
                            <br><small class="text-muted">ID: ${usuario.id}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <i class="fas fa-envelope text-muted"></i> 
                    <a href="mailto:${usuario.correo}">${usuario.correo}</a>
                </td>
                <td>
                    <span class="badge ${rolInfo.class}">
                        ${rolInfo.icon} ${rolInfo.nombre}
                    </span>
                </td>
                <td>${estadoBadge}</td>
                <td>
                    <small class="text-muted">
                        📋 ${stats.tickets} tickets<br>
                        💬 ${stats.comentarios} comentarios
                    </small>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-info" onclick="verDetallesUsuario(${usuario.id})" title="Ver detalles">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-warning" onclick="editarUsuario(${usuario.id})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="eliminarUsuario(${usuario.id}, '${usuario.nombre}')" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function obtenerInfoRol(rolId) {
    const roles = {
        1: { nombre: 'Cliente', icon: '👤', class: 'bg-info' },
        2: { nombre: 'Técnico', icon: '🔧', class: 'bg-warning' },
        3: { nombre: 'Admin', icon: '👑', class: 'bg-success' }
    };
    return roles[rolId] || { nombre: 'Desconocido', icon: '❓', class: 'bg-secondary' };
}

function obtenerEstadisticasUsuario(usuarioId) {
    // Aquí se pueden calcular estadísticas reales o simular
    return {
        tickets: Math.floor(Math.random() * 50),
        comentarios: Math.floor(Math.random() * 100)
    };
}

function actualizarEstadisticas() {
    const total = todosLosUsuarios.length;
    const clientes = todosLosUsuarios.filter(u => u.rol_id === 1).length;
    const tecnicos = todosLosUsuarios.filter(u => u.rol_id === 2).length;
    const admins = todosLosUsuarios.filter(u => u.rol_id === 3).length;
    
    document.getElementById('totalUsuarios').textContent = total;
    document.getElementById('totalClientes').textContent = clientes;
    document.getElementById('totalTecnicos').textContent = tecnicos;
    document.getElementById('totalAdmins').textContent = admins;
}

function configurarFiltros() {
    const filtroRol = document.getElementById('filtroRol');
    const filtroEstado = document.getElementById('filtroEstado');
    const busqueda = document.getElementById('busquedaUsuario');
    
    [filtroRol, filtroEstado, busqueda].forEach(elemento => {
        if (elemento) {
            elemento.addEventListener('change', aplicarFiltros);
            elemento.addEventListener('input', aplicarFiltros);
        }
    });
}

function aplicarFiltros() {
    const rol = document.getElementById('filtroRol').value;
    const estado = document.getElementById('filtroEstado').value;
    const busqueda = document.getElementById('busquedaUsuario').value.toLowerCase();
    
    let usuariosFiltrados = todosLosUsuarios.filter(usuario => {
        const cumpleRol = !rol || usuario.rol_id == rol;
        const cumpleEstado = estado === '' || usuario.activo == estado;
        const cumpleBusqueda = !busqueda || 
            usuario.nombre.toLowerCase().includes(busqueda) ||
            usuario.correo.toLowerCase().includes(busqueda);
        
        return cumpleRol && cumpleEstado && cumpleBusqueda;
    });
    
    mostrarUsuarios(usuariosFiltrados);
}

function limpiarFiltros() {
    document.getElementById('filtroRol').value = '';
    document.getElementById('filtroEstado').value = '';
    document.getElementById('busquedaUsuario').value = '';
    mostrarUsuarios(todosLosUsuarios);
}

function mostrarModalCrear() {
    modoEdicion = false;
    usuarioSeleccionado = null;
    
    // Resetear formulario
    const form = document.getElementById('formUsuario');
    if (form) {
        form.reset();
    }
    
    const usuarioIdField = document.getElementById('usuarioId');
    if (usuarioIdField) {
        usuarioIdField.value = '';
    }
    
    // Configurar modal para creación
    const title = document.getElementById('usuarioModalTitle');
    if (title) {
        title.innerHTML = '<i class="fas fa-user-plus"></i> Nuevo Usuario';
    }
    
    const btn = document.getElementById('btnGuardarUsuario');
    if (btn) {
        btn.innerHTML = '<i class="fas fa-save"></i> Crear Usuario';
    }
    
    // Hacer campos de contraseña obligatorios
    const password = document.getElementById('passwordUsuario');
    const confirmar = document.getElementById('confirmarPassword');
    const helpText = document.getElementById('passwordHelp');
    
    if (password) password.required = true;
    if (confirmar) confirmar.required = true;
    if (helpText) helpText.textContent = 'La contraseña debe tener al menos 6 caracteres';
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('usuarioModal'));
    modal.show();
}

async function editarUsuario(id) {
    modoEdicion = true;
    usuarioSeleccionado = id;
    
    try {
        const usuario = await api.getUsuario(id);
        
        // Llenar formulario con datos del usuario
        const campos = {
            'usuarioId': usuario.id,
            'nombreUsuario': usuario.nombre,
            'correoUsuario': usuario.correo,
            'rolUsuario': usuario.rol_id,
            'estadoUsuario': usuario.activo ? 1 : 0
        };
        
        Object.entries(campos).forEach(([campoId, valor]) => {
            const campo = document.getElementById(campoId);
            if (campo) {
                campo.value = valor;
            }
        });
        
        // Configurar modal para edición
        const title = document.getElementById('usuarioModalTitle');
        if (title) {
            title.innerHTML = '<i class="fas fa-user-edit"></i> Editar Usuario';
        }
        
        const btn = document.getElementById('btnGuardarUsuario');
        if (btn) {
            btn.innerHTML = '<i class="fas fa-save"></i> Actualizar Usuario';
        }
        
        // Hacer contraseña opcional
        const password = document.getElementById('passwordUsuario');
        const confirmar = document.getElementById('confirmarPassword');
        const helpText = document.getElementById('passwordHelp');
        
        if (password) password.required = false;
        if (confirmar) confirmar.required = false;
        if (helpText) helpText.textContent = 'Deja en blanco para mantener la contraseña actual';
        
        // Mostrar modal
        const modal = new bootstrap.Modal(document.getElementById('usuarioModal'));
        modal.show();
        
    } catch (error) {
        console.error('Error cargando usuario:', error);
        showAlert('Error al cargar datos del usuario: ' + error.message, 'danger');
    }
}

async function guardarUsuario() {
    const form = document.getElementById('formUsuario');
    
    if (!form || !form.checkValidity()) {
        if (form) form.reportValidity();
        return;
    }
    
    // Validaciones adicionales
    if (!validarFormularioUsuario()) {
        return;
    }
    
    const formData = new FormData(form);
    const data = {};
    
    // Recopilar datos
    for (let [key, value] of formData.entries()) {
        if (key !== 'confirmar_password' && key !== 'id') {
            data[key] = value.trim();
        }
    }
    
    // Convertir valores
    data.rol_id = parseInt(data.rol_id);
    data.activo = parseInt(data.activo) === 1;
    
    // En modo edición, no enviar contraseña si está vacía
    if (modoEdicion && (!data.password || data.password === '')) {
        delete data.password;
    }
    
    console.log('Guardando usuario:', data);
    
    try {
        // Deshabilitar botón
        const btn = document.getElementById('btnGuardarUsuario');
        const textoOriginal = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        }
        
        if (modoEdicion) {
            await api.updateUsuario(usuarioSeleccionado, data);
            showAlert('Usuario actualizado correctamente', 'success');
        } else {
            await api.createUsuario(data);
            showAlert('Usuario creado correctamente', 'success');
        }
        
        // Cerrar modal y recargar
        const modalElement = document.getElementById('usuarioModal');
        if (modalElement) {
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) {
                modalInstance.hide();
            }
        }
        
        cargarUsuarios();
        
        // Restaurar botón
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = textoOriginal;
        }
        
    } catch (error) {
        console.error('Error guardando usuario:', error);
        showAlert('Error al guardar usuario: ' + error.message, 'danger');
        
        // Restaurar botón
        const btn = document.getElementById('btnGuardarUsuario');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = modoEdicion ? 
                '<i class="fas fa-save"></i> Actualizar Usuario' : 
                '<i class="fas fa-save"></i> Crear Usuario';
        }
    }
}

function validarFormularioUsuario() {
    const password = document.getElementById('passwordUsuario');
    const confirmar = document.getElementById('confirmarPassword');
    const email = document.getElementById('correoUsuario');
    
    if (!password || !confirmar || !email) {
        showAlert('Error: No se encontraron todos los campos del formulario', 'danger');
        return false;
    }
    
    const passwordValue = password.value;
    const confirmarValue = confirmar.value;
    const emailValue = email.value;
    
    // Validar email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(emailValue)) {
        showAlert('Por favor ingresa un correo electrónico válido', 'warning');
        return false;
    }
    
    // Validar contraseña solo si no está vacía o es creación
    if (passwordValue || !modoEdicion) {
        if (passwordValue.length < 6) {
            showAlert('La contraseña debe tener al menos 6 caracteres', 'warning');
            return false;
        }
        
        if (passwordValue !== confirmarValue) {
            showAlert('Las contraseñas no coinciden', 'warning');
            return false;
        }
    }
    
    return true;
}

function eliminarUsuario(id, nombre) {
    usuarioSeleccionado = id;
    const elemento = document.getElementById('usuarioEliminar');
    if (elemento) {
        elemento.textContent = nombre;
    }
    
    const modal = new bootstrap.Modal(document.getElementById('eliminarModal'));
    modal.show();
}

async function confirmarEliminacion() {
    if (!usuarioSeleccionado) return;
    
    try {
        const btn = document.getElementById('btnConfirmarEliminar');
        const textoOriginal = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Eliminando...';
        }
        
        await api.deleteUsuario(usuarioSeleccionado);
        
        showAlert('Usuario eliminado correctamente', 'success');
        
        // Cerrar modal y recargar
        const modalElement = document.getElementById('eliminarModal');
        if (modalElement) {
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) {
                modalInstance.hide();
            }
        }
        
        cargarUsuarios();
        
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = textoOriginal;
        }
        
    } catch (error) {
        console.error('Error eliminando usuario:', error);
        showAlert('Error al eliminar usuario: ' + error.message, 'danger');
        
        const btn = document.getElementById('btnConfirmarEliminar');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-trash"></i> Eliminar Usuario';
        }
    }
}

async function verDetallesUsuario(id) {
    try {
        usuarioSeleccionado = id;
        
        // Mostrar loading en modal
        const content = document.getElementById('detallesUsuarioContent');
        if (content) {
            content.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Cargando información...</p>
                </div>
            `;
        }
        
        // Mostrar modal
        const modal = new bootstrap.Modal(document.getElementById('detallesModal'));
        modal.show();
        
        // Cargar datos del usuario
        const usuario = await api.getUsuario(id);
        
        // Simular carga de estadísticas (se puede implementar en el backend)
        const stats = await obtenerEstadisticasDetalladas(id);
        
        // Mostrar información completa
        mostrarDetallesCompletos(usuario, stats);
        
    } catch (error) {
        console.error('Error cargando detalles:', error);
        const content = document.getElementById('detallesUsuarioContent');
        if (content) {
            content.innerHTML = `
                <div class="text-center py-4 text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                    <p class="mt-2">Error al cargar información</p>
                </div>
            `;
        }
    }
}

async function obtenerEstadisticasDetalladas(usuarioId) {
    // Aquí se pueden hacer múltiples llamadas a la API para obtener estadísticas
    // Por ahora simulamos los datos
    return {
        totalTickets: Math.floor(Math.random() * 50),
        ticketsAbiertos: Math.floor(Math.random() * 10),
        ticketsCerrados: Math.floor(Math.random() * 40),
        comentarios: Math.floor(Math.random() * 100),
        ultimaActividad: new Date().toISOString(),
        fechaRegistro: new Date(Date.now() - Math.random() * 365 * 24 * 60 * 60 * 1000).toISOString()
    };
}

function mostrarDetallesCompletos(usuario, stats) {
    const rolInfo = obtenerInfoRol(usuario.rol_id);
    const estadoBadge = usuario.activo ? 
        '<span class="badge bg-success">✅ Activo</span>' : 
        '<span class="badge bg-danger">❌ Inactivo</span>';
    
    const content = document.getElementById('detallesUsuarioContent');
    if (!content) return;
    
    content.innerHTML = `
        <div class="row">
            <!-- Información personal -->
            <div class="col-md-6">
                <h6 class="text-muted mb-3">
                    <i class="fas fa-user"></i> Información Personal
                </h6>
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-circle-large me-3">
                            ${usuario.nombre.charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <h5 class="mb-1">${usuario.nombre}</h5>
                            <p class="text-muted mb-0">ID: #${usuario.id}</p>
                        </div>
                    </div>
                </div>
                
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="fas fa-envelope text-muted"></i> Correo:</span>
                        <span>${usuario.correo}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="fas fa-shield-alt text-muted"></i> Rol:</span>
                        <span class="badge ${rolInfo.class}">${rolInfo.icon} ${rolInfo.nombre}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="fas fa-toggle-on text-muted"></i> Estado:</span>
                        ${estadoBadge}
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="fas fa-calendar text-muted"></i> Registro:</span>
                        <span>${formatDate(stats.fechaRegistro)}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="fas fa-clock text-muted"></i> Última actividad:</span>
                        <span>${formatDate(stats.ultimaActividad)}</span>
                    </div>
                </div>
            </div>
            
            <!-- Estadísticas -->
            <div class="col-md-6">
                <h6 class="text-muted mb-3">
                    <i class="fas fa-chart-bar"></i> Estadísticas de Actividad
                </h6>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <div class="card text-center border-primary">
                            <div class="card-body py-3">
                                <h4 class="text-primary mb-1">${stats.totalTickets}</h4>
                                <small class="text-muted">Total Tickets</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card text-center border-warning">
                            <div class="card-body py-3">
                                <h4 class="text-warning mb-1">${stats.ticketsAbiertos}</h4>
                                <small class="text-muted">Abiertos</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <div class="card text-center border-success">
                            <div class="card-body py-3">
                                <h4 class="text-success mb-1">${stats.ticketsCerrados}</h4>
                                <small class="text-muted">Cerrados</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card text-center border-info">
                            <div class="card-body py-3">
                                <h4 class="text-info mb-1">${stats.comentarios}</h4>
                                <small class="text-muted">Comentarios</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Gráfico de actividad (simulado) -->
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="card-title">Actividad Reciente</h6>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-success" style="width: ${Math.random() * 100}%"></div>
                        </div>
                        <small class="text-muted">Nivel de actividad en los últimos 30 días</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Acciones rápidas -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button class="btn btn-outline-info" onclick="verTicketsUsuario(${usuario.id})">
                        <i class="fas fa-ticket-alt"></i> Ver Tickets
                    </button>
                    <button class="btn btn-outline-success" onclick="enviarEmail('${usuario.correo}')">
                        <i class="fas fa-envelope"></i> Enviar Email
                    </button>
                </div>
            </div>
        </div>
    `;
    
    // Agregar estilos CSS si no existen
    agregarEstilosAvatares();
}

function agregarEstilosAvatares() {
    if (!document.getElementById('usuariosStyles')) {
        const style = document.createElement('style');
        style.id = 'usuariosStyles';
        style.textContent = `
            .avatar-circle {
                width: 32px;
                height: 32px;
                border-radius: 50%;
                background: linear-gradient(45deg, #007bff, #28a745);
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                font-size: 14px;
            }
            .avatar-circle-large {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background: linear-gradient(45deg, #007bff, #28a745);
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                font-size: 24px;
            }
        `;
        document.head.appendChild(style);
    }
}

function editarUsuarioActual() {
    if (usuarioSeleccionado) {
        // Cerrar modal de detalles
        const modalElement = document.getElementById('detallesModal');
        if (modalElement) {
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) {
                modalInstance.hide();
            }
        }
        
        // Abrir modal de edición
        setTimeout(() => {
            editarUsuario(usuarioSeleccionado);
        }, 300);
    }
}

function verTicketsUsuario(usuarioId) {
    // Redirigir a la vista de tickets con filtro de usuario
    window.location.href = `?ruta=tickets&usuario=${usuarioId}`;
}

function enviarEmail(correo) {
    // Abrir cliente de correo
    window.location.href = `mailto:${correo}`;
}

function configurarValidaciones() {
    // Validación en tiempo real de contraseñas
    const password = document.getElementById('passwordUsuario');
    const confirmar = document.getElementById('confirmarPassword');
    const matchIndicator = document.getElementById('passwordMatch');
    
    function validarPasswords() {
        if (!confirmar || !password || !matchIndicator) return;
        
        if (confirmar.value === '') {
            matchIndicator.textContent = '';
            matchIndicator.className = 'form-text';
            return;
        }
        
        if (password.value === confirmar.value) {
            matchIndicator.textContent = '✅ Las contraseñas coinciden';
            matchIndicator.className = 'form-text text-success';
        } else {
            matchIndicator.textContent = '❌ Las contraseñas no coinciden';
            matchIndicator.className = 'form-text text-danger';
        }
    }
    
    if (password && confirmar) {
        password.addEventListener('input', validarPasswords);
        confirmar.addEventListener('input', validarPasswords);
    }
    
    // Validación de email en tiempo real
    const emailField = document.getElementById('correoUsuario');
    if (emailField) {
        emailField.addEventListener('input', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                this.setCustomValidity('Por favor ingresa un correo válido');
            } else {
                this.setCustomValidity('');
            }
        });
    }
}

function configurarEventListeners() {
    // Event listener para el botón de crear usuario
    const btnCrear = document.querySelector('[onclick="mostrarModalCrear()"]');
    if (btnCrear) {
        btnCrear.addEventListener('click', mostrarModalCrear);
    }
}

function togglePassword() {
    const password = document.getElementById('passwordUsuario');
    const icon = document.getElementById('passwordIcon');
    
    if (!password || !icon) return;
    
    if (password.type === 'password') {
        password.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        password.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Utilidades
function obtenerRolUsuario() {
    return window.userRole || 1;
}

function obtenerUsuarioId() {
    return window.userId || 1;
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

function showLoading(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                    <br>Cargando usuarios...
                </td>
            </tr>
        `;
    }
}

// Hacer funciones globales para ser llamadas desde el HTML
window.mostrarModalCrear = mostrarModalCrear;
window.editarUsuario = editarUsuario;
window.eliminarUsuario = eliminarUsuario;
window.confirmarEliminacion = confirmarEliminacion;
window.verDetallesUsuario = verDetallesUsuario;
window.editarUsuarioActual = editarUsuarioActual;
window.verTicketsUsuario = verTicketsUsuario;
window.enviarEmail = enviarEmail;
window.togglePassword = togglePassword;
window.guardarUsuario = guardarUsuario;
window.limpiarFiltros = limpiarFiltros;