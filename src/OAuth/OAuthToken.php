<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */

namespace Brown\Longbridge\OAuth;

final class OAuthToken
{
    public function __construct(
        public readonly string $accessToken,
        public readonly string $refreshToken = '',
        public readonly string $tokenType = 'Bearer',
        public readonly int $expiresIn = 0,
        public readonly ?int $expiresAt = null,
        public readonly array $raw = [],
    ) {
    }

    public static function fromArray(array $data, ?int $now = null): self
    {
        $now ??= time();

        $accessToken = trim((string)($data['access_token'] ?? ''));
        if ($accessToken === '') {
            throw new \InvalidArgumentException('OAuth response missing access_token.');
        }

        $expiresIn = (int)($data['expires_in'] ?? 0);

        return new self(
            accessToken: $accessToken,
            refreshToken: (string)($data['refresh_token'] ?? ''),
            tokenType: (string)($data['token_type'] ?? 'Bearer'),
            expiresIn: $expiresIn,
            expiresAt: $expiresIn > 0 ? $now + $expiresIn : null,
            raw: $data,
        );
    }

    public function authorizationHeader(): string
    {
        return trim($this->tokenType) . ' ' . $this->accessToken;
    }

    public function isExpired(int $skewSeconds = 300, ?int $now = null): bool
    {
        if ($this->expiresAt === null) {
            return false;
        }

        $now ??= time();

        return $this->expiresAt <= $now + $skewSeconds;
    }

    public function toArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'token_type' => $this->tokenType,
            'expires_in' => $this->expiresIn,
            'expires_at' => $this->expiresAt,
        ];
    }
}