<?php
// /controllers/SecurityController.php

namespace Controllers;

use Core\BaseController;
use PDO;
use Controllers\AuditController;

class SecurityController extends BaseController
{
    public function login(array $data): void
    {
        if (!isset($data['correo'], $data['password'])) {
            $this->json(['error' => 'Credenciales incompletas'], 400);
            return;
        }

        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE correo = ? AND activo = 1 LIMIT 1");
        $stmt->execute([trim($data['correo'])]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario || !password_verify($data['password'], $usuario['password'])) {
            $this->json(['error' => 'Credenciales inválidas'], 401);
            return;
        }

        $_SESSION['usuario'] = [
            'id' => $usuario['id'],
            'nombre' => $usuario['nombre'],
            'rol_id' => $usuario['rol_id']
        ];

        AuditController::log($this->db, $usuario['id'], 'login');
        $this->json(['mensaje' => 'Sesión iniciada correctamente']);
    }

    public function logout(): void
    {
        if (isset($_SESSION['usuario']['id'])) {
            AuditController::log($this->db, $_SESSION['usuario']['id'], 'logout');
        }

        session_unset();
        session_destroy();

        $this->json(['mensaje' => 'Sesión finalizada']);
    }

    public function csrf(): void
    {
        require_once __DIR__ . '/../security/csrf.php';
        $token = generarTokenCSRF();
        $this->json(['csrf_token' => $token]);
    }
}
