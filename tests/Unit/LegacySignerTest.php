<?php

declare(strict_types=1);

namespace Brown\Longbridge\Tests\Unit;

use Brown\Longbridge\Http\LegacySigner;
use PHPUnit\Framework\TestCase;

final class LegacySignerTest extends TestCase
{
    public function testCreatesStableLongbridgeSignature(): void
    {
        $signer = new LegacySigner();

        $signature = $signer->sign(
            method: 'GET',
            path: '/v1/asset/account',
            queryString: 'currency=USD&currency=HKD',
            headers: [
                'authorization' => 'token',
                'x-api-key' => 'key',
                'x-timestamp' => '1700000000000',
            ],
            body: '',
            appSecret: 'secret',
        );

        self::assertSame(
            'HMAC-SHA256 SignedHeaders=authorization;x-api-key;x-timestamp, Signature=6816055d0ad4b02dcb07203e71b312709bfc3fb72c6a87336ac22f37102dd7b5',
            $signature
        );
    }
}
