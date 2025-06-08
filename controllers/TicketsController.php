<?php
// /controllers/TicketsController.php

namespace Controllers;

use Core\BaseController;
use PDO;

class TicketsController extends BaseController
{
    public function listar(): void
    {
        // Obtener usuario actual de la sesión
        $usuarioActual = $_SESSION['usuario'] ?? null;
        
        if (!$usuarioActual) {
            $this->json(['error' => 'Usuario no autenticado'], 401);
            return;
        }
        
        $usuarioId = $usuarioActual['id'];
        $rolId = $usuarioActual['rol_id'];
        
        $sql = "
            SELECT t.id, t.titulo, t.estado, t.prioridad, t.creado_en, t.categoria,
                   u.nombre AS cliente, 
                   tech.nombre AS tecnico,
                   t.actualizado_en
            FROM tickets t 
            JOIN usuarios u ON t.cliente_id = u.id 
            LEFT JOIN usuarios tech ON t.tecnico_id = tech.id
        ";
        
        $params = [];
        
        // 🔐 FILTRO POR ROL
        if ($rolId == 1) { // Cliente - solo sus tickets
            $sql .= " WHERE t.cliente_id = ?";
            $params[] = $usuarioId;
        }
        // Técnicos (rol 2) y Admins (rol 3) ven todos los tickets
        
        $sql .= " ORDER BY t.creado_en DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->json($tickets);
    }

    public function obtener(int $id): void
    {
        // Obtener usuario actual
        $usuarioActual = $_SESSION['usuario'] ?? null;
        
        if (!$usuarioActual) {
            $this->json(['error' => 'Usuario no autenticado'], 401);
            return;
        }
        
        $usuarioId = $usuarioActual['id'];
        $rolId = $usuarioActual['rol_id'];
        
        $sql = "
            SELECT t.*, 
                   c.nombre AS cliente, 
                   c.correo AS cliente_correo,
                   tech.nombre AS tecnico,
                   t.tecnico_id,
                   t.cliente_id
            FROM tickets t 
            LEFT JOIN usuarios c ON t.cliente_id = c.id 
            LEFT JOIN usuarios tech ON t.tecnico_id = tech.id 
            WHERE t.id = ?
        ";
        
        $params = [$id];
        
        // 🔐 FILTRO POR ROL - Clientes solo pueden ver sus tickets
        if ($rolId == 1) { // Cliente
            $sql .= " AND t.cliente_id = ?";
            $params[] = $usuarioId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ticket) {
            $this->json($ticket);
        } else {
            $this->json(['error' => 'Ticket no encontrado o sin permisos'], 404);
        }
    }

    public function crear(array $data): void
    {
        // Obtener usuario actual
        $usuarioActual = $_SESSION['usuario'] ?? null;
        
        if (!$usuarioActual) {
            $this->json(['error' => 'Usuario no autenticado'], 401);
            return;
        }
        
        if (!isset($data['titulo'], $data['descripcion'], $data['categoria'], $data['prioridad'])) {
            $this->json(['error' => 'Datos incompletos'], 400);
            return;
        }

        // Para clientes, usar su propio ID como cliente_id
        // Para técnicos/admins, pueden especificar cliente_id
        $clienteId = $usuarioActual['id']; // Default: usuario actual
        
        if ($usuarioActual['rol_id'] >= 2 && isset($data['cliente_id'])) {
            // Técnicos/admins pueden crear tickets para otros usuarios
            $clienteId = intval($data['cliente_id']);
        }

        $stmt = $this->db->prepare("INSERT INTO tickets (titulo, descripcion, categoria, prioridad, cliente_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            trim($data['titulo']),
            trim($data['descripcion']),
            trim($data['categoria']),
            trim($data['prioridad']),
            $clienteId
        ]);

