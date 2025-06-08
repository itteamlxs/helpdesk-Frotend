<?php
// /security/middleware.php

function requireLogin(): void
{
    if (empty($_SESSION['usuario'])) {
        http_response_code(401);
        echo json_encode(['error' => 'No autenticado']);
        exit;
    }
}

function requireRole(array $rolesPermitidos): void
{
    if (!isset($_SESSION['usuario']['rol_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Acceso denegado']);
        exit;
    }

    $rolUsuario = (int) $_SESSION['usuario']['rol_id'];
    if (!in_array($rolUsuario, $rolesPermitidos, true)) {
        http_response_code(403);
        echo json_encode(['error' => 'Rol sin permiso']);
        exit;
    }
}
