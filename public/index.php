<?php
// /public/index.php - ACTUALIZADO CON STATSCONTROLLER

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/env_loader.php';
require_once __DIR__ . '/../config/session_start.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/BaseController.php';

spl_autoload_register(function ($clase) {
    $rutaBase = __DIR__ . '/../';
    $rutaClase = $rutaBase . str_replace(['\\', 'Controllers'], ['/', 'controllers'], $clase) . '.php';

    if (file_exists($rutaClase)) {
        require_once $rutaClase;
    }
});

// 🆕 DETECTAR SI ES LLAMADA WEB O API
$esWeb = !isset($_GET['api']) && 
         (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'text/html') !== false || 
          !isset($_SERVER['HTTP_ACCEPT']));

if ($esWeb) {
    // Router de vistas web
    require_once __DIR__ . '/../views/router.php';
    exit;
}

// ⬇️ CONTINÚA CON LA API REST EXISTENTE + NUEVAS RUTAS DE ESTADÍSTICAS
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

use Controllers\UsersController;
use Controllers\TicketsController;
use Controllers\CommentsController;
use Controllers\SettingsController;
use Controllers\SLAController;
use Controllers\AuditController;
use Controllers\SecurityController;
use Controllers\StatsController; // 🆕 NUEVO CONTROLADOR
use Core\Database;

$ruta = $_GET['ruta'] ?? '';
$metodo = $_SERVER['REQUEST_METHOD'];

