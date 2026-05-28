<?php

declare(strict_types=1);

namespace Brown\Longbridge\Tests\Unit;

use Brown\Longbridge\OAuth\OAuthToken;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class OAuthTokenTest extends TestCase
{
    public function testCreatesTokenFromOAuthResponse(): void
    {
        $token = OAuthToken::fromArray([
            'access_token' => 'access-token',
            'refresh_token' => 'refresh-token',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ], 1_700_000_000);

        self::assertSame('Bearer access-token', $token->authorizationHeader());
        self::assertSame(1_700_003_600, $token->expiresAt);
        self::assertFalse($token->isExpired(300, 1_700_003_000));
        self::assertTrue($token->isExpired(300, 1_700_003_301));
        self::assertSame([
            'access_token' => 'access-token',
            'refresh_token' => 'refresh-token',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'expires_at' => 1_700_003_600,
        ], $token->toArray());
    }

    public function testRequiresAccessToken(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('OAuth response missing access_token.');

        OAuthToken::fromArray([]);
    }
}
