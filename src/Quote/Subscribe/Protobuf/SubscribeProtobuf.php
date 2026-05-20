<?php

declare(strict_types=1);

namespace Brown\Longbridge\Quote\Subscribe\Protobuf;

use Brown\Longbridge\Proto\Quote\SubType;
use Brown\Longbridge\Proto\Quote\SubscribeRequest;
use Brown\Longbridge\Proto\Quote\SubscriptionRequest;
use Brown\Longbridge\Proto\Quote\UnsubscribeRequest;
use Brown\Longbridge\Support\Protobuf;
use Google\Protobuf\Internal\Message;
use InvalidArgumentException;

final class SubscribeProtobuf
{
    private const SUB_TYPE_MAP = [
        'quote' => SubType::QUOTE,
        'depth' => SubType::DEPTH,
        'brokers' => SubType::BROKERS,
        'trade' => SubType::TRADE,
    ];

    /**
     * 构造行情订阅请求。
     *
     * @param array<int,string> $symbols 标的代码列表。
     * @param array<int,int|string> $subTypes 订阅类型，支持 SubType 常量或 quote/depth/brokers/trade。
     */
    public static function subscribeRequest(
        array $symbols,
        array $subTypes = [SubType::QUOTE],
        bool $isFirstPush = true
    ): string {
        $request = new SubscribeRequest();
        $request->setSymbol(array_values($symbols));
        $request->setSubType(self::normalizeSubTypes($subTypes));
        $request->setIsFirstPush($isFirstPush);

        return $request->serializeToString();
    }

    /**
     * 构造行情退订请求。
     *
     * @param array<int,string> $symbols 标的代码列表。
     * @param array<int,int|string> $subTypes 订阅类型，空数组表示仅按 unsub_all 处理。
     */
    public static function unsubscribeRequest(
        array $symbols = [],
        array $subTypes = [],
        bool $unsubAll = false
    ): string {
        $request = new UnsubscribeRequest();
        $request->setSymbol(array_values($symbols));
        $request->setSubType(self::normalizeSubTypes($subTypes));
        $request->setUnsubAll($unsubAll);

        return $request->serializeToString();
    }

    /**
     * 构造查询当前订阅请求。
     */
    public static function subscriptionRequest(): string
    {
        return (new SubscriptionRequest())->serializeToString();
    }

    /**
     * 反序列化 protobuf 响应为 snake_case 数组。
     *
     * @param class-string<Message> $messageClass
     */
    public static function decode(string $body, string $messageClass): array
    {
        return Protobuf::decode($body, $messageClass);
    }

    /**
     * @param array<int,int|string> $subTypes
     * @return array<int,int>
     */
    public static function normalizeSubTypes(array $subTypes): array
    {
        $values = [];
        foreach ($subTypes as $subType) {
            if (is_int($subType)) {
                $values[] = $subType;
                continue;
            }

            $key = strtolower((string)$subType);
            if (!array_key_exists($key, self::SUB_TYPE_MAP)) {
                throw new InvalidArgumentException("unsupported quote sub type: {$subType}");
            }

            $values[] = self::SUB_TYPE_MAP[$key];
        }

        return $values;
    }
}
