# Longbridge PHP

一个轻量的长桥 OpenAPI PHP 客户端，覆盖 HTTP/OAuth 接口和 WebSocket/protobuf 行情、交易推送能力。项目保持数组返回风格，不引入 DTO。

## 环境要求

- PHP `>= 8.2`
- `guzzlehttp/guzzle`
- `google/protobuf`
- WebSocket 功能需要 `ext-swoole`

安装依赖：

```bash
composer install
```

## 初始化客户端

仅使用 HTTP/OAuth：

```php
use Brown\Longbridge\LongbridgeClient;

$client = LongbridgeClient::cnHttp('YOUR_OAUTH_ACCESS_TOKEN');
$client = LongbridgeClient::hkHttp('YOUR_OAUTH_ACCESS_TOKEN');
```

需要 WebSocket OTP 时使用带 legacy 凭证的入口：

```php
$client = LongbridgeClient::cnOAuth(
    legacyAppKey: 'APP_KEY',
    legacyAppSecret: 'APP_SECRET',
    legacyAccessToken: 'LEGACY_ACCESS_TOKEN',
    accessToken: 'OAUTH_ACCESS_TOKEN',
);
```

刷新 OAuth token 后：

```php
$client->setAccessToken('NEW_OAUTH_ACCESS_TOKEN');
```

## OAuth 获取 Token

生成授权地址：

```bash
php examples/oauth_authorize_url.php <client_id> <redirect_uri>
```

使用回调中的 `code` 换取 token：

```bash
php examples/exchange_code.php <client_id> <redirect_uri> <code> <code_verifier>
```

## HTTP API 示例

资产：

```php
$balance = $client->asset()->getAccountBalance(['USD', 'HKD']);
$stocks = $client->asset()->getStockPositions(['AAPL.US', '700.HK']);
$funds = $client->asset()->getFundPositions();
```

交易：

```php
$order = $client->trade()->submitOrder([
    'symbol' => 'AAPL.US',
    'order_type' => 'LO',
    'side' => 'Buy',
    'submitted_quantity' => '1',
    'submitted_price' => '180',
    'time_in_force' => 'Day',
]);

$orders = $client->trade()->getTodayOrders(['symbol' => 'AAPL.US']);
$detail = $client->trade()->getOrderDetail((string)$order['order_id']);
```

账户和市场：

```php
$plans = $client->dca()->listPlans(['status' => 'Active']);
$alerts = $client->alert()->listAlerts('AAPL.US');
$profit = $client->portfolio()->profitAnalysis([
    'start_date' => '2026-01-01',
    'end_date' => '2026-05-19',
]);

$status = $client->market()->marketStatus();
$earnings = $client->calendar()->earningsCalendar('2026-05-01', '2026-05-31');
$profile = $client->fundamental()->companyProfile('AAPL.US');
```

HTTP 行情辅助：

```php
$groups = $client->quoteHttp()->watchlistGroups();
$filings = $client->quoteHttp()->filings('AAPL.US');
$securities = $client->quoteHttp()->securityList('US', 'stock');
```

## WebSocket 行情

自动获取 OTP、连接并鉴权：

```php
$quote = $client->quoteSocket();
```

如果你已经有 OTP 或底层连接：

```php
$ws = $client->quoteWs('SOCKET_OTP');
$quote = $client->quoteSocket($ws);
```

拉取行情：

```php
use Brown\Longbridge\Proto\Quote\CalcIndex;
use Brown\Longbridge\Proto\Quote\Period;

$profile = $quote->pull()->userQuoteProfile('zh-CN');
$quotes = $quote->pull()->quote(['AAPL.US', '700.HK']);
$depth = $quote->pull()->depth('AAPL.US');
$trades = $quote->pull()->trades('AAPL.US', 50);
$intraday = $quote->pull()->intraday('AAPL.US', ['trade_session' => 0]);
$klines = $quote->pull()->candlesticks('AAPL.US', Period::DAY, 100);
$history = $quote->pull()->historyCandlesticks('AAPL.US', Period::DAY, [
    'start_date' => '2026-01-01',
    'end_date' => '2026-05-20',
]);
$indexes = $quote->pull()->calcIndexes(['AAPL.US'], [
    CalcIndex::CALCINDEX_LAST_DONE,
    CalcIndex::CALCINDEX_VOLUME,
]);
```

订阅和等待推送：

```php
$quote->subscribe()->subscribeQuote(['AAPL.US']);
$quote->subscribe()->subscribe(['AAPL.US'], ['quote', 'depth', 'trade']);

$push = $quote->push()->wait();
$quotePush = $quote->push()->waitQuote();
$depthPush = $quote->push()->waitDepth();
```

退订和查询订阅：

```php
$current = $quote->subscribe()->subscriptions();
$quote->subscribe()->unsubscribe(['AAPL.US'], ['quote', 'depth']);
$quote->subscribe()->unsubscribe(unsubAll: true);
```

## WebSocket 交易推送

交易推送连接使用 `openapi-trade` 网关：

```php
$tradeSocket = $client->tradeSocket();
$tradeSocket->push()->subscribePrivate();

$notification = $tradeSocket->push()->waitNotification();
$payload = $notification['payload']['data_json'] ?? $notification['payload']['data'];
```

退订：

```php
$tradeSocket->push()->unsubscribe(['private']);
```

## 异常处理

HTTP/OAuth 错误抛出 `Brown\Longbridge\Exception\LongbridgeException`：

```php
use Brown\Longbridge\Exception\LongbridgeException;

try {
    $data = $client->asset()->getAccountBalance();
} catch (LongbridgeException $e) {
    echo $e->getMessage();
    echo $e->httpStatus;
    echo $e->responseBody;
}
```

WebSocket 连接、鉴权、超时和业务状态错误抛出 `RuntimeException`。

## 开发检查

生成 protobuf：

```bash
protoc --proto_path=proto --php_out=generated proto/control.proto
protoc --proto_path=proto --php_out=generated proto/api.proto
protoc --proto_path=proto --php_out=generated proto/subscribe.proto
```

静态检查：

```bash
composer validate --no-check-publish
composer dump-autoload
Get-ChildItem src,generated,examples -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
```
