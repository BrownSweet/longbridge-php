# Feature Coverage Matrix

This document describes the current feature coverage, automated test status, and real integration-test boundary for `brown/longbridge-php`.

## Status Legend

| Label | Meaning |
|---|---|
| Implemented | Public SDK entrypoint exists |
| Tested | Covered by unit tests or API wrapper behavior tests |
| Optional integration | Requires real token, network, or extension and is skipped by default |
| Test pending | Implemented, but automated coverage is not complete yet |
| Needs confirmation | Requires further validation against official docs or real environments |
| N/A | No matching test category is needed |

## Core Capabilities

| Requirement / Feature | SDK Entry | Implementation | Automated Test | Integration Test | Notes |
|---|---|---:|---:|---:|---|
| Mainland China OAuth HTTP client | `LongbridgeClient::cnHttp()` | Implemented | Tested | Test pending | Token normalization covered |
| Overseas OAuth HTTP client | `LongbridgeClient::hkHttp()` | Implemented | Tested | Test pending | Endpoint selection covered |
| Mainland China legacy signed client | `LongbridgeClient::cnLegacy()` | Implemented | Tested | Test pending | Legacy token validation covered |
| Overseas legacy signed client | `LongbridgeClient::hkLegacy()` | Implemented | Tested | Test pending | Endpoint selection covered |
| OAuth + legacy hybrid client | `LongbridgeClient::cnHybrid()` / `hkHybrid()` | Implemented | Tested | Test pending | OAuth is preferred for HTTP calls |
| Refresh OAuth token in client | `LongbridgeClient::setAccessToken()` | Implemented | Test pending | Test pending | Low-level token behavior is covered |
| Raw HTTP calls for uncovered APIs | `$client->http()->get/post/put/delete()` | Implemented | Tested | Test pending | Query serialization covered |
| OAuth PKCE | `Pkce::generateCodeVerifier()` / `buildCodeChallenge()` | Implemented | Tested | N/A | RFC 7636 vector covered |
| OAuth token object | `OAuthToken::fromArray()` | Implemented | Tested | N/A | Expiry checks covered |
| OAuth registration, authorize URL, token exchange | `OAuthClient` | Implemented | Test pending | Test pending | Guzzle mock tests should be added |
| Legacy signature | `LegacySigner::sign()` | Implemented | Tested | Test pending | Stable signature snapshot covered |
| Unified exception type | `LongbridgeException` | Implemented | Test pending | Test pending | Token invalid / rate-limited helpers should be covered |
| Symbol conversion | `Symbol` | Implemented | Tested | N/A | counter_id, quote id, and index id covered |

## HTTP API Coverage

