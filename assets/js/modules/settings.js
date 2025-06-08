// /assets/js/modules/settings.js

let configuracionActual = {};
let cambiosPendientes = false;

document.addEventListener('DOMContentLoaded', function() {
    cargarConfiguracion();
    cargarEstadisticas();
    configurarEventListeners();
});

async function cargarConfiguracion() {
    try {
        console.log('Cargando configuración...');
        const config = await api.getSettings();
        console.log('Configuración obtenida:', config);
        
        configuracionActual = config;
        mostrarConfiguracion(config);
        
        if (config.creado_en) {
            document.getElementById('ultimaConfiguracion').textContent = 
                formatDate(config.creado_en);
        }
        
    } catch (error) {
        console.error('Error cargando configuración:', error);
        showAlert('Error al cargar configuración: ' + error.message, 'warning');
        
        // Cargar valores por defecto
        mostrarConfiguracion({
            nombre_empresa: '',
            zona_horaria: 'UTC',
            tiempo_max_respuesta: 60,
            tiempo_cierre_tras_respuesta: 1440,
            smtp_host: '',
            smtp_user: '',
            smtp_pass: '',
            notificaciones_email: true
        });
    }
}

function mostrarConfiguracion(config) {
    // Información de empresa
    document.getElementById('nombre_empresa').value = config.nombre_empresa || '';
    document.getElementById('zona_horaria').value = config.zona_horaria || 'UTC';
    
    // Tiempos
    document.getElementById('tiempo_max_respuesta').value = config.tiempo_max_respuesta || 60;
    document.getElementById('tiempo_cierre_tras_respuesta').value = config.tiempo_cierre_tras_respuesta || 1440;
    
    // Email
    document.getElementById('smtp_host').value = config.smtp_host || '';
    document.getElementById('smtp_user').value = config.smtp_user || '';
    document.getElementById('smtp_pass').value = config.smtp_pass || '';
    document.getElementById('notificaciones_email').checked = config.notificaciones_email !== false;
    
    // Actualizar estado SMTP
    if (config.smtp_host && config.smtp_user) {
        document.getElementById('estadoSMTP').innerHTML = 
            '<i class="fas fa-check"></i> Configurado';
        document.getElementById('estadoSMTP').className = 'badge bg-success';
    }
}

async function cargarEstadisticas() {
    try {
        // Cargar estadísticas básicas
        const [usuarios, tickets] = await Promise.all([
            api.getUsuarios(),
            api.getTickets()
        ]);
        
        const abiertos = tickets.filter(t => t.estado === 'abierto').length;
        
        document.getElementById('totalUsuarios').textContent = usuarios.length;
        document.getElementById('totalTickets').textContent = tickets.length;
        document.getElementById('ticketsAbiertos').textContent = abiertos;
        document.getElementById('promedioRespuesta').textContent = '4.2h'; // Simulado
        
    } catch (error) {
        console.error('Error cargando estadísticas:', error);
    }
}

function configurarEventListeners() {
    // Detectar cambios en el formulario
    const form = document.getElementById('formConfiguracion');
    const inputs = form.querySelectorAll('input, select');
    
    inputs.forEach(input => {
        input.addEventListener('input', detectarCambios);
        input.addEventListener('change', detectarCambios);
    });
    
    // Email toggle
    document.getElementById('notificaciones_email').addEventListener('change', detectarCambios);
}

function detectarCambios() {
    const form = document.getElementById('formConfiguracion');
    const formData = new FormData(form);
    
    // Comparar con configuración actual
    let haycambios = false;
    
    for (let [key, value] of formData.entries()) {
        if (configuracionActual[key] != value) {
            haycambios = true;
            break;
        }
    }
    
    // Verificar checkbox de notificaciones
    const notificacionesActual = document.getElementById('notificaciones_email').checked;
    if (configuracionActual.notificaciones_email !== notificacionesActual) {
        hayChanges = true;
    }
    
    cambiosPendientes = hayChangios;
    
    // Actualizar UI
    const btnGuardar = document.getElementById('btnGuardarConfig');
    if (hayChangios) {
        btnGuardar.classList.remove('btn-success');
        btnGuardar.classList.add('btn-warning');
        btnGuardar.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios Pendientes';
    } else {
        btnGuardar.classList.remove('btn-warning');
        btnGuardar.classList.add('btn-success');
        btnGuardar.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
    }
}

