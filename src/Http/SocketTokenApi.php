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

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $authorizationHeader
    )
    {
        $this->client = new Client([
            'base_uri' => rtrim($this->baseUrl, '/'),
            'timeout' => 10,
        ]);
    }

    public function getOtp(): string
    {
        $response = $this->client->get('/v1/socket/token', [
            'headers' => [
                'Authorization' => $this->authorizationHeader,
                'Content-Type' => 'application/json; charset=utf-8',
            ],
        ]);

        $json = json_decode((string)$response->getBody(), true);

        if (!is_array($json)) {
            throw new RuntimeException('invalid socket token response.');
        }

        if (($json['code'] ?? -1) !== 0) {
            $message = $json['message'] ?? $json['msg'] ?? 'unknown error';
            throw new RuntimeException("get socket token failed: {$message}");
        }

        $otp = $json['data']['otp'] ?? null;
        if (!$otp) {
            throw new RuntimeException('otp not found in response.');
        }

        return $otp;
    }
}