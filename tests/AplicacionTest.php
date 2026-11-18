<?php

declare(strict_types=1);

namespace Tests;

use Floreria\Arranque;
use Floreria\Web\Aplicacion;
use Floreria\Web\Respuesta;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AplicacionTest extends TestCase
{
    private CatalogoEnMemoria $catalogo;
    private Aplicacion $app;

    protected function setUp(): void
    {
        $this->catalogo = new CatalogoEnMemoria([
            CatalogoEnMemoria::producto('CRU-3001', 'cruces', 418.0),
            CatalogoEnMemoria::producto('MAT-2201', 'matrimonios', 0.0),
            CatalogoEnMemoria::producto('SHO-2301', 'showers', 190.0),
            CatalogoEnMemoria::producto('ANI-1501', 'aniversarios', 0.0),
            CatalogoEnMemoria::producto('COR-2901', 'coronas', 300.0, '<script>alert(1)</script> Rosas'),
        ]);
        $this->app = Arranque::aplicacion(dirname(__DIR__), ['api_url' => 'https://api.test'], $this->catalogo);
    }

    private function get(string $ruta, array $query = []): Respuesta
    {
        return $this->app->manejar('GET', $ruta, $query);
    }

    public function test_portada_y_paginas_fijas(): void
    {
        foreach (['/', '/nuestra-empresa', '/cuidado-de-las-flores', '/dedicatorias', '/contactenos', '/ofertas'] as $ruta) {
            $this->assertSame(200, $this->get($ruta)->estado, $ruta);
        }
        $this->assertStringContainsString('Bienvenidos a Florería Estherlicia', $this->get('/')->cuerpo);
    }

    public function test_listado_de_categoria_con_titulo_precio_y_enlace_de_pedido(): void
    {
        $html = $this->get('/categoria/cruces')->cuerpo;

        $this->assertStringContainsString('Defunciones / Cruces', $html);
        $this->assertStringContainsString('Código: CRU-3001', $html);
        $this->assertStringContainsString('S/. 418.00', $html);
        $this->assertStringContainsString('href="/pedido/CRU-3001"', $html);
    }

    public function test_consultar_precio_como_la_web_antigua(): void
    {
        $this->assertStringContainsString('(consultar precio)', $this->get('/categoria/showers')->cuerpo, 'showers siempre consultaba');
        $this->assertStringNotContainsString('S/. 190.00', $this->get('/categoria/showers')->cuerpo);
        $this->assertStringContainsString('(consultar precio)', $this->get('/categoria/aniversarios')->cuerpo, 'precio 0');
    }

    public function test_el_texto_de_la_api_se_escapa(): void
    {
        $html = $this->get('/categoria/coronas')->cuerpo;

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    public function test_paginacion_valida_el_numero(): void
    {
        $this->get('/categoria/cruces', ['pagina' => '3']);
        $this->get('/categoria/cruces', ['pagina' => '-1']);
        $this->get('/categoria/cruces', ['pagina' => ['x']]);
        $this->get('/categoria/cruces', ['pagina' => '99999']);

        $this->assertSame([3, 1, 1, 1], array_column($this->catalogo->llamadas, 2));
    }

    public function test_busqueda_por_codigo(): void
    {
        $this->assertStringContainsString('Código: CRU-3001', $this->get('/buscar', ['prefijo' => 'CRU-', 'numero' => '3001'])->cuerpo);
        $this->assertStringContainsString('No se encontró el código CRU-9999', $this->get('/buscar', ['prefijo' => 'CRU-', 'numero' => '9999'])->cuerpo);
    }

    /** @return iterable<string, array{array<string, mixed>}> */
    public static function busquedasInvalidas(): iterable
    {
        yield 'prefijo inventado' => [['prefijo' => "X'-", 'numero' => '1']];
        yield 'numero con letras' => [['prefijo' => 'CRU-', 'numero' => '30a1']];
        yield 'arrays' => [['prefijo' => ['CRU-'], 'numero' => ['1']]];
        yield 'vacio' => [[]];
    }

    #[DataProvider('busquedasInvalidas')]
    public function test_busqueda_invalida_no_consulta_la_api(array $query): void
    {
        $this->assertSame(200, $this->get('/buscar', $query)->estado);
        $this->assertSame([], $this->catalogo->llamadas);
    }

    public function test_tipo_y_precio_solo_aceptan_la_lista_blanca(): void
    {
        $this->assertSame(200, $this->get('/tipo/rosas')->estado);
        $this->assertSame(404, $this->get('/tipo/cactus')->estado);
        $this->assertSame(200, $this->get('/precio', ['rango' => '301-500'])->estado);
        $this->assertSame(404, $this->get('/precio', ['rango' => '1-999999'])->estado);
        $this->assertSame(['precio', '301-500', 1], $this->catalogo->llamadas[1]);
    }

    public function test_pedido(): void
    {
        $html = $this->get('/pedido/CRU-3001')->cuerpo;
        $this->assertStringContainsString('Producto: CRU-3001', $html);
        $this->assertStringContainsString('data-whatsapp="51966233373"', $html, 'el número recibe el código de país');

        $this->assertSame(404, $this->get('/pedido/NO-EXISTE')->estado);
        $this->assertSame(404, $this->get('/pedido/<script>')->estado);
    }

    /** @return iterable<string, array{string, array<string, mixed>, string}> */
    public static function urlsAntiguas(): iterable
    {
        yield 'portada' => ['/index.php', [], '/'];
        yield 'defunciones' => ['/prod-de.php', ['cate' => '18'], '/categoria/cruces'];
        yield 'matrimonios' => ['/prod-ev-matri.php', ['cate' => '5', 'moneda' => 'S/.'], '/categoria/matrimonios'];
        yield 'empresa' => ['/nuestra-empresa.php', [], '/nuestra-empresa'];
        yield 'tipo' => ['/busq-x-tipo.php', ['tipo' => 'rosas'], '/tipo/rosas'];
        yield 'ofertas' => ['/prod-ofer.php', ['cate' => '26'], '/ofertas'];
    }

    #[DataProvider('urlsAntiguas')]
    public function test_redirige_las_urls_de_la_web_antigua(string $ruta, array $query, string $destino): void
    {
        $r = $this->get($ruta, $query);

        $this->assertSame(301, $r->estado);
        $this->assertSame($destino, $r->cabeceras['Location']);
    }

    public function test_archivos_peligrosos_de_la_web_antigua_dan_404(): void
    {
        foreach (['/admin/wp-login.php', '/moist.php', '/StereosStarved.php', '/admin/productos-edit.php', '/prod-de.php'] as $ruta) {
            $this->assertSame(404, $this->get($ruta)->estado, $ruta);
        }
        $this->assertSame(404, $this->get('/prod-de.php', ['cate' => '999'])->estado);
    }

    public function test_solo_get(): void
    {
        $this->assertSame(405, $this->app->manejar('POST', '/', [])->estado);
    }

    public function test_numero_de_whatsapp(): void
    {
        $this->assertSame('51966233373', Arranque::numeroWhatsapp('966 233 373'));
        $this->assertSame('51966233373', Arranque::numeroWhatsapp('+51 966-233-373'));
        $this->assertSame('', Arranque::numeroWhatsapp(''));
    }
}
