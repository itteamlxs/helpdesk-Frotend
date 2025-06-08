<?php
// /debug_session.php
session_start();

header('Content-Type: text/html; charset=utf-8');
echo "<h2>Debug Sesión</h2>";

echo "<h3>Variables de sesión:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Datos del usuario:</h3>";
if (isset($_SESSION['usuario'])) {
    echo "✅ Usuario encontrado:<br>";
    echo "ID: " . $_SESSION['usuario']['id'] . "<br>";
    echo "Nombre: " . $_SESSION['usuario']['nombre'] . "<br>";
    echo "Rol: " . $_SESSION['usuario']['rol_id'] . "<br>";
    echo "Correo: " . $_SESSION['usuario']['correo'] . "<br>";
} else {
    echo "❌ No hay usuario en sesión<br>";
    echo "¿Estás logueado?<br>";
}

echo "<h3>Session ID:</h3>";
echo session_id();
?>