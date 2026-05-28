# 功能覆盖表

本文档用于说明 `brown/longbridge-php` 对长桥 OpenAPI 的功能覆盖、自动化测试状态和真实集成验证边界。

## 状态说明

| 标记 | 含义 |
|---|---|
| 已实现 | SDK 已提供公开调用入口 |
| 已测 | 已有自动化单元测试或 wrapper 行为测试 |
| 可选集成 | 需要真实 token、网络或扩展，默认跳过 |
| 待补测试 | 已实现但尚未覆盖自动化测试 |
| 待确认 | 需要继续和官方文档或真实环境核对 |
| 不适用 | 该项不需要对应测试类型 |

## 基础能力

| 需求/能力 | SDK 入口 | 实现状态 | 自动化测试 | 集成测试 | 备注 |
|---|---|---:|---:|---:|---|
| 中国区 OAuth HTTP 客户端 | `LongbridgeClient::cnHttp()` | 已实现 | 已测 | 待补 | token 规范化已测 |
| 海外区 OAuth HTTP 客户端 | `LongbridgeClient::hkHttp()` | 已实现 | 已测 | 待补 | endpoint 配置已测 |
| 中国区 legacy 签名客户端 | `LongbridgeClient::cnLegacy()` | 已实现 | 已测 | 待补 | legacy token 校验已测 |
| 海外区 legacy 签名客户端 | `LongbridgeClient::hkLegacy()` | 已实现 | 已测 | 待补 | endpoint 配置已测 |
| OAuth + legacy hybrid 客户端 | `LongbridgeClient::cnHybrid()` / `hkHybrid()` | 已实现 | 已测 | 待补 | HTTP 默认优先 OAuth |
| OAuth token 刷新后替换 | `LongbridgeClient::setAccessToken()` | 已实现 | 待补测试 | 待补 | 目前仅底层 token 逻辑已测 |
| 未封装 HTTP 调用 | `$client->http()->get/post/put/delete()` | 已实现 | 已测 | 待补 | query 序列化已测 |
| OAuth PKCE | `Pkce::generateCodeVerifier()` / `buildCodeChallenge()` | 已实现 | 已测 | 不适用 | 使用 RFC 7636 示例向量 |
| OAuth token 对象 | `OAuthToken::fromArray()` | 已实现 | 已测 | 不适用 | 过期判断已测 |
| OAuth 注册/授权/换 token | `OAuthClient` | 已实现 | 待补测试 | 待补 | 建议补 Guzzle mock 测试 |
| Legacy 签名 | `LegacySigner::sign()` | 已实现 | 已测 | 待补 | 有固定签名快照测试 |
| 统一异常 | `LongbridgeException` | 已实现 | 待补测试 | 待补 | token invalid/rate limited 可补测试 |
| Symbol 转换 | `Symbol` | 已实现 | 已测 | 不适用 | counter_id、quote id、index id 已测 |

## HTTP API 覆盖

