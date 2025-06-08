<?php
// 📦 /core/Database.php
// Clase de conexión PDO usando configuración desde .env

namespace Core;

use PDO;
use PDOException;

class Database
{
    private static $conexion = null;

    public static function obtenerConexion(): PDO
    {
        if (self::$conexion === null) {
            $host = getenv('DB_HOST');
            $db   = getenv('DB_NAME');
            $user = getenv('DB_USER');
            $pass = getenv('DB_PASS');

            $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

            try {
                self::$conexion = new PDO($dsn, $user, $pass);
                self::$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die('Error de conexión a la base de datos: ' . $e->getMessage());
            }
        }

        return self::$conexion;
    }
}
