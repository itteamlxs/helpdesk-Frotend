<?php 
$titulo = 'Gestión de SLA (Service Level Agreement)';
$jsModules = ['sla'];
ob_start(); 
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3">
            <i class="fas fa-clock"></i> Gestión de SLA
        </h1>
        <p class="text-muted">Configura los tiempos de respuesta y resolución según la prioridad del ticket</p>
    </div>
    <div class="col-md-4 text-end">
        <button class="btn btn-success" onclick="guardarSLA()" id="btnGuardarSLA">
            <i class="fas fa-save"></i> Guardar Configuración
        </button>
    </div>
</div>

<!-- Estadísticas de cumplimiento SLA -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line"></i> Cumplimiento SLA Actual
                </h5>
            </div>
            <div class="card-body">
                <div class="row" id="slaStats">
                    <div class="col-12 text-center">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                        <p>Cargando estadísticas de cumplimiento...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Configuración de tiempos SLA -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-cog"></i> Configuración de Tiempos SLA
                </h5>
            </div>
            <div class="card-body">
                <form id="formSLA">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Nota:</strong> Los tiempos se especifican en <strong>minutos</strong>. 
                        El sistema calculará automáticamente el cumplimiento basado en estos valores.
                    </div>

                    <!-- SLA para Prioridad Urgente -->
                    <div class="card mb-3 border-danger">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-exclamation-circle"></i> Prioridad: URGENTE
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="urgente_respuesta" class="form-label">
                                            <i class="fas fa-reply"></i> Tiempo de Primera Respuesta
                                        </label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="urgente_respuesta" 
                                                   name="urgente_respuesta" min="1" max="1440" value="60" required>
                                            <span class="input-group-text">minutos</span>
                                        </div>
                                        <div class="form-text">Recomendado: 15-60 minutos</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="urgente_resolucion" class="form-label">
                                            <i class="fas fa-check-circle"></i> Tiempo de Resolución
                                        </label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="urgente_resolucion" 
                                                   name="urgente_resolucion" min="1" max="10080" value="240" required>
                                            <span class="input-group-text">minutos</span>
                                        </div>
                                        <div class="form-text">Recomendado: 2-4 horas (120-240 min)</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SLA para Prioridad Alta -->
                    <div class="card mb-3 border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">
                                <i class="fas fa-exclamation-triangle"></i> Prioridad: ALTA
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="alta_respuesta" class="form-label">
                                            <i class="fas fa-reply"></i> Tiempo de Primera Respuesta
                                        </label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="alta_respuesta" 
                                                   name="alta_respuesta" min="1" max="1440" value="240" required>
                                            <span class="input-group-text">minutos</span>
                                        </div>
                                        <div class="form-text">Recomendado: 2-4 horas</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="alta_resolucion" class="form-label">
                                            <i class="fas fa-check-circle"></i> Tiempo de Resolución
                                        </label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="alta_resolucion" 
                                                   name="alta_resolucion" min="1" max="10080" value="720" required>
                                            <span class="input-group-text">minutos</span>
                                        </div>
                                        <div class="form-text">Recomendado: 8-12 horas</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SLA para Prioridad Media -->
                    <div class="card mb-3 border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-minus-circle"></i> Prioridad: MEDIA
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="media_respuesta" class="form-label">
                                            <i class="fas fa-reply"></i> Tiempo de Primera Respuesta
                                        </label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="media_respuesta" 
                                                   name="media_respuesta" min="1" max="1440" value="720" required>
                                            <span class="input-group-text">minutos</span>
                                        </div>
                                        <div class="form-text">Recomendado: 8-12 horas</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="media_resolucion" class="form-label">
                                            <i class="fas fa-check-circle"></i> Tiempo de Resolución
                                        </label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="media_resolucion" 
                                                   name="media_resolucion" min="1" max="10080" value="1440" required>
                                            <span class="input-group-text">minutos</span>
                                        </div>
                                        <div class="form-text">Recomendado: 1-2 días</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SLA para Prioridad Baja -->
                    <div class="card mb-3 border-secondary">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-circle"></i> Prioridad: BAJA
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="baja_respuesta" class="form-label">
                                            <i class="fas fa-reply"></i> Tiempo de Primera Respuesta
                                        </label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="baja_respuesta" 
                                                   name="baja_respuesta" min="1" max="10080" value="1440" required>
                                            <span class="input-group-text">minutos</span>
                                        </div>
                                        <div class="form-text">Recomendado: 1-2 días</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="baja_resolucion" class="form-label">
                                            <i class="fas fa-check-circle"></i> Tiempo de Resolución
                                        </label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="baja_resolucion" 
                                                   name="baja_resolucion" min="1" max="10080" value="2880" required>
                                            <span class="input-group-text">minutos</span>
                                        </div>
                                        <div class="form-text">Recomendado: 2-5 días</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Configuración adicional -->
                    <div class="card border-light">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-cogs"></i> Configuración Adicional
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="notificar_sla" checked>
                                        <label class="form-check-label" for="notificar_sla">
                                            <i class="fas fa-bell"></i> Notificar cuando SLA esté próximo a vencer
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="escalamiento_auto" checked>
                                        <label class="form-check-label" for="escalamiento_auto">
                                            <i class="fas fa-arrow-up"></i> Escalamiento automático cuando se incumple SLA
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tiempo_notificacion" class="form-label">
                                            Notificar X minutos antes del vencimiento
                                        </label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="tiempo_notificacion" 
                                                   value="30" min="5" max="480">
                                            <span class="input-group-text">minutos</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="horario_laboral" class="form-label">
                                            Considerar solo horario laboral
                                        </label>
                                        <select class="form-select" id="horario_laboral">
                                            <option value="24x7" selected>24/7 - Todos los días</option>
                                            <option value="laboral">Solo días laborales (Lun-Vie)</option>
                                            <option value="personalizado">Horario personalizado</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar con información -->
    <div class="col-lg-4">
        <!-- Indicadores de SLA -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-tachometer-alt"></i> Indicadores SLA
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Cumplimiento Global:</span>
                        <span class="fw-bold text-success" id="cumplimiento-global">-</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" id="progress-global" style="width: 0%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Tickets en Riesgo:</span>
                        <span class="fw-bold text-warning" id="tickets-riesgo">-</span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>SLA Incumplidos Hoy:</span>
                        <span class="fw-bold text-danger" id="incumplidos-hoy">-</span>
                    </div>
                </div>
                
                <div class="mb-0">
                    <div class="d-flex justify-content-between">
                        <span>Tiempo Prom. Resolución:</span>
                        <span class="fw-bold text-info" id="tiempo-promedio">-</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leyenda de tiempos -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle"></i> Leyenda de Tiempos
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small><strong>Primera Respuesta:</strong> Tiempo desde la creación del ticket hasta el primer comentario del técnico</small>
                </div>
                <div class="mb-2">
                    <small><strong>Resolución:</strong> Tiempo desde la creación hasta el cierre del ticket</small>
                </div>
                <hr>
                <div class="mb-2">
                    <span class="badge bg-success">Dentro SLA</span>
                    <small> - Cumple tiempos establecidos</small>
                </div>
                <div class="mb-2">
                    <span class="badge bg-warning">En Riesgo</span>
                    <small> - 80% del tiempo SLA consumido</small>
                </div>
                <div class="mb-0">
                    <span class="badge bg-danger">Incumplido</span>
                    <small> - Superó el tiempo SLA</small>
                </div>
            </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-bolt"></i> Acciones Rápidas
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary btn-sm" onclick="restaurarDefecto()">
                        <i class="fas fa-undo"></i> Restaurar Valores por Defecto
                    </button>
                    <button class="btn btn-outline-info btn-sm" onclick="exportarSLA()">
                        <i class="fas fa-download"></i> Exportar Configuración
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="window.location.href='?ruta=dashboard'">
                        <i class="fas fa-chart-line"></i> Ver Dashboard
                    </button>
                    <button class="btn btn-outline-warning btn-sm" onclick="simularIncumplimiento()">
                        <i class="fas fa-exclamation-triangle"></i> Simular Alertas
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de simulación -->
<div class="modal fade" id="simulacionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-flask"></i> Simulación de Alertas SLA
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Esta función simula diferentes escenarios de SLA para probar el sistema de alertas:</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-warning" onclick="simular('riesgo')">
                        <i class="fas fa-clock"></i> Simular Tickets en Riesgo
                    </button>
                    <button class="btn btn-outline-danger" onclick="simular('incumplido')">
                        <i class="fas fa-exclamation-triangle"></i> Simular SLA Incumplido
                    </button>
                    <button class="btn btn-outline-info" onclick="simular('notificacion')">
                        <i class="fas fa-bell"></i> Simular Notificación
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php 
$contenido = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>