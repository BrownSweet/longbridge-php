<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2026-05-15 14:58
 */

namespace Brown\Longbridge\Http;

final class LegacySigner
{
    private const ALG = 'HMAC-SHA256';

    private const SIGNED_HEADERS = [
        'authorization',
        'x-api-key',
        'x-timestamp',
    ];

    public function sign(
        string $method,
        string $path,
        string $queryString,
        array  $headers,
        string $body,
        string $appSecret
    ): string
    {
        $method = strtoupper($method);
        $normalizedHeaders = [];
        foreach ($headers as $key => $value) {
            $normalizedHeaders[strtolower($key)] = trim((string)$value);
        }

        $plain = $method . '|' . $path . '|' . $queryString . '|';

        $headerPlain = '';
        foreach (self::SIGNED_HEADERS as $headerName) {
            $headerPlain .= $headerName . ':' . ($normalizedHeaders[$headerName] ?? '') . "\n";
        }

        $plain .= $headerPlain;
        $plain .= '|' . implode(';', self::SIGNED_HEADERS) . '|';

        if ($body !== '') {
            $plain .= sha1($body);
        }

        $textToSign = self::ALG . '|' . sha1($plain);

        $signature = hash_hmac('sha256', $textToSign, $appSecret);

        return self::ALG
            . ' SignedHeaders=' . implode(';', self::SIGNED_HEADERS)
            . ', Signature=' . $signature;
    }
}