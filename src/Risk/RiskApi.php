<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */

declare(strict_types=1);

namespace Brown\Longbridge\Risk;

use Brown\Longbridge\Http\OAuthHttpClient;

final class RiskApi
{
    public function __construct(
        private readonly OAuthHttpClient $client
    ) {
    }

    /**
     * 查询标的保证金比例。
     *
     * 官方路径：GET /v1/risk/margin-ratio
     *
     * @param string $symbol 标的代码，例如 AAPL.US。
     * @return array 返回 Longbridge data 数组。
     */
    public function getMarginRatio(string $symbol): array
    {
        $symbol = trim($symbol);
        if ($symbol === '') {
            throw new \InvalidArgumentException('symbol is empty.');
        }

        return $this->client->get('/v1/risk/margin-ratio', [
            'symbol' => $symbol,
        ]);
    }
}
