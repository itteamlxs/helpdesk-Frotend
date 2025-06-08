<?php
// /tests/general_test.php

require_once __DIR__ . '/../config/env_loader.php';
require_once __DIR__ . '/../core/Database.php';

use Core\Database;

header('Content-Type: application/json');

try {
    $db = Database::obtenerConexion();

    $checkUsuarios = $db->query("SELECT COUNT(*) AS total FROM usuarios")->fetch()['total'];
    $checkTickets = $db->query("SELECT COUNT(*) AS total FROM tickets")->fetch()['total'];
    $checkComentarios = $db->query("SELECT COUNT(*) AS total FROM comentarios")->fetch()['total'];
    $checkSLA = $db->query("SELECT COUNT(*) AS total FROM sla")->fetch()['total'];
    $checkConfig = $db->query("SELECT COUNT(*) AS total FROM configuracion")->fetch()['total'];

    echo json_encode([
        'ok' => true,
        'verificaciones' => [
            'usuarios' => $checkUsuarios,
            'tickets' => $checkTickets,
            'comentarios' => $checkComentarios,
            'sla' => $checkSLA,
            'configuracion' => $checkConfig,
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ]);
}
