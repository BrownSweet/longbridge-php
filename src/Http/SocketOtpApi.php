<?php

declare(strict_types=1);

namespace Brown\Longbridge\Http;

use Brown\Longbridge\Exception\LongbridgeException;

final class SocketOtpApi
{
    public function __construct(
        private readonly ?HttpClientInterface $oauthClient,
        private readonly ?SocketTokenApi $legacyClient,
    ) {
        if ($this->oauthClient === null && $this->legacyClient === null) {
            throw new \InvalidArgumentException('OAuth token or legacy credentials are required for socket OTP.');
        }
    }

    public function getOtp(): string
    {
        if ($this->oauthClient !== null) {
            return $this->extractOtp(
                $this->oauthClient->get('/v1/socket/token'),
                '/v1/socket/token'
            );
        }

        return $this->legacyClient->getOtp();
    }

    public function refreshLegacySigner(): array
    {
        if ($this->legacyClient === null) {
            throw new \RuntimeException('Legacy credentials are not configured.');
        }

        return $this->legacyClient->refreshLegacySigner();
    }

    public function refreshLagacySigner(): array
    {
        return $this->refreshLegacySigner();
    }

    private function extractOtp(array $data, string $uri): string
    {
        $otp = (string)($data['otp'] ?? $data['Otp'] ?? '');
        if ($otp === '') {
            throw new LongbridgeException(
                message: 'Longbridge socket otp not found.',
                data: $data,
                uri: $uri,
            );
        }

        return $otp;
    }
}
