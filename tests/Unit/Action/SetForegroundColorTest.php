<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Action\SetForegroundColor;
use PhpTui\Term\Colors;
use PHPUnit\Framework\TestCase;

final class SetForegroundColorTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new SetForegroundColor(Colors::Red));
    }

    public function testExposesColor(): void
    {
        foreach (Colors::cases() as $color) {
            self::assertSame($color, (new SetForegroundColor($color))->color);
        }
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('SetForegroundColor(Red)', (string) new SetForegroundColor(Colors::Red));
        self::assertSame('SetForegroundColor(LightBlue)', (string) new SetForegroundColor(Colors::LightBlue));
    }
}
