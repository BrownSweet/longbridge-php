<?php

declare(strict_types=1);

namespace Brown\Longbridge\Tests\Api;

use Brown\Longbridge\Account\PortfolioApi;
use Brown\Longbridge\Tests\Support\SpyHttpClient;
use PHPUnit\Framework\TestCase;

final class PortfolioApiTest extends TestCase
{
    public function testProfitAnalysisSummaryNormalizesDateRange(): void
    {
        $http = new SpyHttpClient();

        (new PortfolioApi($http))->profitAnalysisSummary([
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-02',
        ]);

        self::assertSame([
            'method' => 'GET',
            'uri' => '/v1/portfolio/profit-analysis-summary',
            'query' => [
                'start' => strtotime('2026-01-01 00:00:00 UTC'),
                'end' => strtotime('2026-01-02 00:00:00 UTC') + 86399,
            ],
            'payload' => [],
        ], $http->lastCall());
    }

    public function testProfitAnalysisCombinesSummaryAndSublistResponses(): void
    {
        $http = new SpyHttpClient([
            ['total' => '10'],
            ['items' => []],
        ]);

        $result = (new PortfolioApi($http))->profitAnalysis(['profit_or_loss' => 'profit']);

        self::assertSame([
            'summary' => ['total' => '10'],
            'sublist' => ['items' => []],
        ], $result);
        self::assertCount(2, $http->calls);
        self::assertSame('/v1/portfolio/profit-analysis-summary', $http->calls[0]['uri']);
        self::assertSame('/v1/portfolio/profit-analysis-sublist', $http->calls[1]['uri']);
    }
}
