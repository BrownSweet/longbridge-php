<?php

declare(strict_types=1);

namespace Brown\Longbridge\Tests\Support;

use Brown\Longbridge\Http\HttpClientInterface;
use RuntimeException;

final class SpyHttpClient implements HttpClientInterface
{
    /** @var list<array{method:string,uri:string,query:array,payload:array}> */
    public array $calls = [];

    /** @var list<array> */
    private array $responses;

    public function __construct(array $responses = [[]])
    {
        $this->responses = $responses;
    }

    public function get(string $uri, array $query = []): array
    {
        return $this->request('GET', $uri, $query);
    }

    public function post(string $uri, array $payload = []): array
    {
        return $this->request('POST', $uri, [], $payload);
    }

    public function put(string $uri, array $payload = []): array
    {
        return $this->request('PUT', $uri, [], $payload);
    }

    public function delete(string $uri, array $query = [], array $payload = []): array
    {
        return $this->request('DELETE', $uri, $query, $payload);
    }

    public function request(
        string $method,
        string $uri,
        array $query = [],
        array $payload = [],
    ): array {
        $this->calls[] = [
            'method' => strtoupper($method),
            'uri' => $uri,
            'query' => $query,
            'payload' => $payload,
        ];

        return array_shift($this->responses) ?? [];
    }

    public function lastCall(): array
    {
        $key = array_key_last($this->calls);
        if ($key === null) {
            throw new RuntimeException('No HTTP calls were recorded.');
        }

        return $this->calls[$key];
    }
}
