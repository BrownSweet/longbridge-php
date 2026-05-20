<?php

declare(strict_types=1);

namespace Brown\Longbridge\Quote\Pull;

use Brown\Longbridge\Proto\Quote\Command as QuoteCommand;
use Brown\Longbridge\Proto\Quote\IssuerInfoResponse;
use Brown\Longbridge\Proto\Quote\MarketTradeDayResponse;
use Brown\Longbridge\Proto\Quote\MarketTradePeriodResponse;
use Brown\Longbridge\Proto\Quote\OptionChainDateListResponse;
use Brown\Longbridge\Proto\Quote\OptionChainDateStrikeInfoRequest;
use Brown\Longbridge\Proto\Quote\OptionChainDateStrikeInfoResponse;
use Brown\Longbridge\Proto\Quote\OptionQuoteResponse;
use Brown\Longbridge\Proto\Quote\ParticipantBrokerIdsResponse;
use Brown\Longbridge\Proto\Quote\SecurityBrokersResponse;
use Brown\Longbridge\Proto\Quote\SecurityCalcQuoteResponse;
use Brown\Longbridge\Proto\Quote\SecurityCandlestickResponse;
use Brown\Longbridge\Proto\Quote\SecurityDepthResponse;
use Brown\Longbridge\Proto\Quote\SecurityIntradayResponse;
use Brown\Longbridge\Proto\Quote\SecurityQuoteResponse;
use Brown\Longbridge\Proto\Quote\SecurityStaticInfoResponse;
use Brown\Longbridge\Proto\Quote\SecurityTradeResponse;
use Brown\Longbridge\Proto\Quote\UserQuoteProfileRequest;
use Brown\Longbridge\Proto\Quote\UserQuoteProfileResponse;
use Brown\Longbridge\Proto\Quote\WarrantFilterListResponse;
use Brown\Longbridge\Proto\Quote\WarrantQuoteResponse;
use Brown\Longbridge\Quote\Pull\Protobuf\QuoteProtobuf;
use Brown\Longbridge\Socket\LongbridgeWsClient;
use RuntimeException;

final class SecurityQuoteApi
{
    public function __construct(
        private readonly LongbridgeWsClient $client
    ) {
    }

    /**
     * 查询用户行情权限信息。
     *
     * @param string $language 语言，例如 zh-CN、en-US。
     * @param array{timeout?:float} $params 可选参数。
     * @return array 用户行情级别、订阅限制、限频等信息。
     *
     * 官方命令：QueryUserQuoteProfile(4)，来源：Quote WebSocket Pull。
     */
    public function userQuoteProfile(string $language = 'zh-CN', array $params = []): array
    {
        $request = new UserQuoteProfileRequest();
        $request->setLanguage($language);

        return $this->request(
            QuoteCommand::QueryUserQuoteProfile,
            $request->serializeToString(),
            UserQuoteProfileResponse::class,
            $params
        );
    }

    /**
     * 查询各市场当日交易时段。
     *
     * @param array{timeout?:float} $params 可选参数。
     * @return array 市场和交易时段列表。
     *
     * 官方命令：QueryMarketTradePeriod(8)，来源：Quote WebSocket Pull。
     */
    public function marketTradePeriod(array $params = []): array
    {
        return $this->request(
            QuoteCommand::QueryMarketTradePeriod,
            '',
            MarketTradePeriodResponse::class,
            $params
        );
    }

    /**
     * 查询指定市场交易日。
     *
     * @param string $market 市场代码，例如 US、HK、CN。
     * @param string $begDay 开始日期，格式 yyyy-MM-dd。
     * @param string $endDay 结束日期，格式 yyyy-MM-dd。
     * @param array{timeout?:float} $params 可选参数。
     * @return array 交易日和半日市列表。
     *
     * 官方命令：QueryMarketTradeDay(9)，来源：Quote WebSocket Pull。
     */
    public function marketTradeDay(string $market, string $begDay, string $endDay, array $params = []): array
    {
        return $this->request(
            QuoteCommand::QueryMarketTradeDay,
            QuoteProtobuf::marketTradeDayRequest($market, $begDay, $endDay),
            MarketTradeDayResponse::class,
            $params
        );
    }