| 模块 | 官方 API / 功能 | SDK 入口 | 实现状态 | 自动化测试 | 集成测试 | 备注 |
|---|---|---|---:|---:|---:|---|
| Asset | GET `/v1/asset/account` | `asset()->getAccountBalance()` | 已实现 | 已测 | 待补 | 只读 |
| Asset | GET `/v1/asset/stock` | `asset()->getStockPositions()` | 已实现 | 已测 | 待补 | 只读 |
| Asset | GET `/v1/asset/fund` | `asset()->getFundPositions()` | 已实现 | 待补测试 | 待补 | 只读 |
| Asset | GET `/v1/asset/cashflow` | `asset()->getCashflow()` | 已实现 | 待补测试 | 待补 | 只读 |
| Statement | GET `/v1/statement/list` | `asset()->getStatements()` | 已实现 | 待补测试 | 待补 | 参考官方 SDK |
| Statement | GET `/v1/statement/download` | `asset()->getStatementDownloadUrl()` | 已实现 | 待补测试 | 待补 | 参考官方 SDK |
| Trade | POST `/v1/trade/order` | `trade()->submitOrder()` | 已实现 | 已测 | 待补 | 危险操作，集成测试默认不应跑 |
| Trade | PUT `/v1/trade/order` | `trade()->replaceOrder()` | 已实现 | 待补测试 | 待补 | 危险操作 |
| Trade | DELETE `/v1/trade/order` | `trade()->withdrawOrder()` / `cancelOrder()` | 已实现 | 已测 | 待补 | 危险操作 |
| Trade | GET `/v1/trade/order` | `trade()->getOrderDetail()` | 已实现 | 待补测试 | 待补 | 只读 |
| Trade | GET `/v1/trade/order/today` | `trade()->getTodayOrders()` | 已实现 | 待补测试 | 待补 | 只读 |
| Trade | GET `/v1/trade/order/history` | `trade()->getHistoryOrders()` | 已实现 | 待补测试 | 待补 | 只读 |
| Trade | GET `/v1/trade/estimate/buy_limit` | `trade()->estimateMaxBuy()` | 已实现 | 待补测试 | 待补 | 只读 |
| Trade | GET `/v1/trade/execution/today` | `trade()->getTodayExecutions()` | 已实现 | 待补测试 | 待补 | 只读 |
| Trade | GET `/v1/trade/execution/history` | `trade()->getHistoryExecutions()` | 已实现 | 待补测试 | 待补 | 只读 |
| Risk | GET `/v1/risk/margin-ratio` | `risk()->getMarginRatio()` | 已实现 | 待补测试 | 待补 | 只读 |
| DCA | GET `/v1/dailycoins/query` | `dca()->listPlans()` | 已实现 | 已测 | 待补 | symbol 转 counter_id 已测 |
| DCA | POST `/v1/dailycoins/create` | `dca()->createPlan()` | 已实现 | 已测 | 待补 | 创建操作 |
| DCA | POST `/v1/dailycoins/update` | `dca()->updatePlan()` | 已实现 | 待补测试 | 待补 | 修改操作 |
| DCA | POST `/v1/dailycoins/toggle` | `pausePlan()` / `resumePlan()` / `stopPlan()` / `deletePlan()` | 已实现 | 待补测试 | 待补 | 修改操作 |
| DCA | GET `/v1/dailycoins/query-records` | `dca()->history()` | 已实现 | 待补测试 | 待补 | 只读 |
| DCA | GET `/v1/dailycoins/statistic` | `dca()->stats()` | 已实现 | 待补测试 | 待补 | 只读 |
| DCA | POST `/v1/dailycoins/batch-check-support` | `dca()->checkSupport()` | 已实现 | 待补测试 | 待补 | 只读语义 |
| DCA | POST `/v1/dailycoins/calc-trd-date` | `dca()->calculateDate()` | 已实现 | 待补测试 | 待补 | 只读语义 |
| DCA | POST `/v1/dailycoins/update-alter-hours` | `dca()->setReminder()` | 已实现 | 待补测试 | 待补 | 修改操作 |
| Alert | GET `/v1/notify/reminders` | `alert()->listAlerts()` | 已实现 | 待补测试 | 待补 | 只读 |
| Alert | POST `/v1/notify/reminders` | `alert()->createAlert()` / `updateAlert()` | 已实现 | 已测 | 待补 | 创建/修改提醒 |
| Alert | DELETE `/v1/notify/reminders` | `alert()->deleteAlerts()` | 已实现 | 已测 | 待补 | 删除操作 |
| Portfolio | GET `/v1/asset/exchange_rates` | `portfolio()->exchangeRates()` | 已实现 | 待补测试 | 待补 | 只读 |
| Portfolio | GET `/v1/portfolio/profit-analysis-summary` | `profitAnalysisSummary()` | 已实现 | 已测 | 待补 | 日期转换已测 |
| Portfolio | GET `/v1/portfolio/profit-analysis-sublist` | `profitAnalysisSublist()` | 已实现 | 已测 | 待补 | 组合调用已测 |
| Portfolio | GET `/v1/portfolio/profit-analysis/by-market` | `profitAnalysisByMarket()` | 已实现 | 待补测试 | 待补 | 只读 |
| Portfolio | GET `/v1/portfolio/profit-analysis/detail` | `profitAnalysisDetail()` | 已实现 | 待补测试 | 待补 | symbol 转换 |
| Portfolio | GET `/v1/portfolio/profit-analysis/flows` | `profitAnalysisFlows()` | 已实现 | 待补测试 | 待补 | symbol 转换 |
| Market | 市场状态、温度、经纪持仓、AH 溢价、异动、指数成分等 | `market()->*` | 已实现 | 待补测试 | 待补 | 只读 |
| Calendar | 财报、分红、拆股、股东会、宏观、IPO、并购日历 | `calendar()->*` | 已实现 | 待补测试 | 待补 | 只读 |
| Fundamental | 财报、评级、分红、估值、公司概览、高管、股东、基金持仓、回购等 | `fundamental()->*` | 已实现 | 待补测试 | 待补 | 只读 |
| Quote HTTP | 自选股分组、置顶、公告、标的列表、空头持仓、期权成交量 | `quoteHttp()->*` | 已实现 | 待补测试 | 待补 | 部分接口会修改自选股 |

