<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2026-05-15 12:03
 */

namespace Brown\Longbridge\Socket;

use Brown\Longbridge\Protocol\LongbridgeCodec;
use Brown\Longbridge\Protocol\Packet;
use RuntimeException;
use Swoole\Coroutine\Http\Client;

class LongbridgeWsClient
{
    private ?Client $client = null;

    public function __construct(
        private readonly string $wsBaseUrl,
        private readonly LongbridgeCodec $codec = new LongbridgeCodec(),
        private readonly RequestIdGenerator $requestIdGenerator = new RequestIdGenerator(),
    ) {
    }

    public function connect(): void
    {
        $url = rtrim($this->wsBaseUrl, '/') . '/?version=1&codec=1&platform=9';
        $parts = parse_url($url);

        if (!$parts || empty($parts['host'])) {
            throw new RuntimeException("invalid websocket url: {$url}");
        }

        $scheme = $parts['scheme'] ?? 'wss';
        $ssl = $scheme === 'wss';
        $port = $parts['port'] ?? ($ssl ? 443 : 80);
        $path = ($parts['path'] ?? '/');

        if (!empty($parts['query'])) {
            $path .= '?' . $parts['query'];
        }

        $client = new Client($parts['host'], $port, $ssl);
        $client->set([
            'timeout' => 10,
            'websocket_mask' => true,
        ]);

        $ok = $client->upgrade($path);

        if (!$ok) {
            throw new RuntimeException(
                'websocket upgrade failed: ' . $client->errCode
            );
        }

        $this->client = $client;
    }

    public function sendRequest(
        int $cmdCode,
        string $protobufBody = '',
        int $timeoutMs = 10000
    ): int {
        if (!$this->client) {
            throw new RuntimeException('websocket not connected.');
        }

        $requestId = $this->requestIdGenerator->next();

        $binary = $this->codec->encodeRequest(
            cmdCode: $cmdCode,
            requestId: $requestId,
            body: $protobufBody,
            timeoutMs: $timeoutMs
        );

        $ok = $this->client->push($binary, \WEBSOCKET_OPCODE_BINARY);

        if (!$ok) {
            throw new RuntimeException('websocket push failed.');
        }

        return $requestId;
    }

    public function recv(float $timeout = 10.0): ?Packet
    {
        if (!$this->client) {
            throw new RuntimeException('websocket not connected.');
        }

        $frame = $this->client->recv($timeout);

        if ($frame === false || $frame === null || !isset($frame->data)) {
            return null;
        }

        return $this->codec->decode($frame->data);
    }
    public function close(): void
    {
        if ($this->client) {
            $this->client->close();
            $this->client = null;
        }
    }
}
