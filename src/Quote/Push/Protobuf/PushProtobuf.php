<?php

declare(strict_types=1);

namespace Brown\Longbridge\Quote\Push\Protobuf;

use Brown\Longbridge\Proto\Quote\PushBrokers;
use Brown\Longbridge\Proto\Quote\PushDepth;
use Brown\Longbridge\Proto\Quote\PushQuote;
use Brown\Longbridge\Proto\Quote\PushTrade;
use Brown\Longbridge\Support\Protobuf;

final class PushProtobuf
{
    /**
     * 解析实时报价推送。
     */
    public static function decodePushQuote(string $body): array
    {
        return Protobuf::decode($body, PushQuote::class);
    }

    /**
     * 解析盘口推送。
     */
    public static function decodePushDepth(string $body): array
    {
        return Protobuf::decode($body, PushDepth::class);
    }

    /**
     * 解析经纪队列推送。
     */
    public static function decodePushBrokers(string $body): array
    {
        return Protobuf::decode($body, PushBrokers::class);
    }

    /**
     * 解析成交明细推送。
     */
    public static function decodePushTrade(string $body): array
    {
        return Protobuf::decode($body, PushTrade::class);
    }
}
