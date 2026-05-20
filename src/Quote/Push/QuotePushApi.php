<?php

declare(strict_types=1);

namespace Brown\Longbridge\Quote\Push;

use Brown\Longbridge\Proto\Quote\Command as QuoteCommand;
use Brown\Longbridge\Quote\Push\Protobuf\PushProtobuf;
use Brown\Longbridge\Socket\LongbridgeWsClient;
use RuntimeException;

final class QuotePushApi
{
    public function __construct(
        private readonly LongbridgeWsClient $client
    ) {
    }

    /**
     * 等待任意行情推送并按类型解析。
     *
     * @param float $timeout 等待超时时间，单位秒。
     * @return array 包含 type、cmd、payload 的推送数组。
     *
     * 官方命令：PushQuoteData(101)、PushDepthData(102)、PushBrokersData(103)、PushTradeData(104)。
     */
    public function wait(float $timeout = 15.0): array
    {
        return $this->waitFor([], $timeout);
    }

    /**
     * 等待实时报价推送。
     *
     * @param float $timeout 等待超时时间，单位秒。
     * @return array 实时报价推送。
     *
     * 官方命令：PushQuoteData(101)，来源：Quote Push。
     */
    public function waitQuote(float $timeout = 15.0): array
    {
        return $this->waitFor([QuoteCommand::PushQuoteData], $timeout);
    }

    /**
     * 兼容旧方法名：等待实时报价推送。
     *
     * @param float $timeout 等待超时时间，单位秒。
     * @return array 实时报价推送。
     *
     * 官方命令：PushQuoteData(101)，来源：Quote Push。
     */
    public function waitQuotePush(float $timeout = 15.0): array
    {
        return $this->waitQuote($timeout);
    }

    /**
     * 等待盘口推送。
     *
     * @param float $timeout 等待超时时间，单位秒。
     * @return array 盘口推送。
     *
     * 官方命令：PushDepthData(102)，来源：Quote Push。
     */
    public function waitDepth(float $timeout = 15.0): array
    {
        return $this->waitFor([QuoteCommand::PushDepthData], $timeout);
    }

    /**
     * 等待经纪队列推送。
     *
     * @param float $timeout 等待超时时间，单位秒。
     * @return array 经纪队列推送。
     *
     * 官方命令：PushBrokersData(103)，来源：Quote Push。
     */
    public function waitBrokers(float $timeout = 15.0): array
    {
        return $this->waitFor([QuoteCommand::PushBrokersData], $timeout);
    }

    /**
     * 等待成交明细推送。
     *
     * @param float $timeout 等待超时时间，单位秒。
     * @return array 成交明细推送。
     *
     * 官方命令：PushTradeData(104)，来源：Quote Push。
     */
    public function waitTrades(float $timeout = 15.0): array
    {
        return $this->waitFor([QuoteCommand::PushTradeData], $timeout);
    }

    /**
     * @param array<int,int> $cmdCodes
     */
    private function waitFor(array $cmdCodes, float $timeout): array
    {
        $deadline = microtime(true) + $timeout;

        while (true) {
            $left = $deadline - microtime(true);
            if ($left <= 0) {
                throw new RuntimeException('Longbridge wait quote push timeout.');
            }

            $packet = $this->client->recv(min($left, 5.0));
            if (!$packet || !$packet->isPush()) {
                continue;
            }

            if ($cmdCodes !== [] && !in_array($packet->cmdCode, $cmdCodes, true)) {
                continue;
            }

            return match ($packet->cmdCode) {
                QuoteCommand::PushQuoteData => [
                    'type' => 'quote',
                    'cmd' => $packet->cmdCode,
                    'payload' => PushProtobuf::decodePushQuote($packet->body),
                ],
                QuoteCommand::PushDepthData => [
                    'type' => 'depth',
                    'cmd' => $packet->cmdCode,
                    'payload' => PushProtobuf::decodePushDepth($packet->body),
                ],
                QuoteCommand::PushBrokersData => [
                    'type' => 'brokers',
                    'cmd' => $packet->cmdCode,
                    'payload' => PushProtobuf::decodePushBrokers($packet->body),
                ],
                QuoteCommand::PushTradeData => [
                    'type' => 'trade',
                    'cmd' => $packet->cmdCode,
                    'payload' => PushProtobuf::decodePushTrade($packet->body),
                ],
                default => [
                    'type' => 'unknown',
                    'cmd' => $packet->cmdCode,
                    'payload' => $packet->body,
                ],
            };
        }
    }
}
