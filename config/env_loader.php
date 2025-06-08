<?php
function cargarEnv($ruta = __DIR__ . '/../.env') {
    if (!file_exists($ruta)) return;

    $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lineas as $linea) {
        if (str_starts_with(trim($linea), '#')) continue;
        list($clave, $valor) = explode('=', $linea, 2);
        putenv(trim($clave) . '=' . trim($valor));
    }
}

// Llamar automáticamente al cargar este archivo
cargarEnv();