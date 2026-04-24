<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Action\SetCursorStyle;
use PhpTui\Term\CursorStyle;
use PHPUnit\Framework\TestCase;

final class SetCursorStyleTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new SetCursorStyle(CursorStyle::SteadyBlock));
    }

    public function testExposesCursorStyle(): void
    {
        foreach (CursorStyle::cases() as $style) {
            self::assertSame($style, (new SetCursorStyle($style))->cursorStyle);
        }
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('SetCursorStyle(SteadyBlock)', (string) new SetCursorStyle(CursorStyle::SteadyBlock));
        self::assertSame('SetCursorStyle(BlinkingBar)', (string) new SetCursorStyle(CursorStyle::BlinkingBar));
    }
}
