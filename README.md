# Longbridge PHP

一个轻量的长桥 OpenAPI PHP 客户端。当前项目同时包含：

- HTTP/OAuth API：交易、资产、定投、价格提醒、组合收益、市场、财经日历、基本面、自选股等。
- WebSocket/protobuf 行情能力：已有的行情拉取、订阅与推送封装仍保留，不在本次 HTTP 封装范围内改动。

## 环境要求

- PHP `>= 8.2`
- `guzzlehttp/guzzle`
- `google/protobuf`
- WebSocket 行情相关功能需要 `ext-swoole`

安装依赖：

```bash
composer install
```

## OAuth 获取 Token

生成授权地址：

```bash
php examples/oauth_authorize_url.php <client_id> <redirect_uri>
```

用户授权后，用回调里的 `code` 换取 token：

```bash
php examples/exchange_code.php <client_id> <redirect_uri> <code> <code_verifier>
```

## 初始化 HTTP 客户端

中国区：

```php
use Brown\Longbridge\LongbridgeClient;

$client = LongbridgeClient::cnHttp('YOUR_OAUTH_ACCESS_TOKEN');
```

海外区：

```php
$client = LongbridgeClient::hkHttp('YOUR_OAUTH_ACCESS_TOKEN');
```

如果还需要旧版签名凭证来获取 socket OTP，可以使用兼容入口：

```php
$client = LongbridgeClient::cnOAuth(
    legacyAppKey: 'APP_KEY',
    legacyAppSecret: 'APP_SECRET',
    legacyAccessToken: 'LEGACY_ACCESS_TOKEN',
    accessToken: 'OAUTH_ACCESS_TOKEN',
);
```

刷新 OAuth access token 后：

```php
$client->setAccessToken('NEW_OAUTH_ACCESS_TOKEN');
```

## 常用 HTTP API

账户资产：

```php
$balance = $client->asset()->getAccountBalance(['USD', 'HKD']);
$stocks = $client->asset()->getStockPositions(['AAPL.US', '700.HK']);
$funds = $client->asset()->getFundPositions();
$cashflow = $client->asset()->getCashflow([
    'start_time' => 1714521600,
    'end_time' => 1717200000,
    'page' => 1,
    'size' => 50,
]);
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

$todayOrders = $client->trade()->getTodayOrders(['symbol' => 'AAPL.US']);
$detail = $client->trade()->getOrderDetail((string)$order['order_id']);
```

定投与价格提醒：

```php
$plans = $client->dca()->listPlans(['status' => 'Active']);
$newPlan = $client->dca()->createPlan('AAPL.US', '100', 'Monthly', [
    'invest_day_of_month' => '15',
]);

$alerts = $client->alert()->listAlerts('AAPL.US');
$alert = $client->alert()->createAlert('AAPL.US', '200', 'rise', 'once');
```

组合收益：

```php
$rates = $client->portfolio()->exchangeRates();
$profit = $client->portfolio()->profitAnalysis([
    'start_date' => '2026-01-01',
    'end_date' => '2026-05-19',
]);
$flows = $client->portfolio()->profitAnalysisFlows('AAPL.US', [
    'page' => 1,
    'size' => 50,
]);
```

市场与财经日历：

```php
$status = $client->market()->marketStatus();
$temperature = $client->market()->marketTemperature('US');
$constituents = $client->market()->indexConstituents('HSI.HK');

$earnings = $client->calendar()->earningsCalendar(
    '2026-05-01',
    '2026-05-31',
    ['market' => 'US'],
);
```

基本面：

```php
$profile = $client->fundamental()->companyProfile('AAPL.US');
$reports = $client->fundamental()->financialReports('AAPL.US', 'ALL');
$ratings = $client->fundamental()->institutionRating('AAPL.US');
$dividends = $client->fundamental()->dividends('AAPL.US');
```

HTTP 行情辅助接口：

```php
$groups = $client->quoteHttp()->watchlistGroups();
$group = $client->quoteHttp()->createWatchlistGroup('Tech', ['AAPL.US', 'MSFT.US']);
$filings = $client->quoteHttp()->filings('AAPL.US');
$securities = $client->quoteHttp()->securityList('US', 'stock');
```

## WebSocket 行情能力

已有 socket/protobuf 封装仍保留在 `src/Quote`、`src/Socket`、`src/Protocol` 下。使用前需要：

1. 使用带 legacy 凭证的 `LongbridgeClient::cnOAuth()` 或 `hkOAuth()`。
2. 通过 `$client->socketOtp()->getOtp()` 获取一次性 socket token。
3. 使用 `LongbridgeWsClient` 连接 quote/trade WebSocket 并进行鉴权。

HTTP-only 客户端 `cnHttp()` / `hkHttp()` 不包含 legacy 凭证，调用 `socketOtp()` 会抛出异常。

## 异常处理

HTTP 与 OAuth 错误会抛出 `Brown\Longbridge\Exception\LongbridgeException`：

```php
use Brown\Longbridge\Exception\LongbridgeException;

try {
    $data = $client->asset()->getAccountBalance();
} catch (LongbridgeException $e) {
    echo $e->getMessage();
    echo $e->httpStatus;
    echo $e->getCode();
    echo $e->responseBody;
}
```

常用判断：

```php
if ($e->isTokenInvalid()) {
    // 重新刷新 OAuth token
}

if ($e->isRateLimited()) {
    // 降低请求频率
}
```

## 开发说明

生成 protobuf：

```bash
protoc --proto_path=proto --php_out=generated proto/control.proto
protoc --proto_path=proto --php_out=generated proto/api.proto
```

静态检查建议：

```bash
composer validate --no-check-publish
composer dump-autoload
Get-ChildItem src,examples -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
```

注意：本项目使用 PHP 8.2 语法，本机 PHP 版本过低时 `php -l` 会误报语法错误。
