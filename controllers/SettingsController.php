<?php
// /controllers/SettingsController.php

namespace Controllers;

use Core\BaseController;
use PDO;

class SettingsController extends BaseController
{
    public function get(): void
    {
        $stmt = $this->db->query("SELECT * FROM configuracion ORDER BY id DESC LIMIT 1");
        $config = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($config) {
            $this->json($config);
        } else {
            $this->json(['error' => 'Configuración no encontrada'], 404);
        }
    }

    public function update(array $data): void
    {
        // Debug: Ver qué datos están llegando
        error_log("SettingsController::update - Datos recibidos: " . json_encode($data));
        
        // Verificar si ya existe una configuración
        $stmt = $this->db->query("SELECT id FROM configuracion ORDER BY id DESC LIMIT 1");
        $configExistente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($configExistente) {
            // Actualizar configuración existente
            $stmt = $this->db->prepare("UPDATE configuracion SET 
                nombre_empresa = ?, 
                zona_horaria = ?, 
                tiempo_max_respuesta = ?,
                tiempo_cierre_tras_respuesta = ?, 
                smtp_host = ?, 
                smtp_user = ?,
                smtp_pass = ?, 
                notificaciones_email = ?
                WHERE id = ?");

            $valores = [
                trim($data['nombre_empresa'] ?? ''),
                trim($data['zona_horaria'] ?? 'UTC'),
                intval($data['tiempo_max_respuesta'] ?? 60),
                intval($data['tiempo_cierre_tras_respuesta'] ?? 1440),
                trim($data['smtp_host'] ?? ''),
                trim($data['smtp_user'] ?? ''),
                trim($data['smtp_pass'] ?? ''),
                isset($data['notificaciones_email']) ? (bool)$data['notificaciones_email'] : false,
                $configExistente['id']
            ];
            
            // Debug: Ver valores que se van a guardar
            error_log("SettingsController::update - Valores para UPDATE: " . json_encode($valores));
            
            $stmt->execute($valores);
        } else {
            // Crear nueva configuración
            $stmt = $this->db->prepare("INSERT INTO configuracion (
                nombre_empresa, zona_horaria, tiempo_max_respuesta,
                tiempo_cierre_tras_respuesta, smtp_host, smtp_user,
                smtp_pass, notificaciones_email
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            $valores = [
                trim($data['nombre_empresa'] ?? ''),
                trim($data['zona_horaria'] ?? 'UTC'),
                intval($data['tiempo_max_respuesta'] ?? 60),
                intval($data['tiempo_cierre_tras_respuesta'] ?? 1440),
                trim($data['smtp_host'] ?? ''),
                trim($data['smtp_user'] ?? ''),
                trim($data['smtp_pass'] ?? ''),
                isset($data['notificaciones_email']) ? (bool)$data['notificaciones_email'] : false
            ];
            
            // Debug: Ver valores que se van a guardar
            error_log("SettingsController::update - Valores para INSERT: " . json_encode($valores));
            
            $stmt->execute($valores);
        }

        $this->json(['mensaje' => 'Configuración guardada correctamente']);
    }
}