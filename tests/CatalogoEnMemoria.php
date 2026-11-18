<?php

declare(strict_types=1);

namespace Tests;

use Floreria\Catalogo\CatalogoRepository;
use Floreria\Catalogo\PaginaProductos;
use Floreria\Catalogo\Producto;

/** Doble de prueba: gracias a la interfaz, el router se prueba sin red ni API. */
final class CatalogoEnMemoria implements CatalogoRepository
{
    /** @var list<array{string, string, int|null}> llamadas registradas */
    public array $llamadas = [];

    /** @param list<Producto> $productos */
    public function __construct(private readonly array $productos = [])
    {
    }

    public static function producto(string $codigo, string $slug, float $precio = 418.0, string $descripcion = 'Rosas'): Producto
    {
        return new Producto($codigo, "Arreglo {$codigo}", $descripcion, $precio, 'https://api.test/c.jpg', 'https://api.test/g.jpg', $slug, ucfirst($slug));
    }

    public function porCategoria(string $slug, int $pagina): PaginaProductos
    {
        $this->llamadas[] = ['categoria', $slug, $pagina];

        return $this->pagina(fn (Producto $p) => $p->categoriaSlug === $slug);
    }

    public function porTipo(string $tipo, int $pagina): PaginaProductos
    {
        $this->llamadas[] = ['tipo', $tipo, $pagina];

        return $this->pagina(fn () => true);
    }

    public function porPrecio(int $minimo, ?int $maximo, int $pagina): PaginaProductos
    {
        $this->llamadas[] = ['precio', "{$minimo}-{$maximo}", $pagina];

        return $this->pagina(fn (Producto $p) => $p->precio >= $minimo && ($maximo === null || $p->precio <= $maximo));
    }

    public function buscarPorCodigo(string $codigo): ?Producto
    {
        $this->llamadas[] = ['codigo', $codigo, null];
        foreach ($this->productos as $p) {
            if ($p->codigo === $codigo) {
                return $p;
            }
        }

        return null;
    }

    public function datosDelSitio(): array
    {
        return ['whatsapp_contacto' => '966233373'];
    }

    private function pagina(callable $filtro): PaginaProductos
    {
        $lista = array_values(array_filter($this->productos, $filtro));

        return new PaginaProductos($lista, 1, 1, count($lista));
    }
}
