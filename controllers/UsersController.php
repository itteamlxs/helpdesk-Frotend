<?php
// /controllers/UsersController.php - VERSIÓN CORREGIDA PARA ELIMINACIÓN

namespace Controllers;

use Core\BaseController;
use PDO;
use Exception;
use PDOException;

class UsersController extends BaseController
{
    public function listar(): void
    {
        try {
            $stmt = $this->db->query("
                SELECT u.id, u.nombre, u.correo, u.rol_id, u.activo, 
                       COALESCE(r.nombre, 'Rol Inválido') AS rol
                FROM usuarios u 
                LEFT JOIN roles r ON u.rol_id = r.id
                ORDER BY u.id DESC
            ");
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Convertir activo a boolean para JavaScript
            foreach ($usuarios as &$usuario) {
                $usuario['activo'] = (bool)$usuario['activo'];
            }
            
            error_log("✅ Usuarios listados: " . count($usuarios));
            $this->json($usuarios);
            
        } catch (Exception $e) {
            error_log("❌ Error listando usuarios: " . $e->getMessage());
            $this->json(['error' => 'Error al obtener usuarios'], 500);
        }
    }

    public function obtener(int $id): void
    {
        try {
            $stmt = $this->db->prepare("
                SELECT u.id, u.nombre, u.correo, u.rol_id, u.activo, 
                       COALESCE(r.nombre, 'Rol Inválido') AS rol
                FROM usuarios u 
                LEFT JOIN roles r ON u.rol_id = r.id 
                WHERE u.id = ?
            ");
            $stmt->execute([$id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                $usuario['activo'] = (bool)$usuario['activo'];
                $this->json($usuario);
            } else {
                $this->json(['error' => 'Usuario no encontrado'], 404);
            }
            
        } catch (Exception $e) {
            error_log("❌ Error obteniendo usuario: " . $e->getMessage());
            $this->json(['error' => 'Error al obtener usuario'], 500);
        }
    }

    public function crear(array $data): void
    {
        try {
            if (!isset($data['nombre'], $data['correo'], $data['password'], $data['rol_id'])) {
                $this->json(['error' => 'Datos incompletos'], 400);
                return;
            }

            // Validar rol
            $stmt = $this->db->prepare("SELECT nombre FROM roles WHERE id = ?");
            $stmt->execute([intval($data['rol_id'])]);
            if (!$stmt->fetch()) {
                $this->json(['error' => 'Rol seleccionado no válido'], 400);
                return;
            }

            // Verificar email único
            $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE correo = ?");
            $stmt->execute([trim($data['correo'])]);
            if ($stmt->fetch()) {
                $this->json(['error' => 'El correo ya está registrado'], 400);
                return;
            }

            $hash = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("
                INSERT INTO usuarios (nombre, correo, password, rol_id, activo) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                trim($data['nombre']),
                trim($data['correo']),
                $hash,
                intval($data['rol_id']),
                isset($data['activo']) ? (bool)$data['activo'] : true
            ]);

            if ($result) {
                $this->json(['mensaje' => 'Usuario creado correctamente']);
            } else {
                $this->json(['error' => 'Error al crear usuario'], 500);
            }
            
        } catch (Exception $e) {
            error_log("❌ Error creando usuario: " . $e->getMessage());
            $this->json(['error' => 'Error en la base de datos'], 500);
        }
    }

    public function actualizar(int $id, array $data): void
    {
        try {
            // Verificar que el usuario existe
            $stmt = $this->db->prepare("SELECT nombre FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                $this->json(['error' => 'Usuario no encontrado'], 404);
                return;
            }

            // Validar rol si se proporciona
            if (isset($data['rol_id'])) {
                $stmt = $this->db->prepare("SELECT nombre FROM roles WHERE id = ?");
                $stmt->execute([intval($data['rol_id'])]);
                if (!$stmt->fetch()) {
                    $this->json(['error' => 'Rol seleccionado no válido'], 400);
                    return;
                }
            }

            // Validar email único
            if (isset($data['correo'])) {
                $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE correo = ? AND id != ?");
                $stmt->execute([trim($data['correo']), $id]);
                if ($stmt->fetch()) {
                    $this->json(['error' => 'El correo ya está registrado'], 400);
                    return;
                }
            }

            $campos = [];
            $valores = [];

            if (isset($data['nombre'])) {
                $campos[] = "nombre = ?";
                $valores[] = trim($data['nombre']);
            }

            if (isset($data['correo'])) {
                $campos[] = "correo = ?";
                $valores[] = trim($data['correo']);
            }

            if (isset($data['rol_id'])) {
                $campos[] = "rol_id = ?";
                $valores[] = intval($data['rol_id']);
            }

            if (isset($data['activo'])) {
                $campos[] = "activo = ?";
                $valores[] = (bool)$data['activo'];
            }

            if (isset($data['password']) && !empty($data['password'])) {
                $campos[] = "password = ?";
                $valores[] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            if (empty($campos)) {
                $this->json(['error' => 'No hay datos para actualizar'], 400);
                return;
            }

            $valores[] = $id;
            $sql = "UPDATE usuarios SET " . implode(', ', $campos) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($valores);

            if ($result && $stmt->rowCount() > 0) {
                $this->json(['mensaje' => 'Usuario actualizado correctamente']);
            } else {
                $this->json(['error' => 'No se realizaron cambios'], 400);
            }

        } catch (Exception $e) {
            error_log("❌ Error actualizando usuario: " . $e->getMessage());
            $this->json(['error' => 'Error en la base de datos'], 500);
        }
    }

    public function eliminar(int $id): void
    {
        error_log("🗑️ INICIO eliminación usuario ID: $id");
        
        try {
            // Verificar que existe
            $stmt = $this->db->prepare("SELECT nombre, rol_id FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuario) {
                error_log("❌ Usuario no encontrado");
                $this->json(['error' => 'Usuario no encontrado'], 404);
                return;
            }

            // 🔒 PREVENIR ELIMINAR ÚLTIMO ADMIN
            if ($usuario['rol_id'] == 3) {
                $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM usuarios WHERE rol_id = 3 AND activo = 1");
                $stmt->execute();
                $totalAdmins = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                if ($totalAdmins <= 1) {
                    error_log("❌ Intento de eliminar último admin");
                    $this->json(['error' => 'No se puede eliminar el último administrador del sistema'], 400);
                    return;
                }
            }

            error_log("🔄 Iniciando transacción");
            $this->db->beginTransaction();

            try {
                // 🔧 SOLUCIÓN: LIMPIAR REFERENCIAS EN EL ORDEN CORRECTO
                
                // 1. Eliminar comentarios (tabla hija)
                error_log("🧹 Eliminando comentarios");
                $stmt = $this->db->prepare("DELETE FROM comentarios WHERE usuario_id = ?");
                $stmt->execute([$id]);
                $comentariosEliminados = $stmt->rowCount();
                error_log("🧹 Comentarios eliminados: $comentariosEliminados");

                // 2. Actualizar tickets donde es técnico (SET NULL)
                error_log("🧹 Limpiando tickets como técnico");
                $stmt = $this->db->prepare("UPDATE tickets SET tecnico_id = NULL WHERE tecnico_id = ?");
                $stmt->execute([$id]);
                $ticketsTecnico = $stmt->rowCount();
                error_log("🧹 Tickets actualizados (técnico): $ticketsTecnico");

                // 3. 🔧 MANEJAR TICKETS COMO CLIENTE
                // Opción A: Transferir a usuario "Sistema" (ID especial)
                // Opción B: Marcar como eliminado
                // Opción C: Eliminar tickets (más agresivo)
                
                // Verificar si tiene tickets como cliente
                $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM tickets WHERE cliente_id = ?");
                $stmt->execute([$id]);
                $totalTicketsCliente = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                if ($totalTicketsCliente > 0) {
                    error_log("⚠️ Usuario tiene $totalTicketsCliente tickets como cliente");
                    
                    // OPCIÓN: Crear usuario "Eliminado" si no existe
                    $stmt = $this->db->prepare("
                        INSERT IGNORE INTO usuarios (id, nombre, correo, password, rol_id, activo) 
                        VALUES (0, 'Usuario Eliminado', 'eliminado@sistema.local', '', 1, 0)
                    ");
                    $stmt->execute();
                    
                    // Transferir tickets al usuario "eliminado"
                    $stmt = $this->db->prepare("UPDATE tickets SET cliente_id = 0 WHERE cliente_id = ?");
                    $stmt->execute([$id]);
                    $ticketsTransferidos = $stmt->rowCount();
                    error_log("🔄 Tickets transferidos: $ticketsTransferidos");
                }

                // 4. Limpiar auditoría (opcional - o mantener para histórico)
                error_log("🧹 Limpiando auditoría");
                $stmt = $this->db->prepare("DELETE FROM auditoria WHERE usuario_id = ?");
                $stmt->execute([$id]);
                $auditoriaEliminada = $stmt->rowCount();
                error_log("🧹 Registros de auditoría eliminados: $auditoriaEliminada");

                // 5. FINALMENTE eliminar el usuario
                error_log("🗑️ Eliminando usuario");
                $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
                $result = $stmt->execute([$id]);
                $filasEliminadas = $stmt->rowCount();
                
                error_log("🗑️ Resultado eliminación: result=$result, rowCount=$filasEliminadas");

                if ($result && $filasEliminadas > 0) {
                    $this->db->commit();
                    error_log("✅ Usuario eliminado exitosamente: {$usuario['nombre']}");
                    
                    // Respuesta exitosa
                    $this->json([
                        'mensaje' => 'Usuario eliminado correctamente',
                        'detalles' => [
                            'comentarios_eliminados' => $comentariosEliminados,
                            'tickets_transferidos' => $totalTicketsCliente,
                            'auditoria_limpiada' => $auditoriaEliminada
                        ]
                    ]);
                    
                } else {
                    $this->db->rollback();
                    error_log("❌ No se pudo eliminar - rowCount: $filasEliminadas");
                    $this->json(['error' => 'No se pudo eliminar el usuario'], 500);
                }

            } catch (Exception $innerE) {
                $this->db->rollback();
                error_log("❌ Error en transacción: " . $innerE->getMessage());
                throw $innerE;
            }

        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            error_log("❌ Error PDO eliminando usuario: " . $e->getMessage());
            error_log("❌ PDO Error Info: " . json_encode($e->errorInfo ?? 'No error info'));
            
            // Mensaje más específico según el error
            if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                $this->json(['error' => 'No se puede eliminar: el usuario tiene datos asociados'], 400);
            } else {
                $this->json(['error' => 'Error en la base de datos: ' . $e->getMessage()], 500);
            }
            
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            error_log("❌ Error general eliminando usuario: " . $e->getMessage());
            $this->json(['error' => 'Error inesperado: ' . $e->getMessage()], 500);
        }
    }

    public function roles(): void
    {
        try {
            $stmt = $this->db->query("SELECT id, nombre FROM roles ORDER BY id");
            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->json($roles);
        } catch (Exception $e) {
            error_log("❌ Error obteniendo roles: " . $e->getMessage());
            $this->json(['error' => 'Error al obtener roles'], 500);
        }
    }
}