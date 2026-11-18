<?php

declare(strict_types=1);

namespace Floreria\Catalogo;

/**
 * Producto tal como lo muestra la web: inmutable y sin dependencias de la API.
 *
 * Por qué un DTO propio y no el array del JSON: las plantillas no conocen el
 * formato de la API; si el contrato cambia, solo cambia fromApi().
 */
final class Producto
{
    public function __construct(
        public readonly string $codigo,
        public readonly string $nombre,
        public readonly string $descripcion,
        public readonly float $precio,
        public readonly string $fotoMiniatura,
        public readonly string $fotoGrande,
        public readonly string $categoriaSlug,
        public readonly string $categoriaNombre,
    ) {
    }

    /** @param array<string, mixed> $d */
    public static function fromApi(array $d): self
    {
        return new self(
            codigo: (string) ($d['codigo'] ?? ''),
            nombre: (string) ($d['nombre'] ?? ''),
            descripcion: (string) ($d['descripcion'] ?? ''),
            precio: (float) ($d['precio_final'] ?? $d['precio'] ?? 0),
            fotoMiniatura: self::urlSegura($d['foto_miniatura_url'] ?? ''),
            fotoGrande: self::urlSegura($d['foto_grande_url'] ?? ''),
            categoriaSlug: (string) ($d['categoria']['slug'] ?? ''),
            categoriaNombre: (string) ($d['categoria']['nombre'] ?? ''),
        );
    }

    public function tienePrecio(): bool
    {
        return $this->precio > 0;
    }

    public function precioFormateado(): string
    {
        return 'S/. ' . number_format($this->precio, 2, '.', ',');
    }

    /** Solo se aceptan URLs http(s): evita "javascript:" u otros esquemas en src/href. */
    private static function urlSegura(mixed $url): string
    {
        $url = is_string($url) ? $url : '';

        return preg_match('#^https?://#i', $url) === 1 ? $url : '';
    }
}
