<?php

declare(strict_types=1);

namespace Brown\Longbridge\Tests\Unit;

use Brown\Longbridge\Proto\Quote\Depth;
use Brown\Longbridge\Proto\Quote\PushDepth;
use Brown\Longbridge\Proto\Quote\PushQuote;
use Brown\Longbridge\Quote\Push\Protobuf\PushProtobuf;
use PHPUnit\Framework\TestCase;

final class PushProtobufTest extends TestCase
{
    public function testDecodesQuotePush(): void
    {
        $message = new PushQuote();
        $message->setSymbol('AAPL.US');
        $message->setLastDone('180.12');
        $message->setVolume(100);

        self::assertSame([
            'symbol' => 'AAPL.US',
            'sequence' => 0,
            'last_done' => '180.12',
            'open' => '',
            'high' => '',
            'low' => '',
            'timestamp' => 0,
            'volume' => 100,
            'turnover' => '',
            'trade_status' => 0,
            'trade_session' => 0,
            'current_volume' => 0,
            'current_turnover' => '',
            'tag' => 0,
        ], PushProtobuf::decodePushQuote($message->serializeToString()));
    }

    public function testDecodesDepthPush(): void
    {
        $depth = new Depth();
        $depth->setPrice('180.00');
        $depth->setVolume(10);

        $message = new PushDepth();
        $message->setSymbol('AAPL.US');
        $message->setAsk([$depth]);

        $payload = PushProtobuf::decodePushDepth($message->serializeToString());

        self::assertSame('AAPL.US', $payload['symbol']);
        self::assertSame('180.00', $payload['ask'][0]['price']);
        self::assertSame(10, $payload['ask'][0]['volume']);
    }
}
