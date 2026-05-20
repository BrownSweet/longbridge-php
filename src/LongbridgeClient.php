<?php

declare(strict_types=1);

namespace Brown\Longbridge;

use Brown\Longbridge\Account\AlertApi;
use Brown\Longbridge\Account\DcaApi;
use Brown\Longbridge\Account\PortfolioApi;
use Brown\Longbridge\Asset\AssetApi;
use Brown\Longbridge\Calendar\CalendarApi;
use Brown\Longbridge\Fundamental\FundamentalApi;
use Brown\Longbridge\Http\AutoHttpClient;
use Brown\Longbridge\Http\LegacyHttpClient;
use Brown\Longbridge\Http\OAuthHttpClient;
use Brown\Longbridge\Http\SocketOtpApi;
use Brown\Longbridge\Http\SocketTokenApi;
use Brown\Longbridge\Market\MarketApi;
use Brown\Longbridge\Quote\Http\QuoteHttpApi;
use Brown\Longbridge\Quote\QuoteSocketApi;
use Brown\Longbridge\Risk\RiskApi;
use Brown\Longbridge\Socket\LongbridgeWsClient;
use Brown\Longbridge\Trade\TradeApi;
use Brown\Longbridge\Trade\TradeSocketApi;

final class LongbridgeClient
{
    private Config $config;
    private AutoHttpClient $http;
    private ?AssetApi $asset = null;
    private ?TradeApi $trade = null;
    private ?RiskApi $risk = null;
    private ?DcaApi $dca = null;
    private ?AlertApi $alert = null;
    private ?PortfolioApi $portfolio = null;
    private ?MarketApi $market = null;
    private ?CalendarApi $calendar = null;
    private ?FundamentalApi $fundamental = null;
    private ?QuoteHttpApi $quoteHttp = null;
    private ?SocketOtpApi $socketOtp = null;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->http = new AutoHttpClient(
            $this->config->hasOAuthToken()
                ? new OAuthHttpClient($this->config->httpBaseUrl, $this->config->accessToken())
                : null,
            $this->config->hasLegacyCredentials()
                ? new LegacyHttpClient(
                    $this->config->httpBaseUrl,
                    $this->config->getLegacyAppKey(),
                    $this->config->getLegacyAppSecret(),
                    $this->config->getLegacyAccessToken(),
                )
                : null,
        );
    }

    /**
     * 创建中国区 HTTP/OAuth 客户端。
     */
    public static function cnHttp(string $accessToken): self
    {
        return new self(Config::cnHttp($accessToken));
    }

    /**
     * 创建海外区 HTTP/OAuth 客户端。
     */
    public static function hkHttp(string $accessToken): self
    {
        return new self(Config::hkHttp($accessToken));
    }

    /**
     * 创建中国区 legacy 签名客户端。
     */
    public static function cnLegacy(string $appKey, string $appSecret, string $accessToken): self
    {
        return new self(Config::cnLegacy($appKey, $appSecret, $accessToken));
    }

    /**
     * 创建海外区 legacy 签名客户端。
     */
    public static function hkLegacy(string $appKey, string $appSecret, string $accessToken): self
    {
        return new self(Config::hkLegacy($appKey, $appSecret, $accessToken));
    }

    /**
     * 创建中国区 hybrid 客户端，兼容旧入口。
     */
    public static function cnOAuth(
        string $legacyAppKey,
        string $legacyAppSecret,
        string $legacyAccessToken,
        string $accessToken
    ): self {
        return new self(Config::cnOAuth($legacyAppKey, $legacyAppSecret, $legacyAccessToken, $accessToken));
    }

    /**
     * 创建海外区 hybrid 客户端，兼容旧入口。
     */
    public static function hkOAuth(
        string $legacyAppKey,
        string $legacyAppSecret,
        string $legacyAccessToken,
        string $accessToken
    ): self {
        return new self(Config::hkOAuth($legacyAppKey, $legacyAppSecret, $legacyAccessToken, $accessToken));
    }

    /**
     * 创建中国区 hybrid 客户端，HTTP 默认优先 OAuth。
     */
    public static function cnHybrid(
        string $legacyAppKey,
        string $legacyAppSecret,
        string $legacyAccessToken,
        string $accessToken
    ): self {
        return new self(Config::cnHybrid($legacyAppKey, $legacyAppSecret, $legacyAccessToken, $accessToken));
    }

    /**
     * 创建海外区 hybrid 客户端，HTTP 默认优先 OAuth。
     */
    public static function hkHybrid(
        string $legacyAppKey,
        string $legacyAppSecret,
        string $legacyAccessToken,
        string $accessToken
    ): self {
        return new self(Config::hkHybrid($legacyAppKey, $legacyAppSecret, $legacyAccessToken, $accessToken));
    }

    /**
     * 返回当前配置。
     */
    public function config(): Config
    {
        return $this->config;
    }

    /**
     * 返回 hybrid 默认 HTTP 客户端，便于调用未封装的新接口。
     */
    public function http(): AutoHttpClient
    {
        return $this->http;
    }

    /**
     * 返回 OAuth HTTP 客户端，便于调用未封装的 OAuth 接口。
     */
    public function oauthHttp(): OAuthHttpClient
    {
        return $this->http->oauth();
    }

    /**
     * 返回 legacy 签名 HTTP 客户端，便于调用未封装的 legacy 接口。
     */
    public function legacyHttp(): LegacyHttpClient
    {
        return $this->http->legacy();
    }

    /**
     * 更新 OAuth access token。
     */
    public function setAccessToken(string $accessToken): void
    {
        $this->http->setAccessToken($accessToken);
        $this->socketOtp = null;
    }

    /**
     * 资产 HTTP API。
     */
    public function asset(): AssetApi
    {
        return $this->asset ??= new AssetApi($this->http);
    }

    /**
     * 交易 HTTP API。
     */
    public function trade(): TradeApi
    {
        return $this->trade ??= new TradeApi($this->http);
    }

    /**
     * 风控 HTTP API。
     */
    public function risk(): RiskApi
    {
        return $this->risk ??= new RiskApi($this->http);
    }

    /**
     * 定投 HTTP API。
     */
    public function dca(): DcaApi
    {
        return $this->dca ??= new DcaApi($this->http);
    }

    /**
     * 价格提醒 HTTP API。
     */
    public function alert(): AlertApi
    {
        return $this->alert ??= new AlertApi($this->http);
    }

    /**
     * 组合收益 HTTP API。
     */
    public function portfolio(): PortfolioApi
    {
        return $this->portfolio ??= new PortfolioApi($this->http);
    }

    /**
     * 市场 HTTP API。
     */
    public function market(): MarketApi
    {
        return $this->market ??= new MarketApi($this->http);
    }

    /**
     * 财经日历 HTTP API。
     */
    public function calendar(): CalendarApi
    {
        return $this->calendar ??= new CalendarApi($this->http);
    }

    /**
     * 基本面 HTTP API。
     */
    public function fundamental(): FundamentalApi
    {
        return $this->fundamental ??= new FundamentalApi($this->http);
    }

    /**
     * HTTP 行情辅助 API，例如自选股、公告、标的列表。
     */
    public function quoteHttp(): QuoteHttpApi
    {
        return $this->quoteHttp ??= new QuoteHttpApi($this->http);
    }

    /**
     * 创建并鉴权 Quote WebSocket 客户端。
     *
     * @param string|null $otp 一次性 socket token；为空时调用 socketOtp()->getOtp() 获取。
     */
    public function quoteWs(?string $otp = null): LongbridgeWsClient
    {
        return $this->connectWs($this->config->quoteWsUrl, $otp);
    }

    /**
     * 创建并鉴权 Trade WebSocket 客户端。
     *
     * @param string|null $otp 一次性 socket token；为空时调用 socketOtp()->getOtp() 获取。
     */
    public function tradeWs(?string $otp = null): LongbridgeWsClient
    {
        return $this->connectWs($this->config->tradeWsUrl, $otp);
    }

    /**
     * Quote WebSocket API 门面，包含 pull、subscribe、push 三组能力。
     */
    public function quoteSocket(?LongbridgeWsClient $client = null, ?string $otp = null): QuoteSocketApi
    {
        return new QuoteSocketApi($client ?? $this->quoteWs($otp));
    }

    /**
     * Trade WebSocket API 门面，包含 private 主题订阅与通知等待。
     */
    public function tradeSocket(?LongbridgeWsClient $client = null, ?string $otp = null): TradeSocketApi
    {
        return new TradeSocketApi($client ?? $this->tradeWs($otp));
    }

    /**
     * 获取 socket OTP。hybrid 优先 OAuth，没有 OAuth 时使用 legacy 签名。
     */
    public function socketOtp(): SocketOtpApi
    {
        return $this->socketOtp ??= new SocketOtpApi(
            $this->http->hasOAuth() ? $this->http->oauth() : null,
            $this->config->hasLegacyCredentials()
                ? new SocketTokenApi(
                    $this->config->httpBaseUrl,
                    $this->config->getLegacyAppKey(),
                    $this->config->getLegacyAppSecret(),
                    $this->config->getLegacyAccessToken(),
                )
                : null,
        );
    }

    private function connectWs(string $wsUrl, ?string $otp): LongbridgeWsClient
    {
        $client = new LongbridgeWsClient($wsUrl);
        $client->connect();
        $client->authenticate($otp ?? $this->socketOtp()->getOtp());

        return $client;
    }
}
