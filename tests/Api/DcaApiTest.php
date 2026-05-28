<?php

declare(strict_types=1);

namespace Brown\Longbridge\Tests\Api;

use Brown\Longbridge\Account\DcaApi;
use Brown\Longbridge\Tests\Support\SpyHttpClient;
use PHPUnit\Framework\TestCase;

final class DcaApiTest extends TestCase
{
    public function testListPlansConvertsSymbolToCounterIdAndAddsPaginationDefaults(): void
    {
        $http = new SpyHttpClient();

        (new DcaApi($http))->listPlans(['symbol' => 'AAPL.US', 'status' => 'Active']);

        self::assertSame([
            'method' => 'GET',
            'uri' => '/v1/dailycoins/query',
            'query' => [
                'page' => 1,
                'limit' => 100,
                'status' => 'Active',
                'counter_id' => 'ST/US/AAPL',
            ],
            'payload' => [],
        ], $http->lastCall());
    }

    public function testCreatePlanConvertsAllowMarginAlias(): void
    {
        $http = new SpyHttpClient();

        (new DcaApi($http))->createPlan('700.HK', '1000', 'Monthly', [
            'allow_margin' => true,
        ]);

        self::assertSame([
            'method' => 'POST',
            'uri' => '/v1/dailycoins/create',
            'query' => [],
            'payload' => [
                'counter_id' => 'ST/HK/700',
                'per_invest_amount' => '1000',
                'invest_frequency' => 'Monthly',
                'allow_margin_finance' => 1,
            ],
        ], $http->lastCall());
    }
}
