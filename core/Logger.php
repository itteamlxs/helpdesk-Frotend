<?php
// /core/Logger.php

namespace Core;

class Logger
{
    private static string $logPath = __DIR__ . '/../logs/app.log';

    public static function registrar(string $mensaje, string $nivel = 'INFO'): void
    {
        $fecha = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $linea = "[$fecha] [$nivel] [$ip] $mensaje" . PHP_EOL;

        file_put_contents(self::$logPath, $linea, FILE_APPEND);
    }

    public static function error(string $mensaje): void
    {
        self::registrar($mensaje, 'ERROR');
    }

    public static function info(string $mensaje): void
    {
        self::registrar($mensaje, 'INFO');
    }

    public static function advertencia(string $mensaje): void
    {
        self::registrar($mensaje, 'WARN');
    }
}
