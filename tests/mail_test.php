<?php
// /tests/mail_test.php

ini_set('display_errors', 1);
error_reporting(E_ALL);


require_once __DIR__ . '/../config/env_loader.php';
require_once __DIR__ . '/../core/Mailer.php';
require_once __DIR__ . '/../core/Logger.php';

use Core\Mailer;

header('Content-Type: application/json');

// Reemplaza por tu correo de destino temporal para prueba
$para = getenv('SMTP_USER');
$asunto = 'Prueba de correo desde Helpdesk';
$mensaje = '<p>Este es un correo de prueba enviado correctamente desde el sistema Helpdesk en PHP.</p>';

$enviado = Mailer::enviar($para, $asunto, $mensaje);

echo json_encode([
    'ok' => $enviado,
    'mensaje' => $enviado ? 'Correo enviado correctamente' : 'No se pudo enviar el correo'
]);