switch ($ruta) {
    // ================================================================================================
    // 📊 NUEVAS RUTAS DE ESTADÍSTICAS - MÓDULO 3
    // ================================================================================================
    
    case 'stats':
    case 'stats/dashboard':
        $controller = new StatsController();
        if ($metodo === 'GET') {
            $controller->dashboard();
        }
        break;
        
    case 'stats/tickets':
        $controller = new StatsController();
        if ($metodo === 'GET') {
            $controller->tickets();
        }
        break;
        
    case 'stats/trends':
        $controller = new StatsController();
        if ($metodo === 'GET') {
            $controller->trends();
        }
        break;
        
    case 'stats/technicians':
        $controller = new StatsController();
        if ($metodo === 'GET') {
            $controller->technicians();
        }
        break;
        
    case 'stats/sla':
        $controller = new StatsController();
        if ($metodo === 'GET') {
            $controller->sla();
        }
        break;
        
    case 'stats/audit':
        $controller = new StatsController();
        if ($metodo === 'GET') {
            $controller->audit();
        }
        break;
        
    case 'stats/recent':
        $controller = new StatsController();
        if ($metodo === 'GET') {
            $controller->recent();
        }
        break;
        
    case 'stats/categories':
        $controller = new StatsController();
        if ($metodo === 'GET') {
            $controller->categories();
        }
        break;
        
    case 'stats/weekly':
        $controller = new StatsController();
        if ($metodo === 'GET') {
            $controller->weekly();
        }
        break;
        
    case 'stats/clear-cache':
        $controller = new StatsController();
        if ($metodo === 'POST') {
            $controller->clearCache();
        }
        break;

    // ================================================================================================
    // 🔄 RUTAS EXISTENTES - SIN CAMBIOS
    // ================================================================================================
    
    case 'usuarios':
        $controller = new UsersController();
        if ($metodo === 'GET') {
            if (isset($_GET['id'])) {
                $controller->obtener((int) $_GET['id']);
            } else {
                $controller->listar();
            }
        } elseif ($metodo === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $controller->crear($data);
        } elseif ($metodo === 'PUT') {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $_GET['id'] ?? null;
            if ($id) {
                $controller->actualizar((int) $id, $data);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'ID requerido para actualización']);
            }
        } elseif ($metodo === 'DELETE') {
            $id = $_GET['id'] ?? null;
            if ($id) {
                $controller->eliminar((int) $id);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'ID requerido para eliminación']);
            }
        }
        break;

    case 'tickets':
        $controller = new TicketsController();
        if ($metodo === 'GET') {
            if (isset($_GET['id'])) {
                $controller->obtener((int) $_GET['id']);
            } else {
                $controller->listar();
            }
        } elseif ($metodo === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $controller->crear($data);
        } elseif ($metodo === 'PUT') {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $_GET['id'] ?? null;
            
            // Debug temporal
            error_log("PUT /tickets - ID: $id");
            error_log("PUT /tickets - Data recibida: " . json_encode($data));
            
            if ($id) {
                $controller->actualizar((int) $id, $data);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'ID requerido para actualización']);
            }
        } elseif ($metodo === 'DELETE') {
            $id = $_GET['id'] ?? null;
            if ($id) {
                $controller->eliminar((int) $id);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'ID requerido para eliminación']);
            }
        }
        break;

    case 'comments':
        $controller = new CommentsController();
        if (!isset($_GET['ticket_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ticket_id requerido']);
            break;
        }
        $ticketId = (int) $_GET['ticket_id'];
        if ($metodo === 'GET') {
            $controller->list($ticketId);
        } elseif ($metodo === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $controller->create($ticketId, $data);
        }
        break;

    case 'sla':
        $controller = new SLAController();
        if ($metodo === 'GET') {
            $controller->list();
        } elseif ($metodo === 'PUT') {
            $data = json_decode(file_get_contents('php://input'), true);
            $controller->update($data);
        }
        break;

    case 'settings':
        $controller = new SettingsController();
        if ($metodo === 'GET') {
            $controller->get();
        } elseif ($metodo === 'PUT') {
            $data = json_decode(file_get_contents('php://input'), true);
            $controller->update($data);
        }
        break;

    case 'audit':
        $controller = new AuditController();
        if ($metodo === 'GET') {
            if (isset($_GET['id'])) {
                $controller->get((int) $_GET['id']);
            } else {
                $controller->list();
            }
        }
        break;

    case 'login':
        $controller = new SecurityController();
        if ($metodo === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $controller->login($data);
        }
        break;

    case 'logout':
        $controller = new SecurityController();
        if ($metodo === 'POST') {
            $controller->logout();
        }
        break;

    case 'csrf-token':
        $controller = new SecurityController();
        if ($metodo === 'GET') {
            $controller->csrf();
        }
        break;

    case 'test-email':
        $controller = new SecurityController();
        if ($metodo === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $controller->testEmail($data);
        }
        break;    

    case 'prueba-db':
        $db = Database::obtenerConexion();
        echo json_encode(['status' => 'ok', 'message' => 'Conexión a la base de datos establecida.']);
        break;

    // ================================================================================================
    // 🆕 ENDPOINT ESPECIAL PARA TESTING DE VISTAS BD
    // ================================================================================================
    
    case 'test-views':
        $controller = new StatsController();
        if ($metodo === 'GET') {
            try {
                $db = Database::obtenerConexion();
                
                // Verificar que las vistas existen
                $stmt = $db->query("
                    SELECT TABLE_NAME 
                    FROM INFORMATION_SCHEMA.VIEWS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME LIKE 'v_%'
                    ORDER BY TABLE_NAME
                ");
                $vistas = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                // Probar algunas vistas clave
                $resultados = [];
                
                foreach (['v_dashboard_metrics', 'v_ticket_stats', 'v_sla_compliance'] as $vista) {
                    try {
                        $stmt = $db->query("SELECT * FROM $vista LIMIT 1");
                        $data = $stmt->fetch(PDO::FETCH_ASSOC);
                        $resultados[$vista] = [
                            'existe' => true,
                            'datos' => $data ? 'OK' : 'VACIA',
                            'campos' => $data ? count($data) : 0
                        ];
                    } catch (Exception $e) {
                        $resultados[$vista] = [
                            'existe' => false,
                            'error' => $e->getMessage()
                        ];
                    }
                }
                
                echo json_encode([
                    'status' => 'ok',
                    'total_vistas' => count($vistas),
                    'vistas_encontradas' => $vistas,
                    'pruebas' => $resultados,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
            }
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Ruta no encontrada']);
        break;
}