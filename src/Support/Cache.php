<?php

declare(strict_types=1);

namespace Floreria\Support;

interface Cache
{
    /**
     * Devuelve el valor guardado o lo calcula y guarda.
     * Si el cálculo falla y existe una copia vencida, devuelve esa copia.
     *
     * @template T of array
     *
     * @param  callable(): T  $calcular
     * @return T
     */
    public function recordar(string $clave, int $ttlSegundos, callable $calcular): array;
}
