<?php
// /controllers/CommentsController.php

namespace Controllers;

use Core\BaseController;
use PDO;

class CommentsController extends BaseController
{
    public function list(int $ticketId): void
    {
        // Obtener usuario actual
        $usuarioActual = $_SESSION['usuario'] ?? null;
        
        if (!$usuarioActual) {
            $this->json(['error' => 'Usuario no autenticado'], 401);
            return;
        }
        
        $usuarioId = $usuarioActual['id'];
        $rolId = $usuarioActual['rol_id'];
        
        // Verificar que el usuario puede ver este ticket
        $sqlTicket = "SELECT cliente_id FROM tickets WHERE id = ?";
        $params = [$ticketId];
        
        if ($rolId == 1) { // Cliente - solo puede ver sus tickets
            $sqlTicket .= " AND cliente_id = ?";
            $params[] = $usuarioId;
        }
        
        $stmt = $this->db->prepare($sqlTicket);
        $stmt->execute($params);
        
        if (!$stmt->fetch()) {
            $this->json(['error' => 'Ticket no encontrado o sin permisos'], 404);
            return;
        }
        
        // Obtener comentarios
        $sql = "
            SELECT c.id, c.contenido, c.interno, c.creado_en, 
                   u.nombre AS autor, u.rol_id
            FROM comentarios c 
            JOIN usuarios u ON c.usuario_id = u.id 
            WHERE c.ticket_id = ?
        ";
        
        // 🔐 FILTRO COMENTARIOS INTERNOS - Solo técnicos/admins los ven
        if ($rolId == 1) { // Cliente - no ve comentarios internos
            $sql .= " AND c.interno = 0";
        }
        
        $sql .= " ORDER BY c.creado_en ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ticketId]);
        $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->json($comentarios);
    }

    public function create(int $ticketId, array $data): void
    {
        // Obtener usuario actual
        $usuarioActual = $_SESSION['usuario'] ?? null;
        
        if (!$usuarioActual) {
            $this->json(['error' => 'Usuario no autenticado'], 401);
            return;
        }
        
        $usuarioId = $usuarioActual['id'];
        $rolId = $usuarioActual['rol_id'];
        
        error_log("CommentsController::create - Usuario: $usuarioId, Rol: $rolId, Ticket: $ticketId");
        
        if (!isset($data['contenido'])) {
            $this->json(['error' => 'Contenido requerido'], 400);
            return;
        }

        $contenido = trim($data['contenido']);
        
        // 🔐 LÓGICA DE COMENTARIOS INTERNOS
        $interno = 0; // Default: público
        
        if (isset($data['interno']) && $data['interno']) {
            // Solo técnicos/admins pueden crear comentarios internos
            if ($rolId >= 2) { // Técnico o Admin
                $interno = 1;
                error_log("Comentario interno creado por técnico/admin");
            } else {
                // Cliente intentó crear comentario interno - ignorar y hacer público
                error_log("Cliente intentó crear comentario interno - convertido a público");
            }
        }

        // Verificar que el usuario puede comentar en este ticket
        $sqlTicket = "SELECT cliente_id FROM tickets WHERE id = ?";
        $params = [$ticketId];
        
        if ($rolId == 1) { // Cliente - solo puede comentar en sus tickets
            $sqlTicket .= " AND cliente_id = ?";
            $params[] = $usuarioId;
        }
        
        $stmt = $this->db->prepare($sqlTicket);
        $stmt->execute($params);
        
        if (!$stmt->fetch()) {
            $this->json(['error' => 'No tiene permisos para comentar en este ticket'], 403);
            return;
        }

        if (empty($contenido)) {
            $this->json(['error' => 'El contenido no puede estar vacío'], 400);
            return;
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO comentarios (ticket_id, usuario_id, contenido, interno) 
                VALUES (?, ?, ?, ?)
            ");
            
            error_log("Ejecutando INSERT comentario: ticket_id=$ticketId, usuario_id=$usuarioId, interno=$interno");
            
            $result = $stmt->execute([
                $ticketId,
                $usuarioId, // Usar usuario de la sesión (más seguro)
                $contenido,
                $interno
            ]);

            if ($result) {
                $comentarioId = $this->db->lastInsertId();
                error_log("Comentario creado exitosamente con ID: $comentarioId");
                
                $this->json([
                    'mensaje' => 'Comentario agregado correctamente',
                    'comentario_id' => $comentarioId
                ]);
            } else {
                error_log("Error: execute() retornó false");
                $this->json(['error' => 'Error al guardar comentario'], 500);
            }

        } catch (PDOException $e) {
            error_log("Error PDO al crear comentario: " . $e->getMessage());
            $this->json(['error' => 'Error en base de datos'], 500);
        } catch (Exception $e) {
            error_log("Error general al crear comentario: " . $e->getMessage());
            $this->json(['error' => 'Error inesperado'], 500);
        }
    }

