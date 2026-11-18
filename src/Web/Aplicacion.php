<?php

declare(strict_types=1);

namespace Floreria\Web;

use Floreria\Catalogo\CatalogoRepository;
use Floreria\Catalogo\PaginaProductos;

/**
 * Front controller: una ruta = un método. Todo parámetro de entrada se valida
 * contra una lista blanca o un patrón antes de usarse; nada llega crudo a la API
 * ni al HTML.
 */
final class Aplicacion
{
    /** Páginas de contenido fijo: ruta => [plantilla, título]. */
    private const PAGINAS = [
        '/nuestra-empresa'       => ['paginas/empresa', 'Nuestra Empresa'],
        '/cuidado-de-las-flores' => ['paginas/cuidado', 'Cuidado de flores'],
        '/dedicatorias'          => ['paginas/dedicatorias', 'Dedicatorias'],
        '/contactenos'           => ['contacto', 'Contáctenos'],
    ];

    /** URLs de la web antigua => nuevas (los enlaces guardados y Google no se pierden). */
    private const LEGADO = [
        '/index.php'             => '/',
        '/nuestra-empresa.php'   => '/nuestra-empresa',
        '/contactenos.php'       => '/contactenos',
        '/cuidado-de-flores.php' => '/cuidado-de-las-flores',
        '/dedicatorias.php'      => '/dedicatorias',
        '/prod-ofer.php'         => '/ofertas',
        '/gracias-form.php'      => '/',
        '/gracias-pedido.php'    => '/',
    ];

    public function __construct(
        private readonly CatalogoRepository $catalogo,
        private readonly Vista $vista,
    ) {
    }

    /** @param array<string, mixed> $query */
    public function manejar(string $metodo, string $ruta, array $query): Respuesta
    {
        if ($metodo !== 'GET' && $metodo !== 'HEAD') {
            return $this->noEncontrado(405);
        }

        $ruta = '/' . trim($ruta, '/');

        return match (true) {
            $ruta === '/'                                      => $this->inicio(),
            isset(self::PAGINAS[$ruta])                        => $this->estatica(...self::PAGINAS[$ruta]),
            $ruta === '/ofertas'                               => $this->ofertas(),
            $ruta === '/buscar'                                => $this->buscar($query),
            $ruta === '/precio'                                => $this->porPrecio($query),
            (bool) preg_match('#^/categoria/([a-z0-9-]{1,60})$#', $ruta, $m) => $this->categoria($m[1], $query),
            (bool) preg_match('#^/tipo/([a-z]{1,20})$#', $ruta, $m)          => $this->porTipo($m[1], $query),
            (bool) preg_match('#^/pedido/([A-Z0-9-]{3,20})$#', $ruta, $m)    => $this->pedido($m[1]),
            default                                            => $this->legado($ruta, $query),
        };
    }

    private function inicio(): Respuesta
    {
        return Respuesta::html($this->vista->pagina('inicio', ['titulo' => null]));
    }

    private function estatica(string $plantilla, string $titulo): Respuesta
    {
        return Respuesta::html($this->vista->pagina($plantilla, ['titulo' => $titulo]));
    }

    /** @param array<string, mixed> $query */
    private function categoria(string $slug, array $query): Respuesta
    {
        $categoria = Menu::categoria($slug);
        if ($categoria === null) {
            return $this->noEncontrado();
        }
        $pagina = $this->catalogo->porCategoria($slug, $this->numeroPagina($query));

        return $this->listado(
            titulo: "{$categoria['grupo']} / {$categoria['nombre']}",
            tituloDocumento: $categoria['nombre'],
            frase: $categoria['frase'],
            pagina: $pagina,
            rutaBase: "/categoria/{$slug}",
        );
    }

    /** @param array<string, mixed> $query */
    private function porTipo(string $tipo, array $query): Respuesta
    {
        if (! isset(Menu::TIPOS[$tipo])) {
            return $this->noEncontrado();
        }

        return $this->listado(
            titulo: 'Resultados de búsqueda por tipo: ' . Menu::TIPOS[$tipo],
            tituloDocumento: Menu::TIPOS[$tipo],
            frase: Menu::INTRO,
            pagina: $this->catalogo->porTipo($tipo, $this->numeroPagina($query)),
            rutaBase: "/tipo/{$tipo}",
        );
    }

