<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2026-05-18 12:11
 */

declare(strict_types=1);

namespace Brown\Longbridge\Trade;

use Brown\Longbridge\Http\OAuthHttpClient;

final class TradeApi
{
    public function __construct(
        private readonly OAuthHttpClient $client
    ) {
    }

    /**
     * 提交订单。
     *
     * 官方路径：POST /v1/trade/order
     *
     * @param array $params 下单参数，例如 symbol、order_type、side、submitted_quantity、time_in_force。
     * @return array 返回 Longbridge data 数组，通常包含 order_id。
     */
    public function submitOrder(array $params): array
    {
        return $this->client->post('/v1/trade/order', $params);
    }

    /**
     * 修改订单价格或数量。
     *
     * 官方路径：PUT /v1/trade/order
     *
     * @param array $params 改单参数，必须包含 order_id。
     * @return array 返回 Longbridge data 数组。
     */
    public function replaceOrder(array $params): array
    {
        return $this->client->put('/v1/trade/order', $params);
    }

    /**
     * 撤销订单。
     *
     * 官方路径：DELETE /v1/trade/order
     *
     * @param string $orderId 订单 ID。
     * @return array 返回 Longbridge data 数组。
     */
    public function withdrawOrder(string $orderId): array
    {
        return $this->client->delete('/v1/trade/order', [
            'order_id' => $orderId,
        ]);
    }

    /**
     * 撤销订单，withdrawOrder 的语义别名。
     *
     * 官方路径：DELETE /v1/trade/order
     */
    public function cancelOrder(string $orderId): array
    {
        return $this->withdrawOrder($orderId);
    }

    /**
     * 查询订单详情。
     *
     * 官方路径：GET /v1/trade/order
     *
     * @param string $orderId 订单 ID。
     * @return array 返回 Longbridge data 数组。
     */
    public function getOrderDetail(string $orderId): array
    {
        return $this->client->get('/v1/trade/order', [
            'order_id' => $orderId,
        ]);
    }

    /**
     * 查询当日订单。
     *
     * 官方路径：GET /v1/trade/order/today
     *
     * @param array $filters 查询条件，例如 symbol、status、side、market。
     * @return array 返回 Longbridge data 数组。
     */
    public function getTodayOrders(array $filters = []): array
    {
        return $this->client->get('/v1/trade/order/today', $filters);
    }

    /**
     * 查询历史订单。
     *
     * 官方路径：GET /v1/trade/order/history
     *
     * @param array $filters 查询条件，例如 symbol、status、side、market、start_at、end_at。
     * @return array 返回 Longbridge data 数组。
     */
    public function getHistoryOrders(array $filters): array
    {
        return $this->client->get('/v1/trade/order/history', $filters);
    }

    /**
     * 预估最大可买数量。
     *
     * 官方路径：GET /v1/trade/estimate/buy_limit
     *
     * @param array $filters 查询条件，例如 symbol、order_type、price、currency、side。
     * @return array 返回 Longbridge data 数组。
     */
    public function estimateMaxBuy(array $filters): array
    {
        return $this->client->get('/v1/trade/estimate/buy_limit', $filters);
    }

    /**
     * 预估最大可买数量，estimateMaxBuy 的清晰别名。
     */
    public function estimateMaxPurchaseQuantity(array $filters): array
    {
        return $this->estimateMaxBuy($filters);
    }

    /**
     * 查询当日成交。
     *
     * 官方路径：GET /v1/trade/execution/today
     *
     * @param array $filters 查询条件，例如 symbol、order_id。
     * @return array 返回 Longbridge data 数组。
     */
    public function getTodayExecutions(array $filters = []): array
    {
        return $this->client->get('/v1/trade/execution/today', $filters);
    }

    /**
     * 查询历史成交。
     *
     * 官方路径：GET /v1/trade/execution/history
     *
     * @param array $filters 查询条件，例如 symbol、start_at、end_at。
     * @return array 返回 Longbridge data 数组。
     */
    public function getHistoryExecutions(array $filters): array
    {
        return $this->client->get('/v1/trade/execution/history', $filters);
    }
}
