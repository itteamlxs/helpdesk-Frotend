<?php
// /scripts/auto_close_tickets.php

require_once __DIR__ . '/../config/env_loader.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Logger.php';

use Core\Database;
use Core\Logger;

$db = Database::obtenerConexion();

// Obtener tiempo configurado para cierre automático (en minutos)
$config = $db->query("SELECT tiempo_cierre_tras_respuesta FROM configuracion ORDER BY id DESC LIMIT 1")->fetch();
$tiempoLimite = (int) ($config['tiempo_cierre_tras_respuesta'] ?? 1440);

// Cerrar tickets en espera que superaron el límite
$stmt = $db->prepare("UPDATE tickets
    SET estado = 'cerrado', cerrado_automaticamente = 1
    WHERE estado = 'en_espera'
      AND actualizado_en < (NOW() - INTERVAL ? MINUTE)");

$stmt->execute([$tiempoLimite]);
$count = $stmt->rowCount();

Logger::info("Auto cierre ejecutado. Tickets cerrados: $count");
echo "Tickets cerrados automáticamente: $count\n";
 