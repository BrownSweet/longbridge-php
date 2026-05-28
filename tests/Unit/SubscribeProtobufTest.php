<?php

declare(strict_types=1);

namespace Brown\Longbridge\Tests\Unit;

use Brown\Longbridge\Proto\Quote\SubType;
use Brown\Longbridge\Proto\Quote\SubscribeRequest;
use Brown\Longbridge\Proto\Quote\UnsubscribeRequest;
use Brown\Longbridge\Quote\Subscribe\Protobuf\SubscribeProtobuf;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SubscribeProtobufTest extends TestCase
{
    public function testBuildsSubscribeRequestFromStringSubTypes(): void
    {
        $request = new SubscribeRequest();
        $request->mergeFromString(SubscribeProtobuf::subscribeRequest(
            ['AAPL.US'],
            ['quote', 'depth', SubType::TRADE],
            false
        ));

        self::assertSame(['AAPL.US'], iterator_to_array($request->getSymbol()));
        self::assertSame([SubType::QUOTE, SubType::DEPTH, SubType::TRADE], iterator_to_array($request->getSubType()));
        self::assertFalse($request->getIsFirstPush());
    }

    public function testBuildsUnsubscribeAllRequest(): void
    {
        $request = new UnsubscribeRequest();
        $request->mergeFromString(SubscribeProtobuf::unsubscribeRequest(unsubAll: true));

        self::assertSame([], iterator_to_array($request->getSymbol()));
        self::assertSame([], iterator_to_array($request->getSubType()));
        self::assertTrue($request->getUnsubAll());
    }

    public function testRejectsUnsupportedSubType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('unsupported quote sub type: bad');

        SubscribeProtobuf::normalizeSubTypes(['bad']);
    }
}
