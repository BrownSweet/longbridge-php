<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */


declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Brown\Longbridge\OAuth\OAuthClient;
use Brown\Longbridge\Exception\LongbridgeException;

$clientId = $argv[1] ?? '';
$redirectUri = $argv[2] ?? '';
$code = $argv[3] ?? '';
$codeVerifier = $argv[4] ?? '';

if ($clientId === '' || $redirectUri === '' || $code === '' || $codeVerifier === '') {
    fwrite(STDERR, "Usage: php examples/exchange_code.php <client_id> <redirect_uri> <code> <code_verifier>\n");
    exit(1);
}

$oauth = new OAuthClient('https://openapi.longbridge.cn');

try {
    $token = $oauth->exchangeCode(
        clientId: $clientId,
        redirectUri: $redirectUri,
        code: $code,
        codeVerifier: $codeVerifier,
    );

    echo json_encode($token->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
} catch (LongbridgeException $e) {
    fwrite(STDERR, "Longbridge error: {$e->getMessage()}\n");
    fwrite(STDERR, "HTTP: " . ($e->httpStatus ?? 'null') . "\n");
    fwrite(STDERR, "Code: {$e->getCode()}\n");
    fwrite(STDERR, "Body: " . ($e->responseBody ?? '') . "\n");
    exit(2);
}
