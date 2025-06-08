<?php
// /controllers/SLAController.php

namespace Controllers;

use Core\BaseController;
use PDO;

class SLAController extends BaseController
{
    public function list(): void
    {
        $stmt = $this->db->query("SELECT * FROM sla ORDER BY FIELD(prioridad, 'urgente', 'alta', 'media', 'baja')");
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->json($datos);
    }

    public function update(array $data): void
    {
        if (!isset($data['valores']) || !is_array($data['valores'])) {
            $this->json(['error' => 'Formato inválido'], 400);
            return;
        }

        foreach ($data['valores'] as $item) {
            if (!isset($item['prioridad'], $item['tiempo_respuesta'], $item['tiempo_resolucion'])) {
                continue;
            }

            $stmt = $this->db->prepare("INSERT INTO sla (prioridad, tiempo_respuesta, tiempo_resolucion)
                VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE tiempo_respuesta = VALUES(tiempo_respuesta), tiempo_resolucion = VALUES(tiempo_resolucion)");

            $stmt->execute([
                $item['prioridad'],
                intval($item['tiempo_respuesta']),
                intval($item['tiempo_resolucion'])
            ]);
        }

        $this->json(['mensaje' => 'SLA actualizado']);
    }
}
