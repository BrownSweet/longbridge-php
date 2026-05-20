<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */

declare(strict_types=1);

namespace Brown\Longbridge\Calendar;

use Brown\Longbridge\Http\HttpClientInterface;

final class CalendarApi
{
    public function __construct(
        private readonly HttpClientInterface $client
    ) {
    }

    /**
     * 查询财经日历事件。
     *
     * 参考官方 SDK：GET /v1/quote/finance_calendar
     *
     * @param string $category 类型：report、dividend、split、ipo、macrodata、closed、meeting、merge。
     * @param string $startDate 开始日期，格式 YYYY-MM-DD。
     * @param string $endDate 结束日期，格式 YYYY-MM-DD。
     * @param array $filters 可选 market 或 markets。
     * @return array 返回 Longbridge data 数组。
     */
    public function financeCalendar(string $category, string $startDate, string $endDate, array $filters = []): array
    {
        $query = [
            'date' => $startDate,
            'date_end' => $endDate,
            'types[]' => $category,
        ];

        if (isset($filters['market'])) {
            $query['markets[]'] = $filters['market'];
            unset($filters['market']);
        }
        if (isset($filters['markets'])) {
            $query['markets[]'] = $filters['markets'];
            unset($filters['markets']);
        }

        return $this->client->get('/v1/quote/finance_calendar', array_merge($query, $filters));
    }

    /**
     * 查询财报日历。
     *
     * 官方目录：Earnings Calendar。
     */
    public function earningsCalendar(string $startDate, string $endDate, array $filters = []): array
    {
        return $this->financeCalendar('report', $startDate, $endDate, $filters);
    }

    /**
     * 查询分红日历。
     */
    public function dividendCalendar(string $startDate, string $endDate, array $filters = []): array
    {
        return $this->financeCalendar('dividend', $startDate, $endDate, $filters);
    }

    /**
     * 查询拆股日历。
     */
    public function splitCalendar(string $startDate, string $endDate, array $filters = []): array
    {
        return $this->financeCalendar('split', $startDate, $endDate, $filters);
    }

    /**
     * 查询股东会/会议日历。
     */
    public function meetingCalendar(string $startDate, string $endDate, array $filters = []): array
    {
        return $this->financeCalendar('meeting', $startDate, $endDate, $filters);
    }

    /**
     * 查询宏观日历。
     */
    public function macroCalendar(string $startDate, string $endDate, array $filters = []): array
    {
        return $this->financeCalendar('macrodata', $startDate, $endDate, $filters);
    }

    /**
     * 查询 IPO 日历。
     */
    public function ipoCalendar(string $startDate, string $endDate, array $filters = []): array
    {
        return $this->financeCalendar('ipo', $startDate, $endDate, $filters);
    }

    /**
     * 查询合并日历。
     */
    public function mergeCalendar(string $startDate, string $endDate, array $filters = []): array
    {
        return $this->financeCalendar('merge', $startDate, $endDate, $filters);
    }
}
