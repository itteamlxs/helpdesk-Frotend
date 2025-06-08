<?php
// /test_comments_api.php - Test directo API de comentarios

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

use Controllers\CommentsController;

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Test API Comentarios</h2>";

try {
    echo "<h3>1. Test conexión BD:</h3>";
    $db = \Core\Database::obtenerConexion();
    echo "✅ Conexión BD OK<br>";
    
    echo "<h3>2. Test tickets existentes:</h3>";
    $stmt = $db->query("SELECT id, titulo FROM tickets LIMIT 3");
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($tickets as $ticket) {
        echo "- Ticket #{$ticket['id']}: {$ticket['titulo']}<br>";
    }
    
    echo "<h3>3. Test usuarios existentes:</h3>";
    $stmt = $db->query("SELECT id, nombre, rol_id FROM usuarios LIMIT 3");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($usuarios as $usuario) {
        echo "- Usuario #{$usuario['id']}: {$usuario['nombre']} (Rol: {$usuario['rol_id']})<br>";
    }
    
    echo "<h3>4. Test crear comentario:</h3>";
    if (!empty($tickets) && !empty($usuarios)) {
        $ticketId = $tickets[0]['id'];
        $usuarioId = $usuarios[0]['id'];
        
        $controller = new CommentsController();
        
        // Simular datos POST
        $data = [
            'usuario_id' => $usuarioId,
            'contenido' => 'Comentario de prueba desde test - ' . date('Y-m-d H:i:s'),
            'interno' => false
        ];
        
        echo "Creando comentario para ticket #$ticketId con usuario #$usuarioId...<br>";
        
        // Capturar output
        ob_start();
        $controller->create($ticketId, $data);
        $output = ob_get_clean();
        
        echo "Respuesta: <code>$output</code><br>";
        
        // Verificar si se creó
        $stmt = $db->prepare("SELECT * FROM comentarios WHERE ticket_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$ticketId]);
        $comentario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($comentario) {
            echo "✅ Comentario creado correctamente:<br>";
            echo "<pre>" . print_r($comentario, true) . "</pre>";
        } else {
            echo "❌ No se encontró el comentario creado<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}
?>