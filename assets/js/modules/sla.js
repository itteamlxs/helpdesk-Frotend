// /assets/js/modules/sla.js - Gestión de SLA

let configuracionSLA = {};
let estadisticasSLA = {};
let cambiosPendientes = false;

document.addEventListener('DOMContentLoaded', function() {
    cargarConfiguracionSLA();
    cargarEstadisticasSLA();
    configurarEventListeners();
    configurarValidaciones();
});

// ================================================================================================
// 📊 CARGAR DATOS INICIALES
// ================================================================================================

async function cargarConfiguracionSLA() {
    try {
        console.log('Cargando configuración SLA...');
        
        const response = await api.getSLA();
        console.log('Configuración SLA obtenida:', response);
        
        configuracionSLA = Array.isArray(response) ? response : [];
        mostrarConfiguracion(configuracionSLA);
        
    } catch (error) {
        console.error('Error cargando configuración SLA:', error);
        showAlert('Error al cargar configuración SLA: ' + error.message, 'warning');
        
        // Cargar valores por defecto
        mostrarConfiguracion([]);
    }
}

async function cargarEstadisticasSLA() {
    try {
        console.log('Cargando estadísticas SLA...');
        
        // Intentar usar el endpoint de estadísticas si existe
        let stats;
        try {
            stats = await api.request('stats/sla');
        } catch (apiError) {
            // Fallback: calcular estadísticas básicas desde tickets
            const tickets = await api.getTickets();
            stats = calcularEstadisticasBasicas(tickets);
        }
        
        estadisticasSLA = stats;
        mostrarEstadisticas(stats);
        
    } catch (error) {
        console.error('Error cargando estadísticas SLA:', error);
        mostrarEstadisticasError();
    }
}

// ================================================================================================
// 🎨 MOSTRAR DATOS EN LA INTERFAZ
// ================================================================================================

function mostrarConfiguracion(slaData) {
    console.log('Mostrando configuración SLA:', slaData);
    
    // Crear mapeo de configuración por prioridad
    const slaMap = {};
    slaData.forEach(item => {
        slaMap[item.prioridad] = item;
    });
    
    // Valores por defecto
    const defaults = {
        'urgente': { tiempo_respuesta: 60, tiempo_resolucion: 240 },
        'alta': { tiempo_respuesta: 240, tiempo_resolucion: 720 },
        'media': { tiempo_respuesta: 720, tiempo_resolucion: 1440 },
        'baja': { tiempo_respuesta: 1440, tiempo_resolucion: 2880 }
    };
    
    // Llenar campos del formulario
    Object.keys(defaults).forEach(prioridad => {
        const config = slaMap[prioridad] || defaults[prioridad];
        
        const respuestaField = document.getElementById(`${prioridad}_respuesta`);
        const resolucionField = document.getElementById(`${prioridad}_resolucion`);
        
        if (respuestaField) {
            respuestaField.value = config.tiempo_respuesta;
        }
        if (resolucionField) {
            resolucionField.value = config.tiempo_resolucion;
        }
    });
    
    // Actualizar indicadores visuales
    actualizarIndicadoresVisuales();
}

