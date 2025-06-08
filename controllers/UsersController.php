<?php
// /controllers/UsersController.php

namespace Controllers;

use Core\BaseController;
use PDO;

class UsersController extends BaseController
{
    public function listar(): void
    {
        $stmt = $this->db->query("SELECT u.id, u.nombre, u.correo, r.nombre AS rol, u.activo FROM usuarios u JOIN roles r ON u.rol_id = r.id");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->json($usuarios);
    }

    public function obtener(int $id): void
    {
        $stmt = $this->db->prepare("SELECT u.id, u.nombre, u.correo, r.nombre AS rol, u.activo FROM usuarios u JOIN roles r ON u.rol_id = r.id WHERE u.id = ?");
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

        $hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO usuarios (nombre, correo, password, rol_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            trim($data['nombre']),
            trim($data['correo']),
            $hash,
            intval($data['rol_id'])
        ]);

        $this->json(['mensaje' => 'Usuario creado correctamente']);
    }

    public function actualizar(int $id, array $data): void
    {
        $stmt = $this->db->prepare("UPDATE usuarios SET nombre = ?, correo = ?, rol_id = ?, activo = ? WHERE id = ?");
        $stmt->execute([
            trim($data['nombre']),
            trim($data['correo']),
            intval($data['rol_id']),
            isset($data['activo']) ? (bool)$data['activo'] : true,
            $id
        ]);

        $this->json(['mensaje' => 'Usuario actualizado']);
    }

    public function eliminar(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $this->json(['mensaje' => 'Usuario eliminado']);
    }
}