## WebSocket / Protobuf 覆盖

| 模块 | 命令/能力 | SDK 入口 | 实现状态 | 自动化测试 | 集成测试 | 备注 |
|---|---|---|---:|---:|---:|---|
| Protocol | Request/Response/Push 编解码 | `LongbridgeCodec` | 已实现 | 已测 | 不适用 | gzip、错误包长、timeout 已测 |
| Socket | 请求 ID | `RequestIdGenerator` | 已实现 | 已测 | 不适用 | uint32 回卷已测 |
| Socket | 连接、鉴权、心跳回复、重连 | `LongbridgeWsClient` | 已实现 | 部分已测 | 可选集成 | 真实连接需要 `ext-swoole` |
| Socket | 可测试接口 | `WsClientInterface` | 已实现 | 已测 | 不适用 | 用于假客户端测试 |
| Quote Pull | QueryUserQuoteProfile(4) | `quoteSocket()->pull()->userQuoteProfile()` | 已实现 | 间接已测 | 可选集成 | 集成测试覆盖该命令 |
| Quote Pull | Subscription(5) | `quoteSocket()->subscribe()->subscriptions()` | 已实现 | protobuf 已测 | 待补 | 查询当前订阅 |
| Quote Subscribe | Subscribe(6) | `subscribe()` / `subscribeQuote()` / `subscribeDepth()` / `subscribeBrokers()` / `subscribeTrade()` | 已实现 | 已测 | 待补 | 请求体和 timeout 已测 |
| Quote Subscribe | Unsubscribe(7) | `unsubscribe()` | 已实现 | protobuf 已测 | 待补 | 退订和 unsub_all 已测 |
| Quote Pull | MarketTradePeriod(8) | `marketTradePeriod()` | 已实现 | 待补测试 | 待补 | 只读 |
| Quote Pull | MarketTradeDay(9) | `marketTradeDay()` | 已实现 | protobuf 已测 | 待补 | 只读 |
| Quote Pull | SecurityStaticInfo(10) | `staticInfo()` | 已实现 | protobuf 已测 | 待补 | 只读 |
| Quote Pull | SecurityQuote(11) | `quote()` | 已实现 | 已测 | 待补 | 响应解码已测 |
| Quote Pull | OptionQuote(12) | `optionQuote()` | 已实现 | protobuf 已测 | 待补 | 只读 |
| Quote Pull | WarrantQuote(13) | `warrantQuote()` | 已实现 | protobuf 已测 | 待补 | 只读 |
| Quote Pull | Depth(14) | `depth()` | 已实现 | protobuf 已测 | 待补 | 只读 |
| Quote Pull | Brokers(15) | `brokers()` | 已实现 | protobuf 已测 | 待补 | 只读 |
| Quote Pull | ParticipantBrokerIds(16) | `participantBrokerIds()` | 已实现 | 待补测试 | 待补 | 只读 |
| Quote Pull | Trade(17) | `trades()` | 已实现 | protobuf 已测 | 待补 | 只读 |
| Quote Pull | Intraday(18) | `intraday()` | 已实现 | protobuf 已测 | 待补 | 只读 |
| Quote Pull | Candlestick(19) | `candlesticks()` | 已实现 | protobuf 已测 | 待补 | 只读 |
| Quote Pull | OptionChainDate(20) | `optionChainDates()` | 已实现 | protobuf 已测 | 待补 | 只读 |
| Quote Pull | OptionChainDateStrikeInfo(21) | `optionChainDateStrikeInfo()` | 已实现 | 待补测试 | 待补 | 只读 |
| Quote Pull | WarrantIssuerInfo(22) | `warrantIssuers()` | 已实现 | 待补测试 | 待补 | 只读 |
| Quote Pull | WarrantFilterList(23) | `warrantFilterList()` | 已实现 | protobuf 已测 | 待补 | 筛选参数已测 |
| Quote Pull | CapitalFlowIntraday(24) | `capitalFlowIntraday()` | 已实现 | protobuf 已测 | 待补 | 只读 |
| Quote Pull | CapitalFlowDistribution(25) | `capitalFlowDistribution()` | 已实现 | protobuf 已测 | 待补 | 只读 |
| Quote Pull | SecurityCalcIndex(26) | `calcIndexes()` | 已实现 | protobuf 已测 | 待补 | 只读 |
| Quote Pull | HistoryCandlestick(27) | `historyCandlesticks()` | 已实现 | protobuf 已测 | 待补 | date/offset 两种查询已测 |
| Quote Push | PushQuoteData(101) | `push()->waitQuote()` | 已实现 | 已测 | 待补 | 等待匹配推送已测 |
| Quote Push | PushDepthData(102) | `push()->waitDepth()` | 已实现 | 已测 | 待补 | protobuf 解码已测 |
| Quote Push | PushBrokersData(103) | `push()->waitBrokers()` | 已实现 | 待补测试 | 待补 | 解码入口已实现 |
| Quote Push | PushTradeData(104) | `push()->waitTrades()` | 已实现 | 待补测试 | 待补 | 解码入口已实现 |
| Trade Push | CMD_SUB(16) | `tradeSocket()->push()->subscribePrivate()` | 已实现 | 已测 | 待补 | 请求体和响应已测 |
| Trade Push | CMD_UNSUB(17) | `tradeSocket()->push()->unsubscribe()` | 已实现 | protobuf 已测 | 待补 | 退订响应入口已实现 |
| Trade Push | CMD_NOTIFY(18) | `tradeSocket()->push()->waitNotification()` | 已实现 | 已测 | 待补 | JSON data 解析已测 |

