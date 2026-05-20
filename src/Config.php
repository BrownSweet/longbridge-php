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
        public readonly string $legacyAppKey = '',
        public readonly string $legacyAppSecret = '',
        private readonly string $legacyAccessToken = '',
        public readonly string $OAuthAccessToken = '',
    ) {
    }

    public static function cnHttp(string $accessToken): self
    {
        return self::cnHybrid('', '', '', $accessToken);
    }

    public static function hkHttp(string $accessToken): self
    {
        return self::hkHybrid('', '', '', $accessToken);
    }

    public static function cnLegacy(string $appKey, string $appSecret, string $accessToken): self
    {
        return self::cnHybrid($appKey, $appSecret, $accessToken, '');
    }

    public static function hkLegacy(string $appKey, string $appSecret, string $accessToken): self
    {
        return self::hkHybrid($appKey, $appSecret, $accessToken, '');
    }

    public static function cnOAuth(string $legacyAppKey, string $legacyAppSecret, string $legacyAccessToken, string $accessToken): self
    {
        return self::cnHybrid($legacyAppKey, $legacyAppSecret, $legacyAccessToken, $accessToken);
    }

    public static function hkOAuth(string $legacyAppKey, string $legacyAppSecret, string $legacyAccessToken, string $accessToken): self
    {
        return self::hkHybrid($legacyAppKey, $legacyAppSecret, $legacyAccessToken, $accessToken);
    }

    public static function cnHybrid(
        string $legacyAppKey,
        string $legacyAppSecret,
        string $legacyAccessToken,
        string $accessToken
    ): self
    {
        return new self(
            httpBaseUrl: 'https://openapi.longbridge.cn',
            quoteWsUrl: 'wss://openapi-quote.longbridge.cn',
            tradeWsUrl: 'wss://openapi-trade.longbridge.cn',
            legacyAppKey: $legacyAppKey,
            legacyAppSecret: $legacyAppSecret,
            legacyAccessToken: $legacyAccessToken,
            OAuthAccessToken: self::oauthToken($accessToken),
        );
    }

    public static function hkHybrid(
        string $legacyAppKey,
        string $legacyAppSecret,
        string $legacyAccessToken,
        string $accessToken
    ): self
    {
        return new self(
            httpBaseUrl: 'https://openapi.longbridge.com',
            quoteWsUrl: 'wss://openapi-quote.longbridge.com',
            tradeWsUrl: 'wss://openapi-trade.longbridge.com',
            legacyAppKey: $legacyAppKey,
            legacyAppSecret: $legacyAppSecret,
            legacyAccessToken: $legacyAccessToken,
            OAuthAccessToken: self::oauthToken($accessToken),
        );
    }

    public function isOAuth(): bool
    {
        return $this->hasOAuthToken();
    }

    public function hasOAuthToken(): bool
    {
        return self::oauthToken($this->OAuthAccessToken) !== '';
    }

    public function accessToken(): string
    {
        $accessToken = self::oauthToken($this->OAuthAccessToken);
        if ($accessToken === '') {
            throw new \InvalidArgumentException('OAuth accessToken is empty.');
        }

        return $accessToken;
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
        $accessToken = trim($this->legacyAccessToken);
        if (str_starts_with($accessToken, 'Bearer ')) {
            throw new \InvalidArgumentException('Legacy access token must not start with Bearer.');
        }

        return $accessToken;
    }

    private static function oauthToken(string $accessToken): string
    {
        $accessToken = trim($accessToken);

        return str_starts_with($accessToken, 'Bearer ')
            ? trim(substr($accessToken, 7))
            : $accessToken;
    }
}
