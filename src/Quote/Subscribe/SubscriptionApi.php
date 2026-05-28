<?php

declare(strict_types=1);

namespace Brown\Longbridge\Quote\Subscribe;

use Brown\Longbridge\Proto\Quote\Command as QuoteCommand;
use Brown\Longbridge\Proto\Quote\SubType;
use Brown\Longbridge\Proto\Quote\SubscriptionResponse;
use Brown\Longbridge\Proto\Quote\UnsubscribeResponse;
use Brown\Longbridge\Quote\Subscribe\Protobuf\SubscribeProtobuf;
use Brown\Longbridge\Socket\WsClientInterface;
use RuntimeException;

final class SubscriptionApi
{
    public function __construct(
        private readonly WsClientInterface $client
    ) {
    }

    /**
     * 订阅行情数据。
     *
     * @param array<int,string> $symbols 标的代码列表。
     * @param array<int,int|string> $subTypes 订阅类型，支持 SubType 常量或 quote/depth/brokers/trade。
     * @param array{is_first_push?:bool,timeout?:float} $params 可选参数。
     * @return array 订阅结果和请求摘要。
     *
     * 官方命令：Subscribe(6)，来源：Quote Subscribe。
     */
    public function subscribe(array $symbols, array $subTypes = [SubType::QUOTE], array $params = []): array
    {
        $isFirstPush = (bool)($params['is_first_push'] ?? true);
        $packet = $this->client->request(
            cmdCode: QuoteCommand::Subscribe,
            protobufBody: SubscribeProtobuf::subscribeRequest($symbols, $subTypes, $isFirstPush),
            timeout: $this->timeout($params),
            timeoutMs: $this->timeoutMs($params)
        );

        if (!$packet->isSuccess()) {
            throw new RuntimeException("Longbridge subscribe failed, status={$packet->status}");
        }

        return [
            'type' => 'longbridge_quote_subscribe',
            'symbols' => array_values($symbols),
            'sub_type' => SubscribeProtobuf::normalizeSubTypes($subTypes),
            'is_first_push' => $isFirstPush,
            'cmd' => $packet->cmdCode,
            'status' => $packet->status,
        ];
    }

    /**
     * 订阅实时报价推送。
     *
     * @param array<int,string> $symbols 标的代码列表。
     * @param bool $isFirstPush 是否立即推送一次快照。
     * @param float $timeout 请求超时时间。
     * @return array 订阅结果。
     *
     * 官方命令：Subscribe(6)，来源：Quote Subscribe。
     */
    public function subscribeQuote(array $symbols, bool $isFirstPush = true, float $timeout = 10.0): array
    {
        return $this->subscribe($symbols, [SubType::QUOTE], [
            'is_first_push' => $isFirstPush,
            'timeout' => $timeout,
        ]);
    }

    /**
     * 订阅盘口推送。
     *
     * @param array<int,string> $symbols 标的代码列表。
     * @param bool $isFirstPush 是否立即推送一次快照。
     * @param float $timeout 请求超时时间。
     * @return array 订阅结果。
     *
     * 官方命令：Subscribe(6)，来源：Quote Subscribe。
     */
    public function subscribeDepth(array $symbols, bool $isFirstPush = true, float $timeout = 10.0): array
    {
        return $this->subscribe($symbols, [SubType::DEPTH], [
            'is_first_push' => $isFirstPush,
            'timeout' => $timeout,
        ]);
    }

    /**
     * 订阅经纪队列推送。
     *
     * @param array<int,string> $symbols 标的代码列表。
     * @param bool $isFirstPush 是否立即推送一次快照。
     * @param float $timeout 请求超时时间。
     * @return array 订阅结果。
     *
     * 官方命令：Subscribe(6)，来源：Quote Subscribe。
     */
    public function subscribeBrokers(array $symbols, bool $isFirstPush = true, float $timeout = 10.0): array
    {
        return $this->subscribe($symbols, [SubType::BROKERS], [
            'is_first_push' => $isFirstPush,
            'timeout' => $timeout,
        ]);
    }

    /**
     * 订阅成交明细推送。
     *
     * @param array<int,string> $symbols 标的代码列表。
     * @param bool $isFirstPush 是否立即推送一次快照。
     * @param float $timeout 请求超时时间。
     * @return array 订阅结果。
     *
     * 官方命令：Subscribe(6)，来源：Quote Subscribe。
     */
    public function subscribeTrade(array $symbols, bool $isFirstPush = true, float $timeout = 10.0): array
    {
        return $this->subscribe($symbols, [SubType::TRADE], [
            'is_first_push' => $isFirstPush,
            'timeout' => $timeout,
        ]);
    }

    /**
     * 取消行情订阅。
     *
     * @param array<int,string> $symbols 标的代码列表。
     * @param array<int,int|string> $subTypes 订阅类型，支持 SubType 常量或 quote/depth/brokers/trade。
     * @param bool $unsubAll 是否取消当前连接全部订阅。
     * @param array{timeout?:float} $params 可选参数。
     * @return array 当前退订响应。
     *
     * 官方命令：Unsubscribe(7)，来源：Quote Subscribe。
     */
    public function unsubscribe(
        array $symbols = [],
        array $subTypes = [],
        bool $unsubAll = false,
        array $params = []
    ): array {
        $packet = $this->client->request(
            cmdCode: QuoteCommand::Unsubscribe,
            protobufBody: SubscribeProtobuf::unsubscribeRequest($symbols, $subTypes, $unsubAll),
            timeout: $this->timeout($params),
            timeoutMs: $this->timeoutMs($params)
        );

        if (!$packet->isSuccess()) {
            throw new RuntimeException("Longbridge unsubscribe failed, status={$packet->status}");
        }

        return SubscribeProtobuf::decode($packet->body, UnsubscribeResponse::class);
    }

    /**
     * 查询当前连接已有订阅。
     *
     * @param array{timeout?:float} $params 可选参数。
     * @return array 当前连接的订阅列表。
     *
     * 官方命令：Subscription(5)，来源：Quote Subscribe。
     */
    public function subscriptions(array $params = []): array
    {
        $packet = $this->client->request(
            cmdCode: QuoteCommand::Subscription,
            protobufBody: SubscribeProtobuf::subscriptionRequest(),
            timeout: $this->timeout($params),
            timeoutMs: $this->timeoutMs($params)
        );

        if (!$packet->isSuccess()) {
            throw new RuntimeException("Longbridge subscriptions failed, status={$packet->status}");
        }

        return SubscribeProtobuf::decode($packet->body, SubscriptionResponse::class);
    }

    private function timeout(array $params): float
    {
        return (float)($params['timeout'] ?? 10.0);
    }

    private function timeoutMs(array $params): int
    {
        return (int)($this->timeout($params) * 1000);
    }
}
