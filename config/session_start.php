<?php
// /config/session_start.php
// Configuración de sesiones seguras con regeneración de ID y HttpOnly

session_name(getenv('SESSION_NAME') ?: 'tickets_session');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']), // true si está en HTTPS
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

// Regenerar ID para prevenir fijación de sesión
if (!isset($_SESSION['iniciada'])) {
    session_regenerate_id(true);
    $_SESSION['iniciada'] = true;
}
