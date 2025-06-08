<?php
// /tests/test_all_modules.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/env_loader.php';
require_once __DIR__ . '/../core/Database.php';

use Core\Database;

header('Content-Type: text/plain');

$db = Database::obtenerConexion();

function testQuery(PDO $db, string $sql, string $descripcion): void
{
    echo "Test: $descripcion\n";
    try {
        $stmt = $db->query($sql);
        $result = $stmt->fetchAll();
        echo "  ✔ OK (" . count($result) . " registros)\n\n";
    } catch (Exception $e) {
        echo "  ✖ ERROR: " . $e->getMessage() . "\n\n";
    }
}

function testTable(PDO $db, string $tabla): void
{
    testQuery($db, "SELECT * FROM $tabla LIMIT 5", "Verificar tabla '$tabla'");
}

$tablas = [
    'roles',
    'usuarios',
    'tickets',
    'comentarios',
    'configuracion',
    'sla',
    'auditoria',
    'reportes_dinamicos'
];

foreach ($tablas as $tabla) {
    testTable($db, $tabla);
}

// Prueba especial: configuración más reciente
try {
    $config = $db->query("SELECT * FROM configuracion ORDER BY id DESC LIMIT 1")->fetch();
    echo "Test: configuración cargada\n";
    echo $config ? "  ✔ OK: {$config['nombre_empresa']}\n" : "  ✖ ERROR: No hay configuración\n";
} catch (Exception $e) {
    echo "  ✖ ERROR al cargar configuración: " . $e->getMessage() . "\n";
}
