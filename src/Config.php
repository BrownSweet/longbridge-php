<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2026-05-15 12:03
 */

namespace Brown\Longbridge;

final class Config
{
    public function __construct(
        public readonly string $httpBaseUrl,
        public readonly string $quoteWsUrl,
        public readonly string $tradeWsUrl,
        public readonly string $authorizationHeader,
    ) {
    }

    public static function hk(string $authorizationHeader): self
    {
        return new self(
            httpBaseUrl: 'https://openapi.longbridge.com',
            quoteWsUrl: 'wss://openapi-quote.longbridge.com',
            tradeWsUrl: 'wss://openapi-trade.longbridge.com',
            authorizationHeader: $authorizationHeader,
        );
    }

    public static function cn(string $authorizationHeader): self
    {
        return new self(
            httpBaseUrl: 'https://openapi.longbridge.cn',
            quoteWsUrl: 'wss://openapi-quote.longbridge.cn',
            tradeWsUrl: 'wss://openapi-trade.longbridge.cn',
            authorizationHeader: $authorizationHeader,
        );
    }

    public function get()
    {
        echo 2;
    }
}