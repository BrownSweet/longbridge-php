<?php

declare(strict_types=1);

namespace Brown\Longbridge\Tests\Unit;

use Brown\Longbridge\Protocol\LongbridgeCodec;
use Brown\Longbridge\Protocol\PacketType;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class LongbridgeCodecTest extends TestCase
{
    public function testEncodesAndDecodesRequestPacket(): void
    {
        $codec = new LongbridgeCodec();

        $packet = $codec->decode($codec->encodeRequest(
            cmdCode: 11,
            requestId: 123,
            body: 'payload',
            timeoutMs: 5000
        ));

        self::assertSame(PacketType::REQUEST, $packet->type);
        self::assertTrue($packet->isRequest());
        self::assertSame(11, $packet->cmdCode);
        self::assertSame(123, $packet->requestId);
        self::assertSame('payload', $packet->body);
        self::assertFalse($packet->gzip);
    }

    public function testEncodesAndDecodesGzipResponsePacket(): void
    {
        $codec = new LongbridgeCodec();

        $packet = $codec->decode($codec->encodeResponse(
            cmdCode: 2,
            requestId: 456,
            status: 0,
            body: 'response-body',
            gzip: true
        ));

        self::assertSame(PacketType::RESPONSE, $packet->type);
        self::assertTrue($packet->isResponse());
        self::assertTrue($packet->isSuccess());
        self::assertSame(2, $packet->cmdCode);
        self::assertSame(456, $packet->requestId);
        self::assertSame('response-body', $packet->body);
        self::assertTrue($packet->gzip);
    }

    public function testRejectsInvalidPacketLength(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('invalid packet length.');

        (new LongbridgeCodec())->decode('');
    }

    public function testRejectsOutOfRangeTimeout(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('timeoutMs must be between 1 and 60000.');

        (new LongbridgeCodec())->encodeRequest(1, 1, timeoutMs: 0);
    }
}
