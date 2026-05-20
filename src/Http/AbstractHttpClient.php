<?php

declare(strict_types=1);

namespace Brown\Longbridge\Http;

use Brown\Longbridge\Exception\LongbridgeException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

abstract class AbstractHttpClient implements HttpClientInterface
{
    private Client $client;

    public function __construct(
        private readonly string $baseUrl,
        private readonly int $timeout = 15,
    ) {
        $this->client = new Client([
            'base_uri' => rtrim($this->baseUrl, '/'),
            'timeout' => $this->timeout,
            'http_errors' => false,
        ]);
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
        $method = strtoupper($method);
        $queryString = $this->buildQuery($query);
        $body = $this->buildBody($payload);

        $options = [
            'headers' => $this->headers($method, $uri, $queryString, $body),
        ];

        if ($queryString !== '') {
            $options['query'] = $queryString;
        }

        if ($body !== '') {
            $options['body'] = $body;
        }

        try {
            $response = $this->client->request($method, $uri, $options);
        } catch (GuzzleException $e) {
            throw new LongbridgeException(
                message: 'Longbridge request failed: ' . $e->getMessage(),
                uri: $uri,
                previous: $e,
            );
        }

        $statusCode = $response->getStatusCode();
        $responseBody = (string)$response->getBody();
        $json = json_decode($responseBody, true);

        if (!is_array($json)) {
            throw LongbridgeException::invalidJson($statusCode, $responseBody, $uri);
        }

        $code = (int)($json['code'] ?? -1);
        $message = (string)($json['msg'] ?? $json['message'] ?? '');
        $data = $json['data'] ?? [];

        if ($statusCode >= 400 || $code !== 0) {
            throw LongbridgeException::apiError(
                httpStatus: $statusCode,
                apiCode: $code !== -1 ? $code : $statusCode,
                message: $message !== '' ? $message : 'Longbridge API error',
                data: $data,
                responseBody: $responseBody,
                uri: $uri,
            );
        }

        return is_array($data) ? $data : [];
    }

    abstract protected function headers(
        string $method,
        string $uri,
        string $queryString,
        string $body
    ): array;

    protected function buildQuery(array $query): string
    {
        $pairs = [];

        foreach ($query as $key => $value) {
            $this->appendQueryValue((string)$key, $value, $pairs);
        }

        return implode('&', $pairs);
    }

    protected function pathForSign(string $uri): string
    {
        return parse_url($uri, PHP_URL_PATH) ?: $uri;
    }

    private function buildBody(array $payload): string
    {
        if (empty($payload)) {
            return '';
        }

        $body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($body === false) {
            throw new \InvalidArgumentException('request payload is not JSON encodable.');
        }

        return $body;
    }

    private function appendQueryValue(string $key, mixed $value, array &$pairs): void
    {
        if ($value === null) {
            return;
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                $this->appendQueryValue($key, $item, $pairs);
            }
            return;
        }

        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }

        $pairs[] = rawurlencode($key) . '=' . rawurlencode((string)$value);
    }
}
