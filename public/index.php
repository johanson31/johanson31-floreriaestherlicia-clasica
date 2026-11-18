<?php

declare(strict_types=1);

/*
 * Único punto de entrada. El resto del código (src/, templates/, config/,
 * storage/) vive FUERA de la carpeta pública y no se puede pedir por URL.
 */

// Dos formas de desplegar, sin editar este archivo:
//  - el dominio apunta a clasica/public       -> el código está en la carpeta de arriba
//  - public/ copiado dentro de public_html     -> el código está en ~/clasica (hermana de public_html)
$raiz = is_dir(dirname(__DIR__) . '/src') ? dirname(__DIR__) : dirname(__DIR__) . '/clasica';

// Autocarga mínima sin Composer: Floreria\X\Y -> src/X/Y.php
spl_autoload_register(static function (string $clase) use ($raiz): void {
    if (str_starts_with($clase, 'Floreria\\')) {
        $archivo = $raiz . '/src/' . str_replace('\\', '/', substr($clase, 9)) . '.php';
        if (is_file($archivo)) {
            require $archivo;
        }
    }
});
require $raiz . '/src/funciones.php';

$config = is_file($raiz . '/config/config.php')
    ? require $raiz . '/config/config.php'
    : require $raiz . '/config/config.example.php';

ini_set('display_errors', ! empty($config['debug']) ? '1' : '0');
Floreria\Arranque::cabecerasDeSeguridad((string) $config['api_url']);

try {
    $ruta = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    Floreria\Arranque::aplicacion($raiz, $config)
        ->manejar($_SERVER['REQUEST_METHOD'] ?? 'GET', $ruta, $_GET)
        ->enviar();
} catch (Throwable $e) {
    error_log('[floreria] ' . $e);
    http_response_code(500);
    echo ! empty($config['debug'])
        ? '<pre>' . htmlspecialchars((string) $e, ENT_QUOTES, 'UTF-8') . '</pre>'
        : 'Disculpe, ocurrió un error. Llámenos al 349-9273.';
}
