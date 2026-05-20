<?php

declare(strict_types=1);

namespace Brown\Longbridge\Http;

final class LegacyHttpClient extends AbstractHttpClient
{
    private LegacySigner $signer;

    public function __construct(
        string $baseUrl,
        private readonly string $appKey,
        private readonly string $appSecret,
        private readonly string $accessToken,
        int $timeout = 15,
    ) {
        $this->assertCredentials();
        $this->signer = new LegacySigner();
        parent::__construct($baseUrl, $timeout);
    }

    protected function headers(
        string $method,
        string $uri,
        string $queryString,
        string $body
    ): array {
        $timestamp = (string)floor(microtime(true) * 1000);
        $headersForSign = [
            'authorization' => trim($this->accessToken),
            'x-api-key' => trim($this->appKey),
            'x-timestamp' => $timestamp,
        ];

        $signature = $this->signer->sign(
            method: $method,
            path: $this->pathForSign($uri),
            queryString: $queryString,
            headers: $headersForSign,
            body: $body,
            appSecret: trim($this->appSecret),
        );

        return [
            'Authorization' => trim($this->accessToken),
            'X-Api-Key' => trim($this->appKey),
            'X-Timestamp' => $timestamp,
            'X-Api-Signature' => $signature,
            'Content-Type' => 'application/json; charset=utf-8',
        ];
    }

    private function assertCredentials(): void
    {
        if (trim($this->appKey) === '') {
            throw new \InvalidArgumentException('Longbridge app key is empty.');
        }

        if (trim($this->appSecret) === '') {
            throw new \InvalidArgumentException('Longbridge app secret is empty.');
        }

        $accessToken = trim($this->accessToken);
        if ($accessToken === '') {
            throw new \InvalidArgumentException('Longbridge legacy access token is empty.');
        }

        if (str_starts_with($accessToken, 'Bearer ')) {
            throw new \InvalidArgumentException('Legacy access token must not start with Bearer.');
        }
    }
}
