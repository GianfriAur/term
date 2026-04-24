<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Action\MoveCursor;
use PHPUnit\Framework\TestCase;

final class MoveCursorTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new MoveCursor(1, 1));
    }

    public function testExposesLineAndCol(): void
    {
        $action = new MoveCursor(3, 7);

        self::assertSame(3, $action->line);
        self::assertSame(7, $action->col);
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('MoveCursor(line=3,col=7)', (string) new MoveCursor(3, 7));
    }
}
