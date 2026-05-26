<?php

declare(strict_types=1);

namespace Brown\Longbridge\Socket;

use Brown\Longbridge\Proto\Control\AuthRequest;
use Brown\Longbridge\Proto\Control\AuthResponse;
use Brown\Longbridge\Proto\Control\ReconnectRequest;
use Brown\Longbridge\Proto\Control\ReconnectResponse;
use Brown\Longbridge\Protocol\ControlCommand;
use Brown\Longbridge\Protocol\LongbridgeCodec;
use Brown\Longbridge\Protocol\Packet;
use RuntimeException;
use Swoole\Coroutine\Http\Client;

final class LongbridgeWsClient
{
    private ?Client $client = null;
    private bool $isAuthenticated = false;
    private bool $isConnected = false;

    public function __construct(
        private readonly string $wsBaseUrl,
        private readonly LongbridgeCodec $codec = new LongbridgeCodec(),
        private readonly RequestIdGenerator $requestIdGenerator = new RequestIdGenerator(),
    ) {
    }

    /**
     * 连接长桥 WebSocket 网关。URL 会自动附加 version、codec、platform 参数。
     */
    public function connect(): void
    {
        if (!class_exists(Client::class)) {
            throw new RuntimeException('ext-swoole is required for Longbridge websocket client.');
        }

        $url = rtrim($this->wsBaseUrl, '/') . '/?version=1&codec=1&platform=9';
        $parts = parse_url($url);

        if (!$parts || empty($parts['host'])) {
            throw new RuntimeException("invalid websocket url: {$url}");
        }

        $scheme = $parts['scheme'] ?? 'wss';
        $ssl = $scheme === 'wss';
        $port = $parts['port'] ?? ($ssl ? 443 : 80);
        $path = $parts['path'] ?? '/';

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
            throw new RuntimeException('websocket upgrade failed: ' . $client->errCode);
        }

        $this->client = $client;
        $this->isConnected = true;
    }

    /**
     * 发送业务请求并返回 request_id，适用于需要自行等待响应的高级场景。
     *
     * @param int $cmdCode 业务命令号。
     * @param string $protobufBody protobuf 序列化后的请求体。
     * @param int $timeoutMs 服务端请求超时，单位毫秒。
     */
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

    /**
     * 发送请求并等待匹配的响应包。
     *
     * @param int $cmdCode 业务命令号。
     * @param string $protobufBody protobuf 序列化后的请求体。
     * @param float $timeout 客户端等待超时，单位秒。
     * @param int $timeoutMs 服务端请求超时，单位毫秒。
     * @return Packet 响应包，调用方负责解析 body。
     */
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

    /**
     * 接收一个 WebSocket 数据包。收到服务端心跳请求时会自动回复并继续等待。
     *
     * @param float $timeout 等待超时时间，单位秒。
     * @return Packet|null 超时返回 null。
     */
    public function recv(float $timeout = 10.0): ?Packet
    {
        if (!$this->client) {
            throw new RuntimeException('websocket not connected.');
        }

        $deadline = microtime(true) + $timeout;

        do {
            $left = max(0.001, $deadline - microtime(true));
            $frame = $this->client->recv($left);

            if ($frame === false || $frame === null) {
                return null;
            }

            if ($frame instanceof \Swoole\WebSocket\CloseFrame) {
                $this->close();
                return null;
            }

            $opcode = (int)($frame->opcode ?? 0);

            // 只解析业务二进制帧；ping/pong/close/text 都不要交给 LongbridgeCodec。
            if ($opcode !== \WEBSOCKET_OPCODE_BINARY) {
                if (defined('WEBSOCKET_OPCODE_PING') && $opcode === \WEBSOCKET_OPCODE_PING) {
                    $this->client->push($frame->data ?? '', \WEBSOCKET_OPCODE_PONG);
                }
                continue;
            }

            if (!isset($frame->data) || $frame->data === '') {
                continue;
            }

            $packet = $this->codec->decode($frame->data);
            if ($this->handleControlRequest($packet)) {
                continue;
            }

            return $packet;
        } while (microtime(true) < $deadline);

        return null;
    }

    /**
     * 发送响应包，主要用于回复服务端控制命令。
     *
     * @param int $cmdCode 命令号。
     * @param int $requestId 对应请求 ID。
     * @param string $body protobuf 序列化后的响应体。
     * @param int $status 响应状态码，0 表示成功。
     */
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

    /**
     * 关闭 WebSocket 连接并清理鉴权状态。
     */
    public function close(): void
    {
        if ($this->client) {
            $this->client->close();
            $this->client = null;
            $this->isConnected = false;
            $this->isAuthenticated = false;
        }
    }

    /**
     * 使用 socket OTP 完成 WebSocket 鉴权。
     *
     * @param string $otp 一次性 socket token。
     * @param float $timeout 鉴权请求超时时间，单位秒。
     * @return AuthResponse 鉴权响应 protobuf 对象。
     */
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

    /**
     * 使用已有 session_id 重新连接会话。
     *
     * @param string $sessionId 鉴权响应中的 session_id。
     * @param array<string,string> $metadata 可选元数据。
     * @param float $timeout 重连请求超时时间，单位秒。
     * @return ReconnectResponse 重连响应 protobuf 对象。
     */
    public function reconnect(string $sessionId, array $metadata = [], float $timeout = 10.0): ReconnectResponse
    {
        $sessionId = trim($sessionId);
        if ($sessionId === '') {
            throw new RuntimeException('Longbridge socket session id is empty.');
        }

        $request = new ReconnectRequest();
        $request->setSessionId($sessionId);
        $request->setMetadata($metadata);

        $packet = $this->request(
            cmdCode: ControlCommand::RECONNECT,
            protobufBody: $request->serializeToString(),
            timeout: $timeout,
            timeoutMs: (int)($timeout * 1000)
        );

        if (!$packet->isSuccess()) {
            throw new RuntimeException("Longbridge reconnect failed, status={$packet->status}");
        }

        $response = new ReconnectResponse();
        $response->mergeFromString($packet->body);
        $this->isAuthenticated = true;

        return $response;
    }

    /**
     * 当前连接是否已鉴权。
     */
    public function isAuthenticated(): bool
    {
        return $this->isAuthenticated;
    }

    /**
     * 当前 WebSocket 是否已连接。
     */
    public function isConnected(): bool
    {
        return $this->isConnected;
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
}