        $ticketId = $this->db->lastInsertId();
        $this->json(['mensaje' => 'Ticket creado correctamente', 'ticket_id' => $ticketId]);
    }

    public function actualizar(int $id, array $data): void
    {
        // Obtener usuario actual
        $usuarioActual = $_SESSION['usuario'] ?? null;
        
        if (!$usuarioActual) {
            $this->json(['error' => 'Usuario no autenticado'], 401);
            return;
        }
        
        $usuarioId = $usuarioActual['id'];
        $rolId = $usuarioActual['rol_id'];
        
        // Verificar permisos para actualizar
        $sqlVerificar = "SELECT cliente_id FROM tickets WHERE id = ?";
        $params = [$id];
        
        if ($rolId == 1) { // Cliente - solo puede actualizar sus tickets
            $sqlVerificar .= " AND cliente_id = ?";
            $params[] = $usuarioId;
        }
        
        $stmt = $this->db->prepare($sqlVerificar);
        $stmt->execute($params);
        
        if (!$stmt->fetch()) {
            $this->json(['error' => 'Ticket no encontrado o sin permisos'], 404);
            return;
        }
        
        $campos = [];
        $valores = [];

        // Campos que solo técnicos/admins pueden actualizar
        if ($rolId >= 2) {
            if (isset($data['estado'])) {
                $campos[] = "estado = ?";
                $valores[] = $data['estado'];
            }
            if (isset($data['tecnico_id'])) {
                $campos[] = "tecnico_id = ?";
                $valores[] = $data['tecnico_id'] ?: null;
            }
        }
        
        // Campos que clientes también pueden actualizar (solo si es su ticket)
        if (isset($data['prioridad'])) {
            $campos[] = "prioridad = ?";
            $valores[] = $data['prioridad'];
        }
        if (isset($data['categoria'])) {
            $campos[] = "categoria = ?";
            $valores[] = trim($data['categoria']);
        }
        if (isset($data['titulo'])) {
            $campos[] = "titulo = ?";
            $valores[] = trim($data['titulo']);
        }
        if (isset($data['descripcion'])) {
            $campos[] = "descripcion = ?";
            $valores[] = trim($data['descripcion']);
        }

        if (empty($campos)) {
            $this->json(['error' => 'Sin datos para actualizar'], 400);
            return;
        }

        // Agregar actualizado_en automáticamente
        $campos[] = "actualizado_en = NOW()";
        $valores[] = $id;
        
        $stmt = $this->db->prepare("UPDATE tickets SET " . implode(', ', $campos) . " WHERE id = ?");
        $stmt->execute($valores);

        if ($stmt->rowCount() > 0) {
            $this->json(['mensaje' => 'Ticket actualizado correctamente']);
        } else {
            $this->json(['error' => 'Ticket no encontrado o sin cambios'], 404);
        }
    }

    public function eliminar(int $id): void
    {
        // Obtener usuario actual
        $usuarioActual = $_SESSION['usuario'] ?? null;
        
        if (!$usuarioActual) {
            $this->json(['error' => 'Usuario no autenticado'], 401);
            return;
        }
        
        $usuarioId = $usuarioActual['id'];
        $rolId = $usuarioActual['rol_id'];
        
        // Solo admins pueden eliminar tickets
        if ($rolId < 3) {
            $this->json(['error' => 'Sin permisos para eliminar tickets'], 403);
            return;
        }
        
        // Verificar si el ticket existe
        $stmt = $this->db->prepare("SELECT id FROM tickets WHERE id = ?");
        $stmt->execute([$id]);
        
        if (!$stmt->fetch()) {
            $this->json(['error' => 'Ticket no encontrado'], 404);
            return;
        }

        // Eliminar comentarios asociados primero (por integridad referencial)
        $stmt = $this->db->prepare("DELETE FROM comentarios WHERE ticket_id = ?");
        $stmt->execute([$id]);

        // Eliminar el ticket
        $stmt = $this->db->prepare("DELETE FROM tickets WHERE id = ?");
        $stmt->execute([$id]);

        $this->json(['mensaje' => 'Ticket eliminado correctamente']);
    }

    // Método adicional para obtener estadísticas según rol
    public function estadisticas(): void
    {
        // Obtener usuario actual
        $usuarioActual = $_SESSION['usuario'] ?? null;
        
        if (!$usuarioActual) {
            $this->json(['error' => 'Usuario no autenticado'], 401);
            return;
        }
        
        $usuarioId = $usuarioActual['id'];
        $rolId = $usuarioActual['rol_id'];
        
        $stats = [];
        $whereClause = "";
        $params = [];
        
        // Filtrar estadísticas según rol
        if ($rolId == 1) { // Cliente - solo sus tickets
            $whereClause = " WHERE cliente_id = ?";
            $params = [$usuarioId];
        }

        // Total de tickets
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM tickets" . $whereClause);
        $stmt->execute($params);
        $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Por estado
        $stmt = $this->db->prepare("
            SELECT estado, COUNT(*) as cantidad 
            FROM tickets" . $whereClause . "
            GROUP BY estado
        ");
        $stmt->execute($params);
        $stats['por_estado'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Por prioridad
        $stmt = $this->db->prepare("
            SELECT prioridad, COUNT(*) as cantidad 
            FROM tickets" . $whereClause . "
            GROUP BY prioridad
        ");
        $stmt->execute($params);
        $stats['por_prioridad'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Resueltos hoy
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as resueltos_hoy 
            FROM tickets 
            WHERE estado = 'cerrado' 
            AND DATE(actualizado_en) = CURDATE()" . 
            ($whereClause ? " AND cliente_id = ?" : "")
        );
        $stmt->execute($rolId == 1 ? [$usuarioId] : []);
        $stats['resueltos_hoy'] = $stmt->fetch(PDO::FETCH_ASSOC)['resueltos_hoy'];

        // Tickets sin asignar (solo para técnicos/admins)
        if ($rolId >= 2) {
            $stmt = $this->db->query("
                SELECT COUNT(*) as sin_asignar 
                FROM tickets 
                WHERE tecnico_id IS NULL 
                AND estado != 'cerrado'
            ");
            $stats['sin_asignar'] = $stmt->fetch(PDO::FETCH_ASSOC)['sin_asignar'];
        }

        $this->json($stats);
    }

    // Método para buscar tickets según permisos
    public function buscar(): void
    {
        // Obtener usuario actual
        $usuarioActual = $_SESSION['usuario'] ?? null;
        
        if (!$usuarioActual) {
            $this->json(['error' => 'Usuario no autenticado'], 401);
            return;
        }
        
        $usuarioId = $usuarioActual['id'];
        $rolId = $usuarioActual['rol_id'];
        
        $busqueda = $_GET['q'] ?? '';
        $estado = $_GET['estado'] ?? '';
        $prioridad = $_GET['prioridad'] ?? '';
        $tecnico_id = $_GET['tecnico_id'] ?? '';

        $sql = "
            SELECT t.id, t.titulo, t.estado, t.prioridad, t.creado_en, t.categoria,
                   u.nombre AS cliente, 
                   tech.nombre AS tecnico,
                   t.actualizado_en
            FROM tickets t 
            JOIN usuarios u ON t.cliente_id = u.id 
            LEFT JOIN usuarios tech ON t.tecnico_id = tech.id
            WHERE 1=1
        ";
        
        $params = [];

        // 🔐 FILTRO POR ROL
        if ($rolId == 1) { // Cliente - solo sus tickets
            $sql .= " AND t.cliente_id = ?";
            $params[] = $usuarioId;
        }

        if ($busqueda) {
            $sql .= " AND (t.titulo LIKE ? OR t.descripcion LIKE ?)";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
        }

        if ($estado) {
            $sql .= " AND t.estado = ?";
            $params[] = $estado;
        }

        if ($prioridad) {
            $sql .= " AND t.prioridad = ?";
            $params[] = $prioridad;
        }

        if ($tecnico_id) {
            $sql .= " AND t.tecnico_id = ?";
            $params[] = $tecnico_id;
        }

        $sql .= " ORDER BY t.creado_en DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->json($tickets);
    }
}