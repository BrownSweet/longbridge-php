<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */

namespace Brown\Longbridge\Quote\Pull\Protobuf;

use Brown\Longbridge\Proto\Control\Depth;
use Brown\Longbridge\Proto\Control\MultiSecurityRequest;
use Brown\Longbridge\Proto\Control\SecurityDepthResponse;

class SecurityDepthProtobuf
{
    public static function multiSecurityRequest(array $symbols): string
    {
        $multiSecurityRequest = new MultiSecurityRequest();

        $multiSecurityRequest->setSymbol($symbols);

        return $multiSecurityRequest->serializeToString();
    }

    public static function decodeSecurityDepthResponse(string $body):array
    {
        $response = new SecurityDepthResponse();
        $response->mergeFromString($body);


        return [
            'symbol'=>$response->getSymbol(),
            'ask' => self::depthList($response->getAsk()),
            'bid' => self::depthList($response->getBid()),
        ];
    }

    private static function depthList(iterable $items): array
    {
        $rows = [];

        foreach ($items as $depth) {
            if (!$depth instanceof Depth) {
                continue;
            }

            $rows[] = [
                'position' => $depth->getPosition(),
                'price' => $depth->getPrice(),
                'volume' => $depth->getVolume(),
                'order_num' => $depth->getOrderNum(),
            ];
        }

        return $rows;
    }
}