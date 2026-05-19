<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */

declare(strict_types=1);

namespace Brown\Longbridge\Market;

use Brown\Longbridge\Http\OAuthHttpClient;
use Brown\Longbridge\Support\Symbol;

final class MarketApi
{
    public function __construct(
        private readonly OAuthHttpClient $client
    ) {
    }

    /**
     * 查询市场交易状态。
     *
     * 参考官方 SDK：GET /v1/quote/market-status
     *
     * @param string|null $market 可选市场代码，例如 US、HK、CN、SG。
     * @return array 返回 Longbridge data 数组。
     */
    public function marketStatus(?string $market = null): array
    {
        return $this->client->get(
            '/v1/quote/market-status',
            $market !== null && trim($market) !== '' ? ['market' => strtoupper(trim($market))] : []
        );
    }

    /**
     * 查询当前市场温度。
     *
     * 官方路径：GET /v1/quote/market_temperature
     *
     * @param string $market 市场代码，例如 US、HK、CN、SG。
     * @return array 返回 Longbridge data 数组。
     */
    public function marketTemperature(string $market): array
    {
        return $this->client->get('/v1/quote/market_temperature', [
            'market' => strtoupper(trim($market)),
        ]);
    }

    /**
     * 查询历史市场温度。
     *
     * 官方路径：GET /v1/quote/history_market_temperature
     *
     * @param string $market 市场代码。
     * @param array $filters 可选 start、end、count 等官方查询字段。
     * @return array 返回 Longbridge data 数组。
     */
    public function historyMarketTemperature(string $market, array $filters = []): array
    {
        return $this->client->get('/v1/quote/history_market_temperature', array_merge($filters, [
            'market' => strtoupper(trim($market)),
        ]));
    }

    /**
     * 查询券商持仓买卖榜。
     *
     * 参考官方 SDK：GET /v1/quote/broker-holding
     *
     * @param string $symbol 标的代码，例如 700.HK。
     * @param string $period 周期，例如 rct_1、rct_5、rct_20、rct_60。
     * @return array 返回 Longbridge data 数组。
     */
    public function brokerHolding(string $symbol, string $period = 'rct_1'): array
    {
        return $this->client->get('/v1/quote/broker-holding', [
            'counter_id' => Symbol::toQuoteCounterId($symbol),
            'type' => $period,
        ]);
    }

    /**
     * 查询券商持仓明细。
     *
     * 参考官方 SDK：GET /v1/quote/broker-holding/detail
     *
     * @param string $symbol 标的代码。
     * @return array 返回 Longbridge data 数组。
     */
    public function brokerHoldingDetail(string $symbol): array
    {
        return $this->client->get('/v1/quote/broker-holding/detail', [
            'counter_id' => Symbol::toQuoteCounterId($symbol),
        ]);
    }

    /**
     * 查询某券商每日持仓变化。
     *
     * 参考官方 SDK：GET /v1/quote/broker-holding/daily
     *
     * @param string $symbol 标的代码。
     * @param string $brokerId 券商席位编号。
     * @return array 返回 Longbridge data 数组。
     */
    public function brokerHoldingDaily(string $symbol, string $brokerId): array
    {
        return $this->client->get('/v1/quote/broker-holding/daily', [
            'counter_id' => Symbol::toQuoteCounterId($symbol),
            'parti_number' => $brokerId,
        ]);
    }

    /**
     * 查询 A/H 溢价 K 线。
     *
     * 参考官方 SDK：GET /v1/quote/ahpremium/klines
     *
     * @param string $symbol AH 标的代码。
     * @param array $filters 可选 line_type、line_num。
     * @return array 返回 Longbridge data 数组。
     */
    public function ahPremium(string $symbol, array $filters = []): array
    {
        return $this->client->get('/v1/quote/ahpremium/klines', array_merge([
            'counter_id' => Symbol::toQuoteCounterId($symbol),
            'line_type' => 'day',
            'line_num' => 100,
        ], $filters));
    }

    /**
     * 查询 A/H 溢价分时。
     *
     * 参考官方 SDK：GET /v1/quote/ahpremium/timeshares
     *
     * @param string $symbol AH 标的代码。
     * @return array 返回 Longbridge data 数组。
     */
    public function ahPremiumIntraday(string $symbol): array
    {
        return $this->client->get('/v1/quote/ahpremium/timeshares', [
            'counter_id' => Symbol::toQuoteCounterId($symbol),
            'days' => 1,
        ]);
    }

    /**
     * 查询成交统计。
     *
     * 参考官方 SDK：GET /v1/quote/trades-statistics
     *
     * @param string $symbol 标的代码。
     * @return array 返回 Longbridge data 数组。
     */
    public function tradingStats(string $symbol): array
    {
        return $this->client->get('/v1/quote/trades-statistics', [
            'counter_id' => Symbol::toQuoteCounterId($symbol),
        ]);
    }

    /**
     * 查询市场异动。
     *
     * 参考官方 SDK：GET /v1/quote/changes
     *
     * @param string $market 市场代码。
     * @param array $filters 可选 category 等官方查询字段。
     * @return array 返回 Longbridge data 数组。
     */
    public function anomalies(string $market, array $filters = []): array
    {
        return $this->client->get('/v1/quote/changes', array_merge([
            'market' => strtoupper(trim($market)),
            'category' => 0,
        ], $filters));
    }

    /**
     * 查询指数成分股。
     *
     * 参考官方 SDK：GET /v1/quote/index-constituents
     *
     * @param string $indexSymbol 指数代码，例如 HSI.HK。
     * @return array 返回 Longbridge data 数组。
     */
    public function indexConstituents(string $indexSymbol): array
    {
        return $this->client->get('/v1/quote/index-constituents', [
            'counter_id' => Symbol::toIndexCounterId($indexSymbol),
        ]);
    }
}
