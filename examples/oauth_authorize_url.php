<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */


declare(strict_types=1);



require_once dirname(__DIR__) . '/vendor/autoload.php';

use Brown\Longbridge\OAuth\OAuthClient;
use Brown\Longbridge\OAuth\Pkce;

$clientId = $argv[1] ?? '';
$redirectUri = $argv[2] ?? '';

if ($clientId === '' || $redirectUri === '') {
    fwrite(STDERR, "Usage: php examples/oauth_authorize_url.php <client_id> <redirect_uri>\n");
    exit(1);
}

$state = bin2hex(random_bytes(20));
$codeVerifier = Pkce::generateCodeVerifier();
$codeChallenge = Pkce::buildCodeChallenge($codeVerifier);

$oauth = new OAuthClient('https://openapi.longbridge.cn');

echo "state:\n{$state}\n\n";
echo "code_verifier, save it for callback exchange:\n{$codeVerifier}\n\n";
echo "authorize_url:\n";
echo $oauth->buildAuthorizeUrl(
        clientId: $clientId,
        redirectUri: $redirectUri,
        state: $state,
        codeChallenge: $codeChallenge,
        scope: '3',
    ) . PHP_EOL;
