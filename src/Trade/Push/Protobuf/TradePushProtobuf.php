<?php

declare(strict_types=1);

namespace Brown\Longbridge\Trade\Push\Protobuf;

use Brown\Longbridge\Proto\Trade\Notification;
use Brown\Longbridge\Proto\Trade\Sub;
use Brown\Longbridge\Proto\Trade\SubResponse;
use Brown\Longbridge\Proto\Trade\Unsub;
use Brown\Longbridge\Proto\Trade\UnsubResponse;
use Brown\Longbridge\Support\Protobuf;

final class TradePushProtobuf
{
    /**
     * 构造交易推送订阅请求。
     *
     * @param array<int,string> $topics 订阅主题，例如 ['private']。
     */
    public static function subRequest(array $topics): string
    {
        $request = new Sub();
        $request->setTopics(array_values($topics));

        return $request->serializeToString();
    }

    /**
     * 构造交易推送退订请求。
     *
     * @param array<int,string> $topics 退订主题，例如 ['private']。
     */
    public static function unsubRequest(array $topics): string
    {
        $request = new Unsub();
        $request->setTopics(array_values($topics));

        return $request->serializeToString();
    }

    /**
     * 解析交易推送订阅响应。
     */
    public static function decodeSubResponse(string $body): array
    {
        return Protobuf::decode($body, SubResponse::class);
    }

    /**
     * 解析交易推送退订响应。
     */
    public static function decodeUnsubResponse(string $body): array
    {
        return Protobuf::decode($body, UnsubResponse::class);
    }

    /**
     * 解析交易推送通知。data 保留原始字节，同时附带 base64；若可解析 JSON 则补 data_json。
     */
    public static function decodeNotification(string $body): array
    {
        $notification = new Notification();
        $notification->mergeFromString($body);

        $data = $notification->getData();
        $result = [
            'topic' => $notification->getTopic(),
            'content_type' => $notification->getContentType(),
            'dispatch_type' => $notification->getDispatchType(),
            'data' => $data,
            'data_base64' => base64_encode($data),
        ];

        if ($data !== '') {
            $json = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $result['data_json'] = $json;
            }
        }

        return $result;
    }
}
