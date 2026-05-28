<?php

declare(strict_types=1);

namespace Brown\Longbridge\Tests\Api;

use Brown\Longbridge\Tests\Support\SpyHttpClient;
use Brown\Longbridge\Trade\TradeApi;
use PHPUnit\Framework\TestCase;

final class TradeApiTest extends TestCase
{
    public function testSubmitOrderPostsPayloadToTradeOrderEndpoint(): void
    {
        $http = new SpyHttpClient([['order_id' => '123']]);

        $result = (new TradeApi($http))->submitOrder([
            'symbol' => 'AAPL.US',
            'order_type' => 'LO',
            'side' => 'Buy',
        ]);

        self::assertSame(['order_id' => '123'], $result);
        self::assertSame([
            'method' => 'POST',
            'uri' => '/v1/trade/order',
            'query' => [],
            'payload' => [
                'symbol' => 'AAPL.US',
                'order_type' => 'LO',
                'side' => 'Buy',
            ],
        ], $http->lastCall());
    }

    public function testCancelOrderUsesDeleteQueryParameter(): void
    {
        $http = new SpyHttpClient();

        (new TradeApi($http))->cancelOrder('order-1');

        self::assertSame([
            'method' => 'DELETE',
            'uri' => '/v1/trade/order',
            'query' => ['order_id' => 'order-1'],
            'payload' => [],
        ], $http->lastCall());
    }
}
