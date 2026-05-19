<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2026-05-16 20:05
 */

namespace Brown\Longbridge\Quote\Pull;
use Brown\Longbridge\Proto\Control\Command;
use Brown\Longbridge\Proto\Control\SecurityDepthResponse;
use Brown\Longbridge\Quote\Pull\Protobuf\SecurityDepthProtobuf;
use Brown\Longbridge\Socket\LongbridgeWsClient;
use Brown\Longbridge\Quote\Pull\Protobuf\QuoteProtobuf;

use RuntimeException;

final class SecurityQuoteApi
{

    public function __construct(
        private readonly LongbridgeWsClient $client
    ) {
    }

    /**
     * 获取股票实时行情
     *
     * @param array $symbols
     * @param float $timeout
     * @return array
     */
    public function quote(array $symbols, float $timeout = 10.0): array
    {
        $packet = $this->client->request(
            cmdCode: Command::QuerySecurityQuote,
            protobufBody: QuoteProtobuf::multiSecurityRequest($symbols),
            timeout: $timeout,
            timeoutMs: (int)($timeout * 1000)
        );

        if (!$packet->isSuccess()) {
            throw new RuntimeException("Longbridge quote failed, status={$packet->status}");
        }

        return QuoteProtobuf::decodeSecurityQuoteResponse($packet->body);
    }


    public function depth(array $symbols, float $timeout = 10.0)
    {
        $packet = $this->client->request(
            cmdCode: Command::QueryDepth,
            protobufBody: SecurityDepthProtobuf::multiSecurityRequest($symbols),
            timeout: $timeout,
            timeoutMs: (int)($timeout * 1000)
        );

        if (!$packet->isSuccess()) {
            throw new RuntimeException("Longbridge quote failed, status={$packet->status}");
        }


        return SecurityDepthProtobuf::decodeSecurityDepthResponse($packet->body);
    }
}