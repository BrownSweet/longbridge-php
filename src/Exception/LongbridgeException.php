<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */

declare(strict_types=1);

namespace Brown\Longbridge\Exception;

use RuntimeException;
use Throwable;

final class LongbridgeException extends RuntimeException
{
    public function __construct(
        string $message,
        int $code = 0,
        public readonly ?int $httpStatus = null,
        public readonly mixed $data = null,
        public readonly ?string $responseBody = null,
        public readonly ?string $uri = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function invalidJson(
        int $httpStatus,
        string $responseBody,
        ?string $uri = null
    ): self {
        return new self(
            message: "Longbridge response is not valid JSON.",
            code: 0,
            httpStatus: $httpStatus,
            responseBody: $responseBody,
            uri: $uri,
        );
    }

    public static function apiError(
        int $httpStatus,
        int $apiCode,
        string $message,
        mixed $data = null,
        ?string $responseBody = null,
        ?string $uri = null
    ): self {
        return new self(
            message: $message,
            code: $apiCode,
            httpStatus: $httpStatus,
            data: $data,
            responseBody: $responseBody,
            uri: $uri,
        );
    }

    public function isTokenInvalid(): bool
    {
        return $this->httpStatus === 401
            || in_array($this->getCode(), [401, 403101], true);
    }

    public function isRateLimited(): bool
    {
        return $this->httpStatus === 429
            || in_array($this->getCode(), [429, 429001], true);
    }
}