<?php

declare(strict_types=1);

namespace Brown\Longbridge\Socket;

use Brown\Longbridge\Proto\Control\AuthResponse;
use Brown\Longbridge\Proto\Control\ReconnectResponse;
use Brown\Longbridge\Protocol\Packet;

interface WsClientInterface
{
    public function connect(): void;

    public function sendRequest(
        int $cmdCode,
        string $protobufBody = '',
        int $timeoutMs = 10000
    ): int;

    public function request(
        int $cmdCode,
        string $protobufBody = '',
        float $timeout = 10.0,
        int $timeoutMs = 10000
    ): Packet;

    public function recv(float $timeout = 10.0): ?Packet;

    public function sendResponse(int $cmdCode, int $requestId, string $body = '', int $status = 0): void;

    public function close(): void;

    public function authenticate(string $otp, float $timeout = 10.0): AuthResponse;

    /**
     * @param array<string,string> $metadata
     */
    public function reconnect(string $sessionId, array $metadata = [], float $timeout = 10.0): ReconnectResponse;

    public function isAuthenticated(): bool;

    public function isConnected(): bool;
}