    /**
     * 查询标的基础信息。
     *
     * @param array<int,string> $symbols 标的代码列表，例如 ['AAPL.US', '700.HK']。
     * @param array|float $params 可传 ['timeout' => 10.0]；兼容旧版第二参数传 timeout。
     * @return array 标的名称、交易所、币种、股本等基础信息。
     *
     * 官方命令：QuerySecurityStaticInfo(10)，来源：Quote WebSocket Pull。
     */
    public function staticInfo(array $symbols, array|float $params = []): array
    {
        return $this->request(
            QuoteCommand::QuerySecurityStaticInfo,
            QuoteProtobuf::multiSecurityRequest($symbols),
            SecurityStaticInfoResponse::class,
            $params
        );
    }

    /**
     * 查询标的实时行情。
     *
     * @param array<int,string> $symbols 标的代码列表。
     * @param array|float $params 可传 ['timeout' => 10.0]；兼容旧版第二参数传 timeout。
     * @return array 通用行情数据。
     *
     * 官方命令：QuerySecurityQuote(11)，来源：Quote WebSocket Pull。
     */
    public function quote(array $symbols, array|float $params = []): array
    {
        return $this->request(
            QuoteCommand::QuerySecurityQuote,
            QuoteProtobuf::multiSecurityRequest($symbols),
            SecurityQuoteResponse::class,
            $params
        );
    }

    /**
     * 查询期权行情。
     *
     * @param array<int,string> $symbols 期权标的代码列表。
     * @param array{timeout?:float} $params 可选参数。
     * @return array 期权行情和期权扩展信息。
     *
     * 官方命令：QueryOptionQuote(12)，来源：Quote WebSocket Pull。
     */
    public function optionQuote(array $symbols, array $params = []): array
    {
        return $this->request(
            QuoteCommand::QueryOptionQuote,
            QuoteProtobuf::multiSecurityRequest($symbols),
            OptionQuoteResponse::class,
            $params
        );
    }

    /**
     * 查询轮证行情。
     *
     * @param array<int,string> $symbols 轮证标的代码列表。
     * @param array{timeout?:float} $params 可选参数。
     * @return array 轮证行情和轮证扩展信息。
     *
     * 官方命令：QueryWarrantQuote(13)，来源：Quote WebSocket Pull。
     */
    public function warrantQuote(array $symbols, array $params = []): array
    {
        return $this->request(
            QuoteCommand::QueryWarrantQuote,
            QuoteProtobuf::multiSecurityRequest($symbols),
            WarrantQuoteResponse::class,
            $params
        );
    }

    /**
     * 查询单个标的盘口。
     *
     * @param string $symbol 标的代码。
     * @param array|float $params 可传 ['timeout' => 10.0]；兼容旧版第二参数传 timeout。
     * @return array 买卖盘队列。
     *
     * 官方命令：QueryDepth(14)，来源：Quote WebSocket Pull。
     */
    public function depth(string $symbol, array|float $params = []): array
    {
        return $this->request(
            QuoteCommand::QueryDepth,
            QuoteProtobuf::securityRequest($symbol),
            SecurityDepthResponse::class,
            $params
        );
    }

    /**
     * 查询单个标的经纪队列。
     *
     * @param string $symbol 标的代码。
     * @param array{timeout?:float} $params 可选参数。
     * @return array 买卖盘经纪席位队列。
     *
     * 官方命令：QueryBrokers(15)，来源：Quote WebSocket Pull。
     */
    public function brokers(string $symbol, array $params = []): array
    {
        return $this->request(
            QuoteCommand::QueryBrokers,
            QuoteProtobuf::securityRequest($symbol),
            SecurityBrokersResponse::class,
            $params
        );
    }

    /**
     * 查询券商经纪席位编号。
     *
     * @param array{timeout?:float} $params 可选参数。
     * @return array 券商名称与席位编号。
     *
     * 官方命令：QueryParticipantBrokerIds(16)，来源：Quote WebSocket Pull。
     */
    public function participantBrokerIds(array $params = []): array
    {
        return $this->request(
            QuoteCommand::QueryParticipantBrokerIds,
            '',
            ParticipantBrokerIdsResponse::class,
            $params
        );
    }

