<?php
// 📦 /core/BaseController.php
// Clase base para todos los controladores del sistema

namespace Core;

use Core\Database;
use PDO;

class BaseController
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::obtenerConexion();
    }

    // Helper para cargar vistas sin lógica
    protected function vista(string $nombre, array $datos = []): void
    {
        $rutaVista = __DIR__ . '/../views/' . $nombre . '.php';
        if (file_exists($rutaVista)) {
            extract($datos);
            include $rutaVista;
        } else {
            http_response_code(404);
            echo "Vista no encontrada: $nombre";
        }
    }

    // Helper para respuesta JSON
    protected function json(array $respuesta, int $codigo = 200): void
    {
        http_response_code($codigo);
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    }
}
