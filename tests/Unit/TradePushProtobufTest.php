<?php

declare(strict_types=1);

namespace Brown\Longbridge\Tests\Unit;

use Brown\Longbridge\Proto\Trade\ContentType;
use Brown\Longbridge\Proto\Trade\DispatchType;
use Brown\Longbridge\Proto\Trade\Notification;
use Brown\Longbridge\Proto\Trade\Sub;
use Brown\Longbridge\Proto\Trade\SubResponse;
use Brown\Longbridge\Trade\Push\Protobuf\TradePushProtobuf;
use PHPUnit\Framework\TestCase;

final class TradePushProtobufTest extends TestCase
{
    public function testBuildsSubRequest(): void
    {
        $request = new Sub();
        $request->mergeFromString(TradePushProtobuf::subRequest(['private']));

        self::assertSame(['private'], iterator_to_array($request->getTopics()));
    }

    public function testDecodesSubResponse(): void
    {
        $response = new SubResponse();
        $response->setSuccess(['private']);
        $response->setCurrent(['private']);

        self::assertSame([
            'success' => ['private'],
            'fail' => [],
            'current' => ['private'],
        ], TradePushProtobuf::decodeSubResponse($response->serializeToString()));
    }

    public function testDecodesNotificationJsonPayload(): void
    {
        $message = new Notification();
        $message->setTopic('private');
        $message->setContentType(ContentType::CONTENT_JSON);
        $message->setDispatchType(DispatchType::DISPATCH_DIRECT);
        $message->setData('{"event":"order_changed"}');

        $payload = TradePushProtobuf::decodeNotification($message->serializeToString());

        self::assertSame('private', $payload['topic']);
        self::assertSame(ContentType::CONTENT_JSON, $payload['content_type']);
        self::assertSame(DispatchType::DISPATCH_DIRECT, $payload['dispatch_type']);
        self::assertSame('{"event":"order_changed"}', $payload['data']);
        self::assertSame(['event' => 'order_changed'], $payload['data_json']);
    }
}
