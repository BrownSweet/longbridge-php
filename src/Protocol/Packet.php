<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */


namespace Brown\Longbridge\Protocol;

final class Packet
{
    public function __construct(
        public readonly int $type,
        public readonly int $cmdCode,
        public readonly string $body,
        public readonly ?int $requestId = null,
        public readonly ?int $status = null,
        public readonly bool $gzip = false,
        public readonly bool $verify = false,
        public readonly int $reserve = 0,
    ) {
    }

    public function isResponse(): bool
    {
        return $this->type === PacketType::RESPONSE;
    }

    public function isRequest(): bool
    {
        return $this->type === PacketType::REQUEST;
    }

    public function isPush(): bool
    {
        return $this->type === PacketType::PUSH;
    }

    public function isSuccess(): bool
    {
        return $this->status === 0;
    }
}
