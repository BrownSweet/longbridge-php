<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */

declare(strict_types=1);

namespace Brown\Longbridge\Http;

use Brown\Longbridge\Exception\LongbridgeException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

final class OAuthHttpClient
{
    private Client $client;

    public function __construct(
        private readonly string $baseUrl,
        private string $accessToken,
        private readonly int $timeout = 15,
    ) {
        if (trim($this->accessToken) === '') {
            throw new \InvalidArgumentException('accessToken is empty.');
        }

        $this->client = new Client([
            'base_uri' => rtrim($this->baseUrl, '/'),
            'timeout' => $this->timeout,
            'http_errors' => false,
        ]);
    }

    public function setAccessToken(string $accessToken): void
    {
        if (trim($accessToken) === '') {
            throw new \InvalidArgumentException('accessToken is empty.');
        }

        $this->accessToken = $accessToken;
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
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json; charset=utf-8',
            ],
        ];

        $queryString = $this->buildQuery($query);
        if ($queryString !== '') {
            $options['query'] = $queryString;
        }

        if (!empty($payload)) {
            $options['json'] = $payload;
        }

        try {
            $response = $this->client->request(strtoupper($method), $uri, $options);
        } catch (GuzzleException $e) {
            throw new LongbridgeException(
                message: 'Longbridge request failed: ' . $e->getMessage(),
                uri: $uri,
                previous: $e,
            );
        }

        $statusCode = $response->getStatusCode();
        $body = (string)$response->getBody();
        $json = json_decode($body, true);

        if (!is_array($json)) {
            throw LongbridgeException::invalidJson($statusCode, $body, $uri);
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
                responseBody: $body,
                uri: $uri,
            );
        }

        return is_array($data) ? $data : [];
    }

    private function buildQuery(array $query): string
    {
        $pairs = [];

        foreach ($query as $key => $value) {
            $this->appendQueryValue((string)$key, $value, $pairs);
        }

        return implode('&', $pairs);
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
