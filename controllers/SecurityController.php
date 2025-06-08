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

    public function testEmail(array $data): void
    {
        if (!isset($data['email'])) {
            $this->json(['error' => 'Email de destino requerido'], 400);
            return;
        }

        $emailDestino = trim($data['email']);
        
        if (!filter_var($emailDestino, FILTER_VALIDATE_EMAIL)) {
            $this->json(['error' => 'Email inválido'], 400);
            return;
        }

        // Usar el Mailer existente
        require_once __DIR__ . '/../core/Mailer.php';
        
        $asunto = 'Prueba de Configuración SMTP - Helpdesk System';
        $mensaje = '
            <h2>✅ Prueba de Email Exitosa</h2>
            <p>Este es un email de prueba enviado desde el sistema Helpdesk.</p>
            <p><strong>Fecha:</strong> ' . date('Y-m-d H:i:s') . '</p>
            <p><strong>Servidor:</strong> ' . ($_SERVER['SERVER_NAME'] ?? 'localhost') . '</p>
            <hr>
            <p><small>Si recibes este mensaje, la configuración SMTP está funcionando correctamente.</small></p>
        ';
        
        $enviado = \Core\Mailer::enviar($emailDestino, $asunto, $mensaje);
        
        if ($enviado) {
            $this->json([
                'success' => true,
                'message' => 'Email de prueba enviado correctamente',
                'destinatario' => $emailDestino
            ]);
        } else {
            $this->json([
                'error' => 'No se pudo enviar el email. Verifica la configuración SMTP.',
                'destinatario' => $emailDestino
            ], 500);
        }
    }
}
