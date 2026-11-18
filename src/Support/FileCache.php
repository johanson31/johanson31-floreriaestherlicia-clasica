<?php

declare(strict_types=1);

namespace Floreria\Support;

use Throwable;

/**
 * Caché en archivos JSON (nunca serialize(): leer datos serializados es un vector
 * conocido de ejecución de código). La carpeta va fuera de la raíz pública.
 */
final class FileCache implements Cache
{
    public function __construct(private readonly string $directorio)
    {
    }

    public function recordar(string $clave, int $ttlSegundos, callable $calcular): array
    {
        $archivo = $this->directorio . '/' . hash('sha256', $clave) . '.json';
        $guardado = $this->leer($archivo);

        if ($guardado !== null && $guardado['expira'] > time()) {
            return $guardado['valor'];
        }

        try {
            $valor = $calcular();
        } catch (Throwable $e) {
            if ($guardado !== null) {
                return $guardado['valor']; // API caída: mejor datos de hace unos minutos que nada
            }
            throw $e;
        }

        $this->escribir($archivo, ['expira' => time() + $ttlSegundos, 'valor' => $valor]);

        return $valor;
    }

    /** @return array{expira: int, valor: array<mixed>}|null */
    private function leer(string $archivo): ?array
    {
        if (! is_file($archivo)) {
            return null;
        }
        $datos = json_decode((string) file_get_contents($archivo), true);

        return is_array($datos) && isset($datos['expira'], $datos['valor']) && is_array($datos['valor'])
            ? ['expira' => (int) $datos['expira'], 'valor' => $datos['valor']]
            : null;
    }

    /** @param array<string, mixed> $datos */
    private function escribir(string $archivo, array $datos): void
    {
        if (! is_dir($this->directorio)) {
            @mkdir($this->directorio, 0750, true);
        }
        // Escritura atómica: otro visitante nunca lee un archivo a medio escribir.
        $temporal = $archivo . '.' . bin2hex(random_bytes(4));
        if (@file_put_contents($temporal, json_encode($datos, JSON_UNESCAPED_UNICODE)) !== false) {
            @rename($temporal, $archivo);
        }
    }
}
