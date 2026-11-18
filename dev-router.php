<?php

/*
 * Solo para desarrollo: php -S 127.0.0.1:8090 -t public dev-router.php
 * Sirve los archivos estáticos de public/ y manda el resto a index.php (como el .htaccess).
 */
$archivo = __DIR__ . '/public' . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (is_file($archivo) && ! str_ends_with($archivo, '.php')) {
    return false;
}

require __DIR__ . '/public/index.php';
