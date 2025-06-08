<?php
// /core/Mailer.php

namespace Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class Mailer
{
    public static function enviar(string $para, string $asunto, string $mensaje): bool
    {
        if (!getenv('SMTP_HOST') || !filter_var($para, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if (getenv('NOTIFICACIONES_EMAIL') !== 'true') {
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = getenv('SMTP_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = getenv('SMTP_USER');
            $mail->Password = getenv('SMTP_PASS');
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom(getenv('SMTP_USER'), 'Soporte Técnico');
            $mail->addAddress($para);
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body = $mensaje;

            $mail->send();
            return true;
        } catch (Exception $e) {
            Logger::error("Error al enviar correo: " . $mail->ErrorInfo);
            return false;
        }
    }
}
