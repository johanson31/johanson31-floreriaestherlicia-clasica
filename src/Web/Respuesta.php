<?php

declare(strict_types=1);

namespace Floreria\Web;

final class Respuesta
{
    /** @param array<string, string> $cabeceras */
    private function __construct(
        public readonly int $estado,
        public readonly string $cuerpo,
        public readonly array $cabeceras = [],
    ) {
    }

    public static function html(string $html, int $estado = 200): self
    {
        return new self($estado, $html, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public static function redirigir(string $ruta, int $estado = 301): self
    {
        return new self($estado, '', ['Location' => $ruta]);
    }

    public function enviar(): void
    {
        http_response_code($this->estado);
        foreach ($this->cabeceras as $nombre => $valor) {
            header("{$nombre}: {$valor}");
        }
        echo $this->cuerpo;
    }
}
