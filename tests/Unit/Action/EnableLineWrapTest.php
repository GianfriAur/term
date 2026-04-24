<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Action\EnableLineWrap;
use PHPUnit\Framework\TestCase;

final class EnableLineWrapTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new EnableLineWrap(true));
    }

    public function testExposesEnableFlag(): void
    {
        self::assertTrue((new EnableLineWrap(true))->enable);
        self::assertFalse((new EnableLineWrap(false))->enable);
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('EnableLineWrap(true)', (string) new EnableLineWrap(true));
        self::assertSame('EnableLineWrap(false)', (string) new EnableLineWrap(false));
    }
}
