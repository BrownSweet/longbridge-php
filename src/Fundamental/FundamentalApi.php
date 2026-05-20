<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */

declare(strict_types=1);

namespace Brown\Longbridge\Fundamental;

use Brown\Longbridge\Http\HttpClientInterface;
use Brown\Longbridge\Support\Symbol;

final class FundamentalApi
{
    public function __construct(
        private readonly HttpClientInterface $client
    ) {
    }

    /**
     * 查询财务报表。
     *
     * 参考官方 SDK：GET /v1/quote/financial-reports
     *
     * @param string $symbol 标的代码，例如 AAPL.US。
     * @param string $kind 报表类型：ALL、IS、BS、CF。
     * @param array $filters 可选 report 等官方查询字段。
     * @return array 返回 Longbridge data 数组。
     */
    public function financialReports(string $symbol, string $kind = 'ALL', array $filters = []): array
    {
        return $this->client->get('/v1/quote/financial-reports', array_merge($filters, [
            'counter_id' => Symbol::toSecurityCounterId($symbol),
            'kind' => $kind,
        ]));
    }

    /**
     * 查询机构评级概览，合并最新评级与评级汇总。
     *
     * 参考官方 SDK：GET /v1/quote/institution-rating-latest 与 /v1/quote/institution-ratings
     */
    public function institutionRating(string $symbol): array
    {
        return [
            'latest' => $this->institutionRatingLatest($symbol),
            'summary' => $this->institutionRatings($symbol),
        ];
    }

    /**
     * 查询最新机构评级。
     *
     * 参考官方 SDK：GET /v1/quote/institution-rating-latest
     */
    public function institutionRatingLatest(string $symbol): array
    {
        return $this->client->get('/v1/quote/institution-rating-latest', $this->counterIdQuery($symbol));
    }

    /**
     * 查询机构评级汇总。
     *
     * 参考官方 SDK：GET /v1/quote/institution-ratings
     */
    public function institutionRatings(string $symbol): array
    {
        return $this->client->get('/v1/quote/institution-ratings', $this->counterIdQuery($symbol));
    }

    /**
     * 查询机构评级历史明细。
     *
     * 参考官方 SDK：GET /v1/quote/institution-ratings/detail
     */
    public function institutionRatingDetail(string $symbol): array
    {
        return $this->client->get('/v1/quote/institution-ratings/detail', $this->counterIdQuery($symbol));
    }

    /**
     * 查询分红历史。
     *
     * 参考官方 SDK：GET /v1/quote/dividends
     */
    public function dividends(string $symbol): array
    {
        return $this->client->get('/v1/quote/dividends', $this->counterIdQuery($symbol));
    }

    /**
     * 查询分红详情。
     *
     * 参考官方 SDK：GET /v1/quote/dividends/details
     */
    public function dividendDetail(string $symbol): array
    {
        return $this->client->get('/v1/quote/dividends/details', $this->counterIdQuery($symbol));
    }

    /**
     * 查询 EPS 预测。
     *
     * 参考官方 SDK：GET /v1/quote/forecast-eps
     */
    public function forecastEps(string $symbol): array
    {
        return $this->client->get('/v1/quote/forecast-eps', $this->counterIdQuery($symbol));
    }

    /**
     * 查询财务一致预期。
     *
     * 参考官方 SDK：GET /v1/quote/financial-consensus-detail
     */
    public function financialConsensus(string $symbol): array
    {
        return $this->client->get('/v1/quote/financial-consensus-detail', $this->counterIdQuery($symbol));
    }

    /**
     * 查询估值指标。
     *
     * 参考官方 SDK：GET /v1/quote/valuation
     *
     * @param array $filters 可选 indicator、range 等官方查询字段。
     */
    public function valuation(string $symbol, array $filters = []): array
    {
        return $this->client->get('/v1/quote/valuation', array_merge([
            'counter_id' => Symbol::toSecurityCounterId($symbol),
            'indicator' => 'pe',
            'range' => 1,
        ], $filters));
    }