## 测试命令

```powershell
& 'G:\phpstudy_pro\Extensions\php\php8.2.9nts\php.exe' -d extension_dir='G:\phpstudy_pro\Extensions\php\php8.2.9nts\ext' -d extension=openssl -d extension=mbstring -d extension=zip vendor\bin\phpunit --testsuite Unit
```

真实 WebSocket 集成测试：

```powershell
$env:LONGBRIDGE_OAUTH_ACCESS_TOKEN="xxx"
$env:LONGBRIDGE_REGION="cn"
& 'G:\phpstudy_pro\Extensions\php\php8.2.9nts\php.exe' -d extension_dir='G:\phpstudy_pro\Extensions\php\php8.2.9nts\ext' -d extension=openssl -d extension=mbstring -d extension=zip vendor\bin\phpunit --testsuite Integration
```

## 下一步测试优先级

1. 为 `MarketApi`、`CalendarApi`、`FundamentalApi`、`QuoteHttpApi` 补齐 wrapper 参数映射测试。
2. 为 `OAuthClient` 增加 mock HTTP 测试，覆盖注册、授权 URL、换 token、刷新 token 和错误响应。
3. 增加只读 HTTP 集成测试，并通过环境变量默认跳过。
4. 增加 WebSocket 订阅/退订真实集成测试，避免默认运行会消耗行情权限或依赖实时推送。
5. 对交易、定投、提醒等会修改账户状态的接口，单独放入 `dangerous` 测试组，默认永不运行。
