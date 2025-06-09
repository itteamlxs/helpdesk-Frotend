// /assets/js/modules/audit.js - Sistema de Auditoría

let auditData = [];
let filteredData = [];
let currentPage = 1;
let itemsPerPage = 50;
let usuarios = [];
let estadisticas = {};

document.addEventListener('DOMContentLoaded', function() {
    inicializarAuditoria();
    configurarEventListeners();
});

// ================================================================================================
// 🚀 INICIALIZACIÓN
// ================================================================================================

async function inicializarAuditoria() {
    try {
        console.log('Inicializando sistema de auditoría...');
        
        // Cargar datos iniciales en paralelo
        await Promise.all([
            cargarDatosAuditoria(),
            cargarUsuarios(),
            cargarEstadisticas()
        ]);
        
        // Configurar interfaz
        configurarFiltroFecha();
        aplicarFiltros();
        
    } catch (error) {
        console.error('Error inicializando auditoría:', error);
        mostrarError('Error al cargar sistema de auditoría');
    }
}

async function cargarDatosAuditoria() {
    try {
        console.log('Cargando datos de auditoría...');
        
        const response = await api.request('audit');
        auditData = Array.isArray(response) ? response : [];
        filteredData = [...auditData];
        
        console.log(`Cargados ${auditData.length} registros de auditoría`);
        
        return auditData;
        
    } catch (error) {
        console.error('Error cargando auditoría:', error);
        auditData = [];
        filteredData = [];
        throw error;
    }
}

async function cargarUsuarios() {
    try {
        usuarios = await api.getUsuarios();
        llenarFiltroUsuarios();
    } catch (error) {
        console.error('Error cargando usuarios:', error);
        usuarios = [];
    }
}

async function cargarEstadisticas() {
    try {
        // Intentar usar endpoint de estadísticas específico
        try {
            estadisticas = await api.request('stats/audit');
        } catch (apiError) {
            // Fallback: calcular estadísticas desde los datos cargados
            estadisticas = calcularEstadisticasLocales();
        }
        
        mostrarEstadisticas();
        
    } catch (error) {
        console.error('Error cargando estadísticas:', error);
        estadisticas = {};
    }
}

// ================================================================================================
// 🎛️ CONFIGURACIÓN DE EVENTOS
// ================================================================================================

function configurarEventListeners() {
    // Filtros
    document.getElementById('filtroFecha').addEventListener('change', manejarCambioFecha);
    document.getElementById('filtroUsuario').addEventListener('change', aplicarFiltros);
    document.getElementById('filtroAccion').addEventListener('change', aplicarFiltros);
    document.getElementById('registrosPorPagina').addEventListener('change', cambiarItemsPorPagina);
    
    // Tabs
    document.querySelectorAll('#auditTabs button').forEach(tab => {
        tab.addEventListener('shown.bs.tab', manejarCambioTab);
    });
    
    // Fechas personalizadas
    document.getElementById('fechaInicio').addEventListener('change', aplicarFiltros);
    document.getElementById('fechaFin').addEventListener('change', aplicarFiltros);
}

function manejarCambioFecha() {
    const filtroFecha = document.getElementById('filtroFecha').value;
    const fechasPersonalizadas = document.getElementById('fechasPersonalizadas');
    
    if (filtroFecha === 'personalizado') {
        fechasPersonalizadas.classList.remove('d-none');
        configurarFechasPersonalizadas();
    } else {
        fechasPersonalizadas.classList.add('d-none');
        aplicarFiltros();
    }
}

function configurarFechasPersonalizadas() {
    const hoy = new Date();
    const fechaFin = document.getElementById('fechaFin');
    const fechaInicio = document.getElementById('fechaInicio');
    
    // Configurar fecha máxima como hoy
    fechaFin.max = hoy.toISOString().split('T')[0];
    fechaInicio.max = hoy.toISOString().split('T')[0];
    
    // Valores por defecto: última semana
    fechaFin.value = hoy.toISOString().split('T')[0];
    
    const semanaAtras = new Date(hoy);
    semanaAtras.setDate(hoy.getDate() - 7);
    fechaInicio.value = semanaAtras.toISOString().split('T')[0];
}

