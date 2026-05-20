<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */

declare(strict_types=1);

namespace Brown\Longbridge\Account;

use Brown\Longbridge\Http\HttpClientInterface;
use Brown\Longbridge\Support\Symbol;

final class DcaApi
{
    public function __construct(
        private readonly HttpClientInterface $client
    ) {
    }

    /**
     * 查询当前用户的定投计划。
     *
     * 参考官方 SDK：GET /v1/dailycoins/query
     *
     * @param array $filters 可选 status、symbol、page、limit；symbol 会转为 counter_id。
     * @return array 返回 Longbridge data 数组。
     */
    public function listPlans(array $filters = []): array
    {
        $query = array_merge(['page' => 1, 'limit' => 100], $filters);
        if (isset($query['symbol'])) {
            $query['counter_id'] = Symbol::toSecurityCounterId((string)$query['symbol']);
            unset($query['symbol']);
        }

        return $this->client->get('/v1/dailycoins/query', $query);
    }

    /**
     * 创建定投计划。
     *
     * 参考官方 SDK：POST /v1/dailycoins/create
     *
     * @param string $symbol 标的代码，例如 AAPL.US。
     * @param string $amount 每次投入金额。
     * @param string $frequency 频率：Daily、Weekly、Fortnightly、Monthly。
     * @param array $params 可选 invest_day_of_week、invest_day_of_month、allow_margin_finance。
     * @return array 返回 Longbridge data 数组，通常包含 id 或 plan_id。
     */
    public function createPlan(string $symbol, string $amount, string $frequency, array $params = []): array
    {
        $payload = array_merge($params, [
            'counter_id' => Symbol::toSecurityCounterId($symbol),
            'per_invest_amount' => $amount,
            'invest_frequency' => $frequency,
        ]);

        if (array_key_exists('allow_margin', $payload) && !array_key_exists('allow_margin_finance', $payload)) {
            $payload['allow_margin_finance'] = $payload['allow_margin'] ? 1 : 0;
            unset($payload['allow_margin']);
        }

        return $this->client->post('/v1/dailycoins/create', $payload);
    }

    /**
     * 更新定投计划。
     *
     * 参考官方 SDK：POST /v1/dailycoins/update
     *
     * @param string $planId 定投计划 ID。
     * @param array $params 需要更新的字段，例如 per_invest_amount、invest_frequency。
     * @return array 返回 Longbridge data 数组。
     */
    public function updatePlan(string $planId, array $params): array
    {
        return $this->client->post('/v1/dailycoins/update', array_merge($params, [
            'plan_id' => $planId,
        ]));
    }

    /**
     * 暂停定投计划。
     *
     * 参考官方 SDK：POST /v1/dailycoins/toggle
     */
    public function pausePlan(string $planId): array
    {
        return $this->togglePlan($planId, 'Suspended');
    }

    /**
     * 恢复定投计划。
     *
     * 参考官方 SDK：POST /v1/dailycoins/toggle
     */
    public function resumePlan(string $planId): array
    {
        return $this->togglePlan($planId, 'Active');
    }

    /**
     * 停止定投计划。
     *
     * 参考官方 SDK：POST /v1/dailycoins/toggle
     */
    public function stopPlan(string $planId): array
    {
        return $this->togglePlan($planId, 'Finished');
    }

    /**
     * 删除定投计划。
     *
     * 官方文档未给独立 HTTP URL；这里沿用官方 SDK 的停止实现，将状态置为 Finished。
     */
    public function deletePlan(string $planId): array
    {
        return $this->stopPlan($planId);
    }

    /**
     * 查询定投执行历史。
     *
     * 参考官方 SDK：GET /v1/dailycoins/query-records
     *
     * @param string $planId 定投计划 ID。
     * @param array $filters 可选 page、limit。
     * @return array 返回 Longbridge data 数组。
     */
    public function history(string $planId, array $filters = []): array
    {
        return $this->client->get('/v1/dailycoins/query-records', array_merge([
            'plan_id' => $planId,
            'page' => 1,
            'limit' => 100,
        ], $filters));
    }

    /**
     * 查询定投统计。
     *
     * 参考官方 SDK：GET /v1/dailycoins/statistic
     *
     * @param array $filters 可选 symbol；symbol 会转为 counter_id。
     * @return array 返回 Longbridge data 数组。
     */
    public function stats(array $filters = []): array
    {
        if (isset($filters['symbol'])) {
            $filters['counter_id'] = Symbol::toSecurityCounterId((string)$filters['symbol']);
            unset($filters['symbol']);
        }

        return $this->client->get('/v1/dailycoins/statistic', $filters);
    }

    /**
     * 批量检查标的是否支持定投。
     *
     * 参考官方 SDK：POST /v1/dailycoins/batch-check-support
     *
     * @param array $symbols 标的代码列表。
     * @return array 返回 Longbridge data 数组。
     */
    public function checkSupport(array $symbols): array
    {
        return $this->client->post('/v1/dailycoins/batch-check-support', [
            'counter_ids' => array_map(
                static fn (string $symbol): string => Symbol::toSecurityCounterId($symbol),
                Symbol::normalizeList($symbols)
            ),
        ]);
    }

    /**
     * 计算定投计划下一次交易日。
     *
     * 参考官方 SDK：POST /v1/dailycoins/calc-trd-date
     *
     * @param string $symbol 标的代码。
     * @param string $frequency 频率：Daily、Weekly、Fortnightly、Monthly。
     * @param array $params 可选 invest_day_of_week、invest_day_of_month。
     * @return array 返回 Longbridge data 数组。
     */
    public function calculateDate(string $symbol, string $frequency, array $params = []): array
    {
        return $this->client->post('/v1/dailycoins/calc-trd-date', array_merge($params, [
            'counter_id' => Symbol::toSecurityCounterId($symbol),
            'invest_frequency' => $frequency,
        ]));
    }

    /**
     * 设置定投提醒提前时间。
     *
     * 参考官方 SDK：POST /v1/dailycoins/update-alter-hours
     *
     * @param string $hours 提醒小时数，官方常用值为 1、6、12。
     * @return array 返回 Longbridge data 数组。
     */
    public function setReminder(string $hours): array
    {
        return $this->client->post('/v1/dailycoins/update-alter-hours', [
            'alter_hours' => $hours,
        ]);
    }

    private function togglePlan(string $planId, string $status): array
    {
        return $this->client->post('/v1/dailycoins/toggle', [
            'plan_id' => $planId,
            'status' => $status,
        ]);
    }
}
