<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2026-05-15 11:54
 */

namespace Brown\Longbridge\Protocol;

use RuntimeException;

final class LongbridgeCodec
{
    private const MAX_BODY_LENGTH = 16 * 1024 * 1024;

    public function encodeRequest(
        int    $cmdCode,
        int    $requestId,
        string $body = '',
        int    $timeoutMs = 10000,
        bool   $gzip = false
    ): string
    {
        if ($timeoutMs < 1 || $timeoutMs > 60000) {
            throw new RuntimeException('timeoutMs must be between 1 and 60000.');
        }

        if ($gzip) {
            $body = gzencode($body);
            if ($body === false) {
                throw new RuntimeException('gzip encode failed.');
            }
        }

        $bodyLen = strlen($body);

        if ($bodyLen > self::MAX_BODY_LENGTH) {
            throw new RuntimeException('body too large.');
        }

        $header = $this->packHeader(
            type: PacketType::REQUEST,
            verify: false,
            gzip: $gzip,
            reserve: 0
        );

        return $header
            . chr($cmdCode & 0xff)
            . pack('N', $requestId)
            . pack('n', $timeoutMs)
            . $this->packUint24($bodyLen)
            . $body;
    }

    private function packHeader(
        int  $type,
        bool $verify = false,
        bool $gzip = false,
        int  $reserve = 0
    ): string
    {
        $byte = ($type & 0x0f)
            | (($verify ? 1 : 0) << 4)
            | (($gzip ? 1 : 0) << 5)
            | (($reserve & 0x03) << 6);

        return chr($byte);
    }

    private function packUint24(int $value): string
    {
        if ($value < 0 || $value > 0xffffff) {
            throw new RuntimeException('uint24 out of range.');
        }

        return chr(($value >> 16) & 0xff)
            . chr(($value >> 8) & 0xff)
            . chr($value & 0xff);
    }

    public function decode(string $data): Packet
    {
        if (strlen($data) < 2) {
            throw new RuntimeException('invalid packet length.');
        }

        $header = ord($data[0]);
        $type = $header & 0x0f;
        $verify = (($header >> 4) & 0x01) === 1;
        $gzip = (($header >> 5) & 0x01) === 1;
        $reserve = ($header >> 6) & 0x03;

        return match ($type) {
            PacketType::REQUEST => $this->decodeRequest($data, $gzip, $verify, $reserve),
            PacketType::RESPONSE => $this->decodeResponse($data, $gzip, $verify, $reserve),
            PacketType::PUSH => $this->decodePush($data, $gzip, $verify, $reserve),
            default => throw new RuntimeException("unsupported packet type: {$type}"),
        };
    }

    public function encodeResponse(
        int $cmdCode,
        int $requestId,
        int $status = 0,
        string $body = '',
        bool $gzip = false
    ): string
    {
        if ($gzip) {
            $body = gzencode($body);
            if ($body === false) {
                throw new RuntimeException('gzip encode failed.');
            }
        }

        $bodyLen = strlen($body);

        if ($bodyLen > self::MAX_BODY_LENGTH) {
            throw new RuntimeException('body too large.');
        }

        $header = $this->packHeader(
            type: PacketType::RESPONSE,
            verify: false,
            gzip: $gzip,
            reserve: 0
        );

        return $header
            . chr($cmdCode & 0xff)
            . pack('N', $requestId)
            . chr($status & 0xff)
            . $this->packUint24($bodyLen)
            . $body;
    }

    private function decodeRequest(
        string $data,
        bool   $gzip,
        bool   $verify,
        int    $reserve
    ): Packet
    {
        if (strlen($data) < 11) {
            throw new RuntimeException('invalid request packet length.');
        }

        $cmdCode = ord($data[1]);
        $requestId = unpack('N', substr($data, 2, 4))[1];
        $bodyLen = $this->unpackUint24(substr($data, 8, 3));

        $bodyStart = 11;
        $body = substr($data, $bodyStart, $bodyLen);
        if (strlen($body) !== $bodyLen) {
            throw new RuntimeException('request body length mismatch.');
        }

        if ($gzip) {
            $body = gzdecode($body);
            if ($body === false) {
                throw new RuntimeException('gzip decode failed.');
            }
        }

        return new Packet(
            type: PacketType::REQUEST,
            cmdCode: $cmdCode,
            body: $body,
            requestId: $requestId,
            status: null,
            gzip: $gzip,
            verify: $verify,
            reserve: $reserve
        );
    }

    private function decodeResponse(
        string $data,
        bool   $gzip,
        bool   $verify,
        int    $reserve
    ): Packet
    {
        if (strlen($data) < 10) {
            throw new RuntimeException('invalid response packet length.');
        }

        $cmdCode = ord($data[1]);
        $requestId = unpack('N', substr($data, 2, 4))[1];
        $status = ord($data[6]);
        $bodyLen = $this->unpackUint24(substr($data, 7, 3));

        $bodyStart = 10;
        $body = substr($data, $bodyStart, $bodyLen);
        if (strlen($body) !== $bodyLen) {
            throw new RuntimeException('response body length mismatch.');
        }

        if ($gzip) {
            $body = gzdecode($body);
            if ($body === false) {
                throw new RuntimeException('gzip decode failed.');
            }
        }

        return new Packet(
            type: PacketType::RESPONSE,
            cmdCode: $cmdCode,
            body: $body,
            requestId: $requestId,
            status: $status,
            gzip: $gzip,
            verify: $verify,
            reserve: $reserve
        );
    }

    private function unpackUint24(string $bytes): int
    {
        if (strlen($bytes) !== 3) {
            throw new RuntimeException('uint24 requires exactly 3 bytes.');
        }

        return (ord($bytes[0]) << 16)
            | (ord($bytes[1]) << 8)
            | ord($bytes[2]);
    }

    private function decodePush(
        string $data,
        bool   $gzip,
        bool   $verify,
        int    $reserve
    ): Packet
    {
        if (strlen($data) < 5) {
            throw new RuntimeException('invalid push packet length.');
        }

        $cmdCode = ord($data[1]);
        $bodyLen = $this->unpackUint24(substr($data, 2, 3));

        $bodyStart = 5;
        $body = substr($data, $bodyStart, $bodyLen);

        if (strlen($body) !== $bodyLen) {
            throw new RuntimeException('push body length mismatch.');
        }

        if ($gzip) {
            $body = gzdecode($body);
            if ($body === false) {
                throw new RuntimeException('gzip decode failed.');
            }
        }

        return new Packet(
            type: PacketType::PUSH,
            cmdCode: $cmdCode,
            body: $body,
            requestId: null,
            status: null,
            gzip: $gzip,
            verify: $verify,
            reserve: $reserve
        );
    }
}
