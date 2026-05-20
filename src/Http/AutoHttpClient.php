<?php

declare(strict_types=1);

namespace Brown\Longbridge\Http;

final class AutoHttpClient implements HttpClientInterface
{
    public function __construct(
        private ?OAuthHttpClient $oauthClient,
        private readonly ?LegacyHttpClient $legacyClient,
    ) {
        if ($this->oauthClient === null && $this->legacyClient === null) {
            throw new \InvalidArgumentException('OAuth access token or legacy credentials are required.');
        }
    }

    public function hasOAuth(): bool
    {
        return $this->oauthClient !== null;
    }

    public function hasLegacy(): bool
    {
        return $this->legacyClient !== null;
    }

    public function oauth(): OAuthHttpClient
    {
        if ($this->oauthClient === null) {
            throw new \RuntimeException('OAuth HTTP client is not configured.');
        }

        return $this->oauthClient;
    }

    public function legacy(): LegacyHttpClient
    {
        if ($this->legacyClient === null) {
            throw new \RuntimeException('Legacy HTTP client is not configured.');
        }

        return $this->legacyClient;
    }

    public function setAccessToken(string $accessToken): void
    {
        $this->oauth()->setAccessToken($accessToken);
    }

    public function get(string $uri, array $query = []): array
    {
        return $this->preferred()->get($uri, $query);
    }

    public function post(string $uri, array $payload = []): array
    {
        return $this->preferred()->post($uri, $payload);
    }

    public function put(string $uri, array $payload = []): array
    {
        return $this->preferred()->put($uri, $payload);
    }

    public function delete(string $uri, array $query = [], array $payload = []): array
    {
        return $this->preferred()->delete($uri, $query, $payload);
    }

    public function request(
        string $method,
        string $uri,
        array $query = [],
        array $payload = [],
    ): array {
        return $this->preferred()->request($method, $uri, $query, $payload);
    }

    private function preferred(): HttpClientInterface
    {
        return $this->oauthClient ?? $this->legacy();
    }
}
