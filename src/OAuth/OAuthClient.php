<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */

declare(strict_types=1);

namespace Brown\Longbridge\OAuth;

use Brown\Longbridge\Exception\LongbridgeException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

final class OAuthClient
{
    private Client $client;

    public function __construct(
        private readonly string $baseUrl = 'https://openapi.longbridge.cn',
        private readonly int $timeout = 15,
    ) {
        $this->client = new Client([
            'base_uri' => rtrim($this->baseUrl, '/'),
            'timeout' => $this->timeout,
            'http_errors' => false,
        ]);
    }

    public function registerClient(
        string $clientName,
        array $redirectUris,
    ): array {
        if ($clientName === '') {
            throw new \InvalidArgumentException('clientName is empty.');
        }

        $redirectUris = array_values(array_filter($redirectUris));
        if (empty($redirectUris)) {
            throw new \InvalidArgumentException('redirectUris is empty.');
        }

        return $this->requestJson('POST', '/oauth2/register', [
            'redirect_uris' => $redirectUris,
            'token_endpoint_auth_method' => 'none',
            'grant_types' => ['authorization_code', 'refresh_token'],
            'response_types' => ['code'],
            'client_name' => $clientName,
        ]);
    }

    public function buildAuthorizeUrl(
        string $clientId,
        string $redirectUri,
        string $state,
        string $codeChallenge,
        string $scope = '3',
    ): string {
        foreach ([
                     'clientId' => $clientId,
                     'redirectUri' => $redirectUri,
                     'state' => $state,
                     'codeChallenge' => $codeChallenge,
                 ] as $name => $value) {
            if (trim($value) === '') {
                throw new \InvalidArgumentException($name . ' is empty.');
            }
        }

        return rtrim($this->baseUrl, '/') . '/oauth2/authorize?' . http_build_query([
                'response_type' => 'code',
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'scope' => $scope,
                'state' => $state,
                'code_challenge' => $codeChallenge,
                'code_challenge_method' => 'S256',
            ], '', '&', PHP_QUERY_RFC3986);
    }

    public function exchangeCode(
        string $clientId,
        string $redirectUri,
        string $code,
        string $codeVerifier,
    ): OAuthToken {
        foreach ([
                     'clientId' => $clientId,
                     'redirectUri' => $redirectUri,
                     'code' => $code,
                     'codeVerifier' => $codeVerifier,
                 ] as $name => $value) {
            if (trim($value) === '') {
                throw new \InvalidArgumentException($name . ' is empty.');
            }
        }

        $data = $this->requestForm('POST', '/oauth2/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'code' => $code,
            'code_verifier' => $codeVerifier,
        ]);

        return OAuthToken::fromArray($data);
    }

    public function refreshToken(
        string $clientId,
        string $refreshToken,
    ): OAuthToken {
        if (trim($clientId) === '') {
            throw new \InvalidArgumentException('clientId is empty.');
        }
        if (trim($refreshToken) === '') {
            throw new \InvalidArgumentException('refreshToken is empty.');
        }

        $data = $this->requestForm('POST', '/oauth2/token', [
            'grant_type' => 'refresh_token',
            'client_id' => $clientId,
            'refresh_token' => $refreshToken,
        ]);

        return OAuthToken::fromArray($data);
    }

    private function requestJson(string $method, string $uri, array $payload): array
    {
        return $this->send($method, $uri, [
            'headers' => ['Content-Type' => 'application/json; charset=utf-8'],
            'json' => $payload,
        ]);
    }

    private function requestForm(string $method, string $uri, array $payload): array
    {
        return $this->send($method, $uri, [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $payload,
        ]);
    }

    private function send(string $method, string $uri, array $options): array
    {
        try {
            $response = $this->client->request($method, $uri, $options);
        } catch (GuzzleException $e) {
            throw new LongbridgeException(
                message: 'Longbridge OAuth request failed: ' . $e->getMessage(),
                uri: $uri,
                previous: $e,
            );
        }

        $statusCode = $response->getStatusCode();
        $body = (string)$response->getBody();
        $data = json_decode($body, true);

        if (!is_array($data)) {
            throw LongbridgeException::invalidJson($statusCode, $body, $uri);
        }

        if (isset($data['error'])) {
            $message = (string)($data['error_description'] ?? $data['error']);
            throw LongbridgeException::apiError(
                httpStatus: $statusCode,
                apiCode: $statusCode,
                message: 'Longbridge OAuth error: ' . $message,
                data: $data,
                responseBody: $body,
                uri: $uri,
            );
        }

        if ($statusCode >= 400) {
            throw LongbridgeException::apiError(
                httpStatus: $statusCode,
                apiCode: $statusCode,
                message: 'Longbridge OAuth HTTP ' . $statusCode,
                data: $data,
                responseBody: $body,
                uri: $uri,
            );
        }

        return $data;
    }
}