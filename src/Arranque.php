<?php

declare(strict_types=1);

namespace Floreria;

use Floreria\Catalogo\ApiCatalogoRepository;
use Floreria\Catalogo\CatalogoRepository;
use Floreria\Http\CurlHttpClient;
use Floreria\Support\FileCache;
use Floreria\Web\Aplicacion;
use Floreria\Web\Vista;

/**
 * Composition root: el único lugar que conoce las clases concretas.
 * El resto del código recibe sus dependencias por constructor.
 */
final class Arranque
{
    /** @param array<string, mixed> $config */
    public static function aplicacion(string $raiz, array $config, ?CatalogoRepository $catalogo = null): Aplicacion
    {
        $apiUrl = rtrim((string) $config['api_url'], '/');

        $catalogo ??= new ApiCatalogoRepository(
            new CurlHttpClient(),
            new FileCache($raiz . '/storage/cache'),
            $apiUrl,
            max(1, (int) ($config['cache_minutos'] ?? 5)) * 60,
        );

        $sitio = $catalogo->datosDelSitio();

        $vista = new Vista($raiz . '/templates', [
            'apiUrl'   => $apiUrl,
            'whatsapp' => self::numeroWhatsapp($sitio['whatsapp_contacto'] ?? '') ?: self::numeroWhatsapp((string) ($config['whatsapp'] ?? '')),
        ]);

        return new Aplicacion($catalogo, $vista);
    }

    /**
     * wa.me exige el número con código de país y solo dígitos.
     * Un celular peruano escrito sin código (966233373) recibe el 51 delante.
     */
    public static function numeroWhatsapp(string $numero): string
    {
        $digitos = (string) preg_replace('/\D+/', '', $numero);

        return preg_match('/^9\d{8}$/', $digitos) === 1 ? '51' . $digitos : $digitos;
    }

    /** Cabeceras de seguridad. CSP estricta: solo recursos propios y la API. */
    public static function cabecerasDeSeguridad(string $apiUrl): void
    {
        $api = parse_url($apiUrl, PHP_URL_SCHEME) . '://' . parse_url($apiUrl, PHP_URL_HOST)
            . (parse_url($apiUrl, PHP_URL_PORT) ? ':' . parse_url($apiUrl, PHP_URL_PORT) : '');

        header("Content-Security-Policy: default-src 'self'; img-src 'self' {$api} data:; connect-src 'self' {$api}; "
            . "script-src 'self'; style-src 'self'; font-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self'; object-src 'none'");
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        if (($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') {
            header('Strict-Transport-Security: max-age=31536000');
        }
        header_remove('X-Powered-By');
    }
}
