<?php

declare(strict_types=1);

/** Escapa texto para HTML (contenido y atributos). */
function e(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
