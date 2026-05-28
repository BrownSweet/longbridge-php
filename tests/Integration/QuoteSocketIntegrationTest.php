<?php

declare(strict_types=1);

namespace Brown\Longbridge\Tests\Integration;

use Brown\Longbridge\LongbridgeClient;
use PHPUnit\Framework\TestCase;

final class QuoteSocketIntegrationTest extends TestCase
{
    public function testCanConnectAndFetchUserQuoteProfile(): void
    {
        $accessToken = getenv('LONGBRIDGE_OAUTH_ACCESS_TOKEN') ?: '';
        if ($accessToken === '') {
            self::markTestSkipped('Set LONGBRIDGE_OAUTH_ACCESS_TOKEN to run websocket integration tests.');
        }

        if (!class_exists(\Swoole\Coroutine\Http\Client::class)) {
            self::markTestSkipped('ext-swoole is required to run websocket integration tests.');
        }

        $region = strtolower((string)(getenv('LONGBRIDGE_REGION') ?: 'cn'));
        $client = $region === 'hk'
            ? LongbridgeClient::hkHttp($accessToken)
            : LongbridgeClient::cnHttp($accessToken);

        $quote = $client->quoteSocket();

        try {
            $profile = $quote->pull()->userQuoteProfile('zh-CN', ['timeout' => 10.0]);

            self::assertArrayHasKey('member_id', $profile);
            self::assertArrayHasKey('quote_level', $profile);
        } finally {
            $quote->client()->close();
        }
    }
}
