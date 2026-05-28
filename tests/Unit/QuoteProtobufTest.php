<?php

declare(strict_types=1);

namespace Brown\Longbridge\Tests\Unit;

use Brown\Longbridge\Proto\Quote\AdjustType;
use Brown\Longbridge\Proto\Quote\Direction;
use Brown\Longbridge\Proto\Quote\HistoryCandlestickQueryType;
use Brown\Longbridge\Proto\Quote\MarketTradeDayRequest;
use Brown\Longbridge\Proto\Quote\MultiSecurityRequest;
use Brown\Longbridge\Proto\Quote\Period;
use Brown\Longbridge\Proto\Quote\SecurityCandlestickRequest;
use Brown\Longbridge\Proto\Quote\SecurityHistoryCandlestickRequest;
use Brown\Longbridge\Proto\Quote\SecurityRequest;
use Brown\Longbridge\Proto\Quote\SecurityTradeRequest;
use Brown\Longbridge\Proto\Quote\WarrantFilterListRequest;
use Brown\Longbridge\Quote\Pull\Protobuf\QuoteProtobuf;
use PHPUnit\Framework\TestCase;

final class QuoteProtobufTest extends TestCase
{
    public function testBuildsMultiSecurityRequest(): void
    {
        $request = new MultiSecurityRequest();
        $request->mergeFromString(QuoteProtobuf::multiSecurityRequest(['AAPL.US', '700.HK']));

        self::assertSame(['AAPL.US', '700.HK'], iterator_to_array($request->getSymbol()));
    }

    public function testBuildsSecurityTradeRequest(): void
    {
        $request = new SecurityTradeRequest();
        $request->mergeFromString(QuoteProtobuf::securityTradeRequest('AAPL.US', 50));

        self::assertSame('AAPL.US', $request->getSymbol());
        self::assertSame(50, $request->getCount());
    }

    public function testBuildsCandlestickRequestWithOptions(): void
    {
        $request = new SecurityCandlestickRequest();
        $request->mergeFromString(QuoteProtobuf::securityCandlestickRequest('AAPL.US', Period::DAY, 100, [
            'adjust_type' => AdjustType::FORWARD_ADJUST,
            'trade_session' => 2,
        ]));

        self::assertSame('AAPL.US', $request->getSymbol());
        self::assertSame(Period::DAY, $request->getPeriod());
        self::assertSame(100, $request->getCount());
        self::assertSame(AdjustType::FORWARD_ADJUST, $request->getAdjustType());
        self::assertSame(2, $request->getTradeSession());
    }

    public function testBuildsHistoryCandlestickDateRequest(): void
    {
        $request = new SecurityHistoryCandlestickRequest();
        $request->mergeFromString(QuoteProtobuf::securityHistoryCandlestickRequest('AAPL.US', Period::DAY, [
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
        ]));

        self::assertSame(HistoryCandlestickQueryType::QUERY_BY_DATE, $request->getQueryType());
        self::assertSame('2026-01-01', $request->getDateRequest()->getStartDate());
        self::assertSame('2026-01-31', $request->getDateRequest()->getEndDate());
    }

    public function testBuildsHistoryCandlestickOffsetRequest(): void
    {
        $request = new SecurityHistoryCandlestickRequest();
        $request->mergeFromString(QuoteProtobuf::securityHistoryCandlestickRequest('AAPL.US', Period::DAY, [
            'direction' => Direction::FORWARD,
            'date' => '2026-01-01',
            'minute' => '09:30',
            'count' => 20,
        ]));

        self::assertSame(HistoryCandlestickQueryType::QUERY_BY_OFFSET, $request->getQueryType());
        self::assertSame(Direction::FORWARD, $request->getOffsetRequest()->getDirection());
        self::assertSame('2026-01-01', $request->getOffsetRequest()->getDate());
        self::assertSame('09:30', $request->getOffsetRequest()->getMinute());
        self::assertSame(20, $request->getOffsetRequest()->getCount());
    }

    public function testBuildsMarketTradeDayRequest(): void
    {
        $request = new MarketTradeDayRequest();
        $request->mergeFromString(QuoteProtobuf::marketTradeDayRequest('US', '2026-01-01', '2026-01-31'));

        self::assertSame('US', $request->getMarket());
        self::assertSame('2026-01-01', $request->getBegDay());
        self::assertSame('2026-01-31', $request->getEndDay());
    }

    public function testBuildsWarrantFilterListRequest(): void
    {
        $request = new WarrantFilterListRequest();
        $request->mergeFromString(QuoteProtobuf::warrantFilterListRequest('700.HK', [
            'sort_by' => 1,
            'type' => [2, '3'],
            'issuer' => 10,
        ], 1));

        self::assertSame('700.HK', $request->getSymbol());
        self::assertSame(1, $request->getLanguage());
        self::assertSame(1, $request->getFilterConfig()->getSortBy());
        self::assertSame([2, 3], iterator_to_array($request->getFilterConfig()->getType()));
        self::assertSame([10], iterator_to_array($request->getFilterConfig()->getIssuer()));
    }

    public function testBuildsSecurityRequest(): void
    {
        $request = new SecurityRequest();
        $request->mergeFromString(QuoteProtobuf::securityRequest('AAPL.US'));

        self::assertSame('AAPL.US', $request->getSymbol());
    }
}
