<?php

declare(strict_types=1);

namespace Floreria\Http;

/**
 * Cliente HTTP mínimo con cURL (viene en cualquier hosting con PHP).
 * Sin dependencias de Composer: la web se sube tal cual, sin vendor/.
 */
final class CurlHttpClient implements HttpClient
{
    public function __construct(private readonly int $timeoutSegundos = 8)
    {
    }

    public function getJson(string $url): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_PROTOCOLS      => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_TIMEOUT        => $this->timeoutSegundos,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            CURLOPT_USERAGENT      => 'FloreriaClasica/1.0',
        ]);

        $cuerpo = curl_exec($ch);
        $codigo = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($cuerpo === false || $codigo < 200 || $codigo >= 300) {
            throw new HttpException("GET {$url} falló ({$codigo}) {$error}");
        }

        $json = json_decode((string) $cuerpo, true);
        if (! is_array($json)) {
            throw new HttpException("GET {$url}: la respuesta no es JSON");
        }

        return $json;
    }
}
