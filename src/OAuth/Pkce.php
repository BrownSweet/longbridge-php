<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2026-05-18 09:19
 */
declare(strict_types=1);
namespace Brown\Longbridge\OAuth;


namespace Brown\Longbridge\OAuth;

final class Pkce
{
    public static function generateCodeVerifier(int $bytes = 64): string
    {
        return self::base64UrlEncode(random_bytes($bytes));
    }

    public static function buildCodeChallenge(string $codeVerifier): string
    {
        return self::base64UrlEncode(hash('sha256', $codeVerifier, true));
    }

    private static function base64UrlEncode(string $raw): string
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }
}