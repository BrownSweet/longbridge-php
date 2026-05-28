<?php

declare(strict_types=1);

namespace Brown\Longbridge\Tests\Api;

use Brown\Longbridge\Account\AlertApi;
use Brown\Longbridge\Tests\Support\SpyHttpClient;
use PHPUnit\Framework\TestCase;

final class AlertApiTest extends TestCase
{
    public function testCreateAlertMapsPercentFallToChangeValuePayload(): void
    {
        $http = new SpyHttpClient();

        (new AlertApi($http))->createAlert('AAPL.US', '5', 'pct_fall', 'daily');

        self::assertSame([
            'method' => 'POST',
            'uri' => '/v1/notify/reminders',
            'query' => [],
            'payload' => [
                'symbol' => 'AAPL.US',
                'indicator_id' => '4',
                'value_map' => ['chg' => '5'],
                'frequency' => 1,
                'enabled' => true,
                'scope' => 0,
                'state' => [1],
            ],
        ], $http->lastCall());
    }

    public function testDeleteAlertsSendsIdsInDeleteBody(): void
    {
        $http = new SpyHttpClient();

        (new AlertApi($http))->deleteAlerts(['1', '2']);

        self::assertSame([
            'method' => 'DELETE',
            'uri' => '/v1/notify/reminders',
            'query' => [],
            'payload' => ['ids' => ['1', '2']],
        ], $http->lastCall());
    }
}