    /** @param array<string, mixed> $query */
    private function porPrecio(array $query): Respuesta
    {
        $clave = is_string($query['rango'] ?? null) ? $query['rango'] : '';
        if (! isset(Menu::RANGOS_PRECIO[$clave])) {
            return $this->noEncontrado();
        }
        [$min, $max] = Menu::RANGOS_PRECIO[$clave];

        return $this->listado(
            titulo: 'Resultados de búsqueda por precio: S/. ' . $min . ' - ' . ($max ?? 'a más'),
            tituloDocumento: 'Precios',
            frase: Menu::INTRO,
            pagina: $this->catalogo->porPrecio($min, $max, $this->numeroPagina($query)),
            rutaBase: '/precio',
            queryExtra: ['rango' => $clave],
        );
    }

    /** @param array<string, mixed> $query */
    private function buscar(array $query): Respuesta
    {
        $prefijo = is_string($query['prefijo'] ?? null) ? $query['prefijo'] : '';
        $numero = is_string($query['numero'] ?? null) ? trim($query['numero']) : '';
        $valido = in_array($prefijo, Menu::PREFIJOS, true) && preg_match('/^\d{1,6}$/', $numero) === 1;
        $producto = $valido ? $this->catalogo->buscarPorCodigo($prefijo . $numero) : null;

        $productos = $producto ? [$producto] : [];

        return $this->listado(
            titulo: 'Resultados de búsqueda',
            tituloDocumento: 'Buscar',
            frase: $valido ? '' : 'Ingrese el código del producto. Ej. ANI-1503',
            pagina: new PaginaProductos($productos, 1, 1, count($productos)),
            rutaBase: '/buscar',
            sinResultados: $valido ? 'No se encontró el código ' . $prefijo . $numero . '.' : null,
        );
    }

    private function ofertas(): Respuesta
    {
        $pagina = $this->catalogo->porCategoria('oferta', 1);

        return Respuesta::html($this->vista->pagina('ofertas', ['titulo' => 'Ofertas', 'pagina' => $pagina]));
    }

    private function pedido(string $codigo): Respuesta
    {
        $producto = $this->catalogo->buscarPorCodigo($codigo);
        if ($producto === null) {
            return $this->noEncontrado();
        }

        return Respuesta::html($this->vista->pagina('pedido', [
            'titulo'          => 'Pedido ' . $producto->codigo,
            'producto'        => $producto,
            'consultarPrecio' => Menu::consultarPrecio($producto->categoriaSlug),
        ]));
    }

    /** @param array<string, string> $queryExtra */
    private function listado(
        string $titulo,
        string $tituloDocumento,
        string $frase,
        PaginaProductos $pagina,
        string $rutaBase,
        array $queryExtra = [],
        ?string $sinResultados = null,
    ): Respuesta {
        return Respuesta::html($this->vista->pagina('listado', [
            'titulo'        => $tituloDocumento,
            'encabezado'    => $titulo,
            'frase'         => $frase,
            'pagina'        => $pagina,
            'rutaBase'      => $rutaBase,
            'queryExtra'    => $queryExtra,
            'sinResultados' => $sinResultados ?? 'Por el momento no hay productos en esta sección.',
        ]));
    }

    /** @param array<string, mixed> $query */
    private function legado(string $ruta, array $query): Respuesta
    {
        if (isset(self::LEGADO[$ruta])) {
            return Respuesta::redirigir(self::LEGADO[$ruta]);
        }

        // prod-cs.php?cate=3, prod-de.php?cate=18, ... -> /categoria/{slug}
        if (preg_match('#^/prod-[a-z0-9 -]+\.php$#', $ruta) === 1 && is_numeric($query['cate'] ?? null)) {
            $slug = Menu::slugPorId((int) $query['cate']);
            if ($slug !== null) {
                return Respuesta::redirigir("/categoria/{$slug}");
            }
        }

        if ($ruta === '/busq-x-tipo.php' && is_string($query['tipo'] ?? null) && isset(Menu::TIPOS[$query['tipo']])) {
            return Respuesta::redirigir('/tipo/' . $query['tipo']);
        }

        return $this->noEncontrado();
    }

    /** @param array<string, mixed> $query */
    private function numeroPagina(array $query): int
    {
        $p = filter_var($query['pagina'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 500]]);

        return $p === false ? 1 : $p;
    }

    private function noEncontrado(int $estado = 404): Respuesta
    {
        return Respuesta::html($this->vista->pagina('404', ['titulo' => 'Página no encontrada']), $estado);
    }
}
