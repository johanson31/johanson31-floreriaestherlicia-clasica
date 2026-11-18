<?php

declare(strict_types=1);

namespace Floreria\Web;

use RuntimeException;

/**
 * Plantillas PHP planas dentro de un layout común.
 * Todo texto variable se imprime con e(): escapa HTML, así no hay XSS.
 */
final class Vista
{
    /** @param array<string, mixed> $global datos disponibles en todas las plantillas */
    public function __construct(
        private readonly string $directorio,
        private readonly array $global = [],
    ) {
    }

    /** @param array<string, mixed> $datos */
    public function pagina(string $plantilla, array $datos = []): string
    {
        $datos += $this->global;
        $datos['contenido'] = $this->parcial($plantilla, $datos);

        return $this->parcial('layout', $datos);
    }

    /** @param array<string, mixed> $datos */
    public function parcial(string $plantilla, array $datos = []): string
    {
        if (preg_match('#^[a-z0-9/_-]+$#', $plantilla) !== 1) {
            throw new RuntimeException("Plantilla inválida: {$plantilla}");
        }
        $archivo = $this->directorio . '/' . $plantilla . '.php';
        $vista = $this;
        $datos += $this->global;

        ob_start();
        try {
            (static function (string $__archivo, array $__datos, Vista $vista): void {
                extract($__datos, EXTR_SKIP);
                require $__archivo;
            })($archivo, $datos, $vista);
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return (string) ob_get_clean();
    }
}
