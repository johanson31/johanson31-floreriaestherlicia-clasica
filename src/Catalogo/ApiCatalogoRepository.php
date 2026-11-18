<?php

declare(strict_types=1);

namespace Floreria\Catalogo;

use Floreria\Http\HttpClient;
use Floreria\Http\HttpException;
use Floreria\Support\Cache;

/**
 * Catálogo leído de la API pública de Laravel (/api/catalogo).
 *
 * La web clásica no tiene base de datos ni credenciales: solo lee la API.
 * Cada respuesta se guarda en caché unos minutos; si la API se cae, se sirve la
 * última copia guardada en vez de una página en blanco.
 */
final class ApiCatalogoRepository implements CatalogoRepository
{
    private const POR_PAGINA = 12;

    public function __construct(
        private readonly HttpClient $http,
        private readonly Cache $cache,
        private readonly string $apiUrl,
        private readonly int $ttlSegundos = 300,
    ) {
    }

    public function porCategoria(string $slug, int $pagina): PaginaProductos
    {
        return $this->listar(['categoria_slug' => $slug], $pagina);
    }

    public function porTipo(string $tipo, int $pagina): PaginaProductos
    {
        return $this->listar(['tipo' => $tipo], $pagina);
    }

    public function porPrecio(int $minimo, ?int $maximo, int $pagina): PaginaProductos
    {
        return $this->listar(array_filter(
            ['precio_min' => $minimo, 'precio_max' => $maximo],
            static fn (?int $v): bool => $v !== null,
        ), $pagina);
    }

    public function buscarPorCodigo(string $codigo): ?Producto
    {
        $json = $this->obtener('/api/catalogo/productos', ['buscar' => $codigo, 'per_page' => 50]);

        foreach (PaginaProductos::fromApi($json)->productos as $producto) {
            // La API busca por "contiene"; la web antigua mostraba solo el código exacto.
            if (strcasecmp($producto->codigo, $codigo) === 0) {
                return $producto;
            }
        }

        return null;
    }

    public function datosDelSitio(): array
    {
        $datos = $this->obtener('/api/config/site')['data'] ?? [];

        return array_map('strval', array_filter(is_array($datos) ? $datos : [], 'is_scalar'));
    }

    /** @param array<string, scalar> $filtros */
    private function listar(array $filtros, int $pagina): PaginaProductos
    {
        $json = $this->obtener('/api/catalogo/productos', $filtros + [
            'orden'    => 'codigo',
            'per_page' => self::POR_PAGINA,
            'page'     => max(1, $pagina),
        ]);

        return PaginaProductos::fromApi($json);
    }

    /**
     * @param  array<string, scalar>  $query
     * @return array<string, mixed>
     */
    private function obtener(string $ruta, array $query = []): array
    {
        ksort($query);
        $url = rtrim($this->apiUrl, '/') . $ruta . ($query ? '?' . http_build_query($query) : '');

        try {
            return $this->cache->recordar('api:' . $url, $this->ttlSegundos, fn (): array => $this->http->getJson($url));
        } catch (HttpException) {
            // Sin copia en caché y con la API caída: listado vacío en lugar de un error 500.
            return [];
        }
    }
}
