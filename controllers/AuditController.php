<?php
// /controllers/AuditController.php

namespace Controllers;

use Core\BaseController;
use PDO;

class AuditController extends BaseController
{
    public function list(): void
    {
        $stmt = $this->db->query("SELECT a.id, a.accion, a.ip, a.creado_en, u.nombre AS usuario FROM auditoria a LEFT JOIN usuarios u ON a.usuario_id = u.id ORDER BY a.creado_en DESC");
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->json($resultados);
    }

    public function get(int $id): void
    {
        $stmt = $this->db->prepare("SELECT a.*, u.nombre AS usuario FROM auditoria a LEFT JOIN usuarios u ON a.usuario_id = u.id WHERE a.id = ?");
        $stmt->execute([$id]);
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($registro) {
            $this->json($registro);
        } else {
            $this->json(['error' => 'Registro no encontrado'], 404);
        }
    }

    public static function log(PDO $db, ?int $usuarioId, string $accion, string $ip = null): void
    {
        $stmt = $db->prepare("INSERT INTO auditoria (usuario_id, accion, ip) VALUES (?, ?, ?)");
        $stmt->execute([$usuarioId, $accion, $ip ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
    }
}
