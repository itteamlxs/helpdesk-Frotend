<?php
// /controllers/NotificationsController.php

namespace Controllers;

use Core\BaseController;
use PDO;
use Exception;
use PDOException;

class NotificationsController extends BaseController
{
    /**
     * 🔔 Obtener notificaciones del usuario actual
     */
    public function getUserNotifications(): void
    {
        $usuarioActual = $_SESSION['usuario'] ?? null;
        
        if (!$usuarioActual) {
            $this->json(['error' => 'Usuario no autenticado'], 401);
            return;
        }
        
        $usuarioId = $usuarioActual['id'];
        $limit = min(50, (int)($_GET['limit'] ?? 20));
        $soloNoLeidas = isset($_GET['solo_no_leidas']) && $_GET['solo_no_leidas'] === 'true';
        
        try {
            // Usar vista optimizada
            $sql = "
                SELECT id, titulo, mensaje, tipo, icono, leida, importante, 
                       ticket_id, url, creado_en, tiempo_transcurrido,
                       ticket_titulo, ticket_estado, ticket_prioridad
                FROM v_notificaciones_complete 
                WHERE usuario_id = ?
            ";
            
            $params = [$usuarioId];
            
            if ($soloNoLeidas) {
                $sql .= " AND leida = FALSE";
            }
            
            $sql .= " ORDER BY importante DESC, creado_en DESC LIMIT ?";
            $params[] = $limit;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Convertir campos booleanos
            foreach ($notificaciones as &$notif) {
                $notif['leida'] = (bool)$notif['leida'];
                $notif['importante'] = (bool)$notif['importante'];
            }
            
            $this->json([
                'notificaciones' => $notificaciones,
                'total' => count($notificaciones),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            error_log("Error obteniendo notificaciones: " . $e->getMessage());
            $this->json(['error' => 'Error al obtener notificaciones'], 500);
        }
    }
    
    /**
     * 📊 Obtener resumen de notificaciones (para la campanita)
     */
    public function getNotificationsSummary(): void
    {
        $usuarioActual = $_SESSION['usuario'] ?? null;
        
        if (!$usuarioActual) {
            $this->json(['error' => 'Usuario no autenticado'], 401);
            return;
        }
        
        $usuarioId = $usuarioActual['id'];
        
        try {
            // Obtener resumen desde vista optimizada
            $stmt = $this->db->prepare("
                SELECT * FROM v_notificaciones_resumen WHERE usuario_id = ?
            ");
            $stmt->execute([$usuarioId]);
            $resumen = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$resumen) {
                $resumen = [
                    'total_notificaciones' => 0,
                    'no_leidas' => 0,
                    'importantes' => 0,
                    'tickets' => 0,
                    'sla' => 0,
                    'sistema' => 0,
                    'ultima_notificacion' => null
                ];
            }
            
            // Obtener las 5 más recientes no leídas para preview
            $stmt = $this->db->prepare("
                SELECT id, titulo, mensaje, tipo, icono, importante, 
                       tiempo_transcurrido, ticket_id, url
                FROM v_notificaciones_complete 
                WHERE usuario_id = ? AND leida = FALSE
                ORDER BY importante DESC, creado_en DESC 
                LIMIT 5
            ");
            $stmt->execute([$usuarioId]);
            $preview = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($preview as &$notif) {
                $notif['importante'] = (bool)$notif['importante'];
            }
            
            $this->json([
                'resumen' => $resumen,
                'preview' => $preview,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            error_log("Error obteniendo resumen de notificaciones: " . $e->getMessage());
            $this->json(['error' => 'Error al obtener resumen'], 500);
        }
    }
    
    /**
     * ✅ Marcar notificación como leída
     */
    public function markAsRead(int $notificationId): void
    {
        $usuarioActual = $_SESSION['usuario'] ?? null;
        
        if (!$usuarioActual) {
            $this->json(['error' => 'Usuario no autenticado'], 401);
            return;
        }
        
        $usuarioId = $usuarioActual['id'];
        
        try {
            $stmt = $this->db->prepare("
                UPDATE notificaciones 
                SET leida = TRUE, leida_en = NOW() 
                WHERE id = ? AND usuario_id = ?
            ");
            $stmt->execute([$notificationId, $usuarioId]);
            
            if ($stmt->rowCount() > 0) {
                $this->json(['mensaje' => 'Notificación marcada como leída']);
            } else {
                $this->json(['error' => 'Notificación no encontrada'], 404);
            }
            
        } catch (Exception $e) {
            error_log("Error marcando notificación como leída: " . $e->getMessage());
            $this->json(['error' => 'Error al marcar notificación'], 500);
        }
    }
    
    /**
     * ✅ Marcar todas las notificaciones como leídas
     */
    public function markAllAsRead(): void
    {
        $usuarioActual = $_SESSION['usuario'] ?? null;
        
        if (!$usuarioActual) {
            $this->json(['error' => 'Usuario no autenticado'], 401);
            return;
        }
        
        $usuarioId = $usuarioActual['id'];
        
        try {
            $stmt = $this->db->prepare("CALL MarcarTodasLeidas(?)");
            $stmt->execute([$usuarioId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->json([
                'mensaje' => 'Todas las notificaciones marcadas como leídas',
                'notificaciones_marcadas' => $result['notificaciones_marcadas'] ?? 0
            ]);
            
        } catch (Exception $e) {
            error_log("Error marcando todas como leídas: " . $e->getMessage());
            $this->json(['error' => 'Error al marcar notificaciones'], 500);
        }
    }
    
    /**
     * 🗑️ Eliminar notificación
     */
    public function deleteNotification(int $notificationId): void
    {
        $usuarioActual = $_SESSION['usuario'] ?? null;
        
        if (!$usuarioActual) {
            $this->json(['error' => 'Usuario no autenticado'], 401);
            return;
        }
        
        $usuarioId = $usuarioActual['id'];
        
        try {
            $stmt = $this->db->prepare("
                DELETE FROM notificaciones 
                WHERE id = ? AND usuario_id = ?
            ");
            $stmt->execute([$notificationId, $usuarioId]);
            
            if ($stmt->rowCount() > 0) {
                $this->json(['mensaje' => 'Notificación eliminada']);
            } else {
                $this->json(['error' => 'Notificación no encontrada'], 404);
            }
            
        } catch (Exception $e) {
            error_log("Error eliminando notificación: " . $e->getMessage());
            $this->json(['error' => 'Error al eliminar notificación'], 500);
        }
    }
    
    /**
     * 🆕 Crear nueva notificación
     */
    public function createNotification(array $data): void
    {
        $usuarioActual = $_SESSION['usuario'] ?? null;
        
        if (!$usuarioActual || $usuarioActual['rol_id'] < 2) {
            $this->json(['error' => 'Sin permisos para crear notificaciones'], 403);
            return;
        }
        
        if (!isset($data['usuario_id'], $data['titulo'], $data['mensaje'])) {
            $this->json(['error' => 'Datos incompletos'], 400);
            return;
        }
        
        try {
            $expiraEn = null;
            if (isset($data['expira_minutos']) && $data['expira_minutos'] > 0) {
                $expiraEn = date('Y-m-d H:i:s', strtotime("+{$data['expira_minutos']} minutes"));
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO notificaciones (
                    usuario_id, titulo, mensaje, tipo, icono, importante, 
                    ticket_id, accion, url, expira_en
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['usuario_id'],
                $data['titulo'],
                $data['mensaje'],
                $data['tipo'] ?? 'info',
                $data['icono'] ?? 'fas fa-bell',
                isset($data['importante']) ? (bool)$data['importante'] : false,
                $data['ticket_id'] ?? null,
                $data['accion'] ?? null,
                $data['url'] ?? null,
                $expiraEn
            ]);
            
            $notificationId = $this->db->lastInsertId();
            
            $this->json([
                'mensaje' => 'Notificación creada correctamente',
                'notificacion_id' => $notificationId
            ]);
            
        } catch (Exception $e) {
            error_log("Error creando notificación: " . $e->getMessage());
            $this->json(['error' => 'Error al crear notificación'], 500);
        }
    }
    
    /**
     * 📋 Crear notificación desde plantilla
     */
    public function createFromTemplate(array $data): void
    {
        $usuarioActual = $_SESSION['usuario'] ?? null;
        
        if (!$usuarioActual || $usuarioActual['rol_id'] < 2) {
            $this->json(['error' => 'Sin permisos para crear notificaciones'], 403);
            return;
        }
        
        if (!isset($data['usuario_id'], $data['plantilla'])) {
            $this->json(['error' => 'usuario_id y plantilla son requeridos'], 400);
            return;
        }
        
        try {
            $stmt = $this->db->prepare("
                CALL CrearNotificacionDesdePlantilla(?, ?, ?, ?, ?)
            ");
            
            $variables = isset($data['variables']) ? json_encode($data['variables']) : null;
            
            $stmt->execute([
                $data['usuario_id'],
                $data['plantilla'],
                $variables,
                $data['ticket_id'] ?? null,
                $data['url'] ?? null
            ]);
            
            $this->json(['mensaje' => 'Notificación creada desde plantilla']);
            
        } catch (Exception $e) {
            error_log("Error creando notificación desde plantilla: " . $e->getMessage());
            $this->json(['error' => 'Error al crear notificación: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * ⚙️ Obtener configuración de notificaciones del usuario
     */
    public function getConfig(): void
    {
        $usuarioActual = $_SESSION['usuario'] ?? null;
        
        if (!$usuarioActual) {
            $this->json(['error' => 'Usuario no autenticado'], 401);
            return;
        }
        
        $usuarioId = $usuarioActual['id'];
        
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM notificaciones_config WHERE usuario_id = ?
            ");
            $stmt->execute([$usuarioId]);
            $config = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$config) {
                // Crear configuración por defecto
                $stmt = $this->db->prepare("
                    INSERT INTO notificaciones_config (usuario_id) VALUES (?)
                ");
                $stmt->execute([$usuarioId]);
                
                // Obtener configuración recién creada
                $stmt = $this->db->prepare("
                    SELECT * FROM notificaciones_config WHERE usuario_id = ?
                ");
                $stmt->execute([$usuarioId]);
                $config = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            // Convertir campos booleanos
            $booleanFields = [
                'email_tickets', 'email_sla', 'email_sistema',
                'navegador_tickets', 'navegador_sla', 'navegador_sistema',
                'solo_horario_laboral', 'resumen_diario', 'resumen_semanal'
            ];
            
            foreach ($booleanFields as $field) {
                if (isset($config[$field])) {
                    $config[$field] = (bool)$config[$field];
                }
            }
            
            $this->json($config);
            
        } catch (Exception $e) {
            error_log("Error obteniendo configuración: " . $e->getMessage());
            $this->json(['error' => 'Error al obtener configuración'], 500);
        }
    }
    
    /**
     * ⚙️ Actualizar configuración de notificaciones
     */
    public function updateConfig(array $data): void
    {
        $usuarioActual = $_SESSION['usuario'] ?? null;
        
        if (!$usuarioActual) {
            $this->json(['error' => 'Usuario no autenticado'], 401);
            return;
        }
        
        $usuarioId = $usuarioActual['id'];
        
        try {
            $campos = [];
            $valores = [];
            
            $allowedFields = [
                'email_tickets', 'email_sla', 'email_sistema',
                'navegador_tickets', 'navegador_sla', 'navegador_sistema',
                'no_molestar_inicio', 'no_molestar_fin', 'solo_horario_laboral',
                'resumen_diario', 'resumen_semanal'
            ];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $campos[] = "$field = ?";
                    $valores[] = $data[$field];
                }
            }
            
            if (empty($campos)) {
                $this->json(['error' => 'No hay datos para actualizar'], 400);
                return;
            }
            
            $valores[] = $usuarioId;
            
            $stmt = $this->db->prepare("
                UPDATE notificaciones_config 
                SET " . implode(', ', $campos) . " 
                WHERE usuario_id = ?
            ");
            $stmt->execute($valores);
            
            if ($stmt->rowCount() > 0) {
                $this->json(['mensaje' => 'Configuración actualizada correctamente']);
            } else {
                $this->json(['error' => 'Sin cambios realizados'], 400);
            }
            
        } catch (Exception $e) {
            error_log("Error actualizando configuración: " . $e->getMessage());
            $this->json(['error' => 'Error al actualizar configuración'], 500);
        }
    }
    
    /**
     * 🧹 Limpiar notificaciones expiradas (método administrativo)
     */
    public function cleanExpired(): void
    {
        $usuarioActual = $_SESSION['usuario'] ?? null;
        
        if (!$usuarioActual || $usuarioActual['rol_id'] < 3) {
            $this->json(['error' => 'Sin permisos administrativos'], 403);
            return;
        }
        
        try {
            $stmt = $this->db->prepare("CALL LimpiarNotificacionesExpiradas()");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->json([
                'mensaje' => 'Limpieza completada',
                'notificaciones_eliminadas' => $result['notificaciones_eliminadas'] ?? 0
            ]);
            
        } catch (Exception $e) {
            error_log("Error limpiando notificaciones: " . $e->getMessage());
            $this->json(['error' => 'Error al limpiar notificaciones'], 500);
        }
    }
    
    /**
     * 📊 Estadísticas de notificaciones (para admins)
     */
    public function getStats(): void
    {
        $usuarioActual = $_SESSION['usuario'] ?? null;
        
        if (!$usuarioActual || $usuarioActual['rol_id'] < 3) {
            $this->json(['error' => 'Sin permisos administrativos'], 403);
            return;
        }
        
        try {
            // Estadísticas generales
            $stmt = $this->db->query("
                SELECT 
                    COUNT(*) as total_notificaciones,
                    COUNT(CASE WHEN leida = FALSE THEN 1 END) as no_leidas_global,
                    COUNT(CASE WHEN importante = TRUE THEN 1 END) as importantes_global,
                    COUNT(CASE WHEN expira_en IS NOT NULL AND expira_en < NOW() THEN 1 END) as expiradas,
                    COUNT(DISTINCT usuario_id) as usuarios_con_notificaciones,
                    AVG(CASE WHEN leida = TRUE THEN TIMESTAMPDIFF(MINUTE, creado_en, leida_en) END) as tiempo_promedio_lectura
                FROM notificaciones
            ");
            $generalStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Estadísticas por tipo
            $stmt = $this->db->query("
                SELECT tipo, COUNT(*) as cantidad, 
                       COUNT(CASE WHEN leida = FALSE THEN 1 END) as no_leidas
                FROM notificaciones 
                GROUP BY tipo 
                ORDER BY cantidad DESC
            ");
            $tipoStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Top usuarios con más notificaciones
            $stmt = $this->db->query("
                SELECT u.nombre, u.correo, COUNT(n.id) as total_notificaciones,
                       COUNT(CASE WHEN n.leida = FALSE THEN 1 END) as no_leidas
                FROM usuarios u
                LEFT JOIN notificaciones n ON u.id = n.usuario_id
                GROUP BY u.id, u.nombre, u.correo
                HAVING total_notificaciones > 0
                ORDER BY total_notificaciones DESC
                LIMIT 10
            ");
            $topUsuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->json([
                'general' => $generalStats,
                'por_tipo' => $tipoStats,
                'top_usuarios' => $topUsuarios,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas: " . $e->getMessage());
            $this->json(['error' => 'Error al obtener estadísticas'], 500);
        }
    }
    
    /**
     * 🔔 Enviar notificación a múltiples usuarios
     */
    public function broadcast(array $data): void
    {
        $usuarioActual = $_SESSION['usuario'] ?? null;
        
        if (!$usuarioActual || $usuarioActual['rol_id'] < 3) {
            $this->json(['error' => 'Sin permisos para broadcast'], 403);
            return;
        }
        
        if (!isset($data['usuarios'], $data['titulo'], $data['mensaje'])) {
            $this->json(['error' => 'usuarios, titulo y mensaje son requeridos'], 400);
            return;
        }
        
        $usuarios = is_array($data['usuarios']) ? $data['usuarios'] : [$data['usuarios']];
        $enviadas = 0;
        $errores = 0;
        
        try {
            $this->db->beginTransaction();
            
            $stmt = $this->db->prepare("
                INSERT INTO notificaciones (
                    usuario_id, titulo, mensaje, tipo, icono, importante, url
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($usuarios as $usuarioId) {
                try {
                    $stmt->execute([
                        $usuarioId,
                        $data['titulo'],
                        $data['mensaje'],
                        $data['tipo'] ?? 'sistema',
                        $data['icono'] ?? 'fas fa-broadcast-tower',
                        isset($data['importante']) ? (bool)$data['importante'] : false,
                        $data['url'] ?? null
                    ]);
                    $enviadas++;
                } catch (Exception $e) {
                    $errores++;
                    error_log("Error enviando a usuario $usuarioId: " . $e->getMessage());
                }
            }
            
            $this->db->commit();
            
            $this->json([
                'mensaje' => 'Broadcast completado',
                'enviadas' => $enviadas,
                'errores' => $errores,
                'total_usuarios' => count($usuarios)
            ]);
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error en broadcast: " . $e->getMessage());
            $this->json(['error' => 'Error al enviar broadcast'], 500);
        }
    }
}