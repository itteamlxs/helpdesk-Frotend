<?php
// /controllers/SettingsController.php

namespace Controllers;

use Core\BaseController;
use PDO;

class SettingsController extends BaseController
{
    public function get(): void
    {
        $stmt = $this->db->query("SELECT * FROM configuracion ORDER BY id DESC LIMIT 1");
        $config = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($config) {
            $this->json($config);
        } else {
            $this->json(['error' => 'Configuración no encontrada'], 404);
        }
    }

    public function update(array $data): void
    {
        $stmt = $this->db->prepare("INSERT INTO configuracion (
            nombre_empresa, zona_horaria, tiempo_max_respuesta,
            tiempo_cierre_tras_respuesta, smtp_host, smtp_user,
            smtp_pass, notificaciones_email
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            trim($data['nombre_empresa'] ?? ''),
            trim($data['zona_horaria'] ?? 'UTC'),
            intval($data['tiempo_max_respuesta'] ?? 60),
            intval($data['tiempo_cierre_tras_respuesta'] ?? 1440),
            trim($data['smtp_host'] ?? ''),
            trim($data['smtp_user'] ?? ''),
            trim($data['smtp_pass'] ?? ''),
            isset($data['notificaciones_email']) ? (bool)$data['notificaciones_email'] : false
        ]);

        $this->json(['mensaje' => 'Configuración guardada']);
    }
}