    public function update(int $commentId, array $data): void
    {
        // Obtener usuario actual
        $usuarioActual = $_SESSION['usuario'] ?? null;
        
        if (!$usuarioActual) {
            $this->json(['error' => 'Usuario no autenticado'], 401);
            return;
        }
        
        $usuarioId = $usuarioActual['id'];
        $rolId = $usuarioActual['rol_id'];
        
        // Verificar que el comentario existe y el usuario tiene permisos
        $stmt = $this->db->prepare("
            SELECT c.usuario_id, c.ticket_id, t.cliente_id 
            FROM comentarios c 
            JOIN tickets t ON c.ticket_id = t.id 
            WHERE c.id = ?
        ");
        $stmt->execute([$commentId]);
        $comentario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$comentario) {
            $this->json(['error' => 'Comentario no encontrado'], 404);
            return;
        }
        
        // Solo el autor del comentario o admins pueden editarlo
        if ($comentario['usuario_id'] != $usuarioId && $rolId < 3) {
            $this->json(['error' => 'Sin permisos para editar este comentario'], 403);
            return;
        }
        
        // Si es cliente, verificar que puede editar comentarios en este ticket
        if ($rolId == 1 && $comentario['cliente_id'] != $usuarioId) {
            $this->json(['error' => 'Sin permisos para editar comentarios en este ticket'], 403);
            return;
        }

        $campos = [];
        $valores = [];

        if (isset($data['contenido'])) {
            $contenido = trim($data['contenido']);
            if (empty($contenido)) {
                $this->json(['error' => 'El contenido no puede estar vacío'], 400);
                return;
            }
            $campos[] = "contenido = ?";
            $valores[] = $contenido;
        }

        // Solo técnicos/admins pueden cambiar el flag interno
        if (isset($data['interno']) && $rolId >= 2) {
            $campos[] = "interno = ?";
            $valores[] = $data['interno'] ? 1 : 0;
        }

        if (empty($campos)) {
            $this->json(['error' => 'Sin datos para actualizar'], 400);
            return;
        }

        $valores[] = $commentId;
        
        try {
            $stmt = $this->db->prepare("UPDATE comentarios SET " . implode(', ', $campos) . " WHERE id = ?");
            $stmt->execute($valores);

            if ($stmt->rowCount() > 0) {
                $this->json(['mensaje' => 'Comentario actualizado correctamente']);
            } else {
                $this->json(['error' => 'Sin cambios realizados'], 400);
            }

        } catch (PDOException $e) {
            error_log("Error al actualizar comentario: " . $e->getMessage());
            $this->json(['error' => 'Error en base de datos'], 500);
        }
    }

    public function delete(int $commentId): void
    {
        // Obtener usuario actual
        $usuarioActual = $_SESSION['usuario'] ?? null;
        
        if (!$usuarioActual) {
            $this->json(['error' => 'Usuario no autenticado'], 401);
            return;
        }
        
        $usuarioId = $usuarioActual['id'];
        $rolId = $usuarioActual['rol_id'];
        
        // Verificar que el comentario existe y el usuario tiene permisos
        $stmt = $this->db->prepare("
            SELECT c.usuario_id, c.ticket_id, t.cliente_id 
            FROM comentarios c 
            JOIN tickets t ON c.ticket_id = t.id 
            WHERE c.id = ?
        ");
        $stmt->execute([$commentId]);
        $comentario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$comentario) {
            $this->json(['error' => 'Comentario no encontrado'], 404);
            return;
        }
        
        // Solo el autor del comentario o admins pueden eliminarlo
        if ($comentario['usuario_id'] != $usuarioId && $rolId < 3) {
            $this->json(['error' => 'Sin permisos para eliminar este comentario'], 403);
            return;
        }
        
        try {
            $stmt = $this->db->prepare("DELETE FROM comentarios WHERE id = ?");
            $stmt->execute([$commentId]);

            if ($stmt->rowCount() > 0) {
                $this->json(['mensaje' => 'Comentario eliminado correctamente']);
            } else {
                $this->json(['error' => 'Comentario no encontrado'], 404);
            }

        } catch (PDOException $e) {
            error_log("Error al eliminar comentario: " . $e->getMessage());
            $this->json(['error' => 'Error en base de datos'], 500);
        }
    }
}