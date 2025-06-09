<?php
// ================================================================================================
// 📊 MÓDULO 3: STATSCONTROLLER OPTIMIZADO - SISTEMA HELPDESK
// ================================================================================================
// Autor: Sistema Helpdesk Optimization
// Fecha: 2025
// Propósito: Controlador de estadísticas que utiliza las vistas de BD del Módulo 1
// ================================================================================================

namespace Controllers;

use Core\BaseController;
use PDO;
use Exception;

class StatsController extends BaseController
{
    // Cache simple en memoria para evitar consultas repetidas
    private static $cache = [];
    private static $cacheTimeout = 300; // 5 minutos
    
    /**
     * 📊 Obtener métricas principales del dashboard
     * Utiliza la vista v_dashboard_metrics del Módulo 1
     */
    public function dashboard(): void
    {
        try {
            $cacheKey = 'dashboard_metrics';
            
            // Verificar cache
            if ($this->isCacheValid($cacheKey)) {
                $this->json(self::$cache[$cacheKey]['data']);
                return;
            }
            
            // Consulta optimizada usando vista
            $stmt = $this->db->query("SELECT * FROM v_dashboard_metrics");
            $metrics = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$metrics) {
                // Fallback si la vista no existe o está vacía
                $metrics = $this->getFallbackDashboardMetrics();
            }
            
            // Agregar métricas adicionales calculadas
            $metrics['timestamp'] = date('Y-m-d H:i:s');
            $metrics['cache_status'] = 'fresh';
            
            // Guardar en cache
            $this->setCache($cacheKey, $metrics);
            
            $this->json($metrics);
            
        } catch (Exception $e) {
            error_log("Error en dashboard stats: " . $e->getMessage());
            $this->json(['error' => 'Error al obtener métricas del dashboard'], 500);
        }
    }
    
    /**
     * 📈 Obtener estadísticas generales de tickets
     * Utiliza la vista v_ticket_stats del Módulo 1
     */
    public function tickets(): void
    {
        try {
            $cacheKey = 'ticket_stats';
            
            // Verificar cache
            if ($this->isCacheValid($cacheKey)) {
                $this->json(self::$cache[$cacheKey]['data']);
                return;
            }
            
            // Consulta optimizada usando vista
            $stmt = $this->db->query("SELECT * FROM v_ticket_stats");
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$stats) {
                throw new Exception("Vista v_ticket_stats no disponible");
            }
            
            // Agregar información adicional
            $stats['ultima_actualizacion'] = date('Y-m-d H:i:s');
            $stats['periodo'] = 'tiempo_real';
            
            // Guardar en cache
            $this->setCache($cacheKey, $stats);
            
            $this->json($stats);
            
        } catch (Exception $e) {
            error_log("Error en ticket stats: " . $e->getMessage());
            $this->json(['error' => 'Error al obtener estadísticas de tickets'], 500);
        }
    }
    
    /**
     * 📊 Obtener tendencias de tickets (últimos 30 días)
     * Utiliza la vista v_ticket_trends del Módulo 1
     */
    public function trends(): void
    {
        try {
            $dias = $_GET['dias'] ?? 30;
            $dias = max(7, min(365, (int)$dias)); // Entre 7 y 365 días
            
            $cacheKey = "ticket_trends_{$dias}";
            
            // Verificar cache
            if ($this->isCacheValid($cacheKey)) {
                $this->json(self::$cache[$cacheKey]['data']);
                return;
            }
            
            // Consulta optimizada usando vista con filtro de días
            $stmt = $this->db->prepare("
                SELECT * FROM v_ticket_trends 
                WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                ORDER BY fecha DESC
                LIMIT ?
            ");
            $stmt->execute([$dias, $dias]);
            $trends = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Agregar metadata
            $response = [
                'data' => $trends,
                'metadata' => [
                    'periodo_dias' => $dias,
                    'total_registros' => count($trends),
                    'fecha_inicio' => $trends ? end($trends)['fecha'] : null,
                    'fecha_fin' => $trends ? $trends[0]['fecha'] : null,
                    'generado_en' => date('Y-m-d H:i:s')
                ]
            ];
            
            // Guardar en cache
            $this->setCache($cacheKey, $response);
            
            $this->json($response);
            
        } catch (Exception $e) {
            error_log("Error en trends: " . $e->getMessage());
            $this->json(['error' => 'Error al obtener tendencias'], 500);
        }
    }
    
    /**
     * 👥 Obtener performance de técnicos
     * Utiliza la vista v_tecnico_performance del Módulo 1
     */
    public function technicians(): void
    {
        try {
            $cacheKey = 'technician_performance';
            
            // Verificar cache
            if ($this->isCacheValid($cacheKey)) {
                $this->json(self::$cache[$cacheKey]['data']);
                return;
            }
            
            // Consulta optimizada usando vista
            $stmt = $this->db->query("SELECT * FROM v_tecnico_performance ORDER BY porcentaje_resolucion DESC");
            $performance = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Agregar rankings y metadata
            $response = [
                'data' => array_map(function($tech, $index) {
                    $tech['ranking'] = $index + 1;
                    $tech['performance_level'] = $this->getPerformanceLevel($tech['porcentaje_resolucion']);
                    return $tech;
                }, $performance, array_keys($performance)),
                'metadata' => [
                    'total_tecnicos' => count($performance),
                    'promedio_resolucion' => count($performance) > 0 ? 
                        round(array_sum(array_column($performance, 'porcentaje_resolucion')) / count($performance), 2) : 0,
                    'generado_en' => date('Y-m-d H:i:s')
                ]
            ];
            
            // Guardar en cache
            $this->setCache($cacheKey, $response);
            
            $this->json($response);
            
        } catch (Exception $e) {
            error_log("Error en technician stats: " . $e->getMessage());
            $this->json(['error' => 'Error al obtener performance de técnicos'], 500);
        }
    }
    
    /**
     * ⏱️ Obtener compliance de SLA
     * Utiliza la vista v_sla_compliance del Módulo 1
     */
    public function sla(): void
    {
        try {
            $cacheKey = 'sla_compliance';
            
            // Verificar cache
            if ($this->isCacheValid($cacheKey)) {
                $this->json(self::$cache[$cacheKey]['data']);
                return;
            }
            
            // Consulta optimizada usando vista
            $stmt = $this->db->query("SELECT * FROM v_sla_compliance ORDER BY FIELD(prioridad, 'urgente', 'alta', 'media', 'baja')");
            $sla = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calcular SLA global
            $totalTickets = array_sum(array_column($sla, 'total_tickets'));
            $totalRespondidosSLA = array_sum(array_column($sla, 'tickets_respondidos_sla'));
            $totalResueltosSLA = array_sum(array_column($sla, 'tickets_resueltos_sla'));
            
            $response = [
                'por_prioridad' => $sla,
                'global' => [
                    'compliance_respuesta' => $totalTickets > 0 ? 
                        round(($totalRespondidosSLA / $totalTickets) * 100, 2) : 0,
                    'compliance_resolucion' => $totalTickets > 0 ? 
                        round(($totalResueltosSLA / $totalTickets) * 100, 2) : 0,
                    'total_tickets_evaluados' => $totalTickets
                ],
                'metadata' => [
                    'generado_en' => date('Y-m-d H:i:s'),
                    'periodo' => 'historico_completo'
                ]
            ];
            
            // Guardar en cache
            $this->setCache($cacheKey, $response);
            
            $this->json($response);
            
        } catch (Exception $e) {
            error_log("Error en SLA stats: " . $e->getMessage());
            $this->json(['error' => 'Error al obtener compliance de SLA'], 500);
        }
    }
    
    /**
     * 🔍 Obtener auditoría resumida
     * Utiliza las vistas v_audit_complete y v_audit_daily_summary del Módulo 1
     */
    public function audit(): void
    {
        try {
            $tipo = $_GET['tipo'] ?? 'resumen'; // 'resumen' o 'detalle'
            $dias = $_GET['dias'] ?? 7;
            $dias = max(1, min(90, (int)$dias));
            
            $cacheKey = "audit_{$tipo}_{$dias}";
            
            // Verificar cache
            if ($this->isCacheValid($cacheKey)) {
                $this->json(self::$cache[$cacheKey]['data']);
                return;
            }
            
            if ($tipo === 'detalle') {
                // Auditoría detallada
                $stmt = $this->db->prepare("
                    SELECT * FROM v_audit_complete 
                    WHERE creado_en >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    ORDER BY creado_en DESC 
                    LIMIT 100
                ");
                $stmt->execute([$dias]);
                $auditData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $response = [
                    'tipo' => 'detalle',
                    'data' => $auditData,
                    'metadata' => [
                        'periodo_dias' => $dias,
                        'total_registros' => count($auditData),
                        'generado_en' => date('Y-m-d H:i:s')
                    ]
                ];
            } else {
                // Resumen diario
                $stmt = $this->db->prepare("
                    SELECT * FROM v_audit_daily_summary 
                    WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                    ORDER BY fecha DESC
                ");
                $stmt->execute([$dias]);
                $auditSummary = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $response = [
                    'tipo' => 'resumen',
                    'data' => $auditSummary,
                    'metadata' => [
                        'periodo_dias' => $dias,
                        'total_dias' => count($auditSummary),
                        'generado_en' => date('Y-m-d H:i:s')
                    ]
                ];
            }
            
            // Guardar en cache
            $this->setCache($cacheKey, $response);
            
            $this->json($response);
            
        } catch (Exception $e) {
            error_log("Error en audit stats: " . $e->getMessage());
            $this->json(['error' => 'Error al obtener datos de auditoría'], 500);
        }
    }
    
    /**
     * 📋 Obtener tickets recientes optimizados
     * Utiliza la vista v_tickets_recientes del Módulo 1
     */
    public function recent(): void
    {
        try {
            $limit = $_GET['limit'] ?? 10;
            $limit = max(5, min(50, (int)$limit));
            
            $cacheKey = "recent_tickets_{$limit}";
            
            // Verificar cache (más corto para tickets recientes)
            if ($this->isCacheValid($cacheKey, 60)) { // 1 minuto de cache
                $this->json(self::$cache[$cacheKey]['data']);
                return;
            }
            
            // Consulta optimizada usando vista
            $stmt = $this->db->prepare("SELECT * FROM v_tickets_recientes LIMIT ?");
            $stmt->execute([$limit]);
            $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response = [
                'data' => $tickets,
                'metadata' => [
                    'limite' => $limit,
                    'total_retornados' => count($tickets),
                    'generado_en' => date('Y-m-d H:i:s'),
                    'cache_timeout' => '1_minuto'
                ]
            ];
            
            // Guardar en cache
            $this->setCache($cacheKey, $response, 60);
            
            $this->json($response);
            
        } catch (Exception $e) {
            error_log("Error en recent tickets: " . $e->getMessage());
            $this->json(['error' => 'Error al obtener tickets recientes'], 500);
        }
    }
    
    /**
     * 📊 Obtener análisis por categorías
     * Utiliza la vista v_categoria_analysis del Módulo 1
     */
    public function categories(): void
    {
        try {
            $cacheKey = 'category_analysis';
            
            // Verificar cache
            if ($this->isCacheValid($cacheKey)) {
                $this->json(self::$cache[$cacheKey]['data']);
                return;
            }
            
            // Consulta optimizada usando vista
            $stmt = $this->db->query("SELECT * FROM v_categoria_analysis");
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Agregar porcentajes y rankings
            $totalTickets = array_sum(array_column($categories, 'total_tickets'));
            
            $response = [
                'data' => array_map(function($cat, $index) use ($totalTickets) {
                    $cat['ranking'] = $index + 1;
                    $cat['porcentaje_del_total'] = $totalTickets > 0 ? 
                        round(($cat['total_tickets'] / $totalTickets) * 100, 2) : 0;
                    $cat['criticidad'] = $this->getCategoryCriticality($cat['porcentaje_urgentes']);
                    return $cat;
                }, $categories, array_keys($categories)),
                'metadata' => [
                    'total_categorias' => count($categories),
                    'total_tickets_evaluados' => $totalTickets,
                    'generado_en' => date('Y-m-d H:i:s')
                ]
            ];
            
            // Guardar en cache
            $this->setCache($cacheKey, $response);
            
            $this->json($response);
            
        } catch (Exception $e) {
            error_log("Error en category analysis: " . $e->getMessage());
            $this->json(['error' => 'Error al obtener análisis de categorías'], 500);
        }
    }
    
    /**
     * 📅 Obtener actividad semanal
     * Utiliza la vista v_weekly_activity del Módulo 1
     */
    public function weekly(): void
    {
        try {
            $semanas = $_GET['semanas'] ?? 12;
            $semanas = max(4, min(52, (int)$semanas));
            
            $cacheKey = "weekly_activity_{$semanas}";
            
            // Verificar cache
            if ($this->isCacheValid($cacheKey)) {
                $this->json(self::$cache[$cacheKey]['data']);
                return;
            }
            
            // Consulta optimizada usando vista
            $stmt = $this->db->prepare("SELECT * FROM v_weekly_activity LIMIT ?");
            $stmt->execute([$semanas]);
            $weekly = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response = [
                'data' => $weekly,
                'metadata' => [
                    'semanas_solicitadas' => $semanas,
                    'semanas_retornadas' => count($weekly),
                    'generado_en' => date('Y-m-d H:i:s')
                ]
            ];
            
            // Guardar en cache
            $this->setCache($cacheKey, $response);
            
            $this->json($response);
            
        } catch (Exception $e) {
            error_log("Error en weekly activity: " . $e->getMessage());
            $this->json(['error' => 'Error al obtener actividad semanal'], 500);
        }
    }
    
    /**
     * 🗑️ Limpiar cache manualmente (solo para admins)
     */
    public function clearCache(): void
    {
        try {
            // Verificar permisos de admin
            $usuario = $_SESSION['usuario'] ?? null;
            if (!$usuario || $usuario['rol_id'] < 3) {
                $this->json(['error' => 'Permisos insuficientes'], 403);
                return;
            }
            
            // Limpiar cache
            self::$cache = [];
            
            $this->json([
                'mensaje' => 'Cache limpiado correctamente',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            error_log("Error limpiando cache: " . $e->getMessage());
            $this->json(['error' => 'Error al limpiar cache'], 500);
        }
    }
    
    // ================================================================================================
    // 🛠️ MÉTODOS PRIVADOS DE UTILIDAD
    // ================================================================================================
    
    /**
     * Verificar si el cache es válido
     */
    private function isCacheValid(string $key, int $timeout = null): bool
    {
        $timeout = $timeout ?? self::$cacheTimeout;
        
        if (!isset(self::$cache[$key])) {
            return false;
        }
        
        $cacheTime = self::$cache[$key]['timestamp'];
        return (time() - $cacheTime) < $timeout;
    }
    
    /**
     * Guardar en cache
     */
    private function setCache(string $key, array $data, int $timeout = null): void
    {
        self::$cache[$key] = [
            'data' => $data,
            'timestamp' => time(),
            'timeout' => $timeout ?? self::$cacheTimeout
        ];
    }
    
    /**
     * Obtener métricas de dashboard como fallback
     */
    private function getFallbackDashboardMetrics(): array
    {
        try {
            $metrics = [];
            
            // Consultas básicas como fallback
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM tickets");
            $metrics['total_tickets'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $stmt = $this->db->query("SELECT COUNT(*) as abiertos FROM tickets WHERE estado = 'abierto'");
            $metrics['tickets_abiertos'] = $stmt->fetch(PDO::FETCH_ASSOC)['abiertos'];
            
            $stmt = $this->db->query("SELECT COUNT(*) as resueltos FROM tickets WHERE estado = 'cerrado' AND DATE(actualizado_en) = CURDATE()");
            $metrics['resueltos_hoy'] = $stmt->fetch(PDO::FETCH_ASSOC)['resueltos'];
            
            $stmt = $this->db->query("SELECT COUNT(*) as usuarios FROM usuarios WHERE activo = 1");
            $metrics['usuarios_activos'] = $stmt->fetch(PDO::FETCH_ASSOC)['usuarios'];
            
            return $metrics;
            
        } catch (Exception $e) {
            error_log("Error en fallback metrics: " . $e->getMessage());
            return [
                'total_tickets' => 0,
                'tickets_abiertos' => 0,
                'resueltos_hoy' => 0,
                'usuarios_activos' => 0,
                'error' => 'Fallback_mode'
            ];
        }
    }
    
    /**
     * Obtener nivel de performance
     */
    private function getPerformanceLevel(float $percentage): string
    {
        if ($percentage >= 90) return 'Excelente';
        if ($percentage >= 75) return 'Bueno';
        if ($percentage >= 60) return 'Regular';
        return 'Necesita Mejora';
    }
    
    /**
     * Obtener criticidad de categoría
     */
    private function getCategoryCriticality(float $urgentPercentage): string
    {
        if ($urgentPercentage >= 30) return 'Crítica';
        if ($urgentPercentage >= 15) return 'Alta';
        if ($urgentPercentage >= 5) return 'Media';
        return 'Baja';
    }
}