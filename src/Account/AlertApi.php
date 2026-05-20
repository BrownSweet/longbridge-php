<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */

declare(strict_types=1);

namespace Brown\Longbridge\Account;

use Brown\Longbridge\Http\HttpClientInterface;

final class AlertApi
{
    public function __construct(
        private readonly HttpClientInterface $client
    ) {
    }

    /**
     * 查询价格提醒。
     *
     * 官方路径：GET /v1/notify/reminders
     *
     * @param string|null $symbol 可选标的代码，例如 TSLA.US。
     * @return array 返回 Longbridge data 数组。
     */
    public function listAlerts(?string $symbol = null): array
    {
        return $this->client->get(
            '/v1/notify/reminders',
            $symbol !== null && trim($symbol) !== '' ? ['symbol' => trim($symbol)] : []
        );
    }

    /**
     * 创建价格提醒。
     *
     * 官方路径：POST /v1/notify/reminders
     *
     * @param string $symbol 标的代码。
     * @param string $price 触发价格或涨跌幅值。
     * @param string $direction rise、fall、pct_rise、pct_fall。
     * @param string $frequency once、every、daily。
     * @param array $params 额外原始字段，会覆盖默认 payload。
     * @return array 返回 Longbridge data 数组，通常包含 id。
     */
    public function createAlert(
        string $symbol,
        string $price,
        string $direction = 'rise',
        string $frequency = 'once',
        array $params = []
    ): array {
        $indicatorId = $this->indicatorId($direction);
        $valueKey = in_array($indicatorId, [3, 4], true) ? 'chg' : 'price';

        return $this->client->post('/v1/notify/reminders', array_merge([
            'symbol' => $symbol,
            'indicator_id' => (string)$indicatorId,
            'value_map' => [$valueKey => $price],
            'frequency' => $this->frequencyId($frequency),
            'enabled' => true,
            'scope' => 0,
            'state' => [1],
        ], $params));
    }

    /**
     * 更新价格提醒。
     *
     * 官方路径：POST /v1/notify/reminders
     *
     * @param string $alertId 提醒 ID。
     * @param array $params 从 listAlerts 返回项中取出的可更新字段。
     * @return array 返回 Longbridge data 数组。
     */
    public function updateAlert(string $alertId, array $params): array
    {
        return $this->client->post('/v1/notify/reminders', array_merge($params, [
            'id' => $alertId,
        ]));
    }

    /**
     * 删除一个或多个价格提醒。
     *
     * 官方路径：DELETE /v1/notify/reminders
     *
     * @param array $alertIds 提醒 ID 列表。
     * @return array 返回 Longbridge data 数组。
     */
    public function deleteAlerts(array $alertIds): array
    {
        return $this->client->delete('/v1/notify/reminders', [], [
            'ids' => array_values($alertIds),
        ]);
    }

    private function indicatorId(string $direction): int
    {
        return match (strtolower(trim($direction))) {
            'fall', 'price_fall' => 2,
            'pct_rise', 'percent_rise' => 3,
            'pct_fall', 'percent_fall' => 4,
            default => 1,
        };
    }

    private function frequencyId(string $frequency): int
    {
        return match (strtolower(trim($frequency))) {
            'daily' => 1,
            'every', 'every_time' => 2,
            default => 3,
        };
    }
}
