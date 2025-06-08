<?php
// /test_api_direct.php - Test directo de la API

require_once __DIR__ . '/config/env_loader.php';
require_once __DIR__ . '/config/session_start.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/BaseController.php';

spl_autoload_register(function ($clase) {
    $rutaBase = __DIR__ . '/';
    $rutaClase = $rutaBase . str_replace(['\\', 'Controllers'], ['/', 'controllers'], $clase) . '.php';
    if (file_exists($rutaClase)) {
        require_once $rutaClase;
    }
});

use Controllers\TicketsController;

header('Content-Type: application/json');

echo "<h2>Test API Direct</h2>";

try {
    echo "<h3>1. Test conexión BD:</h3>";
    $db = \Core\Database::obtenerConexion();
    echo "✅ Conexión BD OK<br>";
    
    echo "<h3>2. Test Controller:</h3>";
    $controller = new TicketsController();
    echo "✅ Controller creado OK<br>";
    
    echo "<h3>3. Test datos tickets:</h3>";
    $stmt = $db->query("SELECT * FROM tickets");
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Tickets en BD: " . count($tickets) . "<br>";
    echo "<pre>" . print_r($tickets, true) . "</pre>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>