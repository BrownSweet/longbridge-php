<?php

namespace Brown\Longbridge\Quote\Push\Protobuf;

use Brown\Longbridge\Proto\Control\PushQuote;
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2026-05-16 20:21
 */
class PushProtobuf
{
    public static function decodePushQuote(string $body): array
    {
        $pushQuote = new PushQuote();
        $pushQuote->mergeFromString($body);

        return [
            'symbol' => $pushQuote->getSymbol(),
            'sequence' => $pushQuote->getSequence(),
            'last_done' => $pushQuote->getLastDone(),
            'open' => $pushQuote->getOpen(),
            'high' => $pushQuote->getHigh(),
            'low' => $pushQuote->getLow(),
            'timestamp' => $pushQuote->getTimestamp(),
            'volume' => $pushQuote->getVolume(),
            'turnover' => $pushQuote->getTurnover(),
            'trade_status' => $pushQuote->getTradeStatus(),
            'trade_session' => $pushQuote->getTradeSession(),
            'current_volume' => $pushQuote->getCurrentVolume(),
            'current_turnover' => $pushQuote->getCurrentTurnover(),
            'tag' => $pushQuote->getTag(),
        ];
    }
}