    /**
     * 查询标的成交明细。
     *
     * @param string $symbol 标的代码。
     * @param int $count 返回数量。
     * @param array{timeout?:float} $params 可选参数。
     * @return array 成交明细列表。
     *
     * 官方命令：QueryTrade(17)，来源：Quote WebSocket Pull。
     */
    public function trades(string $symbol, int $count = 50, array $params = []): array
    {
        return $this->request(
            QuoteCommand::QueryTrade,
            QuoteProtobuf::securityTradeRequest($symbol, $count),
            SecurityTradeResponse::class,
            $params
        );
    }

    /**
     * 查询标的当日分时。
     *
     * @param string $symbol 标的代码。
     * @param array{trade_session?:int,timeout?:float} $params 可选参数。
     * @return array 分时线数据。
     *
     * 官方命令：QueryIntraday(18)，来源：Quote WebSocket Pull。
     */
    public function intraday(string $symbol, array $params = []): array
    {
        return $this->request(
            QuoteCommand::QueryIntraday,
            QuoteProtobuf::securityIntradayRequest($symbol, $params),
            SecurityIntradayResponse::class,
            $params
        );
    }

    /**
     * 查询标的 K 线。
     *
     * @param string $symbol 标的代码。
     * @param int $period K 线周期，使用 Brown\Longbridge\Proto\Quote\Period 常量。
     * @param int $count 返回数量。
     * @param array{adjust_type?:int,trade_session?:int,timeout?:float} $params 可选参数。
     * @return array K 线数据。
     *
     * 官方命令：QueryCandlestick(19)，来源：Quote WebSocket Pull。
     */
    public function candlesticks(string $symbol, int $period, int $count, array $params = []): array
    {
        return $this->request(
            QuoteCommand::QueryCandlestick,
            QuoteProtobuf::securityCandlestickRequest($symbol, $period, $count, $params),
            SecurityCandlestickResponse::class,
            $params
        );
    }

    /**
     * 查询标的期权链日期列表。
     *
     * @param string $symbol 标的代码。
     * @param array{timeout?:float} $params 可选参数。
     * @return array 可用到期日列表。
     *
     * 官方命令：QueryOptionChainDate(20)，来源：Quote WebSocket Pull。
     */
    public function optionChainDates(string $symbol, array $params = []): array
    {
        return $this->request(
            QuoteCommand::QueryOptionChainDate,
            QuoteProtobuf::securityRequest($symbol),
            OptionChainDateListResponse::class,
            $params
        );
    }

    /**
     * 查询期权链指定到期日的行权价信息。
     *
     * @param string $symbol 标的代码。
     * @param string $expiryDate 到期日，格式 yyyy-MM-dd。
     * @param array{timeout?:float} $params 可选参数。
     * @return array 行权价、call/put 标的代码等信息。
     *
     * 官方命令：QueryOptionChainDateStrikeInfo(21)，来源：Quote WebSocket Pull。
     */
    public function optionChainDateStrikeInfo(string $symbol, string $expiryDate, array $params = []): array
    {
        $request = new OptionChainDateStrikeInfoRequest();
        $request->setSymbol($symbol);
        $request->setExpiryDate($expiryDate);

        return $this->request(
            QuoteCommand::QueryOptionChainDateStrikeInfo,
            $request->serializeToString(),
            OptionChainDateStrikeInfoResponse::class,
            $params
        );
    }

    /**
     * 查询轮证发行商 ID。
     *
     * @param array{timeout?:float} $params 可选参数。
     * @return array 发行商 ID 与多语言名称。
     *
     * 官方命令：QueryWarrantIssuerInfo(22)，来源：Quote WebSocket Pull。
     */
    public function warrantIssuers(array $params = []): array
    {
        return $this->request(
            QuoteCommand::QueryWarrantIssuerInfo,
            '',
            IssuerInfoResponse::class,
            $params
        );
    }

