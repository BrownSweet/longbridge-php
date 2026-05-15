<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2026-05-15 12:01
 */

namespace Brown\Longbridge\Http;

use GuzzleHttp\Client;
use RuntimeException;

final class SocketTokenApi
{
    private Client $client;
    private LegacySigner $signer;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $appKey,
        private readonly string $appSecret,
        private readonly string $accessToken
    ) {
        $this->client = new Client([
            'base_uri' => rtrim($this->baseUrl, '/'),
            'timeout' => 10,
            'http_errors' => false,
        ]);

        $this->signer = new LegacySigner();
    }

    public function getOtp(): string
    {
        $method = 'GET';
        $path = '/v1/socket/token';
        $queryString = '';
        $body = '';

        $appKey = trim($this->appKey);
        $appSecret = trim($this->appSecret);
        $accessToken = trim($this->accessToken);

        if ($appKey === '') {
            throw new RuntimeException('Longbridge app key is empty.');
        }

        if ($appSecret === '') {
            throw new RuntimeException('Longbridge app secret is empty.');
        }

        if ($accessToken === '') {
            throw new RuntimeException('Longbridge legacy access token is empty.');
        }

        if (str_starts_with($accessToken, 'Bearer ')) {
            throw new RuntimeException('Legacy access token must not start with Bearer.');
        }

        $timestamp = (string) floor(microtime(true) * 1000);

        $headersForSign = [
            'authorization' => $accessToken,
            'x-api-key' => $appKey,
            'x-timestamp' => $timestamp,
        ];

        $signature = $this->signer->sign(
            method: $method,
            path: $path,
            queryString: $queryString,
            headers: $headersForSign,
            body: $body,
            appSecret: $appSecret
        );

        $headers = [
            'Authorization' => $accessToken,
            'X-Api-Key' => $appKey,
            'X-Timestamp' => $timestamp,
            'X-Api-Signature' => $signature,
            'Content-Type' => 'application/json; charset=utf-8',
        ];

        $response = $this->client->request($method, $path, [
            'headers' => $headers,
        ]);

        $statusCode = $response->getStatusCode();
        $responseBody = (string) $response->getBody();

        $json = json_decode($responseBody, true);

        if (!is_array($json)) {
            throw new RuntimeException("invalid response: HTTP {$statusCode}, body={$responseBody}");
        }

        if ($statusCode >= 400 || ($json['code'] ?? -1) !== 0) {
            throw new RuntimeException(
                'get socket token failed: '
                . 'HTTP ' . $statusCode
                . ', code=' . ($json['code'] ?? 'unknown')
                . ', message=' . ($json['message'] ?? $json['msg'] ?? 'unknown')
                . ', body=' . $responseBody
            );
        }

        $otp = $json['data']['otp'] ?? null;

        if (!$otp) {
            throw new RuntimeException('otp not found: ' . $responseBody);
        }

        return $otp;
    }
}





