<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Action\SetRgbBackgroundColor;
use PHPUnit\Framework\TestCase;

final class SetRgbBackgroundColorTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new SetRgbBackgroundColor(0, 0, 0));
    }

    public function testExposesRgbComponents(): void
    {
        $action = new SetRgbBackgroundColor(255, 100, 50);

        self::assertSame(255, $action->r);
        self::assertSame(100, $action->g);
        self::assertSame(50, $action->b);
    }

    public function testStringRepresentation(): void
    {
        self::assertSame(
            'SetRgbBackgroundColor(255, 100, 50)',
            (string) new SetRgbBackgroundColor(255, 100, 50),
        );
    }
}
