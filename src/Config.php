<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2026-05-15 12:03
 */

declare(strict_types=1);

namespace Brown\Longbridge;

final class Config
{
    public function __construct(
        public readonly string $httpBaseUrl,
        public readonly string $quoteWsUrl,
        public readonly string $tradeWsUrl,
        public readonly string $legacyAppKey,
        public readonly string $legacyAppSecret,
        private readonly string $legacyAccessToken,

        public readonly string $OAuthAccessToken,
    )
    {
    }

    public static function cnHttp(string $accessToken): self
    {
        return self::cnOAuth('', '', '', $accessToken);
    }

    public static function hkHttp(string $accessToken): self
    {
        return self::hkOAuth('', '', '', $accessToken);
    }

    public static function cnOAuth(string $legacyAppKey, string $legacyAppSecret, string $legacyAccessToken, string $accessToken): self
    {
        return new self(
            httpBaseUrl: 'https://openapi.longbridge.cn',
            quoteWsUrl: 'wss://openapi-quote.longbridge.cn',
            tradeWsUrl: 'wss://openapi-trade.longbridge.cn',
            legacyAppKey: $legacyAppKey,
            legacyAppSecret: $legacyAppSecret,
            legacyAccessToken: $legacyAccessToken,
            OAuthAccessToken: self::bearer($accessToken),
        );
    }

    public static function hkOAuth(string $legacyAppKey, string $legacyAppSecret, string $legacyAccessToken, string $accessToken): self
    {
        return new self(
            httpBaseUrl: 'https://openapi.longbridge.com',
            quoteWsUrl: 'wss://openapi-quote.longbridge.com',
            tradeWsUrl: 'wss://openapi-trade.longbridge.com',
            legacyAppKey: $legacyAppKey,
            legacyAppSecret: $legacyAppSecret,
            legacyAccessToken: $legacyAccessToken,
            OAuthAccessToken: self::bearer($accessToken),
        );
    }

    public function isOAuth(): bool
    {
        return str_starts_with($this->OAuthAccessToken, 'Bearer ');
    }

    public function accessToken(): string
    {
        if ($this->isOAuth()) {
            return substr($this->OAuthAccessToken, 7);
        }

        return $this->OAuthAccessToken;
    }

    public function hasLegacyCredentials(): bool
    {
        return trim($this->legacyAppKey) !== ''
            && trim($this->legacyAppSecret) !== ''
            && trim($this->legacyAccessToken) !== '';
    }

    public function getLegacyAppKey(): string
    {
        return $this->legacyAppKey;
    }

    public function getLegacyAppSecret(): string
    {
        return $this->legacyAppSecret;
    }

    public function getLegacyAccessToken(): string
    {
        return $this->legacyAccessToken;
    }

    private static function bearer(string $accessToken): string
    {
        $accessToken = trim($accessToken);
        if ($accessToken === '') {
            throw new \InvalidArgumentException('accessToken is empty.');
        }

        return str_starts_with($accessToken, 'Bearer ')
            ? $accessToken
            : 'Bearer ' . $accessToken;
    }
}
