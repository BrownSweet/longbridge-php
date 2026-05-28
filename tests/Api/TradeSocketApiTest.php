<?php

declare(strict_types=1);

namespace Brown\Longbridge\Tests\Api;

use Brown\Longbridge\Proto\Trade\Command as TradeCommand;
use Brown\Longbridge\Proto\Trade\ContentType;
use Brown\Longbridge\Proto\Trade\DispatchType;
use Brown\Longbridge\Proto\Trade\Notification;
use Brown\Longbridge\Proto\Trade\Sub;
use Brown\Longbridge\Proto\Trade\SubResponse;
use Brown\Longbridge\Protocol\Packet;
use Brown\Longbridge\Protocol\PacketType;
use Brown\Longbridge\Tests\Support\SpyWsClient;
use Brown\Longbridge\Trade\Push\TradePushApi;
use PHPUnit\Framework\TestCase;

final class TradeSocketApiTest extends TestCase
{
    public function testTradePushSubscribeSendsSubRequestAndDecodesResponse(): void
    {
        $response = new SubResponse();
        $response->setSuccess(['private']);
        $response->setCurrent(['private']);

        $ws = new SpyWsClient([
            new Packet(PacketType::RESPONSE, TradeCommand::CMD_SUB, $response->serializeToString(), 1, 0),
        ]);

        $result = (new TradePushApi($ws))->subscribePrivate(['timeout' => 2.5]);

        $request = new Sub();
        $request->mergeFromString($ws->requests[0]['protobufBody']);

        self::assertSame(['private'], $result['success']);
        self::assertSame(TradeCommand::CMD_SUB, $ws->requests[0]['cmdCode']);
        self::assertSame(['private'], iterator_to_array($request->getTopics()));
        self::assertSame(2500, $ws->requests[0]['timeoutMs']);
    }

    public function testWaitNotificationDecodesJsonNotificationPush(): void
    {
        $notification = new Notification();
        $notification->setTopic('private');
        $notification->setContentType(ContentType::CONTENT_JSON);
        $notification->setDispatchType(DispatchType::DISPATCH_DIRECT);
        $notification->setData('{"event":"filled"}');

        $ws = new SpyWsClient(recvPackets: [
            new Packet(PacketType::PUSH, TradeCommand::CMD_NOTIFY, $notification->serializeToString()),
        ]);

        $push = (new TradePushApi($ws))->waitNotification(1.0);

        self::assertSame('trade_notification', $push['type']);
        self::assertSame(TradeCommand::CMD_NOTIFY, $push['cmd']);
        self::assertSame('private', $push['payload']['topic']);
        self::assertSame(['event' => 'filled'], $push['payload']['data_json']);
    }
}
