<?php
// /tests/test_insert_config.php

require_once __DIR__ . '/../config/env_loader.php';
require_once __DIR__ . '/../core/Database.php';

use Core\Database;

header('Content-Type: application/json');

$db = Database::obtenerConexion();

try {
    $stmt = $db->prepare("INSERT INTO configuracion (
        nombre_empresa, zona_horaria, tiempo_max_respuesta,
        tiempo_cierre_tras_respuesta, smtp_host, smtp_user, smtp_pass,
        notificaciones_email
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        'Mi Empresa S.A.',
        'America/Argentina/Buenos_Aires',
        60,
        1440,
        'smtp.miempresa.com',
        'soporte@miempresa.com',
        'clave123',
        true
    ]);

    echo json_encode(['ok' => true, 'mensaje' => 'Configuración insertada con éxito']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
