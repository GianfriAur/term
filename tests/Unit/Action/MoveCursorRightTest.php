<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Action\MoveCursorRight;
use PHPUnit\Framework\TestCase;

final class MoveCursorRightTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new MoveCursorRight(1));
    }

    public function testExposesCols(): void
    {
        self::assertSame(5, (new MoveCursorRight(5))->cols);
        self::assertSame(0, (new MoveCursorRight(0))->cols);
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('MoveCursorRight(3)', (string) new MoveCursorRight(3));
    }
}
