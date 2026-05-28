<?php

declare(strict_types=1);

namespace Brown\Longbridge\Tests\Support;

use Brown\Longbridge\Proto\Control\AuthResponse;
use Brown\Longbridge\Proto\Control\ReconnectResponse;
use Brown\Longbridge\Protocol\Packet;
use Brown\Longbridge\Socket\WsClientInterface;
use RuntimeException;

final class SpyWsClient implements WsClientInterface
{
    /** @var list<array{cmdCode:int,protobufBody:string,timeout:float,timeoutMs:int}> */
    public array $requests = [];

    /** @var list<array{cmdCode:int,protobufBody:string,timeoutMs:int}> */
    public array $sentRequests = [];

    /** @var list<array{cmdCode:int,requestId:int,body:string,status:int}> */
    public array $responses = [];

    /** @var list<Packet> */
    private array $requestPackets;

    /** @var list<Packet|null> */
    private array $recvPackets;

    public bool $connected = false;
    public bool $authenticated = false;
    private int $nextRequestId = 1;

    /**
     * @param list<Packet> $requestPackets
     * @param list<Packet|null> $recvPackets
     */
    public function __construct(array $requestPackets = [], array $recvPackets = [])
    {
        $this->requestPackets = $requestPackets;
        $this->recvPackets = $recvPackets;
    }

    public function connect(): void
    {
        $this->connected = true;
    }

    public function sendRequest(
        int $cmdCode,
        string $protobufBody = '',
        int $timeoutMs = 10000
    ): int {
        $this->sentRequests[] = [
            'cmdCode' => $cmdCode,
            'protobufBody' => $protobufBody,
            'timeoutMs' => $timeoutMs,
        ];

        return $this->nextRequestId++;
    }

    public function request(
        int $cmdCode,
        string $protobufBody = '',
        float $timeout = 10.0,
        int $timeoutMs = 10000
    ): Packet {
        $this->requests[] = [
            'cmdCode' => $cmdCode,
            'protobufBody' => $protobufBody,
            'timeout' => $timeout,
            'timeoutMs' => $timeoutMs,
        ];

        $packet = array_shift($this->requestPackets);
        if (!$packet) {
            throw new RuntimeException('No websocket response packet was queued.');
        }

        return $packet;
    }

    public function recv(float $timeout = 10.0): ?Packet
    {
        return array_shift($this->recvPackets);
    }

    public function sendResponse(int $cmdCode, int $requestId, string $body = '', int $status = 0): void
    {
        $this->responses[] = [
            'cmdCode' => $cmdCode,
            'requestId' => $requestId,
            'body' => $body,
            'status' => $status,
        ];
    }

    public function close(): void
    {
        $this->connected = false;
        $this->authenticated = false;
    }

    public function authenticate(string $otp, float $timeout = 10.0): AuthResponse
    {
        $this->authenticated = true;

        return new AuthResponse();
    }

    public function reconnect(string $sessionId, array $metadata = [], float $timeout = 10.0): ReconnectResponse
    {
        $this->authenticated = true;

        return new ReconnectResponse();
    }

    public function isAuthenticated(): bool
    {
        return $this->authenticated;
    }

    public function isConnected(): bool
    {
        return $this->connected;
    }
}
