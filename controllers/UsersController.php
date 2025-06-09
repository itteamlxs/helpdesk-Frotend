<?php
// /controllers/UsersController.php - VERSIÓN CORREGIDA COMPLETA

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
        
        // 🔧 DESHABILITAR ERRORES DE PHP PARA EVITAR CONTAMINAR JSON
        $errorReporting = error_reporting(0);
        
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
                // 🔧 PASO 1: CREAR/VERIFICAR USUARIO "ELIMINADO"
                $usuarioEliminadoId = $this->crearUsuarioEliminado();
                error_log("👤 Usuario eliminado ID: $usuarioEliminadoId");

                // 🔧 PASO 2: TRANSFERIR TICKETS COMO CLIENTE
                $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM tickets WHERE cliente_id = ?");
                $stmt->execute([$id]);
                $totalTicketsCliente = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                if ($totalTicketsCliente > 0) {
                    error_log("🔄 Transfiriendo $totalTicketsCliente tickets como cliente");
                    $stmt = $this->db->prepare("UPDATE tickets SET cliente_id = ? WHERE cliente_id = ?");
                    $stmt->execute([$usuarioEliminadoId, $id]);
                    error_log("✅ Tickets transferidos: " . $stmt->rowCount());
                }

                // 🔧 PASO 3: LIMPIAR TICKETS COMO TÉCNICO (SET NULL)
                $stmt = $this->db->prepare("UPDATE tickets SET tecnico_id = NULL WHERE tecnico_id = ?");
                $stmt->execute([$id]);
                $ticketsTecnico = $stmt->rowCount();
                error_log("🧹 Tickets limpiados como técnico: $ticketsTecnico");

                // 🔧 PASO 4: ELIMINAR COMENTARIOS
                $stmt = $this->db->prepare("DELETE FROM comentarios WHERE usuario_id = ?");
                $stmt->execute([$id]);
                $comentariosEliminados = $stmt->rowCount();
                error_log("🧹 Comentarios eliminados: $comentariosEliminados");

                // 🔧 PASO 5: LIMPIAR AUDITORÍA
                $stmt = $this->db->prepare("DELETE FROM auditoria WHERE usuario_id = ?");
                $stmt->execute([$id]);
                $auditoriaEliminada = $stmt->rowCount();
                error_log("🧹 Auditoría eliminada: $auditoriaEliminada");

                // 🔧 PASO 6: ELIMINAR USUARIO
                $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
                $result = $stmt->execute([$id]);
                $filasEliminadas = $stmt->rowCount();
                
                error_log("🗑️ Usuario eliminado - filas: $filasEliminadas");

                if ($result && $filasEliminadas > 0) {
                    $this->db->commit();
                    error_log("✅ Usuario eliminado exitosamente: {$usuario['nombre']}");
                    
                    // 🔧 RESTAURAR ERROR REPORTING ANTES DEL JSON
                    error_reporting($errorReporting);
                    
                    $this->json([
                        'success' => true,
                        'mensaje' => 'Usuario eliminado correctamente',
                        'detalles' => [
                            'tickets_transferidos' => $totalTicketsCliente,
                            'tickets_liberados' => $ticketsTecnico,
                            'comentarios_eliminados' => $comentariosEliminados,
                            'auditoria_limpiada' => $auditoriaEliminada
                        ]
                    ]);
                    
                } else {
                    $this->db->rollback();
                    error_log("❌ No se pudo eliminar - rowCount: $filasEliminadas");
                    error_reporting($errorReporting);
                    $this->json(['error' => 'No se pudo eliminar el usuario'], 500);
                }

            } catch (Exception $innerE) {
                $this->db->rollback();
                error_log("❌ Error en transacción: " . $innerE->getMessage());
                error_reporting($errorReporting);
                throw $innerE;
            }

        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            error_log("❌ Error PDO eliminando usuario: " . $e->getMessage());
            error_reporting($errorReporting);
            $this->json(['error' => 'Error en la base de datos: ' . $e->getMessage()], 500);
            
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            error_log("❌ Error general eliminando usuario: " . $e->getMessage());
            error_reporting($errorReporting);
            $this->json(['error' => 'Error inesperado: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 🛠️ MÉTODO AUXILIAR: Crear usuario "eliminado" para transferir tickets
     */
    private function crearUsuarioEliminado(): int
    {
        // Verificar si ya existe un usuario "eliminado"
        $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE correo = 'usuario-eliminado@sistema.local' LIMIT 1");
        $stmt->execute();
        $usuarioExistente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuarioExistente) {
            return (int)$usuarioExistente['id'];
        }
        
        // Crear nuevo usuario "eliminado"
        $stmt = $this->db->prepare("
            INSERT INTO usuarios (nombre, correo, password, rol_id, activo) 
            VALUES ('Usuario Eliminado', 'usuario-eliminado@sistema.local', '', 1, 0)
        ");
        $stmt->execute();
        
        $nuevoId = (int)$this->db->lastInsertId();
        error_log("🆕 Usuario eliminado creado con ID: $nuevoId");
        
        return $nuevoId;
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