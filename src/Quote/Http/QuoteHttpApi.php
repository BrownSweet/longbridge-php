<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */

declare(strict_types=1);

namespace Brown\Longbridge\Quote\Http;

use Brown\Longbridge\Http\OAuthHttpClient;
use Brown\Longbridge\Support\Symbol;

final class QuoteHttpApi
{
    public function __construct(
        private readonly OAuthHttpClient $client
    ) {
    }

    /**
     * 查询自选股分组。
     *
     * 官方路径：GET /v1/watchlist/groups
     *
     * @return array 返回 Longbridge data 数组。
     */
    public function watchlistGroups(): array
    {
        return $this->client->get('/v1/watchlist/groups');
    }

    /**
     * 创建自选股分组。
     *
     * 官方路径：POST /v1/watchlist/groups
     *
     * @param string $name 分组名。
     * @param array $symbols 初始标的列表。
     * @return array 返回 Longbridge data 数组，通常包含 id。
     */
    public function createWatchlistGroup(string $name, array $symbols = []): array
    {
        return $this->client->post('/v1/watchlist/groups', [
            'name' => $name,
            'securities' => Symbol::normalizeList($symbols),
        ]);
    }

    /**
     * 更新自选股分组。
     *
     * 官方路径：PUT /v1/watchlist/groups
     *
     * @param int|string $groupId 分组 ID。
     * @param string $name 分组名。
     * @param array $symbols 标的列表。
     * @param string $mode 更新模式，按官方值传入，例如 add、remove、replace。
     * @return array 返回 Longbridge data 数组。
     */
    public function updateWatchlistGroup(int|string $groupId, string $name, array $symbols = [], string $mode = 'add'): array
    {
        return $this->client->put('/v1/watchlist/groups', [
            'id' => $groupId,
            'name' => $name,
            'securities' => Symbol::normalizeList($symbols),
            'mode' => $mode,
        ]);
    }

    /**
     * 删除自选股分组。
     *
     * 官方路径：DELETE /v1/watchlist/groups
     *
     * @param int|string $groupId 分组 ID。
     * @param bool $purge 是否同时从其他分组移除这些标的。
     * @return array 返回 Longbridge data 数组。
     */
    public function deleteWatchlistGroup(int|string $groupId, bool $purge = false): array
    {
        return $this->client->delete('/v1/watchlist/groups', [], [
            'id' => $groupId,
            'purge' => $purge,
        ]);
    }

    /**
     * 置顶或取消置顶分组内标的。
     *
     * 官方文档当前标注：PUT /watchlist/groups
     *
     * @param int|string $groupId 分组 ID。
     * @param string $symbol 标的代码。
     * @param bool $isPinned true 为置顶，false 为取消置顶。
     * @return array 返回 Longbridge data 数组。
     */
    public function updatePinned(int|string $groupId, string $symbol, bool $isPinned): array
    {
        return $this->client->put('/watchlist/groups', [
            'id' => $groupId,
            'symbol' => $symbol,
            'is_pinned' => $isPinned,
        ]);
    }

    /**
     * 按官方 SDK 的 /v1/watchlist/pinned 批量置顶或取消置顶。
     *
     * 参考官方 SDK：POST /v1/watchlist/pinned
     *
     * @param array $symbols 标的列表。
     * @param string $mode pin 或 unpin。
     * @return array 返回 Longbridge data 数组。
     */
    public function updatePinnedBySymbols(array $symbols, string $mode = 'pin'): array
    {
        return $this->client->post('/v1/watchlist/pinned', [
            'mode' => $mode,
            'securities' => Symbol::normalizeList($symbols),
        ]);
    }

    /**
     * 查询公告文件。
     *
     * 官方路径：GET /v1/quote/filings
     *
     * @param string $symbol 标的代码。
     * @return array 返回 Longbridge data 数组。
     */
    public function filings(string $symbol): array
    {
        return $this->client->get('/v1/quote/filings', [
            'symbol' => $symbol,
        ]);
    }

    /**
     * 查询标的列表。
     *
     * 官方路径：GET /v1/quote/get_security_list
     *
     * @param string $market 市场代码，例如 US、HK、CN、SG。
     * @param string $category 标的类别，按官方文档原值传入。
     * @param array $filters 额外查询条件。
     * @return array 返回 Longbridge data 数组。
     */
    public function securityList(string $market, string $category, array $filters = []): array
    {
        return $this->client->get('/v1/quote/get_security_list', array_merge($filters, [
            'market' => strtoupper(trim($market)),
            'category' => $category,
        ]));
    }

    /**
     * 查询美股卖空数据。
     *
     * 参考官方 SDK：GET /v1/quote/short-positions/us
     *
     * @param string $symbol 标的代码。
     * @param array $filters 可选 last_timestamp、page_size。
     * @return array 返回 Longbridge data 数组。
     */
    public function shortPositions(string $symbol, array $filters = []): array
    {
        return $this->client->get('/v1/quote/short-positions/us', array_merge([
            'counter_id' => Symbol::toSecurityCounterId($symbol),
            'last_timestamp' => 0,
            'page_size' => 100,
        ], $filters));
    }

    /**
     * 查询期权成交量统计。
     *
     * 参考官方 SDK：GET /v1/quote/option-volume-stats
     *
     * @param string $symbol 标的代码。
     * @return array 返回 Longbridge data 数组。
     */
    public function optionVolume(string $symbol): array
    {
        return $this->client->get('/v1/quote/option-volume-stats', [
            'symbol' => $symbol,
        ]);
    }

    /**
     * 查询每日期权成交量统计。
     *
     * 参考官方 SDK：GET /v1/quote/option-volume-stats/daily
     *
     * @param string $symbol 标的代码。
     * @param string $startDate 开始日期，格式 YYYY-MM-DD。
     * @param string $endDate 结束日期，格式 YYYY-MM-DD。
     * @return array 返回 Longbridge data 数组。
     */
    public function optionVolumeDaily(string $symbol, string $startDate, string $endDate): array
    {
        return $this->client->get('/v1/quote/option-volume-stats/daily', [
            'symbol' => $symbol,
            'start' => $startDate,
            'end' => $endDate,
        ]);
    }
}
