<?php
// /tests/test_insert_tickets_sla_comments.php

require_once __DIR__ . '/../config/env_loader.php';
require_once __DIR__ . '/../core/Database.php';

use Core\Database;

header('Content-Type: application/json');

$db = Database::obtenerConexion();

try {
    // 1. Insertar configuración SLA
    $sla = [
        ['baja', 1440, 2880],
        ['media', 720, 1440],
        ['alta', 240, 720],
        ['urgente', 60, 240]
    ];

    foreach ($sla as $s) {
        $stmt = $db->prepare("INSERT INTO sla (prioridad, tiempo_respuesta, tiempo_resolucion)
            VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE tiempo_respuesta = VALUES(tiempo_respuesta), tiempo_resolucion = VALUES(tiempo_resolucion)");
        $stmt->execute($s);
    }

    // 2. Insertar ticket de prueba
    $stmt = $db->prepare("INSERT INTO tickets (titulo, descripcion, categoria, prioridad, cliente_id)
        VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        'Internet lento',
        'El internet está funcionando muy lento en la oficina 3.',
        'Redes',
        'alta',
        1 // cliente_id = usuario prueba
    ]);

    $ticketId = $db->lastInsertId();

    // 3. Insertar comentario asociado
    $stmt = $db->prepare("INSERT INTO comentarios (ticket_id, usuario_id, contenido, interno)
        VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $ticketId,
        1, // usuario prueba
        'Se ha reportado a soporte técnico, en espera de diagnóstico.',
        0 // interno = false
    ]);

    echo json_encode([
        'ok' => true,
        'mensaje' => 'SLA, ticket y comentario insertados con éxito',
        'ticket_id' => $ticketId
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
