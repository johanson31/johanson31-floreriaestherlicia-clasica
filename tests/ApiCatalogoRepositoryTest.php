<?php

declare(strict_types=1);

namespace Tests;

use Floreria\Catalogo\ApiCatalogoRepository;
use Floreria\Catalogo\Producto;
use Floreria\Http\HttpClient;
use Floreria\Http\HttpException;
use Floreria\Support\FileCache;
use PHPUnit\Framework\TestCase;

final class ApiCatalogoRepositoryTest extends TestCase
{
    private string $dirCache;

    protected function setUp(): void
    {
        $this->dirCache = sys_get_temp_dir() . '/floreria-test-' . bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->dirCache . '/*') ?: []);
        @rmdir($this->dirCache);
    }

    /** @param callable(string): array<string, mixed> $responder */
    private function http(callable $responder): HttpClient
    {
        return new class ($responder) implements HttpClient {
            /** @var list<string> */
            public array $urls = [];

            public function __construct(private $responder)
            {
            }

            public function getJson(string $url): array
            {
                $this->urls[] = $url;

                return ($this->responder)($url);
            }
        };
    }

    private function producto(string $codigo): array
    {
        return [
            'codigo' => $codigo, 'nombre' => 'X', 'descripcion' => 'Rosas', 'precio_final' => 418,
            'foto_miniatura_url' => 'https://api.test/storage/productos/a.jpg', 'foto_grande_url' => 'javascript:alert(1)',
            'categoria' => ['slug' => 'cruces', 'nombre' => 'Cruces'],
        ];
    }

    public function test_listado_pide_12_por_pagina_ordenado_por_codigo(): void
    {
        $http = $this->http(fn () => ['data' => [$this->producto('CRU-3001')], 'meta' => ['current_page' => 2, 'last_page' => 3, 'total' => 30]]);
        $repo = new ApiCatalogoRepository($http, new FileCache($this->dirCache), 'https://api.test/');

        $pagina = $repo->porCategoria('cruces', 2);

        $this->assertSame('https://api.test/api/catalogo/productos?categoria_slug=cruces&orden=codigo&page=2&per_page=12', $http->urls[0]);
        $this->assertSame(2, $pagina->paginaActual);
        $this->assertSame(3, $pagina->ultimaPagina);
        $this->assertInstanceOf(Producto::class, $pagina->productos[0]);
    }

    public function test_urls_que_no_son_http_se_descartan(): void
    {
        $p = Producto::fromApi($this->producto('CRU-3001'));

        $this->assertSame('https://api.test/storage/productos/a.jpg', $p->fotoMiniatura);
        $this->assertSame('', $p->fotoGrande);
    }

    public function test_busqueda_devuelve_solo_el_codigo_exacto(): void
    {
        $http = $this->http(fn () => ['data' => [$this->producto('CRU-30011'), $this->producto('CRU-3001')]]);
        $repo = new ApiCatalogoRepository($http, new FileCache($this->dirCache), 'https://api.test');

        $this->assertSame('CRU-3001', $repo->buscarPorCodigo('CRU-3001')?->codigo);
        $this->assertNull($repo->buscarPorCodigo('CRU-300'));
    }

    public function test_usa_la_cache_y_sirve_la_copia_si_la_api_cae(): void
    {
        $caida = false;
        $http = $this->http(function () use (&$caida) {
            if ($caida) {
                throw new HttpException('API caída');
            }

            return ['data' => [$this->producto('CRU-3001')], 'meta' => ['total' => 1]];
        });
        // TTL 0: cada lectura intenta refrescar, así se prueba el respaldo.
        $repo = new ApiCatalogoRepository($http, new FileCache($this->dirCache), 'https://api.test', 0);

        $this->assertCount(1, $repo->porCategoria('cruces', 1)->productos);
        $caida = true;
        $this->assertCount(1, $repo->porCategoria('cruces', 1)->productos, 'copia guardada');
        $this->assertSame([], $repo->porCategoria('coronas', 1)->productos, 'sin copia: vacío, no error');
    }

    public function test_datos_del_sitio_solo_escalares(): void
    {
        $http = $this->http(fn () => ['data' => ['whatsapp_contacto' => '966233373', 'raro' => ['x']]]);
        $repo = new ApiCatalogoRepository($http, new FileCache($this->dirCache), 'https://api.test');

        $this->assertSame(['whatsapp_contacto' => '966233373'], $repo->datosDelSitio());
    }
}
