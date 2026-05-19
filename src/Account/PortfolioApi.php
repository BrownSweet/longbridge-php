<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */

declare(strict_types=1);

namespace Brown\Longbridge\Account;

use Brown\Longbridge\Http\OAuthHttpClient;
use Brown\Longbridge\Support\Symbol;

final class PortfolioApi
{
    public function __construct(
        private readonly OAuthHttpClient $client
    ) {
    }

    /**
     * 查询汇率。
     *
     * 参考官方 SDK：GET /v1/asset/exchange_rates
     *
     * @return array 返回 Longbridge data 数组。
     */
    public function exchangeRates(): array
    {
        return $this->client->get('/v1/asset/exchange_rates');
    }

    /**
     * 查询收益分析总览。
     *
     * 参考官方 SDK：GET /v1/portfolio/profit-analysis-summary
     *
     * @param array $filters 可选 start_date、end_date，或直接传 start、end 时间戳。
     * @return array 返回 Longbridge data 数组。
     */
    public function profitAnalysisSummary(array $filters = []): array
    {
        return $this->client->get(
            '/v1/portfolio/profit-analysis-summary',
            $this->normalizeDateRange($filters)
        );
    }

    /**
     * 查询收益分析持仓明细列表。
     *
     * 参考官方 SDK：GET /v1/portfolio/profit-analysis-sublist
     *
     * @param array $filters 可选 start_date、end_date、profit_or_loss。
     * @return array 返回 Longbridge data 数组。
     */
    public function profitAnalysisSublist(array $filters = []): array
    {
        return $this->client->get(
            '/v1/portfolio/profit-analysis-sublist',
            array_merge(['profit_or_loss' => 'all'], $this->normalizeDateRange($filters))
        );
    }

    /**
     * 查询收益分析总览并合并持仓明细。
     *
     * 参考官方 SDK：组合调用 summary 与 sublist 两个接口。
     *
     * @param array $filters 可选 start_date、end_date、profit_or_loss。
     * @return array 包含 summary 与 sublist。
     */
    public function profitAnalysis(array $filters = []): array
    {
        return [
            'summary' => $this->profitAnalysisSummary($filters),
            'sublist' => $this->profitAnalysisSublist($filters),
        ];
    }

    /**
     * 按市场分页查询收益分析。
     *
     * 参考官方 SDK：GET /v1/portfolio/profit-analysis/by-market
     *
     * @param array $filters 可选 market、currency、page、size、start_date、end_date。
     * @return array 返回 Longbridge data 数组。
     */
    public function profitAnalysisByMarket(array $filters = []): array
    {
        return $this->client->get(
            '/v1/portfolio/profit-analysis/by-market',
            $this->normalizeDateRange(array_merge(['page' => 1, 'size' => 50], $filters))
        );
    }

    /**
     * 查询单个标的收益详情。
     *
     * 参考官方 SDK：GET /v1/portfolio/profit-analysis/detail
     *
     * @param string $symbol 标的代码，例如 TSLA.US。
     * @param array $filters 可选 start_date、end_date。
     * @return array 返回 Longbridge data 数组。
     */
    public function profitAnalysisDetail(string $symbol, array $filters = []): array
    {
        return $this->client->get(
            '/v1/portfolio/profit-analysis/detail',
            $this->normalizeDateRange(array_merge($filters, [
                'counter_id' => Symbol::toSecurityCounterId($symbol),
            ]))
        );
    }

    /**
     * 查询单个标的收益流水。
     *
     * 参考官方 SDK：GET /v1/portfolio/profit-analysis/flows
     *
     * @param string $symbol 标的代码，例如 TSLA.US。
     * @param array $filters 可选 page、size、derivative、start、end。
     * @return array 返回 Longbridge data 数组。
     */
    public function profitAnalysisFlows(string $symbol, array $filters = []): array
    {
        return $this->client->get('/v1/portfolio/profit-analysis/flows', array_merge([
            'counter_id' => Symbol::toSecurityCounterId($symbol),
            'page' => 1,
            'size' => 50,
            'derivative' => false,
        ], $filters));
    }

    private function normalizeDateRange(array $filters): array
    {
        if (isset($filters['start_date']) && !isset($filters['start'])) {
            $filters['start'] = $this->dateToTimestamp((string)$filters['start_date'], false);
        }
        if (isset($filters['end_date']) && !isset($filters['end'])) {
            $filters['end'] = $this->dateToTimestamp((string)$filters['end_date'], true);
        }

        unset($filters['start_date'], $filters['end_date']);

        return $filters;
    }

    private function dateToTimestamp(string $date, bool $endOfDay): int
    {
        $timestamp = strtotime(trim($date) . ' 00:00:00 UTC');
        if ($timestamp === false) {
            throw new \InvalidArgumentException('date must use YYYY-MM-DD format.');
        }

        return $endOfDay ? $timestamp + 86399 : $timestamp;
    }
}
