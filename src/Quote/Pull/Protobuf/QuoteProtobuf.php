<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2026-05-16 19:26
 */


namespace Brown\Longbridge\Quote\Pull\Protobuf;

use Brown\Longbridge\Proto\Control\MultiSecurityRequest;
use Brown\Longbridge\Proto\Control\SecurityQuote;
use Brown\Longbridge\Proto\Control\SecurityQuoteResponse;
use Brown\Longbridge\Proto\Control\PrePostQuote;


final class QuoteProtobuf
{
    public static function multiSecurityRequest(array $symbols): string
    {
        $multiSecurityRequest = new MultiSecurityRequest();

        $multiSecurityRequest->setSymbol($symbols);

        return $multiSecurityRequest->serializeToString();
    }

    public static function decodeSecurityQuoteResponse(string $body): array
    {
        $response = new SecurityQuoteResponse();
        $response->mergeFromString($body);

        $quotes = [];
        foreach ($response->getSecuQuote() as $quote) {
            $quotes[] = self::securityQuoteToArray($quote);
        }

        return [
            'secu_quote' => $quotes,
        ];
    }

    private static function securityQuoteToArray(SecurityQuote $quote): array
    {
        return [
            'symbol' => $quote->getSymbol(),
            'last_done' => $quote->getLastDone(),
            'prev_close' => $quote->getPrevClose(),
            'open' => $quote->getOpen(),
            'high' => $quote->getHigh(),
            'low' => $quote->getLow(),
            'timestamp' => $quote->getTimestamp(),
            'volume' => $quote->getVolume(),
            'turnover' => $quote->getTurnover(),
            'trade_status' => $quote->getTradeStatus(),
            'pre_market_quote' => $quote->hasPreMarketQuote()
                ? self::prePostQuoteToArray($quote->getPreMarketQuote())
                : null,
            'post_market_quote' => $quote->hasPostMarketQuote()
                ? self::prePostQuoteToArray($quote->getPostMarketQuote())
                : null,
            'over_night_quote' => $quote->hasOverNightQuote()
                ? self::prePostQuoteToArray($quote->getOverNightQuote())
                : null,
        ];
    }

    private static function prePostQuoteToArray(PrePostQuote $quote): array
    {
        return [
            'last_done' => $quote->getLastDone(),
            'timestamp' => $quote->getTimestamp(),
            'volume' => $quote->getVolume(),
            'turnover' => $quote->getTurnover(),
            'high' => $quote->getHigh(),
            'low' => $quote->getLow(),
            'prev_close' => $quote->getPrevClose(),
        ];
    }

}
