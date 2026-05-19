<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */
declare(strict_types=1);

namespace Brown\Longbridge\Http;

use Brown\Longbridge\Exception\LongbridgeException;

final class OAuthSocketTokenApi
{
    private OAuthHttpClient $client;

    public function __construct(
        string $baseUrl,
        string $accessToken,
        int $timeout = 15,
    ) {
        $this->client = new OAuthHttpClient($baseUrl, $accessToken, $timeout);
    }

    public function getOtp(): string
    {

        $data = $this->client->get('/v1/socket/token');

        $otp = (string)($data['otp'] ?? $data['Otp'] ?? '');
        if ($otp === '') {
            throw new LongbridgeException(
                message: 'Longbridge socket otp not found.',
                data: $data,
                uri: '/v1/socket/token',
            );
        }

        return $otp;
    }
}
