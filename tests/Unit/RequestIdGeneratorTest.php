<?php

declare(strict_types=1);

namespace Brown\Longbridge\Tests\Unit;

use Brown\Longbridge\Socket\RequestIdGenerator;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class RequestIdGeneratorTest extends TestCase
{
    public function testGeneratesIncrementingIds(): void
    {
        $generator = new RequestIdGenerator();

        self::assertSame(1, $generator->next());
        self::assertSame(2, $generator->next());
    }

    public function testWrapsAfterUint32Max(): void
    {
        $generator = new RequestIdGenerator();
        $current = new ReflectionProperty($generator, 'current');
        $current->setValue($generator, 0xffffffff);

        self::assertSame(1, $generator->next());
    }
}
