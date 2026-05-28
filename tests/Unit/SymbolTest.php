<?php

declare(strict_types=1);

namespace Brown\Longbridge\Tests\Unit;

use Brown\Longbridge\Support\Symbol;
use PHPUnit\Framework\TestCase;

final class SymbolTest extends TestCase
{
    public function testConvertsSymbolsToLongbridgeCounterIds(): void
    {
        self::assertSame('ST/US/AAPL', Symbol::toSecurityCounterId('AAPL.US'));
        self::assertSame('700_HK', Symbol::toQuoteCounterId('700.HK'));
        self::assertSame('IX_HSI_HK', Symbol::toIndexCounterId('HSI.HK'));
        self::assertSame('AAPL.US', Symbol::fromSecurityCounterId('ST/US/AAPL'));
        self::assertSame('700.HK', Symbol::fromSecurityCounterId('700_HK'));
    }

    public function testNormalizeListTrimsAndDropsEmptyValues(): void
    {
        self::assertSame(['AAPL.US', '700.HK'], Symbol::normalizeList([' AAPL.US ', '', null, '700.HK']));
    }
}
