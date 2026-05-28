<?php

declare(strict_types=1);

namespace Brown\Longbridge\Tests\Api;

use Brown\Longbridge\Proto\Quote\Command as QuoteCommand;
use Brown\Longbridge\Proto\Quote\PushQuote;
use Brown\Longbridge\Proto\Quote\SecurityQuote;
use Brown\Longbridge\Proto\Quote\SecurityQuoteResponse;
use Brown\Longbridge\Proto\Quote\SubscribeRequest;
use Brown\Longbridge\Protocol\Packet;
use Brown\Longbridge\Protocol\PacketType;
use Brown\Longbridge\Quote\Pull\SecurityQuoteApi;
use Brown\Longbridge\Quote\Push\QuotePushApi;
use Brown\Longbridge\Quote\Subscribe\SubscriptionApi;
use Brown\Longbridge\Tests\Support\SpyWsClient;
use PHPUnit\Framework\TestCase;

final class QuoteSocketApiTest extends TestCase
{
    public function testSecurityQuoteApiSendsQuoteRequestAndDecodesResponse(): void
    {
        $quote = new SecurityQuote();
        $quote->setSymbol('AAPL.US');
        $quote->setLastDone('180.12');
        $response = new SecurityQuoteResponse();
        $response->setSecuQuote([$quote]);

        $ws = new SpyWsClient([
            new Packet(PacketType::RESPONSE, QuoteCommand::QuerySecurityQuote, $response->serializeToString(), 1, 0),
        ]);

        $result = (new SecurityQuoteApi($ws))->quote(['AAPL.US'], ['timeout' => 1.5]);

        self::assertSame('AAPL.US', $result['secu_quote'][0]['symbol']);
        self::assertSame('180.12', $result['secu_quote'][0]['last_done']);
        self::assertSame(QuoteCommand::QuerySecurityQuote, $ws->requests[0]['cmdCode']);
        self::assertSame(1.5, $ws->requests[0]['timeout']);
        self::assertSame(1500, $ws->requests[0]['timeoutMs']);
    }

    public function testSubscriptionApiSendsSubscribeRequest(): void
    {
        $ws = new SpyWsClient([
            new Packet(PacketType::RESPONSE, QuoteCommand::Subscribe, '', 1, 0),
        ]);

        $result = (new SubscriptionApi($ws))->subscribe(['AAPL.US'], ['quote', 'depth'], [
            'is_first_push' => false,
            'timeout' => 2.0,
        ]);

        $request = new SubscribeRequest();
        $request->mergeFromString($ws->requests[0]['protobufBody']);

        self::assertSame('longbridge_quote_subscribe', $result['type']);
        self::assertSame(QuoteCommand::Subscribe, $ws->requests[0]['cmdCode']);
        self::assertSame(['AAPL.US'], iterator_to_array($request->getSymbol()));
        self::assertSame([1, 2], iterator_to_array($request->getSubType()));
        self::assertFalse($request->getIsFirstPush());
        self::assertSame(2000, $ws->requests[0]['timeoutMs']);
    }

    public function testQuotePushApiWaitsForMatchingQuotePush(): void
    {
        $message = new PushQuote();
        $message->setSymbol('AAPL.US');
        $message->setLastDone('180.12');

        $ws = new SpyWsClient(recvPackets: [
            new Packet(PacketType::RESPONSE, QuoteCommand::Subscribe, '', 1, 0),
            new Packet(PacketType::PUSH, QuoteCommand::PushDepthData, ''),
            new Packet(PacketType::PUSH, QuoteCommand::PushQuoteData, $message->serializeToString()),
        ]);

        $push = (new QuotePushApi($ws))->waitQuote(1.0);

        self::assertSame('quote', $push['type']);
        self::assertSame(QuoteCommand::PushQuoteData, $push['cmd']);
        self::assertSame('AAPL.US', $push['payload']['symbol']);
        self::assertSame('180.12', $push['payload']['last_done']);
    }
}