| Module | Official API / Feature | SDK Entry | Implementation | Automated Test | Integration Test | Notes |
|---|---|---|---:|---:|---:|---|
| Asset | GET `/v1/asset/account` | `asset()->getAccountBalance()` | Implemented | Tested | Test pending | Read-only |
| Asset | GET `/v1/asset/stock` | `asset()->getStockPositions()` | Implemented | Tested | Test pending | Read-only |
| Asset | GET `/v1/asset/fund` | `asset()->getFundPositions()` | Implemented | Test pending | Test pending | Read-only |
| Asset | GET `/v1/asset/cashflow` | `asset()->getCashflow()` | Implemented | Test pending | Test pending | Read-only |
| Statement | GET `/v1/statement/list` | `asset()->getStatements()` | Implemented | Test pending | Test pending | Based on official SDK behavior |
| Statement | GET `/v1/statement/download` | `asset()->getStatementDownloadUrl()` | Implemented | Test pending | Test pending | Based on official SDK behavior |
| Trade | POST `/v1/trade/order` | `trade()->submitOrder()` | Implemented | Tested | Test pending | Dangerous operation; do not run by default |
| Trade | PUT `/v1/trade/order` | `trade()->replaceOrder()` | Implemented | Test pending | Test pending | Dangerous operation |
| Trade | DELETE `/v1/trade/order` | `trade()->withdrawOrder()` / `cancelOrder()` | Implemented | Tested | Test pending | Dangerous operation |
| Trade | GET `/v1/trade/order` | `trade()->getOrderDetail()` | Implemented | Test pending | Test pending | Read-only |
| Trade | GET `/v1/trade/order/today` | `trade()->getTodayOrders()` | Implemented | Test pending | Test pending | Read-only |
| Trade | GET `/v1/trade/order/history` | `trade()->getHistoryOrders()` | Implemented | Test pending | Test pending | Read-only |
| Trade | GET `/v1/trade/estimate/buy_limit` | `trade()->estimateMaxBuy()` | Implemented | Test pending | Test pending | Read-only |
| Trade | GET `/v1/trade/execution/today` | `trade()->getTodayExecutions()` | Implemented | Test pending | Test pending | Read-only |
| Trade | GET `/v1/trade/execution/history` | `trade()->getHistoryExecutions()` | Implemented | Test pending | Test pending | Read-only |
| Risk | GET `/v1/risk/margin-ratio` | `risk()->getMarginRatio()` | Implemented | Test pending | Test pending | Read-only |
| DCA | GET `/v1/dailycoins/query` | `dca()->listPlans()` | Implemented | Tested | Test pending | symbol to counter_id covered |
| DCA | POST `/v1/dailycoins/create` | `dca()->createPlan()` | Implemented | Tested | Test pending | Creates a plan |
| DCA | POST `/v1/dailycoins/update` | `dca()->updatePlan()` | Implemented | Test pending | Test pending | Modifies a plan |
| DCA | POST `/v1/dailycoins/toggle` | `pausePlan()` / `resumePlan()` / `stopPlan()` / `deletePlan()` | Implemented | Test pending | Test pending | Modifies plan status |
| DCA | GET `/v1/dailycoins/query-records` | `dca()->history()` | Implemented | Test pending | Test pending | Read-only |
| DCA | GET `/v1/dailycoins/statistic` | `dca()->stats()` | Implemented | Test pending | Test pending | Read-only |
| DCA | POST `/v1/dailycoins/batch-check-support` | `dca()->checkSupport()` | Implemented | Test pending | Test pending | Read-only semantics |
| DCA | POST `/v1/dailycoins/calc-trd-date` | `dca()->calculateDate()` | Implemented | Test pending | Test pending | Read-only semantics |
| DCA | POST `/v1/dailycoins/update-alter-hours` | `dca()->setReminder()` | Implemented | Test pending | Test pending | Modifies reminder settings |
| Alert | GET `/v1/notify/reminders` | `alert()->listAlerts()` | Implemented | Test pending | Test pending | Read-only |
| Alert | POST `/v1/notify/reminders` | `alert()->createAlert()` / `updateAlert()` | Implemented | Tested | Test pending | Creates or updates alerts |
| Alert | DELETE `/v1/notify/reminders` | `alert()->deleteAlerts()` | Implemented | Tested | Test pending | Deletes alerts |
| Portfolio | GET `/v1/asset/exchange_rates` | `portfolio()->exchangeRates()` | Implemented | Test pending | Test pending | Read-only |
| Portfolio | GET `/v1/portfolio/profit-analysis-summary` | `profitAnalysisSummary()` | Implemented | Tested | Test pending | Date conversion covered |
| Portfolio | GET `/v1/portfolio/profit-analysis-sublist` | `profitAnalysisSublist()` | Implemented | Tested | Test pending | Combined call covered |
| Portfolio | GET `/v1/portfolio/profit-analysis/by-market` | `profitAnalysisByMarket()` | Implemented | Test pending | Test pending | Read-only |
| Portfolio | GET `/v1/portfolio/profit-analysis/detail` | `profitAnalysisDetail()` | Implemented | Test pending | Test pending | Symbol conversion |
| Portfolio | GET `/v1/portfolio/profit-analysis/flows` | `profitAnalysisFlows()` | Implemented | Test pending | Test pending | Symbol conversion |
| Market | Market status, temperature, broker holding, AH premium, changes, index constituents | `market()->*` | Implemented | Test pending | Test pending | Read-only |
| Calendar | Earnings, dividends, splits, meetings, macro, IPO, merge calendars | `calendar()->*` | Implemented | Test pending | Test pending | Read-only |
| Fundamental | Reports, ratings, dividends, valuation, profile, executives, shareholders, holdings, buybacks | `fundamental()->*` | Implemented | Test pending | Test pending | Read-only |
| Quote HTTP | Watchlists, pinned symbols, filings, security list, short positions, option volume | `quoteHttp()->*` | Implemented | Test pending | Test pending | Some methods modify watchlists |

## WebSocket / Protobuf Coverage