async function guardarConfiguracion() {
    const form = document.getElementById('formConfiguracion');
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const data = {};
    
    // Recopilar datos del formulario
    for (let [key, value] of formData.entries()) {
        data[key] = value.trim();
    }
    
    // Agregar checkbox de notificaciones
    data.notificaciones_email = document.getElementById('notificaciones_email').checked;
    
    console.log('Guardando configuración:', data);
    
    try {
        // Deshabilitar botón
        const btnGuardar = document.getElementById('btnGuardarConfig');
        const textoOriginal = btnGuardar.innerHTML;
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        
        // Guardar via API
        await api.updateSettings(data);
        
        // Actualizar configuración local
        configuracionActual = { ...data };
        cambiosPendientes = false;
        
        // Restaurar botón
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = textoOriginal;
        btnGuardar.classList.remove('btn-warning');
        btnGuardar.classList.add('btn-success');
        
        // Actualizar timestamp
        document.getElementById('ultimaConfiguracion').textContent = 
            formatDate(new Date().toISOString());
        
        showAlert('Configuración guardada correctamente', 'success');
        
    } catch (error) {
        console.error('Error guardando configuración:', error);
        showAlert('Error al guardar configuración: ' + error.message, 'danger');
        
        // Restaurar botón
        const btnGuardar = document.getElementById('btnGuardarConfig');
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
    }
}

function probarEmail() {
    const smtpHost = document.getElementById('smtp_host').value;
    const smtpUser = document.getElementById('smtp_user').value;
    
    if (!smtpHost || !smtpUser) {
        showAlert('Debes configurar SMTP Host y Usuario antes de probar', 'warning');
        return;
    }
    
    // Prellenar email del usuario
    document.getElementById('emailPrueba').value = smtpUser;
    
    const modal = new bootstrap.Modal(document.getElementById('pruebaEmailModal'));
    modal.show();
}

async function enviarEmailPrueba() {
    const emailDestino = document.getElementById('emailPrueba').value;
    
    if (!emailDestino) {
        showAlert('Debes ingresar un email de destino', 'warning');
        return;
    }
    
    const resultadoDiv = document.getElementById('resultadoPrueba');
    resultadoDiv.innerHTML = `
        <div class="alert alert-info">
            <i class="fas fa-spinner fa-spin"></i> Enviando email de prueba...
        </div>
    `;
    
    try {
        // Simular envío de email de prueba
        // En la implementación real, harías una llamada a un endpoint específico
        const response = await fetch('?api=1&ruta=test-email', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                email: emailDestino
            })
        });
        
        if (response.ok) {
            resultadoDiv.innerHTML = `
                <div class="alert alert-success">
                    <i class="fas fa-check"></i> Email enviado correctamente a ${emailDestino}
                </div>
            `;
            
            // Actualizar estado SMTP
            document.getElementById('estadoSMTP').innerHTML = 
                '<i class="fas fa-check"></i> Funcionando';
            document.getElementById('estadoSMTP').className = 'badge bg-success';
            
        } else {
            throw new Error('Error en el servidor');
        }
        
    } catch (error) {
        console.error('Error enviando email de prueba:', error);
        resultadoDiv.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-times"></i> Error al enviar email: ${error.message}
                <br><small>Verifica la configuración SMTP</small>
            </div>
        `;
        
        // Actualizar estado SMTP
        document.getElementById('estadoSMTP').innerHTML = 
            '<i class="fas fa-times"></i> Error';
        document.getElementById('estadoSMTP').className = 'badge bg-danger';
    }
}

function toggleSmtpPassword() {
    const password = document.getElementById('smtp_pass');
    const icon = document.getElementById('smtpPassIcon');
    
    if (password.type === 'password') {
        password.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        password.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

function resetearConfiguracion() {
    if (!confirm('¿Estás seguro de restablecer la configuración a valores por defecto?')) {
        return;
    }
    
    mostrarConfiguracion({
        nombre_empresa: 'Mi Empresa S.A.',
        zona_horaria: 'UTC',
        tiempo_max_respuesta: 60,
        tiempo_cierre_tras_respuesta: 1440,
        smtp_host: '',
        smtp_user: '',
        smtp_pass: '',
        notificaciones_email: true
    });
    
    detectarCambios();
    showAlert('Configuración restablecida a valores por defecto', 'info');
}

function exportarConfiguracion() {
    const config = {
        ...configuracionActual,
        exportado_en: new Date().toISOString()
    };
    
    const dataStr = JSON.stringify(config, null, 2);
    const dataBlob = new Blob([dataStr], {type: 'application/json'});
    
    const link = document.createElement('a');
    link.href = URL.createObjectURL(dataBlob);
    link.download = 'helpdesk_config_' + new Date().toISOString().split('T')[0] + '.json';
    link.click();
    
    showAlert('Configuración exportada correctamente', 'success');
}

// Utilidades
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

// Prevenir pérdida de datos
window.addEventListener('beforeunload', function(e) {
    if (cambiosPendientes) {
        e.preventDefault();
        e.returnValue = '';
        return '';
    }
});