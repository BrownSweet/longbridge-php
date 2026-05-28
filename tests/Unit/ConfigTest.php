<?php

declare(strict_types=1);

namespace Brown\Longbridge\Tests\Unit;

use Brown\Longbridge\Config;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testCnHttpUsesCnEndpointsAndNormalizesBearerToken(): void
    {
        $config = Config::cnHttp('Bearer oauth-token');

        self::assertSame('https://openapi.longbridge.cn', $config->httpBaseUrl);
        self::assertSame('wss://openapi-quote.longbridge.cn', $config->quoteWsUrl);
        self::assertSame('wss://openapi-trade.longbridge.cn', $config->tradeWsUrl);
        self::assertTrue($config->hasOAuthToken());
        self::assertFalse($config->hasLegacyCredentials());
        self::assertSame('oauth-token', $config->accessToken());
    }

    public function testHkLegacyKeepsLegacyCredentials(): void
    {
        $config = Config::hkLegacy('app-key', 'app-secret', 'legacy-token');

        self::assertSame('https://openapi.longbridge.com', $config->httpBaseUrl);
        self::assertFalse($config->hasOAuthToken());
        self::assertTrue($config->hasLegacyCredentials());
        self::assertSame('app-key', $config->getLegacyAppKey());
        self::assertSame('app-secret', $config->getLegacyAppSecret());
        self::assertSame('legacy-token', $config->getLegacyAccessToken());
    }

    public function testLegacyTokenRejectsBearerPrefix(): void
    {
        $config = Config::cnLegacy('app-key', 'app-secret', 'Bearer legacy-token');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Legacy access token must not start with Bearer.');

        $config->getLegacyAccessToken();
    }
}
