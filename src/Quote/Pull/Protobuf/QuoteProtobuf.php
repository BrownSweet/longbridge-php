<?php

declare(strict_types=1);

namespace Brown\Longbridge\Quote\Pull\Protobuf;

use Brown\Longbridge\Proto\Quote\AdjustType;
use Brown\Longbridge\Proto\Quote\Direction;
use Brown\Longbridge\Proto\Quote\FilterConfig;
use Brown\Longbridge\Proto\Quote\HistoryCandlestickQueryType;
use Brown\Longbridge\Proto\Quote\MarketTradeDayRequest;
use Brown\Longbridge\Proto\Quote\MultiSecurityRequest;
use Brown\Longbridge\Proto\Quote\SecurityCalcQuoteRequest;
use Brown\Longbridge\Proto\Quote\SecurityCandlestickRequest;
use Brown\Longbridge\Proto\Quote\SecurityHistoryCandlestickRequest;
use Brown\Longbridge\Proto\Quote\SecurityHistoryCandlestickRequest\DateQuery;
use Brown\Longbridge\Proto\Quote\SecurityHistoryCandlestickRequest\OffsetQuery;
use Brown\Longbridge\Proto\Quote\SecurityIntradayRequest;
use Brown\Longbridge\Proto\Quote\SecurityRequest;
use Brown\Longbridge\Proto\Quote\SecurityTradeRequest;
use Brown\Longbridge\Proto\Quote\WarrantFilterListRequest;
use Brown\Longbridge\Support\Protobuf;
use Google\Protobuf\Internal\Message;

final class QuoteProtobuf
{
    /**
     * 构造多标的请求，适用于报价、静态信息、期权/轮证报价等命令。
     */
    public static function multiSecurityRequest(array $symbols): string
    {
        $request = new MultiSecurityRequest();
        $request->setSymbol(array_values($symbols));

        return $request->serializeToString();
    }

    /**
     * 构造单标的请求，适用于盘口、经纪队列、期权链、资金分布等命令。
     */
    public static function securityRequest(string $symbol): string
    {
        $request = new SecurityRequest();
        $request->setSymbol($symbol);

        return $request->serializeToString();
    }

    /**
     * 构造成交明细请求。
     */
    public static function securityTradeRequest(string $symbol, int $count): string
    {
        $request = new SecurityTradeRequest();
        $request->setSymbol($symbol);
        $request->setCount($count);

        return $request->serializeToString();
    }

    /**
     * 构造当日分时请求。
     */
    public static function securityIntradayRequest(string $symbol, array $params = []): string
    {
        $request = new SecurityIntradayRequest();
        $request->setSymbol($symbol);
        $request->setTradeSession((int)($params['trade_session'] ?? 0));

        return $request->serializeToString();
    }

    /**
     * 构造 K 线请求。
     */
    public static function securityCandlestickRequest(
        string $symbol,
        int $period,
        int $count,
        array $params = []
    ): string {
        $request = new SecurityCandlestickRequest();
        $request->setSymbol($symbol);
        $request->setPeriod($period);
        $request->setCount($count);
        $request->setAdjustType((int)($params['adjust_type'] ?? AdjustType::NO_ADJUST));
        $request->setTradeSession((int)($params['trade_session'] ?? 0));

        return $request->serializeToString();
    }

    /**
     * 构造历史 K 线请求，支持按日期区间或按偏移翻页查询。
     */
    public static function securityHistoryCandlestickRequest(
        string $symbol,
        int $period,
        array $params = []
    ): string {
        $request = new SecurityHistoryCandlestickRequest();
        $request->setSymbol($symbol);
        $request->setPeriod($period);
        $request->setAdjustType((int)($params['adjust_type'] ?? AdjustType::NO_ADJUST));
        $request->setTradeSession((int)($params['trade_session'] ?? 0));

        if (isset($params['start_date']) || isset($params['end_date'])) {
            $dateQuery = new DateQuery();
            $dateQuery->setStartDate((string)($params['start_date'] ?? ''));
            $dateQuery->setEndDate((string)($params['end_date'] ?? ''));
            $request->setQueryType(HistoryCandlestickQueryType::QUERY_BY_DATE);
            $request->setDateRequest($dateQuery);
        } else {
            $offsetQuery = new OffsetQuery();
            $offsetQuery->setDirection((int)($params['direction'] ?? Direction::BACKWARD));
            $offsetQuery->setDate((string)($params['date'] ?? ''));
            $offsetQuery->setMinute((string)($params['minute'] ?? ''));
            $offsetQuery->setCount((int)($params['count'] ?? 50));
            $request->setQueryType(HistoryCandlestickQueryType::QUERY_BY_OFFSET);
            $request->setOffsetRequest($offsetQuery);
        }

        return $request->serializeToString();
    }

    /**
     * 构造交易日请求。
     */
    public static function marketTradeDayRequest(string $market, string $begDay, string $endDay): string
    {
        $request = new MarketTradeDayRequest();
        $request->setMarket($market);
        $request->setBegDay($begDay);
        $request->setEndDay($endDay);

        return $request->serializeToString();
    }

    /**
     * 构造轮证筛选请求。
     */
    public static function warrantFilterListRequest(
        string $symbol,
        array $filterConfig = [],
        int $language = 0
    ): string {
        $config = new FilterConfig();
        self::setInt($config, 'setSortBy', $filterConfig, 'sort_by');
        self::setInt($config, 'setSortOrder', $filterConfig, 'sort_order');
        self::setInt($config, 'setSortOffset', $filterConfig, 'sort_offset');
        self::setInt($config, 'setSortCount', $filterConfig, 'sort_count');
        self::setIntList($config, 'setType', $filterConfig, 'type');
        self::setIntList($config, 'setIssuer', $filterConfig, 'issuer');
        self::setIntList($config, 'setExpiryDate', $filterConfig, 'expiry_date');
        self::setIntList($config, 'setPriceType', $filterConfig, 'price_type');
        self::setIntList($config, 'setStatus', $filterConfig, 'status');

        $request = new WarrantFilterListRequest();
        $request->setSymbol($symbol);
        $request->setFilterConfig($config);
        $request->setLanguage($language);

        return $request->serializeToString();
    }

    /**
     * 构造指标查询请求。
     */
    public static function securityCalcQuoteRequest(array $symbols, array $calcIndexes): string
    {
        $request = new SecurityCalcQuoteRequest();
        $request->setSymbols(array_values($symbols));
        $request->setCalcIndex(array_map('intval', array_values($calcIndexes)));

        return $request->serializeToString();
    }

    /**
     * 反序列化 protobuf 响应为 snake_case 数组。
     *
     * @param class-string<Message> $messageClass
     */
    public static function decode(string $body, string $messageClass): array
    {
        return Protobuf::decode($body, $messageClass);
    }

    private static function setInt(FilterConfig $config, string $setter, array $params, string $key): void
    {
        if (array_key_exists($key, $params)) {
            $config->{$setter}((int)$params[$key]);
        }
    }

    private static function setIntList(FilterConfig $config, string $setter, array $params, string $key): void
    {
        if (array_key_exists($key, $params)) {
            $config->{$setter}(array_map('intval', (array)$params[$key]));
        }
    }
}
