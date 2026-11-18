<?php

declare(strict_types=1);

namespace Floreria\Http;

interface HttpClient
{
    /**
     * @return array<string, mixed>
     *
     * @throws HttpException si la respuesta no es 2xx o no es JSON
     */
    public function getJson(string $url): array;
}
