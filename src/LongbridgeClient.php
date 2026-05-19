<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */
declare(strict_types=1);

namespace Brown\Longbridge;

use Brown\Longbridge\Account\AlertApi;
use Brown\Longbridge\Account\DcaApi;
use Brown\Longbridge\Account\PortfolioApi;
use Brown\Longbridge\Asset\AssetApi;
use Brown\Longbridge\Calendar\CalendarApi;
use Brown\Longbridge\Fundamental\FundamentalApi;
use Brown\Longbridge\Http\OAuthHttpClient;
use Brown\Longbridge\Http\SocketTokenApi;
use Brown\Longbridge\Market\MarketApi;
use Brown\Longbridge\Quote\Http\QuoteHttpApi;
use Brown\Longbridge\Risk\RiskApi;
use Brown\Longbridge\Trade\TradeApi;

final class LongbridgeClient
{
    private Config $config;
    private OAuthHttpClient $http;
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
    private ?SocketTokenApi $socketOtp = null;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->http = new OAuthHttpClient(
            $this->config->httpBaseUrl,
            $this->config->accessToken()
        );
    }

    /**
     * 创建中国区 HTTP/OAuth 客户端，不需要 legacy/socket 凭证。
     */
    public static function cnHttp(string $accessToken): self
    {
        return new self(Config::cnHttp($accessToken));
    }

    /**
     * 创建海外区 HTTP/OAuth 客户端，不需要 legacy/socket 凭证。
     */
    public static function hkHttp(string $accessToken): self
    {
        return new self(Config::hkHttp($accessToken));
    }

    /**
     * 创建中国区客户端，同时保留 legacy 凭证以支持 socket OTP。
     */
    public static function cnOAuth(string $legacyAppKey, string $legacyAppSecret, string $legacyAccessToken, string $accessToken): self
    {
        return new self(Config::cnOAuth($legacyAppKey, $legacyAppSecret, $legacyAccessToken, $accessToken));
    }

    /**
     * 创建海外区客户端，同时保留 legacy 凭证以支持 socket OTP。
     */
    public static function hkOAuth(string $legacyAppKey, string $legacyAppSecret, string $legacyAccessToken, string $accessToken): self
    {
        return new self(Config::hkOAuth($legacyAppKey, $legacyAppSecret, $legacyAccessToken, $accessToken));
    }

    /**
     * 返回当前配置。
     */
    public function config(): Config
    {
        return $this->config;
    }

    /**
     * 返回底层 HTTP 客户端，便于调用未封装的新接口。
     */
    public function http(): OAuthHttpClient
    {
        return $this->http;
    }

    /**
     * 更新 OAuth access token。
     */
    public function setAccessToken(string $accessToken): void
    {
        $accessToken = trim($accessToken);
        if (str_starts_with($accessToken, 'Bearer ')) {
            $accessToken = substr($accessToken, 7);
        }

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
     * 获取 socket OTP。仅 legacy 凭证齐全时可用。
     */
    public function socketOtp(): SocketTokenApi
    {
        if (!$this->config->hasLegacyCredentials()) {
            throw new \RuntimeException('Legacy app key, app secret and access token are required for socketOtp().');
        }

        return $this->socketOtp ??= new SocketTokenApi(
            $this->config->httpBaseUrl,
            $this->config->getLegacyAppKey(),
            $this->config->getLegacyAppSecret(),
            $this->config->getLegacyAccessToken(),
        );
    }
}
