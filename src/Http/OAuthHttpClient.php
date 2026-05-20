<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */

declare(strict_types=1);

namespace Brown\Longbridge\Http;

final class OAuthHttpClient extends AbstractHttpClient
{
    private string $accessToken;

    public function __construct(
        string $baseUrl,
        string $accessToken,
        int $timeout = 15,
    ) {
        $this->setAccessToken($accessToken);
        parent::__construct($baseUrl, $timeout);
    }

    public function setAccessToken(string $accessToken): void
    {
        $accessToken = $this->normalizeAccessToken($accessToken);
        if ($accessToken === '') {
            throw new \InvalidArgumentException('accessToken is empty.');
        }

        $this->accessToken = $accessToken;
    }

    protected function headers(
        string $method,
        string $uri,
        string $queryString,
        string $body
    ): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Content-Type' => 'application/json; charset=utf-8',
        ];
    }

    private function normalizeAccessToken(string $accessToken): string
    {
        $accessToken = trim($accessToken);

        if (str_starts_with($accessToken, 'Bearer ')) {
            return trim(substr($accessToken, 7));
        }

        return $accessToken;
    }
}
