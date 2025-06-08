<?php
// /controllers/UsersController.php - FINAL FIX

namespace Controllers;

use Core\BaseController;
use PDO;

class UsersController extends BaseController
{
    public function listar(): void
    {
        // ✅ JOIN CORRECTO con roles existentes
        $stmt = $this->db->query("
            SELECT u.id, u.nombre, u.correo, u.rol_id, u.activo, 
                   COALESCE(r.nombre, 'Rol Inválido') AS rol
            FROM usuarios u 
            LEFT JOIN roles r ON u.rol_id = r.id
            ORDER BY u.id DESC
        ");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Usuarios con roles: " . json_encode($usuarios));
        
        $this->json($usuarios);
    }

    public function obtener(int $id): void
    {
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
            $this->json($usuario);
        } else {
            $this->json(['error' => 'Usuario no encontrado'], 404);
        }
    }

    public function crear(array $data): void
    {
        if (!isset($data['nombre'], $data['correo'], $data['password'], $data['rol_id'])) {
            $this->json(['error' => 'Datos incompletos'], 400);
            return;
        }

        // ✅ VALIDAR QUE EL ROL EXISTA EN LA TABLA ROLES
        $stmt = $this->db->prepare("SELECT nombre FROM roles WHERE id = ?");
        $stmt->execute([intval($data['rol_id'])]);
        $rol = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$rol) {
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

        try {
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
                error_log("Usuario creado: {$data['nombre']} con rol: {$rol['nombre']}");
                $this->json(['mensaje' => 'Usuario creado correctamente']);
            } else {
                $this->json(['error' => 'Error al crear usuario'], 500);
            }
            
        } catch (PDOException $e) {
            error_log("Error creando usuario: " . $e->getMessage());
            $this->json(['error' => 'Error en la base de datos'], 500);
        }
    }

    public function actualizar(int $id, array $data): void
    {
        // Verificar que el usuario existe
        $stmt = $this->db->prepare("SELECT nombre FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            $this->json(['error' => 'Usuario no encontrado'], 404);
            return;
        }

        // ✅ VALIDAR ROL SI SE PROPORCIONA
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

        try {
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

        } catch (PDOException $e) {
            error_log("Error actualizando usuario: " . $e->getMessage());
            $this->json(['error' => 'Error en la base de datos'], 500);
        }
    }

    public function eliminar(int $id): void
    {
        error_log("🗑️ Eliminando usuario ID: $id");
        
        try {
            // Verificar que existe
            $stmt = $this->db->prepare("SELECT nombre, rol_id FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuario) {
                $this->json(['error' => 'Usuario no encontrado'], 404);
                return;
            }

            // ✅ PREVENIR ELIMINAR ÚLTIMO ADMIN
            if ($usuario['rol_id'] == 3) { // Es admin
                $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM usuarios WHERE rol_id = 3 AND activo = 1");
                $stmt->execute();
                $totalAdmins = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                if ($totalAdmins <= 1) {
                    $this->json(['error' => 'No se puede eliminar el último administrador del sistema'], 400);
                    return;
                }
            }

            // ✅ ELIMINACIÓN CON TRANSACCIÓN
            $this->db->beginTransaction();

            // Limpiar referencias en otras tablas
            $stmt = $this->db->prepare("DELETE FROM comentarios WHERE usuario_id = ?");
            $stmt->execute([$id]);

            $stmt = $this->db->prepare("UPDATE tickets SET tecnico_id = NULL WHERE tecnico_id = ?");
            $stmt->execute([$id]);

            $stmt = $this->db->prepare("UPDATE tickets SET cliente_id = NULL WHERE cliente_id = ?");
            $stmt->execute([$id]);

            // Eliminar usuario
            $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
            $result = $stmt->execute([$id]);

            if ($result && $stmt->rowCount() > 0) {
                $this->db->commit();
                error_log("✅ Usuario eliminado: {$usuario['nombre']}");
                
                // ✅ RESPUESTA JSON LIMPIA
                $this->json(['mensaje' => 'Usuario eliminado correctamente']);
            } else {
                $this->db->rollback();
                $this->json(['error' => 'No se pudo eliminar el usuario'], 500);
            }

        } catch (PDOException $e) {
            $this->db->rollback();
            error_log("❌ Error PDO eliminando usuario: " . $e->getMessage());
            $this->json(['error' => 'Error en la base de datos'], 500);
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("❌ Error general eliminando usuario: " . $e->getMessage());
            $this->json(['error' => 'Error inesperado'], 500);
        }
    }

    // ✅ ENDPOINT PARA OBTENER ROLES DISPONIBLES
    public function roles(): void
    {
        $stmt = $this->db->query("SELECT id, nombre FROM roles ORDER BY id");
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->json($roles);
    }
}