function manejarCambioTab(event) {
    const tabId = event.target.getAttribute('data-bs-target').replace('#', '');
    
    switch (tabId) {
        case 'resumen':
            cargarResumenDiario();
            break;
        case 'usuarios':
            cargarActividadUsuarios();
            break;
        case 'ips':
            cargarAnalisisIPs();
            break;
        default:
            // Vista detallada ya está cargada
            break;
    }
}

// ================================================================================================
// 🔍 FILTROS Y BÚSQUEDA
// ================================================================================================

function aplicarFiltros() {
    const filtroFecha = document.getElementById('filtroFecha').value;
    const filtroUsuario = document.getElementById('filtroUsuario').value;
    const filtroAccion = document.getElementById('filtroAccion').value;
    
    console.log('Aplicando filtros:', { filtroFecha, filtroUsuario, filtroAccion });
    
    filteredData = auditData.filter(item => {
        // Filtro de fecha
        if (!cumpleFiltroFecha(item, filtroFecha)) return false;
        
        // Filtro de usuario
        if (filtroUsuario && item.usuario_id != filtroUsuario) return false;
        
        // Filtro de acción
        if (filtroAccion && !cumpleFiltroAccion(item, filtroAccion)) return false;
        
        return true;
    });
    
    // Resetear página y mostrar resultados
    currentPage = 1;
    mostrarResultados();
    actualizarContadores();
}

function cumpleFiltroFecha(item, filtro) {
    const fechaItem = new Date(item.creado_en);
    const hoy = new Date();
    hoy.setHours(23, 59, 59, 999); // Final del día
    
    switch (filtro) {
        case 'hoy':
            const inicioHoy = new Date(hoy);
            inicioHoy.setHours(0, 0, 0, 0);
            return fechaItem >= inicioHoy && fechaItem <= hoy;
            
        case 'ayer':
            const ayer = new Date(hoy);
            ayer.setDate(hoy.getDate() - 1);
            ayer.setHours(0, 0, 0, 0);
            const finAyer = new Date(ayer);
            finAyer.setHours(23, 59, 59, 999);
            return fechaItem >= ayer && fechaItem <= finAyer;
            
        case 'semana':
            const semanaAtras = new Date(hoy);
            semanaAtras.setDate(hoy.getDate() - 7);
            semanaAtras.setHours(0, 0, 0, 0);
            return fechaItem >= semanaAtras && fechaItem <= hoy;
            
        case 'mes':
            const mesAtras = new Date(hoy);
            mesAtras.setMonth(hoy.getMonth() - 1);
            mesAtras.setHours(0, 0, 0, 0);
            return fechaItem >= mesAtras && fechaItem <= hoy;
            
        case 'personalizado':
            const fechaInicio = new Date(document.getElementById('fechaInicio').value);
            const fechaFin = new Date(document.getElementById('fechaFin').value);
            fechaFin.setHours(23, 59, 59, 999);
            return fechaItem >= fechaInicio && fechaItem <= fechaFin;
            
        default:
            return true;
    }
}

function cumpleFiltroAccion(item, filtro) {
    const accion = item.accion.toLowerCase();
    
    switch (filtro) {
        case 'login':
            return accion.includes('login') || accion.includes('logout');
        case 'ticket':
            return accion.includes('ticket');
        case 'usuario':
            return accion.includes('usuario') || accion.includes('user');
        case 'config':
            return accion.includes('config') || accion.includes('setting');
        case 'delete':
            return accion.includes('delete') || accion.includes('elimina');
        default:
            return true;
    }
}

// ================================================================================================
// 📊 MOSTRAR DATOS
// ================================================================================================

function mostrarResultados() {
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedData = filteredData.slice(startIndex, endIndex);
    
    mostrarTablaDetallada(paginatedData);
    crearPaginacion();
}

