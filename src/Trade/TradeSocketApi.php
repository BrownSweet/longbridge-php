<?php

declare(strict_types=1);

namespace Brown\Longbridge\Trade;

use Brown\Longbridge\Socket\LongbridgeWsClient;
use Brown\Longbridge\Socket\WsClientInterface;
use Brown\Longbridge\Trade\Push\TradePushApi;

final class TradeSocketApi
{
    private ?TradePushApi $push = null;

    public function __construct(
        private readonly WsClientInterface $client
    ) {
    }

    /**
     * 返回底层 WebSocket 客户端，便于调用未封装的命令。
     */
    public function client(): WsClientInterface
    {
        return $this->client;
    }

    /**
     * 交易私有推送 API，用于订阅 private 主题和等待通知。
     */
    public function push(): TradePushApi
    {
        return $this->push ??= new TradePushApi($this->client);
    }
}
