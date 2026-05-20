<?php

declare(strict_types=1);

namespace Brown\Longbridge\Support;

use Google\Protobuf\Internal\Message;
use ReflectionMethod;

final class Protobuf
{
    /**
     * 将 protobuf 消息转为 snake_case PHP 数组，便于公开 API 统一返回轻量数组。
     */
    public static function messageToArray(Message $message): array
    {
        $result = [];
        $reflection = new \ReflectionClass($message);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isStatic() || $method->getNumberOfRequiredParameters() > 0) {
                continue;
            }

            $methodName = $method->getName();
            if (!str_starts_with($methodName, 'get')) {
                continue;
            }

            $declaringClass = $method->getDeclaringClass()->getName();
            if (!str_starts_with($declaringClass, 'Brown\\Longbridge\\Proto\\')) {
                continue;
            }

            $field = self::camelToSnake(substr($methodName, 3));
            $result[$field] = self::normalize($message->{$methodName}());
        }

        return $result;
    }

    /**
     * 反序列化指定 protobuf 响应类并转为 snake_case 数组。
     *
     * @param class-string<Message> $messageClass
     */
    public static function decode(string $body, string $messageClass): array
    {
        $message = new $messageClass();
        $message->mergeFromString($body);

        return self::messageToArray($message);
    }

    private static function normalize(mixed $value): mixed
    {
        if ($value instanceof Message) {
            return self::messageToArray($value);
        }

        if ($value instanceof \Traversable) {
            $items = [];
            foreach ($value as $key => $item) {
                $items[$key] = self::normalize($item);
            }

            return $items;
        }

        if (is_array($value)) {
            return array_map(static fn (mixed $item): mixed => self::normalize($item), $value);
        }

        return $value;
    }

    private static function camelToSnake(string $value): string
    {
        $value = preg_replace('/(?<!^)[A-Z]/', '_$0', $value) ?? $value;

        return strtolower($value);
    }
}
