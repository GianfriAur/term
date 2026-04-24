<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Action\SetBackgroundColor;
use PhpTui\Term\Colors;
use PHPUnit\Framework\TestCase;

final class SetBackgroundColorTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new SetBackgroundColor(Colors::Red));
    }

    public function testExposesColor(): void
    {
        foreach (Colors::cases() as $color) {
            self::assertSame($color, (new SetBackgroundColor($color))->color);
        }
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('SetBackgroundColor(Red)', (string) new SetBackgroundColor(Colors::Red));
        self::assertSame('SetBackgroundColor(LightBlue)', (string) new SetBackgroundColor(Colors::LightBlue));
    }
}
