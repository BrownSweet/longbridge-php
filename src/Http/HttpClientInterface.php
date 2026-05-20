<?php

declare(strict_types=1);

namespace Brown\Longbridge\Http;

interface HttpClientInterface
{
    public function get(string $uri, array $query = []): array;

    public function post(string $uri, array $payload = []): array;

    public function put(string $uri, array $payload = []): array;

    public function delete(string $uri, array $query = [], array $payload = []): array;

    public function request(
        string $method,
        string $uri,
        array $query = [],
        array $payload = [],
    ): array;
}
