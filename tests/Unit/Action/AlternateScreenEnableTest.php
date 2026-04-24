<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PHPUnit\Framework\TestCase;
use PhpTui\Term\Action;
use PhpTui\Term\Action\AlternateScreenEnable;

final class AlternateScreenEnableTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new AlternateScreenEnable(true));
    }

    public function testExposesEnableFlag(): void
    {
        self::assertTrue((new AlternateScreenEnable(true))->enable);
        self::assertFalse((new AlternateScreenEnable(false))->enable);
    }

    public function testStringRepresentation(): void
    {
        self::assertSame(
            'AlternateScreenEnable(true)',
            (string) new AlternateScreenEnable(true),
        );
        self::assertSame(
            'AlternateScreenEnable(false)',
            (string) new AlternateScreenEnable(false),
        );
    }
}
