<?php

declare(strict_types=1);

namespace Brown\Longbridge\Trade\Push;

use Brown\Longbridge\Proto\Trade\Command as TradeCommand;
use Brown\Longbridge\Socket\LongbridgeWsClient;
use Brown\Longbridge\Trade\Push\Protobuf\TradePushProtobuf;
use RuntimeException;

final class TradePushApi
{
    public function __construct(
        private readonly LongbridgeWsClient $client
    ) {
    }

    /**
     * 订阅交易私有推送主题。
     *
     * @param array<int,string> $topics 主题列表，官方私有推送主题为 private。
     * @param array{timeout?:float} $params 可选参数。
     * @return array 订阅成功、失败和当前主题列表。
     *
     * 官方命令：CMD_SUB(16)，来源：Trade Push。
     */
    public function subscribe(array $topics = ['private'], array $params = []): array
    {
        $packet = $this->client->request(
            cmdCode: TradeCommand::CMD_SUB,
            protobufBody: TradePushProtobuf::subRequest($topics),
            timeout: $this->timeout($params),
            timeoutMs: $this->timeoutMs($params)
        );

        if (!$packet->isSuccess()) {
            throw new RuntimeException("Longbridge trade subscribe failed, status={$packet->status}");
        }

        return TradePushProtobuf::decodeSubResponse($packet->body);
    }

    /**
     * 订阅官方 private 私有交易推送主题。
     *
     * @param array{timeout?:float} $params 可选参数。
     * @return array 订阅成功、失败和当前主题列表。
     *
     * 官方命令：CMD_SUB(16)，来源：Trade Push。
     */
    public function subscribePrivate(array $params = []): array
    {
        return $this->subscribe(['private'], $params);
    }

    /**
     * 取消交易私有推送主题。
     *
     * @param array<int,string> $topics 主题列表，官方私有推送主题为 private。
     * @param array{timeout?:float} $params 可选参数。
     * @return array 当前订阅主题列表。
     *
     * 官方命令：CMD_UNSUB(17)，来源：Trade Push。
     */
    public function unsubscribe(array $topics = ['private'], array $params = []): array
    {
        $packet = $this->client->request(
            cmdCode: TradeCommand::CMD_UNSUB,
            protobufBody: TradePushProtobuf::unsubRequest($topics),
            timeout: $this->timeout($params),
            timeoutMs: $this->timeoutMs($params)
        );

        if (!$packet->isSuccess()) {
            throw new RuntimeException("Longbridge trade unsubscribe failed, status={$packet->status}");
        }

        return TradePushProtobuf::decodeUnsubResponse($packet->body);
    }

    /**
     * 等待交易私有推送通知。
     *
     * @param float $timeout 等待超时时间，单位秒。
     * @return array 交易通知，包含 topic、content_type、dispatch_type、data、data_base64 和可选 data_json。
     *
     * 官方命令：CMD_NOTIFY(18)，来源：Trade Push。
     */
    public function waitNotification(float $timeout = 15.0): array
    {
        $deadline = microtime(true) + $timeout;

        while (true) {
            $left = $deadline - microtime(true);
            if ($left <= 0) {
                throw new RuntimeException('Longbridge wait trade notification timeout.');
            }

            $packet = $this->client->recv(min($left, 5.0));
            if (!$packet || !$packet->isPush() || $packet->cmdCode !== TradeCommand::CMD_NOTIFY) {
                continue;
            }

            return [
                'type' => 'trade_notification',
                'cmd' => $packet->cmdCode,
                'payload' => TradePushProtobuf::decodeNotification($packet->body),
            ];
        }
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
