<?php

declare(strict_types=1);

namespace Brown\Longbridge\Tests\Unit;

use Brown\Longbridge\Http\AbstractHttpClient;
use PHPUnit\Framework\TestCase;

final class AbstractHttpClientTest extends TestCase
{
    public function testBuildQueryRepeatsArrayValuesAndSkipsNulls(): void
    {
        $client = new QueryStringHttpClient('https://example.test');

        self::assertSame(
            'symbol=AAPL.US&symbol=700.HK&active=true&count=2',
            $client->queryString([
                'symbol' => ['AAPL.US', '700.HK'],
                'skip' => null,
                'active' => true,
                'count' => 2,
            ])
        );
    }
}

final class QueryStringHttpClient extends AbstractHttpClient
{
    public function queryString(array $query): string
    {
        return $this->buildQuery($query);
    }

    protected function headers(
        string $method,
        string $uri,
        string $queryString,
        string $body
    ): array {
        return [];
    }
}