    /**
     * 查询估值历史。
     *
     * 参考官方 SDK：GET /v1/quote/valuation/detail
     */
    public function valuationHistory(string $symbol, array $filters = []): array
    {
        return $this->client->get('/v1/quote/valuation/detail', array_merge(
            $this->counterIdQuery($symbol),
            $filters
        ));
    }

    /**
     * 查询行业估值对比。
     *
     * 参考官方 SDK：GET /v1/quote/industry-valuation-comparison
     */
    public function industryValuation(string $symbol): array
    {
        return $this->client->get('/v1/quote/industry-valuation-comparison', $this->counterIdQuery($symbol));
    }

    /**
     * 查询行业估值分布。
     *
     * 参考官方 SDK：GET /v1/quote/industry-valuation-distribution
     */
    public function industryValuationDistribution(string $symbol): array
    {
        return $this->client->get('/v1/quote/industry-valuation-distribution', $this->counterIdQuery($symbol));
    }

    /**
     * 查询公司资料。
     *
     * 参考官方 SDK：GET /v1/quote/comp-overview
     */
    public function companyProfile(string $symbol): array
    {
        return $this->client->get('/v1/quote/comp-overview', $this->counterIdQuery($symbol));
    }

    /**
     * 查询高管与董事资料。
     *
     * 参考官方 SDK：GET /v1/quote/company-professionals
     */
    public function executives(string $symbol): array
    {
        return $this->client->get('/v1/quote/company-professionals', [
            'counter_ids' => Symbol::toSecurityCounterId($symbol),
        ]);
    }

    /**
     * 查询主要股东。
     *
     * 参考官方 SDK：GET /v1/quote/shareholders
     */
    public function shareholders(string $symbol): array
    {
        return $this->client->get('/v1/quote/shareholders', $this->counterIdQuery($symbol));
    }

    /**
     * 查询基金持仓。
     *
     * 参考官方 SDK：GET /v1/quote/fund-holders
     */
    public function fundHoldings(string $symbol): array
    {
        return $this->client->get('/v1/quote/fund-holders', $this->counterIdQuery($symbol));
    }

    /**
     * 查询公司行动。
     *
     * 参考官方 SDK：GET /v1/quote/company-act
     *
     * @param array $filters 可选 req_type、version，默认取官方 SDK 值。
     */
    public function corporateActions(string $symbol, array $filters = []): array
    {
        return $this->client->get('/v1/quote/company-act', array_merge([
            'counter_id' => Symbol::toSecurityCounterId($symbol),
            'req_type' => 1,
            'version' => 3,
        ], $filters));
    }

    /**
     * 查询投资关系。
     *
     * 参考官方 SDK：GET /v1/quote/invest-relations
     */
    public function investmentRelations(string $symbol, array $filters = []): array
    {
        return $this->client->get('/v1/quote/invest-relations', array_merge([
            'counter_id' => Symbol::toSecurityCounterId($symbol),
            'count' => 0,
        ], $filters));
    }

    /**
     * 查询经营指标。
     *
     * 参考官方 SDK：GET /v1/quote/operatings
     */
    public function operatingMetrics(string $symbol): array
    {
        return $this->client->get('/v1/quote/operatings', $this->counterIdQuery($symbol));
    }

    /**
     * 查询回购数据。
     *
     * 参考官方 SDK：GET /v1/quote/buy-backs
     */
    public function buyback(string $symbol): array
    {
        return $this->client->get('/v1/quote/buy-backs', $this->counterIdQuery($symbol));
    }

    /**
     * 查询股票综合评级。
     *
     * 参考官方 SDK：GET /v1/quote/ratings
     */
    public function ratings(string $symbol): array
    {
        return $this->client->get('/v1/quote/ratings', $this->counterIdQuery($symbol));
    }

    private function counterIdQuery(string $symbol): array
    {
        return ['counter_id' => Symbol::toSecurityCounterId($symbol)];
    }
}