    /**
     * 查询轮证筛选列表。
     *
     * @param string $symbol 正股标的代码。
     * @param array $filterConfig 筛选配置，例如 sort_by、issuer、type、status。
     * @param array{language?:int,timeout?:float} $params 可选参数。
     * @return array 轮证列表与总数。
     *
     * 官方命令：QueryWarrantFilterList(23)，来源：Quote WebSocket Pull。
     */
    public function warrantFilterList(string $symbol, array $filterConfig = [], array $params = []): array
    {
        return $this->request(
            QuoteCommand::QueryWarrantFilterList,
            QuoteProtobuf::warrantFilterListRequest(
                $symbol,
                $filterConfig,
                (int)($params['language'] ?? 0)
            ),
            WarrantFilterListResponse::class,
            $params
        );
    }

    /**
     * 查询标的资金流分时。
     *
     * @param string $symbol 标的代码。
     * @param array{timeout?:float} $params 可选参数。
     * @return array 资金流分时线。
     *
     * 官方命令：QueryCapitalFlowIntraday(24)，来源：Quote WebSocket Pull。
     */
    public function capitalFlowIntraday(string $symbol, array $params = []): array
    {
        return $this->request(
            QuoteCommand::QueryCapitalFlowIntraday,
            QuoteProtobuf::securityRequest($symbol),
            \Brown\Longbridge\Proto\Quote\CapitalFlowIntradayResponse::class,
            $params
        );
    }

    /**
     * 查询标的资金流大小单分布。
     *
     * @param string $symbol 标的代码。
     * @param array{timeout?:float} $params 可选参数。
     * @return array 大中小单流入和流出。
     *
     * 官方命令：QueryCapitalFlowDistribution(25)，来源：Quote WebSocket Pull。
     */
    public function capitalFlowDistribution(string $symbol, array $params = []): array
    {
        return $this->request(
            QuoteCommand::QueryCapitalFlowDistribution,
            QuoteProtobuf::securityRequest($symbol),
            \Brown\Longbridge\Proto\Quote\CapitalDistributionResponse::class,
            $params
        );
    }

    /**
     * 查询标的计算指标。
     *
     * @param array<int,string> $symbols 标的代码列表。
     * @param array<int,int> $calcIndexes 指标常量，使用 Brown\Longbridge\Proto\Quote\CalcIndex。
     * @param array{timeout?:float} $params 可选参数。
     * @return array 指标结果列表。
     *
     * 官方命令：QuerySecurityCalcIndex(26)，来源：Quote WebSocket Pull。
     */
    public function calcIndexes(array $symbols, array $calcIndexes, array $params = []): array
    {
        return $this->request(
            QuoteCommand::QuerySecurityCalcIndex,
            QuoteProtobuf::securityCalcQuoteRequest($symbols, $calcIndexes),
            SecurityCalcQuoteResponse::class,
            $params
        );
    }

    /**
     * 查询标的历史 K 线。
     *
     * @param string $symbol 标的代码。
     * @param int $period K 线周期，使用 Brown\Longbridge\Proto\Quote\Period 常量。
     * @param array $params 可传 start_date/end_date 或 direction/date/minute/count。
     * @return array 历史 K 线数据。
     *
     * 官方命令：QueryHistoryCandlestick(27)，来源：Quote WebSocket Pull。
     */
    public function historyCandlesticks(string $symbol, int $period, array $params = []): array
    {
        return $this->request(
            QuoteCommand::QueryHistoryCandlestick,
            QuoteProtobuf::securityHistoryCandlestickRequest($symbol, $period, $params),
            SecurityCandlestickResponse::class,
            $params
        );
    }

    private function request(int $cmdCode, string $body, string $responseClass, array|float $params): array
    {
        $timeout = $this->timeout($params);
        $packet = $this->client->request(
            cmdCode: $cmdCode,
            protobufBody: $body,
            timeout: $timeout,
            timeoutMs: (int)($timeout * 1000)
        );

        if (!$packet->isSuccess()) {
            throw new RuntimeException("Longbridge quote websocket request failed, cmd={$cmdCode}, status={$packet->status}");
        }

        return QuoteProtobuf::decode($packet->body, $responseClass);
    }

    private function timeout(array|float $params): float
    {
        if (is_float($params)) {
            return $params;
        }

        return (float)($params['timeout'] ?? 10.0);
    }
}
