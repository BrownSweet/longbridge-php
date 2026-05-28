<?php

declare(strict_types=1);

namespace Brown\Longbridge\Tests\Unit;

use Brown\Longbridge\OAuth\Pkce;
use PHPUnit\Framework\TestCase;

final class PkceTest extends TestCase
{
    public function testBuildsRfc7636CodeChallenge(): void
    {
        $verifier = 'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk';

        self::assertSame(
            'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM',
            Pkce::buildCodeChallenge($verifier)
        );
    }

    public function testGeneratedCodeVerifierIsUrlSafe(): void
    {
        $verifier = Pkce::generateCodeVerifier(64);

        self::assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $verifier);
        self::assertGreaterThanOrEqual(43, strlen($verifier));
    }
}
