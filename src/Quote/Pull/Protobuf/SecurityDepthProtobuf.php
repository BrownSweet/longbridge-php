<?php

declare(strict_types=1);

namespace Brown\Longbridge\Quote\Pull\Protobuf;

use Brown\Longbridge\Proto\Quote\SecurityDepthResponse;

final class SecurityDepthProtobuf
{
    /**
     * 构造单标的盘口请求。
     */
    public static function securityRequest(string $symbol): string
    {
        return QuoteProtobuf::securityRequest($symbol);
    }

    /**
     * 兼容旧方法名。盘口官方请求实际为单标的，传入数组时取第一个标的。
     */
    public static function multiSecurityRequest(array $symbols): string
    {
        return self::securityRequest((string)reset($symbols));
    }

    /**
     * 解析盘口响应。
     */
    public static function decodeSecurityDepthResponse(string $body): array
    {
        return QuoteProtobuf::decode($body, SecurityDepthResponse::class);
    }
}
