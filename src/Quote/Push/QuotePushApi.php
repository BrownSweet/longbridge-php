<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2026-05-16 20:23
 */


namespace Brown\Longbridge\Quote\Push;

use Brown\Longbridge\Proto\Control\Command;
use Brown\Longbridge\Quote\Push\Protobuf\PushProtobuf;
use Brown\Longbridge\Socket\LongbridgeWsClient;
use RuntimeException;

final class QuotePushApi
{
    public function __construct(
        private readonly LongbridgeWsClient $client
    ) {
    }

    public function waitQuotePush(float $timeout = 15.0): array
    {
        $deadline = microtime(true) + $timeout;

        while (true) {
            $left = $deadline - microtime(true);
            if ($left <= 0) {
                throw new RuntimeException('Longbridge wait quote push timeout.');
            }

            $packet = $this->client->recv(min($left, 5.0));

            if (!$packet) {
                continue;
            }

            if ($packet->isPush() && $packet->cmdCode === Command::PushQuoteData) {
                return [
                    'type' => 'longbridge_push_quote',
                    'msg' => 'success',
                    'cmd' => $packet->cmdCode,
                    'quote' => PushProtobuf::decodePushQuote($packet->body),
                ];
            }
        }
    }

}