function mostrarTablaDetallada(data) {
    const tbody = document.getElementById('auditTableBody');
    
    if (data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4 text-muted">
                    <i class="fas fa-search fa-2x mb-3"></i>
                    <br>No se encontraron registros con los filtros aplicados
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = data.map(item => {
        const fechaFormateada = formatDate(item.creado_en);
        const accionClass = getAccionClass(item.accion);
        const rolBadge = getRolBadge(item.rol_nombre || 'Usuario');
        
        return `
            <tr onclick="verDetalleAuditoria(${item.id})" style="cursor: pointer;">
                <td>
                    <div class="fw-bold">${fechaFormateada.fecha}</div>
                    <small class="text-muted">${fechaFormateada.hora}</small>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar-circle me-2">
                            ${(item.usuario_nombre || 'U').charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <div class="fw-bold">${item.usuario_nombre || 'Usuario eliminado'}</div>
                            <small class="text-muted">${item.usuario_correo || 'N/A'}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge ${accionClass.class}">
                        ${accionClass.icon} ${item.accion}
                    </span>
                </td>
                <td>
                    <code class="small">${item.ip || 'N/A'}</code>
                </td>
                <td>
                    ${rolBadge}
                </td>
                <td>
                    <button class="btn btn-outline-info btn-sm" onclick="event.stopPropagation(); verDetalleAuditoria(${item.id})">
                        <i class="fas fa-info-circle"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function mostrarEstadisticas() {
    const stats = estadisticas;
    
    // Estadísticas principales
    document.getElementById('totalAcciones').textContent = stats.total_acciones || auditData.length;
    document.getElementById('usuariosActivos').textContent = stats.usuarios_activos || 0;
    document.getElementById('totalLogins').textContent = stats.logins_hoy || 0;
    document.getElementById('accionesRiesgo').textContent = stats.acciones_riesgo || 0;
    
    // Estadísticas adicionales
    document.getElementById('accionesHoy').textContent = `+${stats.acciones_hoy || 0} hoy`;
    document.getElementById('usuariosHoy').textContent = `${stats.usuarios_hoy || 0} hoy`;
    document.getElementById('loginsRecientes').textContent = `Último: ${stats.ultimo_login || 'N/A'}`;
    document.getElementById('ultimaAccionRiesgo').textContent = `Última: ${stats.ultima_accion_riesgo || 'N/A'}`;
}

// ================================================================================================
// 📄 PAGINACIÓN
// ================================================================================================

function crearPaginacion() {
    const totalPages = Math.ceil(filteredData.length / itemsPerPage);
    const paginacion = document.getElementById('paginacion');
    
    if (totalPages <= 1) {
        paginacion.innerHTML = '';
        return;
    }
    
    let html = '';
    
    // Botón anterior
    html += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <button class="page-link" onclick="cambiarPagina(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
                <i class="fas fa-chevron-left"></i>
            </button>
        </li>
    `;
    
    // Páginas
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);
    
    if (startPage > 1) {
        html += `<li class="page-item"><button class="page-link" onclick="cambiarPagina(1)">1</button></li>`;
        if (startPage > 2) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        html += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <button class="page-link" onclick="cambiarPagina(${i})">${i}</button>
            </li>
        `;
    }
    
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        html += `<li class="page-item"><button class="page-link" onclick="cambiarPagina(${totalPages})">${totalPages}</button></li>`;
    }
    
    // Botón siguiente
    html += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <button class="page-link" onclick="cambiarPagina(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
                <i class="fas fa-chevron-right"></i>
            </button>
        </li>
    `;
    
    paginacion.innerHTML = html;
}

function cambiarPagina(pagina) {
    const totalPages = Math.ceil(filteredData.length / itemsPerPage);
    
    if (pagina < 1 || pagina > totalPages) return;
    
    currentPage = pagina;
    mostrarResultados();
    
    // Scroll to top
    document.getElementById('detalle').scrollIntoView({ behavior: 'smooth' });
}

function cambiarItemsPorPagina() {
    itemsPerPage = parseInt(document.getElementById('registrosPorPagina').value);
    currentPage = 1;
    mostrarResultados();
}

// ================================================================================================
// 📈 VISTAS ADICIONALES
// ================================================================================================

async function cargarResumenDiario() {
    try {
        let resumenData;
        
        try {
            // Intentar usar endpoint específico
            resumenData = await api.request('stats/audit?tipo=resumen&dias=30');
        } catch (apiError) {
            // Fallback: calcular desde datos locales
            resumenData = calcularResumenDiario();
        }
        
        mostrarResumenDiario(resumenData);
        crearGraficoActividad(resumenData);
        
    } catch (error) {
        console.error('Error cargando resumen diario:', error);
        document.getElementById('resumenTabla').innerHTML = `
            <tr><td colspan="3" class="text-center text-danger">Error al cargar resumen</td></tr>
        `;
    }
}

function mostrarResumenDiario(data) {
    const tbody = document.getElementById('resumenTabla');
    
    if (!data || !Array.isArray(data.data)) {
        tbody.innerHTML = `<tr><td colspan="3" class="text-center text-muted">Sin datos disponibles</td></tr>`;
        return;
    }
    
    tbody.innerHTML = data.data.slice(0, 10).map(item => `
        <tr>
            <td>${formatearFecha(item.fecha)}</td>
            <td><span class="badge bg-primary">${item.total_acciones || 0}</span></td>
            <td><span class="badge bg-info">${item.usuarios_activos || 0}</span></td>
        </tr>
    `).join('');
}

async function cargarActividadUsuarios() {
    try {
        const actividadPorUsuario = calcularActividadPorUsuario();
        mostrarActividadUsuarios(actividadPorUsuario);
    } catch (error) {
        console.error('Error cargando actividad por usuario:', error);
    }
}

function mostrarActividadUsuarios(actividad) {
    const container = document.getElementById('actividadUsuarios');
    
    if (actividad.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center text-muted">
                <i class="fas fa-users fa-2x mb-3"></i>
                <p>No hay actividad de usuarios en el período seleccionado</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = actividad.slice(0, 12).map(user => `
        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar-circle-large mx-auto mb-2">
                        ${user.nombre.charAt(0).toUpperCase()}
                    </div>
                    <h6 class="card-title">${user.nombre}</h6>
                    <p class="card-text small text-muted">${user.correo}</p>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h6 text-primary">${user.total_acciones}</div>
                            <small class="text-muted">Acciones</small>
                        </div>
                        <div class="col-6">
                            <div class="h6 text-success">${user.logins}</div>
                            <small class="text-muted">Logins</small>
                        </div>
                    </div>
                    <small class="text-muted">Última: ${formatearFecha(user.ultima_actividad)}</small>
                </div>
            </div>
        </div>
    `).join('');
}

async function cargarAnalisisIPs() {
    try {
        const analisisIPs = calcularAnalisisIPs();
        mostrarAnalisisIPs(analisisIPs);
    } catch (error) {
        console.error('Error cargando análisis de IPs:', error);
    }
}

function mostrarAnalisisIPs(analisis) {
    const tbody = document.getElementById('ipsTabla');
    
    tbody.innerHTML = analisis.ips.slice(0, 20).map(ip => {
        const estado = ip.acciones > 50 ? 'Sospechosa' : 'Normal';
        const estadoClass = ip.acciones > 50 ? 'warning' : 'success';
        
        return `
            <tr>
                <td><code>${ip.ip}</code></td>
                <td><span class="badge bg-primary">${ip.acciones}</span></td>
                <td><span class="badge bg-info">${ip.usuarios_unicos}</span></td>
                <td><small>${formatearFecha(ip.ultima_actividad)}</small></td>
                <td><span class="badge bg-${estadoClass}">${estado}</span></td>
            </tr>
        `;
    }).join('');
    
    // Actualizar estadísticas de seguridad
    document.getElementById('ipsUnicas').textContent = analisis.estadisticas.ips_unicas;
    document.getElementById('ipsSospechosas').textContent = analisis.estadisticas.ips_sospechosas;
    document.getElementById('intentosFallidos').textContent = analisis.estadisticas.intentos_fallidos;
}

// ================================================================================================
// 🔧 FUNCIONES AUXILIARES
// ================================================================================================

function llenarFiltroUsuarios() {
    const select = document.getElementById('filtroUsuario');
    
    select.innerHTML = '<option value="">Todos los usuarios</option>' +
        usuarios.map(usuario => 
            `<option value="${usuario.id}">${usuario.nombre} (${usuario.correo})</option>`
        ).join('');
}

function configurarFiltroFecha() {
    const hoy = new Date();
    document.getElementById('fechaFin').max = hoy.toISOString().split('T')[0];
}

function actualizarContadores() {
    document.getElementById('totalRegistros').textContent = filteredData.length;
}

function getAccionClass(accion) {
    const accionLower = accion.toLowerCase();
    
    if (accionLower.includes('login')) {
        return { class: 'bg-success', icon: '🔐' };
    } else if (accionLower.includes('logout')) {
        return { class: 'bg-info', icon: '🚪' };
    } else if (accionLower.includes('delete') || accionLower.includes('elimina')) {
        return { class: 'bg-danger', icon: '🗑️' };
    } else if (accionLower.includes('ticket')) {
        return { class: 'bg-primary', icon: '🎫' };
    } else if (accionLower.includes('usuario') || accionLower.includes('user')) {
        return { class: 'bg-warning', icon: '👤' };
    } else {
        return { class: 'bg-secondary', icon: '⚙️' };
    }
}

function getRolBadge(rol) {
    const roles = {
        'admin': 'bg-success',
        'administrador': 'bg-success',
        'tecnico': 'bg-warning',
        'técnico': 'bg-warning',
        'cliente': 'bg-info'
    };
    
    const rolLower = rol.toLowerCase();
    const badgeClass = roles[rolLower] || 'bg-secondary';
    
    return `<span class="badge ${badgeClass}">${rol}</span>`;
}

function formatDate(dateString) {
    if (!dateString) return { fecha: 'N/A', hora: 'N/A' };
    
    const date = new Date(dateString);
    const fecha = date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
    const hora = date.toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit'
    });
    
    return { fecha, hora };
}

function formatearFecha(dateString) {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('es-ES');
}

// ================================================================================================
// 📊 CÁLCULOS LOCALES (FALLBACK)
// ================================================================================================

function calcularEstadisticasLocales() {
    const hoy = new Date();
    const inicioHoy = new Date(hoy);
    inicioHoy.setHours(0, 0, 0, 0);
    
    const accionesHoy = auditData.filter(item => 
        new Date(item.creado_en) >= inicioHoy
    );
    
    const logins = auditData.filter(item => 
        item.accion.toLowerCase().includes('login') && 
        new Date(item.creado_en) >= inicioHoy
    );
    
    return {
        total_acciones: auditData.length,
        acciones_hoy: accionesHoy.length,
        usuarios_activos: new Set(auditData.map(item => item.usuario_id)).size,
        usuarios_hoy: new Set(accionesHoy.map(item => item.usuario_id)).size,
        logins_hoy: logins.length,
        acciones_riesgo: auditData.filter(item => 
            item.accion.toLowerCase().includes('delete')
        ).length,
        ultimo_login: logins.length > 0 ? formatearFecha(logins[logins.length - 1].creado_en) : 'N/A'
    };
}

function calcularResumenDiario() {
    const resumenPorFecha = {};
    
    filteredData.forEach(item => {
        const fecha = item.creado_en.split('T')[0]; // Solo fecha, sin hora
        
        if (!resumenPorFecha[fecha]) {
            resumenPorFecha[fecha] = {
                fecha: fecha,
                total_acciones: 0,
                usuarios_activos: new Set(),
                logins: 0
            };
        }
        
        resumenPorFecha[fecha].total_acciones++;
        resumenPorFecha[fecha].usuarios_activos.add(item.usuario_id);
        
        if (item.accion.toLowerCase().includes('login')) {
            resumenPorFecha[fecha].logins++;
        }
    });
    
    // Convertir Sets a números
    const data = Object.values(resumenPorFecha).map(item => ({
        ...item,
        usuarios_activos: item.usuarios_activos.size
    })).sort((a, b) => new Date(b.fecha) - new Date(a.fecha));
    
    return { data };
}

function calcularActividadPorUsuario() {
    const actividadPorUsuario = {};
    
    filteredData.forEach(item => {
        const userId = item.usuario_id;
        
        if (!actividadPorUsuario[userId]) {
            actividadPorUsuario[userId] = {
                id: userId,
                nombre: item.usuario_nombre || 'Usuario eliminado',
                correo: item.usuario_correo || 'N/A',
                total_acciones: 0,
                logins: 0,
                ultima_actividad: item.creado_en
            };
        }
        
        actividadPorUsuario[userId].total_acciones++;
        
        if (item.accion.toLowerCase().includes('login')) {
            actividadPorUsuario[userId].logins++;
        }
        
        // Actualizar última actividad
        if (new Date(item.creado_en) > new Date(actividadPorUsuario[userId].ultima_actividad)) {
            actividadPorUsuario[userId].ultima_actividad = item.creado_en;
        }
    });
    
    return Object.values(actividadPorUsuario)
        .sort((a, b) => b.total_acciones - a.total_acciones);
}

function calcularAnalisisIPs() {
    const ipAnalisis = {};
    let intentosFallidos = 0;
    
    filteredData.forEach(item => {
        const ip = item.ip || 'unknown';
        
        if (!ipAnalisis[ip]) {
            ipAnalisis[ip] = {
                ip: ip,
                acciones: 0,
                usuarios_unicos: new Set(),
                ultima_actividad: item.creado_en
            };
        }
        
        ipAnalisis[ip].acciones++;
        ipAnalisis[ip].usuarios_unicos.add(item.usuario_id);
        
        if (new Date(item.creado_en) > new Date(ipAnalisis[ip].ultima_actividad)) {
            ipAnalisis[ip].ultima_actividad = item.creado_en;
        }
        
        // Simular intentos fallidos
        if (item.accion.toLowerCase().includes('login') && Math.random() < 0.1) {
            intentosFallidos++;
        }
    });
    
    const ips = Object.values(ipAnalisis).map(item => ({
        ...item,
        usuarios_unicos: item.usuarios_unicos.size
    })).sort((a, b) => b.acciones - a.acciones);
    
    const estadisticas = {
        ips_unicas: ips.length,
        ips_sospechosas: ips.filter(ip => ip.acciones > 50).length,
        intentos_fallidos: intentosFallidos
    };
    
    return { ips, estadisticas };
}

// ================================================================================================
// 🎬 ACCIONES DE INTERFAZ
// ================================================================================================

async function verDetalleAuditoria(id) {
    try {
        const item = auditData.find(a => a.id === id);
        
        if (!item) {
            showAlert('Registro de auditoría no encontrado', 'warning');
            return;
        }
        
        const modal = new bootstrap.Modal(document.getElementById('detalleModal'));
        const content = document.getElementById('detalleContent');
        
        content.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted">Información General</h6>
                    <table class="table table-sm">
                        <tr><td><strong>ID:</strong></td><td>${item.id}</td></tr>
                        <tr><td><strong>Fecha/Hora:</strong></td><td>${formatDate(item.creado_en).fecha} ${formatDate(item.creado_en).hora}</td></tr>
                        <tr><td><strong>Acción:</strong></td><td>${item.accion}</td></tr>
                        <tr><td><strong>IP:</strong></td><td><code>${item.ip || 'N/A'}</code></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted">Usuario</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Nombre:</strong></td><td>${item.usuario_nombre || 'Usuario eliminado'}</td></tr>
                        <tr><td><strong>Correo:</strong></td><td>${item.usuario_correo || 'N/A'}</td></tr>
                        <tr><td><strong>Rol:</strong></td><td>${getRolBadge(item.rol_nombre || 'Usuario')}</td></tr>
                        <tr><td><strong>ID Usuario:</strong></td><td>${item.usuario_id || 'N/A'}</td></tr>
                    </table>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="text-muted">Contexto Adicional</h6>
                    <div class="bg-light p-3 rounded">
                        <p class="mb-2"><strong>Día de la semana:</strong> ${item.dia_semana || 'N/A'}</p>
                        <p class="mb-2"><strong>Hora:</strong> ${item.hora || 'N/A'}</p>
                        <p class="mb-0"><strong>Acción completa:</strong> <code>${item.accion}</code></p>
                    </div>
                </div>
            </div>
        `;
        
        modal.show();
        
    } catch (error) {
        console.error('Error mostrando detalle:', error);
        showAlert('Error al cargar detalles', 'danger');
    }
}

async function refrescarAuditoria() {
    try {
        const btnRefrescar = document.querySelector('button[onclick="refrescarAuditoria()"]');
        const textoOriginal = btnRefrescar.innerHTML;
        
        btnRefrescar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
        btnRefrescar.disabled = true;
        
        await cargarDatosAuditoria();
        aplicarFiltros();
        await cargarEstadisticas();
        
        btnRefrescar.innerHTML = textoOriginal;
        btnRefrescar.disabled = false;
        
        showAlert('Auditoría actualizada correctamente', 'success');
        
    } catch (error) {
        console.error('Error refrescando auditoría:', error);
        showAlert('Error al refrescar auditoría', 'danger');
    }
}

function exportarAuditoria() {
    const modal = new bootstrap.Modal(document.getElementById('exportModal'));
    modal.show();
}

function confirmarExportacion() {
    const formato = document.getElementById('formatoExport').value;
    const rango = document.getElementById('rangoExport').value;
    const incluirDetalles = document.getElementById('incluirDetalles').checked;
    
    let dataToExport = [];
    
    switch (rango) {
        case 'filtro_actual':
            dataToExport = filteredData;
            break;
        case 'todo':
            dataToExport = auditData;
            break;
        case 'personalizado':
            // Implementar lógica de rango personalizado
            dataToExport = filteredData;
            break;
    }
    
    switch (formato) {
        case 'csv':
            exportarCSV(dataToExport, incluirDetalles);
            break;
        case 'json':
            exportarJSON(dataToExport, incluirDetalles);
            break;
        case 'pdf':
            showAlert('Exportación PDF próximamente disponible', 'info');
            break;
    }
    
    bootstrap.Modal.getInstance(document.getElementById('exportModal')).hide();
}

function exportarCSV(data, incluirDetalles) {
    const headers = incluirDetalles ? 
        ['ID', 'Fecha', 'Hora', 'Usuario', 'Correo', 'Rol', 'Acción', 'IP'] :
        ['Fecha', 'Usuario', 'Acción', 'IP'];
    
    const rows = data.map(item => {
        const fechaHora = formatDate(item.creado_en);
        
        return incluirDetalles ? [
            item.id,
            fechaHora.fecha,
            fechaHora.hora,
            item.usuario_nombre || 'Usuario eliminado',
            item.usuario_correo || 'N/A',
            item.rol_nombre || 'Usuario',
            item.accion,
            item.ip || 'N/A'
        ] : [
            `${fechaHora.fecha} ${fechaHora.hora}`,
            item.usuario_nombre || 'Usuario eliminado',
            item.accion,
            item.ip || 'N/A'
        ];
    });
    
    const csvContent = [headers, ...rows]
        .map(row => row.map(field => `"${field}"`).join(','))
        .join('\n');
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `auditoria_${new Date().toISOString().split('T')[0]}.csv`;
    link.click();
    
    showAlert('Archivo CSV exportado correctamente', 'success');
}

function exportarJSON(data, incluirDetalles) {
    const exportData = {
        metadata: {
            exportado_en: new Date().toISOString(),
            total_registros: data.length,
            incluye_detalles: incluirDetalles,
            filtros_aplicados: {
                fecha: document.getElementById('filtroFecha').value,
                usuario: document.getElementById('filtroUsuario').value,
                accion: document.getElementById('filtroAccion').value
            }
        },
        auditoria: incluirDetalles ? data : data.map(item => ({
            fecha: item.creado_en,
            usuario: item.usuario_nombre,
            accion: item.accion,
            ip: item.ip
        }))
    };
    
    const jsonContent = JSON.stringify(exportData, null, 2);
    const blob = new Blob([jsonContent], { type: 'application/json' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `auditoria_${new Date().toISOString().split('T')[0]}.json`;
    link.click();
    
    showAlert('Archivo JSON exportado correctamente', 'success');
}

function analizarSeguridad() {
    // Simular análisis de seguridad más detallado
    showAlert('🔍 Análisis de seguridad iniciado. Esta función estará disponible próximamente.', 'info');
}

function mostrarError(mensaje) {
    showAlert(mensaje, 'danger');
}

function crearGraficoActividad(data) {
    // Esta función requeriría Chart.js para implementar gráficos
    // Por ahora, dejamos un placeholder
    console.log('Gráfico de actividad:', data);
}

// Hacer funciones globales
window.aplicarFiltros = aplicarFiltros;
window.cambiarPagina = cambiarPagina;
window.verDetalleAuditoria = verDetalleAuditoria;
window.refrescarAuditoria = refrescarAuditoria;
window.exportarAuditoria = exportarAuditoria;
window.confirmarExportacion = confirmarExportacion;
window.analizarSeguridad = analizarSeguridad;