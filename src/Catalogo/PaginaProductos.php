<?php

declare(strict_types=1);

namespace Floreria\Catalogo;

/** Una página del listado (la web antigua mostraba 12 productos por página). */
final class PaginaProductos
{
    /** @param list<Producto> $productos */
    public function __construct(
        public readonly array $productos,
        public readonly int $paginaActual,
        public readonly int $ultimaPagina,
        public readonly int $total,
    ) {
    }

    public static function vacia(): self
    {
        return new self([], 1, 1, 0);
    }

    /** @param array<string, mixed> $json */
    public static function fromApi(array $json): self
    {
        $productos = array_map(
            static fn (array $p): Producto => Producto::fromApi($p),
            array_values(array_filter($json['data'] ?? [], 'is_array')),
        );
        $meta = $json['meta'] ?? [];

        return new self(
            $productos,
            max(1, (int) ($meta['current_page'] ?? 1)),
            max(1, (int) ($meta['last_page'] ?? 1)),
            (int) ($meta['total'] ?? count($productos)),
        );
    }
}
