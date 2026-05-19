<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2026-05-16 20:15
 */



namespace Brown\Longbridge\Quote\Subscribe\Protobuf;

use Brown\Longbridge\Proto\Control\SubscribeRequest;
use Brown\Longbridge\Proto\Control\SubType;

final class SubscribeProtobuf
{
    public static function subscribeRequest(
        array $symbols,
        array $subTypes = [SubType::QUOTE],
        bool $isFirstPush = true
    ): string {
        $request = new SubscribeRequest();
        $request->setSymbol($symbols);
        $request->setSubType($subTypes);
        $request->setIsFirstPush($isFirstPush);

        return $request->serializeToString();
    }


}
