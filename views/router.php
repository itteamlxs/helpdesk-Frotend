<?php
// /views/router.php - Router de vistas web

// Verificar autenticación para rutas protegidas
$rutasPublicas = ['login', 'logout'];
$ruta = $_GET['ruta'] ?? 'dashboard';

if (!in_array($ruta, $rutasPublicas) && !isset($_SESSION['usuario'])) {
    header('Location: ?ruta=login');
    exit;
}

// Si está autenticado pero va a login, redirigir al dashboard
if ($ruta === 'login' && isset($_SESSION['usuario'])) {
    header('Location: ?ruta=dashboard');
    exit;
}

// Mapeo de rutas a archivos de vista
$rutasVistas = [
    'login' => 'auth/login.php',
    'logout' => 'auth/logout.php',
    'dashboard' => 'dashboard/index.php',
    'usuarios' => 'usuarios/index.php',
    'usuarios-crear' => 'usuarios/create.php',
    'usuarios-editar' => 'usuarios/edit.php',
    'tickets' => 'tickets/index.php',
    'tickets-crear' => 'tickets/create.php',
    'tickets-ver' => 'tickets/show.php',
    'tickets-editar' => 'tickets/edit.php',
    'settings' => 'settings/index.php',
    'sla' => 'sla/index.php',
    'audit' => 'audit/index.php'
];

$archivoVista = $rutasVistas[$ruta] ?? 'dashboard/index.php';
$rutaCompleta = __DIR__ . '/' . $archivoVista;

if (file_exists($rutaCompleta)) {
    include $rutaCompleta;
} else {
    include __DIR__ . '/errors/404.php';
}
?>