<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */


declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Brown\Longbridge\Exception\LongbridgeException;
use Brown\Longbridge\LongbridgeClient;

$accessToken = $argv[1] ?? '';

if ($accessToken === '') {
    fwrite(STDERR, "Usage: php examples/oauth_client.php <access_token>\n");
    exit(1);
}

$client = LongbridgeClient::cnHttp($accessToken);

try {
    echo "== Account Balance ==\n";
    $balance = $client->asset()->getAccountBalance();
    echo json_encode($balance, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";

    echo "== Today Orders ==\n";
    $orders = $client->trade()->getTodayOrders();
    echo json_encode($orders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";

    echo "== Market Status ==\n";
    $marketStatus = $client->market()->marketStatus();
    echo json_encode($marketStatus, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";
} catch (LongbridgeException $e) {
    fwrite(STDERR, "Longbridge error: {$e->getMessage()}\n");
    fwrite(STDERR, "HTTP: " . ($e->httpStatus ?? 'null') . "\n");
    fwrite(STDERR, "Code: {$e->getCode()}\n");
    fwrite(STDERR, "Body: " . ($e->responseBody ?? '') . "\n");
    exit(2);
}
