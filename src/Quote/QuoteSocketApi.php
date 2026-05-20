<?php

declare(strict_types=1);

namespace Brown\Longbridge\Quote;

use Brown\Longbridge\Quote\Pull\SecurityQuoteApi;
use Brown\Longbridge\Quote\Push\QuotePushApi;
use Brown\Longbridge\Quote\Subscribe\SubscriptionApi;
use Brown\Longbridge\Socket\LongbridgeWsClient;

final class QuoteSocketApi
{
    private ?SecurityQuoteApi $pull = null;
    private ?SubscriptionApi $subscribe = null;
    private ?QuotePushApi $push = null;

    public function __construct(
        private readonly LongbridgeWsClient $client
    ) {
    }

    /**
     * 返回底层 WebSocket 客户端，便于调用未封装的命令。
     */
    public function client(): LongbridgeWsClient
    {
        return $this->client;
    }

    /**
     * 行情拉取 API，覆盖 Quote WebSocket Pull 命令。
     */
    public function pull(): SecurityQuoteApi
    {
        return $this->pull ??= new SecurityQuoteApi($this->client);
    }

    /**
     * 行情订阅 API，覆盖订阅、退订和查询当前订阅。
     */
    public function subscribe(): SubscriptionApi
    {
        return $this->subscribe ??= new SubscriptionApi($this->client);
    }

    /**
     * 行情推送 API，用于等待并解析 quote/depth/brokers/trade 推送。
     */
    public function push(): QuotePushApi
    {
        return $this->push ??= new QuotePushApi($this->client);
    }
}