| Module | Command / Feature | SDK Entry | Implementation | Automated Test | Integration Test | Notes |
|---|---|---|---:|---:|---:|---|
| Protocol | Request/Response/Push codec | `LongbridgeCodec` | Implemented | Tested | N/A | gzip, invalid length, and timeout covered |
| Socket | Request ID generation | `RequestIdGenerator` | Implemented | Tested | N/A | uint32 wrap covered |
| Socket | Connect, auth, heartbeat reply, reconnect | `LongbridgeWsClient` | Implemented | Partially tested | Optional integration | Real connection requires `ext-swoole` |
| Socket | Testable client boundary | `WsClientInterface` | Implemented | Tested | N/A | Enables fake WebSocket client tests |
| Quote Pull | QueryUserQuoteProfile(4) | `quoteSocket()->pull()->userQuoteProfile()` | Implemented | Indirectly tested | Optional integration | Integration test covers this command |
| Quote Pull | Subscription(5) | `quoteSocket()->subscribe()->subscriptions()` | Implemented | Protobuf tested | Test pending | Queries current subscriptions |
| Quote Subscribe | Subscribe(6) | `subscribe()` / `subscribeQuote()` / `subscribeDepth()` / `subscribeBrokers()` / `subscribeTrade()` | Implemented | Tested | Test pending | Request payload and timeout covered |
| Quote Subscribe | Unsubscribe(7) | `unsubscribe()` | Implemented | Protobuf tested | Test pending | unsubscribe and unsub_all covered |
| Quote Pull | MarketTradePeriod(8) | `marketTradePeriod()` | Implemented | Test pending | Test pending | Read-only |
| Quote Pull | MarketTradeDay(9) | `marketTradeDay()` | Implemented | Protobuf tested | Test pending | Read-only |
| Quote Pull | SecurityStaticInfo(10) | `staticInfo()` | Implemented | Protobuf tested | Test pending | Read-only |
| Quote Pull | SecurityQuote(11) | `quote()` | Implemented | Tested | Test pending | Response decoding covered |
| Quote Pull | OptionQuote(12) | `optionQuote()` | Implemented | Protobuf tested | Test pending | Read-only |
| Quote Pull | WarrantQuote(13) | `warrantQuote()` | Implemented | Protobuf tested | Test pending | Read-only |
| Quote Pull | Depth(14) | `depth()` | Implemented | Protobuf tested | Test pending | Read-only |
| Quote Pull | Brokers(15) | `brokers()` | Implemented | Protobuf tested | Test pending | Read-only |
| Quote Pull | ParticipantBrokerIds(16) | `participantBrokerIds()` | Implemented | Test pending | Test pending | Read-only |
| Quote Pull | Trade(17) | `trades()` | Implemented | Protobuf tested | Test pending | Read-only |
| Quote Pull | Intraday(18) | `intraday()` | Implemented | Protobuf tested | Test pending | Read-only |
| Quote Pull | Candlestick(19) | `candlesticks()` | Implemented | Protobuf tested | Test pending | Read-only |
| Quote Pull | OptionChainDate(20) | `optionChainDates()` | Implemented | Protobuf tested | Test pending | Read-only |
| Quote Pull | OptionChainDateStrikeInfo(21) | `optionChainDateStrikeInfo()` | Implemented | Test pending | Test pending | Read-only |
| Quote Pull | WarrantIssuerInfo(22) | `warrantIssuers()` | Implemented | Test pending | Test pending | Read-only |
| Quote Pull | WarrantFilterList(23) | `warrantFilterList()` | Implemented | Protobuf tested | Test pending | Filter parameters covered |
| Quote Pull | CapitalFlowIntraday(24) | `capitalFlowIntraday()` | Implemented | Protobuf tested | Test pending | Read-only |
| Quote Pull | CapitalFlowDistribution(25) | `capitalFlowDistribution()` | Implemented | Protobuf tested | Test pending | Read-only |
| Quote Pull | SecurityCalcIndex(26) | `calcIndexes()` | Implemented | Protobuf tested | Test pending | Read-only |
| Quote Pull | HistoryCandlestick(27) | `historyCandlesticks()` | Implemented | Protobuf tested | Test pending | Date and offset query modes covered |
| Quote Push | PushQuoteData(101) | `push()->waitQuote()` | Implemented | Tested | Test pending | Matching push wait covered |
| Quote Push | PushDepthData(102) | `push()->waitDepth()` | Implemented | Tested | Test pending | Protobuf decoding covered |
| Quote Push | PushBrokersData(103) | `push()->waitBrokers()` | Implemented | Test pending | Test pending | Decode entry exists |
| Quote Push | PushTradeData(104) | `push()->waitTrades()` | Implemented | Test pending | Test pending | Decode entry exists |
| Trade Push | CMD_SUB(16) | `tradeSocket()->push()->subscribePrivate()` | Implemented | Tested | Test pending | Request and response covered |
| Trade Push | CMD_UNSUB(17) | `tradeSocket()->push()->unsubscribe()` | Implemented | Protobuf tested | Test pending | Unsubscribe response entry exists |
| Trade Push | CMD_NOTIFY(18) | `tradeSocket()->push()->waitNotification()` | Implemented | Tested | Test pending | JSON data parsing covered |

## Test Commands

```powershell
& 'G:\phpstudy_pro\Extensions\php\php8.2.9nts\php.exe' -d extension_dir='G:\phpstudy_pro\Extensions\php\php8.2.9nts\ext' -d extension=openssl -d extension=mbstring -d extension=zip vendor\bin\phpunit --testsuite Unit
```

Real WebSocket integration test:

```powershell
$env:LONGBRIDGE_OAUTH_ACCESS_TOKEN="xxx"
$env:LONGBRIDGE_REGION="cn"
& 'G:\phpstudy_pro\Extensions\php\php8.2.9nts\php.exe' -d extension_dir='G:\phpstudy_pro\Extensions\php\php8.2.9nts\ext' -d extension=openssl -d extension=mbstring -d extension=zip vendor\bin\phpunit --testsuite Integration
```

## Next Testing Priorities

1. Add wrapper mapping tests for `MarketApi`, `CalendarApi`, `FundamentalApi`, and `QuoteHttpApi`.
2. Add mock HTTP tests for `OAuthClient`, covering registration, authorize URL generation, token exchange, token refresh, and error responses.
3. Add read-only HTTP integration tests, skipped by default through environment variables.
4. Add real WebSocket subscribe/unsubscribe integration tests without making them part of the default suite.
5. Put trade, DCA, alert, and other account-mutating integration tests into a separate `dangerous` group that never runs by default.
