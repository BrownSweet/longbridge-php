<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2026-05-15 12:01
 */

declare(strict_types=1);

namespace Brown\Longbridge\Http;

use Brown\Longbridge\Exception\LongbridgeException;

final class SocketTokenApi
{
    private LegacyHttpClient $client;

    public function __construct(
        string $baseUrl,
        string $appKey,
        string $appSecret,
        string $accessToken,
        int $timeout = 10,
    ) {
        $this->client = new LegacyHttpClient($baseUrl, $appKey, $appSecret, $accessToken, $timeout);
    }

    public function getOtp(): string
    {
        $data = $this->client->get('/v1/socket/token');

        return $this->extractOtp($data, '/v1/socket/token');
    }

    public function refreshLagacySigner(): array
    {
        return $this->refreshLegacySigner();
    }

    public function refreshLegacySigner(): array
    {
        return $this->client->get('/v1/token/refresh');
    }

    private function extractOtp(array $data, string $uri): string
    {
        $otp = (string)($data['otp'] ?? $data['Otp'] ?? '');
        if ($otp === '') {
            throw new LongbridgeException(
                message: 'Longbridge socket otp not found.',
                data: $data,
                uri: $uri,
            );
        }

        return $otp;
    }
}
