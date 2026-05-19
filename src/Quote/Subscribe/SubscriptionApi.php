<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2026-05-16 20:18
 */


namespace Brown\Longbridge\Quote\Subscribe;

use Brown\Longbridge\Proto\Control\Command;
use Brown\Longbridge\Proto\Control\SubType;
use Brown\Longbridge\Quote\Subscribe\Protobuf\SubscribeProtobuf;
use Brown\Longbridge\Socket\LongbridgeWsClient;
use RuntimeException;

final class SubscriptionApi
{
    public function __construct(
        private readonly LongbridgeWsClient $client
    ) {
    }

    public function subscribeQuote(array $symbols, bool $isFirstPush = true, float $timeout = 10.0): array
    {
        $packet = $this->client->request(
            cmdCode: Command::Subscribe,
            protobufBody: SubscribeProtobuf::subscribeRequest($symbols, [SubType::QUOTE], $isFirstPush),
            timeout: $timeout,
            timeoutMs: (int)($timeout * 1000)
        );

        if (!$packet->isSuccess()) {
            throw new RuntimeException("Longbridge subscribe quote failed, status={$packet->status}");
        }

        return [
            'type' => 'longbridge_subscribe_quote',
            'msg' => 'success',
            'symbols' => $symbols,
            'cmd' => $packet->cmdCode,
            'status' => $packet->status,
            'body_len' => strlen($packet->body),
        ];
    }
}
