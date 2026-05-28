<?php

declare(strict_types=1);

namespace Brown\Longbridge\Tests\Api;

use Brown\Longbridge\Asset\AssetApi;
use Brown\Longbridge\Tests\Support\SpyHttpClient;
use PHPUnit\Framework\TestCase;

final class AssetApiTest extends TestCase
{
    public function testGetAccountBalanceMapsCurrenciesToRepeatedQueryParameter(): void
    {
        $http = new SpyHttpClient([['balances' => []]]);
        $result = (new AssetApi($http))->getAccountBalance(['USD', 'HKD']);

        self::assertSame(['balances' => []], $result);
        self::assertSame([
            'method' => 'GET',
            'uri' => '/v1/asset/account',
            'query' => ['currency' => ['USD', 'HKD']],
            'payload' => [],
        ], $http->lastCall());
    }

    public function testGetStockPositionsMapsSymbolsToRepeatedQueryParameter(): void
    {
        $http = new SpyHttpClient();
        (new AssetApi($http))->getStockPositions(['AAPL.US', '700.HK']);

        self::assertSame([
            'method' => 'GET',
            'uri' => '/v1/asset/stock',
            'query' => ['symbol' => ['AAPL.US', '700.HK']],
            'payload' => [],
        ], $http->lastCall());
    }
}
