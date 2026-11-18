<?php

declare(strict_types=1);

namespace Floreria\Catalogo;

/**
 * Contrato del catálogo (Repository + Inversión de Dependencias).
 *
 * Las páginas dependen de esta interfaz, no de la API: en los tests se usa un
 * repositorio en memoria y, si mañana el catálogo viene de otra fuente, solo se
 * escribe otra implementación.
 */
interface CatalogoRepository
{
    public function porCategoria(string $slug, int $pagina): PaginaProductos;

    public function porTipo(string $tipo, int $pagina): PaginaProductos;

    public function porPrecio(int $minimo, ?int $maximo, int $pagina): PaginaProductos;

    public function buscarPorCodigo(string $codigo): ?Producto;

    /**
     * Datos de contacto configurados en el admin (whatsapp_contacto, email, ...).
     *
     * @return array<string, string>
     */
    public function datosDelSitio(): array;
}
