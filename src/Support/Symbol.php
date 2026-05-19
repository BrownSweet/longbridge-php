<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */

declare(strict_types=1);

namespace Brown\Longbridge\Support;

final class Symbol
{
    /**
     * 转为账户/基本面接口使用的 counter_id，例如 AAPL.US => ST/US/AAPL。
     */
    public static function toSecurityCounterId(string $symbol): string
    {
        $symbol = trim($symbol);
        $pos = strrpos($symbol, '.');
        if ($symbol === '' || $pos === false) {
            return $symbol;
        }

        $code = substr($symbol, 0, $pos);
        $market = strtoupper(substr($symbol, $pos + 1));

        return 'ST/' . $market . '/' . $code;
    }

    /**
     * 转为部分行情 HTTP 接口使用的 counter_id，例如 700.HK => 700_HK。
     */
    public static function toQuoteCounterId(string $symbol): string
    {
        return str_replace('.', '_', trim($symbol));
    }

    /**
     * 转为指数成分股接口使用的 counter_id，例如 HSI.HK => IX_HSI_HK。
     */
    public static function toIndexCounterId(string $symbol): string
    {
        $symbol = trim($symbol);
        $pos = strrpos($symbol, '.');
        if ($symbol === '' || $pos === false) {
            return $symbol;
        }

        return 'IX_' . substr($symbol, 0, $pos) . '_' . strtoupper(substr($symbol, $pos + 1));
    }

    /**
     * 将官方 counter_id 转回 ticker.region 形式，无法识别时返回原值。
     */
    public static function fromSecurityCounterId(string $counterId): string
    {
        $parts = explode('/', trim($counterId), 3);
        if (count($parts) === 3) {
            return $parts[2] . '.' . $parts[1];
        }

        $pos = strrpos($counterId, '_');
        if ($pos !== false) {
            return substr($counterId, 0, $pos) . '.' . substr($counterId, $pos + 1);
        }

        return $counterId;
    }

    /**
     * 清理 symbol 数组，过滤空值并重建连续索引。
     */
    public static function normalizeList(array $symbols): array
    {
        return array_values(array_filter(array_map(
            static fn (mixed $symbol): string => trim((string)$symbol),
            $symbols
        )));
    }
}