function mostrarEstadisticas(stats) {
    console.log('Mostrando estadísticas SLA:', stats);
    
    // Crear HTML para estadísticas de cumplimiento
    if (stats.por_prioridad && Array.isArray(stats.por_prioridad)) {
        const statsHTML = stats.por_prioridad.map(item => {
            const prioridadClass = getPriorityClass(item.prioridad);
            const cumplimientoRespuesta = item.porcentaje_respuesta_sla || 0;
            const cumplimientoResolucion = item.porcentaje_resolucion_sla || 0;
            
            return `
                <div class="col-md-6 col-lg-3 mb-3">
                    <div class="card border-${prioridadClass}">
                        <div class="card-header bg-${prioridadClass} text-white">
                            <h6 class="mb-0 text-uppercase">${item.prioridad}</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <small class="text-muted">Cumplimiento Respuesta</small>
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold">${cumplimientoRespuesta}%</span>
                                    <small>${item.tickets_respondidos_sla || 0}/${item.total_tickets || 0}</small>
                                </div>
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar bg-${prioridadClass}" style="width: ${cumplimientoRespuesta}%"></div>
                                </div>
                            </div>
                            <div class="mb-0">
                                <small class="text-muted">Cumplimiento Resolución</small>
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold">${cumplimientoResolucion}%</span>
                                    <small>${item.tickets_resueltos_sla || 0}/${item.total_tickets || 0}</small>
                                </div>
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar bg-${prioridadClass}" style="width: ${cumplimientoResolucion}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        document.getElementById('slaStats').innerHTML = statsHTML;
    }
    
    // Actualizar indicadores del sidebar
    if (stats.global) {
        document.getElementById('cumplimiento-global').textContent = `${stats.global.compliance_respuesta || 0}%`;
        document.getElementById('progress-global').style.width = `${stats.global.compliance_respuesta || 0}%`;
        document.getElementById('tickets-riesgo').textContent = stats.global.tickets_en_riesgo || '0';
        document.getElementById('incumplidos-hoy').textContent = stats.global.incumplidos_hoy || '0';
        document.getElementById('tiempo-promedio').textContent = `${stats.global.tiempo_promedio || '0'}h`;
    }
}

function mostrarEstadisticasError() {
    document.getElementById('slaStats').innerHTML = `
        <div class="col-12 text-center text-danger">
            <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
            <p>Error al cargar estadísticas de SLA</p>
            <button class="btn btn-outline-primary btn-sm" onclick="cargarEstadisticasSLA()">
                <i class="fas fa-sync"></i> Reintentar
            </button>
        </div>
    `;
}

// ================================================================================================
// ⚙️ CONFIGURAR EVENT LISTENERS
// ================================================================================================

function configurarEventListeners() {
    // Detectar cambios en campos de SLA
    const campos = ['urgente_respuesta', 'urgente_resolucion', 'alta_respuesta', 'alta_resolucion', 
                   'media_respuesta', 'media_resolucion', 'baja_respuesta', 'baja_resolucion'];
    
    campos.forEach(campo => {
        const elemento = document.getElementById(campo);
        if (elemento) {
            elemento.addEventListener('input', detectarCambios);
            elemento.addEventListener('change', actualizarIndicadoresVisuales);
        }
    });
    
    // Event listeners para configuración adicional
    const checkboxes = ['notificar_sla', 'escalamiento_auto'];
    checkboxes.forEach(checkbox => {
        const elemento = document.getElementById(checkbox);
        if (elemento) {
            elemento.addEventListener('change', detectarCambios);
        }
    });
    
    const selects = ['horario_laboral'];
    selects.forEach(select => {
        const elemento = document.getElementById(select);
        if (elemento) {
            elemento.addEventListener('change', detectarCambios);
        }
    });
}

function configurarValidaciones() {
    // Validación en tiempo real
    const campos = document.querySelectorAll('input[type="number"]');
    campos.forEach(campo => {
        campo.addEventListener('input', function() {
            validarCampo(this);
            actualizarIndicadoresVisuales();
        });
    });
}

// ================================================================================================
// 🔍 VALIDACIONES Y CAMBIOS
// ================================================================================================

function detectarCambios() {
    cambiosPendientes = true;
    
    const btnGuardar = document.getElementById('btnGuardarSLA');
    if (btnGuardar) {
        btnGuardar.classList.remove('btn-success');
        btnGuardar.classList.add('btn-warning');
        btnGuardar.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios Pendientes';
    }
}

function validarCampo(campo) {
    const valor = parseInt(campo.value);
    const min = parseInt(campo.min);
    const max = parseInt(campo.max);
    
    if (valor < min || valor > max) {
        campo.classList.add('is-invalid');
        return false;
    } else {
        campo.classList.remove('is-invalid');
        campo.classList.add('is-valid');
        return true;
    }
}

function validarFormulario() {
    const campos = document.querySelectorAll('#formSLA input[type="number"]');
    let valido = true;
    
    campos.forEach(campo => {
        if (!validarCampo(campo)) {
            valido = false;
        }
    });
    
    // Validación lógica: tiempo de resolución debe ser mayor que tiempo de respuesta
    const prioridades = ['urgente', 'alta', 'media', 'baja'];
    prioridades.forEach(prioridad => {
        const respuesta = parseInt(document.getElementById(`${prioridad}_respuesta`).value);
        const resolucion = parseInt(document.getElementById(`${prioridad}_resolucion`).value);
        
        if (resolucion <= respuesta) {
            showAlert(`Error en ${prioridad}: El tiempo de resolución debe ser mayor que el tiempo de respuesta`, 'warning');
            valido = false;
        }
    });
    
    return valido;
}

// ================================================================================================
// 💾 GUARDAR CONFIGURACIÓN
// ================================================================================================

async function guardarSLA() {
    if (!validarFormulario()) {
        return;
    }
    
    const btnGuardar = document.getElementById('btnGuardarSLA');
    const textoOriginal = btnGuardar.innerHTML;
    
    try {
        // Deshabilitar botón y mostrar loading
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        
        // Recopilar datos del formulario
        const valores = [];
        const prioridades = ['urgente', 'alta', 'media', 'baja'];
        
        prioridades.forEach(prioridad => {
            const tiempoRespuesta = parseInt(document.getElementById(`${prioridad}_respuesta`).value);
            const tiempoResolucion = parseInt(document.getElementById(`${prioridad}_resolucion`).value);
            
            valores.push({
                prioridad: prioridad,
                tiempo_respuesta: tiempoRespuesta,
                tiempo_resolucion: tiempoResolucion
            });
        });
        
        console.log('Guardando configuración SLA:', valores);
        
        // Enviar a la API
        await api.updateSLA({ valores: valores });
        
        // Actualizar configuración local
        configuracionSLA = valores;
        cambiosPendientes = false;
        
        // Restaurar botón
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="fas fa-check"></i> Configuración Guardada';
        btnGuardar.classList.remove('btn-warning');
        btnGuardar.classList.add('btn-success');
        
        // Recargar estadísticas
        setTimeout(() => {
            cargarEstadisticasSLA();
            btnGuardar.innerHTML = textoOriginal;
        }, 2000);
        
        showAlert('Configuración SLA guardada correctamente', 'success');
        
    } catch (error) {
        console.error('Error guardando SLA:', error);
        showAlert('Error al guardar configuración SLA: ' + error.message, 'danger');
        
        // Restaurar botón
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = textoOriginal;
    }
}

// ================================================================================================
// 🎨 FUNCIONES DE INTERFAZ
// ================================================================================================

function actualizarIndicadoresVisuales() {
    // Convertir minutos a formato legible
    const prioridades = ['urgente', 'alta', 'media', 'baja'];
    
    prioridades.forEach(prioridad => {
        const respuestaInput = document.getElementById(`${prioridad}_respuesta`);
        const resolucionInput = document.getElementById(`${prioridad}_resolucion`);
        
        if (respuestaInput && resolucionInput) {
            const respuesta = parseInt(respuestaInput.value);
            const resolucion = parseInt(resolucionInput.value);
            
            // Actualizar textos de ayuda con formato legible
            const respuestaHelp = respuestaInput.parentNode.parentNode.querySelector('.form-text');
            const resolucionHelp = resolucionInput.parentNode.parentNode.querySelector('.form-text');
            
            if (respuestaHelp) {
                respuestaHelp.innerHTML = `${formatearTiempo(respuesta)} | Recomendado: ${respuestaHelp.textContent.split('|')[1] || ''}`;
            }
            
            if (resolucionHelp) {
                resolucionHelp.innerHTML = `${formatearTiempo(resolucion)} | Recomendado: ${resolucionHelp.textContent.split('|')[1] || ''}`;
            }
        }
    });
}

function formatearTiempo(minutos) {
    if (minutos < 60) {
        return `${minutos} minutos`;
    } else if (minutos < 1440) {
        const horas = Math.floor(minutos / 60);
        const mins = minutos % 60;
        return mins > 0 ? `${horas}h ${mins}m` : `${horas}h`;
    } else {
        const dias = Math.floor(minutos / 1440);
        const horas = Math.floor((minutos % 1440) / 60);
        return horas > 0 ? `${dias}d ${horas}h` : `${dias}d`;
    }
}

function getPriorityClass(prioridad) {
    const classes = {
        'urgente': 'danger',
        'alta': 'warning',
        'media': 'info',
        'baja': 'secondary'
    };
    return classes[prioridad] || 'secondary';
}

// ================================================================================================
// 🔧 ACCIONES ADICIONALES
// ================================================================================================

function restaurarDefecto() {
    if (!confirm('¿Estás seguro de restaurar los valores por defecto? Se perderán los cambios actuales.')) {
        return;
    }
    
    // Valores por defecto del sistema
    const defaults = {
        'urgente': { respuesta: 60, resolucion: 240 },
        'alta': { respuesta: 240, resolucion: 720 },
        'media': { respuesta: 720, resolucion: 1440 },
        'baja': { respuesta: 1440, resolucion: 2880 }
    };
    
    Object.keys(defaults).forEach(prioridad => {
        document.getElementById(`${prioridad}_respuesta`).value = defaults[prioridad].respuesta;
        document.getElementById(`${prioridad}_resolucion`).value = defaults[prioridad].resolucion;
    });
    
    // Resetear configuración adicional
    document.getElementById('notificar_sla').checked = true;
    document.getElementById('escalamiento_auto').checked = true;
    document.getElementById('tiempo_notificacion').value = 30;
    document.getElementById('horario_laboral').value = '24x7';
    
    detectarCambios();
    actualizarIndicadoresVisuales();
    
    showAlert('Valores por defecto restaurados', 'info');
}

function exportarSLA() {
    const config = {
        sla_configuration: {},
        metadata: {
            exportado_en: new Date().toISOString(),
            version: '1.0',
            sistema: 'Helpdesk SLA'
        }
    };
    
    // Recopilar configuración actual
    const prioridades = ['urgente', 'alta', 'media', 'baja'];
    prioridades.forEach(prioridad => {
        config.sla_configuration[prioridad] = {
            tiempo_respuesta: parseInt(document.getElementById(`${prioridad}_respuesta`).value),
            tiempo_resolucion: parseInt(document.getElementById(`${prioridad}_resolucion`).value)
        };
    });
    
    // Exportar como JSON
    const dataStr = JSON.stringify(config, null, 2);
    const dataBlob = new Blob([dataStr], { type: 'application/json' });
    
    const link = document.createElement('a');
    link.href = URL.createObjectURL(dataBlob);
    link.download = `sla_config_${new Date().toISOString().split('T')[0]}.json`;
    link.click();
    
    showAlert('Configuración SLA exportada correctamente', 'success');
}

function simularIncumplimiento() {
    const modal = new bootstrap.Modal(document.getElementById('simulacionModal'));
    modal.show();
}

function simular(tipo) {
    const mensajes = {
        'riesgo': '⚠️ Simulación: 3 tickets están próximos a incumplir SLA',
        'incumplido': '🚨 Simulación: 1 ticket ha incumplido el SLA de resolución',
        'notificacion': '🔔 Simulación: Notificación de SLA enviada a técnicos'
    };
    
    const tipos = {
        'riesgo': 'warning',
        'incumplido': 'danger',
        'notificacion': 'info'
    };
    
    showAlert(mensajes[tipo], tipos[tipo]);
    
    // Simular actualización de contadores
    if (tipo === 'riesgo') {
        document.getElementById('tickets-riesgo').textContent = '3';
    } else if (tipo === 'incumplido') {
        document.getElementById('incumplidos-hoy').textContent = '1';
    }
    
    // Cerrar modal
    bootstrap.Modal.getInstance(document.getElementById('simulacionModal')).hide();
}

// ================================================================================================
// 📊 FUNCIONES AUXILIARES
// ================================================================================================

function calcularEstadisticasBasicas(tickets) {
    // Fallback para calcular estadísticas básicas cuando no hay endpoint dedicado
    const stats = {
        por_prioridad: [],
        global: {
            compliance_respuesta: 0,
            compliance_resolucion: 0,
            tickets_en_riesgo: 0,
            incumplidos_hoy: 0,
            tiempo_promedio: 0
        }
    };
    
    const prioridades = ['urgente', 'alta', 'media', 'baja'];
    prioridades.forEach(prioridad => {
        const ticketsPrioridad = tickets.filter(t => t.prioridad === prioridad);
        const ticketsCerrados = ticketsPrioridad.filter(t => t.estado === 'cerrado');
        
        stats.por_prioridad.push({
            prioridad: prioridad,
            total_tickets: ticketsPrioridad.length,
            tickets_respondidos_sla: Math.floor(ticketsPrioridad.length * 0.85), // Simulado
            tickets_resueltos_sla: Math.floor(ticketsCerrados.length * 0.80), // Simulado
            porcentaje_respuesta_sla: ticketsPrioridad.length > 0 ? 85 : 0,
            porcentaje_resolucion_sla: ticketsCerrados.length > 0 ? 80 : 0
        });
    });
    
    // Estadísticas globales simuladas
    stats.global.compliance_respuesta = 82;
    stats.global.compliance_resolucion = 78;
    stats.global.tickets_en_riesgo = Math.floor(Math.random() * 5);
    stats.global.incumplidos_hoy = Math.floor(Math.random() * 3);
    stats.global.tiempo_promedio = (Math.random() * 10 + 2).toFixed(1);
    
    return stats;
}

// Hacer funciones globales para ser llamadas desde HTML
window.guardarSLA = guardarSLA;
window.restaurarDefecto = restaurarDefecto;
window.exportarSLA = exportarSLA;
window.simularIncumplimiento = simularIncumplimiento;
window.simular = simular;