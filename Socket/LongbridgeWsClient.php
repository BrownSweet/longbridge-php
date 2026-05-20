<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2026-05-15 12:03
 */

namespace Brown\Longbridge\Socket;

use Brown\Longbridge\Proto\Control\AuthRequest;
use Brown\Longbridge\Proto\Control\AuthResponse;
use Brown\Longbridge\Protocol\ControlCommand;
use Brown\Longbridge\Protocol\LongbridgeCodec;
use Brown\Longbridge\Protocol\Packet;
use RuntimeException;
use Swoole\Coroutine\Http\Client;

class LongbridgeWsClient
{
    private ?Client $client = null;
    private $isAuthenticated = false;
    private $isConnected = false;
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
        $this->isConnected = true;
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

    public function request(
        int $cmdCode,
        string $protobufBody = '',
        float $timeout = 10.0,
        int $timeoutMs = 10000
    ): Packet {
        $requestId = $this->sendRequest(
            cmdCode: $cmdCode,
            protobufBody: $protobufBody,
            timeoutMs: $timeoutMs
        );

        $deadline = microtime(true) + $timeout;

        while (true) {
            $left = $deadline - microtime(true);
            if ($left <= 0) {
                throw new RuntimeException("Longbridge request timeout, cmd={$cmdCode}, request_id={$requestId}");
            }

            $packet = $this->recv($left);
            if (!$packet) {
                continue;
            }

            if (
                $packet->isResponse()
                && $packet->cmdCode === $cmdCode
                && $packet->requestId === $requestId
            ) {
                return $packet;
            }
        }
    }

    public function recv(float $timeout = 10.0): ?Packet
    {
        if (!$this->client) {
            throw new RuntimeException('websocket not connected.');
        }

        $deadline = microtime(true) + $timeout;

        do {
            $left = max(0.001, $deadline - microtime(true));
            $frame = $this->client->recv($left);

            if ($frame === false || $frame === null || !isset($frame->data)) {
                return null;
            }

            $packet = $this->codec->decode($frame->data);
            if ($this->handleControlRequest($packet)) {
                continue;
            }

            return $packet;
        } while (microtime(true) < $deadline);

        return null;
    }

    public function sendResponse(int $cmdCode, int $requestId, string $body = '', int $status = 0): void
    {
        if (!$this->client) {
            throw new RuntimeException('websocket not connected.');
        }

        $binary = $this->codec->encodeResponse(
            cmdCode: $cmdCode,
            requestId: $requestId,
            status: $status,
            body: $body
        );

        $ok = $this->client->push($binary, \WEBSOCKET_OPCODE_BINARY);

        if (!$ok) {
            throw new RuntimeException('websocket response push failed.');
        }
    }

    private function handleControlRequest(Packet $packet): bool
    {
        if (!$packet->isRequest()) {
            return false;
        }

        if ($packet->cmdCode === ControlCommand::HEARTBEAT && $packet->requestId !== null) {
            $this->sendResponse(
                cmdCode: ControlCommand::HEARTBEAT,
                requestId: $packet->requestId,
                body: $packet->body,
                status: 0
            );
            return true;
        }

        return false;
    }
    public function close(): void
    {
        if ($this->client) {
            $this->client->close();
            $this->client = null;
            $this->isConnected = false;
            $this->isAuthenticated = false;
        }
    }

    public function authenticate(string $otp, float $timeout = 10.0): AuthResponse
    {
        $otp = trim($otp);
        if ($otp === '') {
            throw new RuntimeException('Longbridge socket otp is empty.');
        }

        $authRequest = new AuthRequest();
        $authRequest->setToken($otp);

        $packet = $this->request(
            cmdCode: ControlCommand::AUTH,
            protobufBody: $authRequest->serializeToString(),
            timeout: $timeout,
            timeoutMs: (int)($timeout * 1000)
        );

        if (!$packet->isSuccess()) {
            throw new RuntimeException("Longbridge auth failed, status={$packet->status}");
        }

        $authResponse = new AuthResponse();
        $authResponse->mergeFromString($packet->body);

        $this->isAuthenticated = true;
        return $authResponse;
    }

    public function isAuthenticated()
    {
        return $this->isAuthenticated;
    }

    public function isConnected()
    {
        return $this->isConnected;
    }

}
