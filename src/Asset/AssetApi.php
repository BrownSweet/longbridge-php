<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */
declare(strict_types=1);

namespace Brown\Longbridge\Asset;

use Brown\Longbridge\Http\OAuthHttpClient;

final class AssetApi
{
    public function __construct(
        private readonly OAuthHttpClient $client
    ) {
    }

    /**
     * 获取账户现金资产。
     *
     * 官方路径：GET /v1/asset/account
     *
     * @param array $currencies 可选币种列表，例如 ['USD', 'HKD']。
     * @return array 返回 Longbridge data 数组。
     */
    public function getAccountBalance(array $currencies = []): array
    {
        return $this->client->get(
            '/v1/asset/account',
            $currencies ? ['currency' => $currencies] : []
        );
    }

    /**
     * 获取股票持仓。
     *
     * 官方路径：GET /v1/asset/stock
     *
     * @param array $symbols 可选标的列表，例如 ['AAPL.US', '700.HK']。
     * @return array 返回 Longbridge data 数组。
     */
    public function getStockPositions(array $symbols = []): array
    {
        return $this->client->get(
            '/v1/asset/stock',
            $symbols ? ['symbol' => $symbols] : []
        );
    }

    /**
     * 获取基金持仓。
     *
     * 官方路径：GET /v1/asset/fund
     *
     * @param array $symbols 可选基金标的列表。
     * @return array 返回 Longbridge data 数组。
     */
    public function getFundPositions(array $symbols = []): array
    {
        return $this->client->get(
            '/v1/asset/fund',
            $symbols ? ['symbol' => $symbols] : []
        );
    }

    /**
     * 获取资金流水。
     *
     * 官方路径：GET /v1/asset/cashflow
     *
     * @param array $filters 查询条件，例如 start_time、end_time、business_type、symbol、page、size。
     * @return array 返回 Longbridge data 数组。
     */
    public function getCashflow(array $filters): array
    {
        return $this->client->get('/v1/asset/cashflow', $filters);
    }

    /**
     * 获取结单列表。
     *
     * 参考官方 SDK：GET /v1/statement/list
     *
     * @param array $filters 查询条件，例如 statement_type、month、page、size。
     * @return array 返回 Longbridge data 数组。
     */
    public function getStatements(array $filters = []): array
    {
        return $this->client->get('/v1/statement/list', $filters);
    }

    /**
     * 获取结单下载地址。
     *
     * 参考官方 SDK：GET /v1/statement/download
     *
     * @param array $filters 查询条件，通常包含 file_key。
     * @return array 返回 Longbridge data 数组，通常包含 url。
     */
    public function getStatementDownloadUrl(array $filters): array
    {
        return $this->client->get('/v1/statement/download', $filters);
    }
}
