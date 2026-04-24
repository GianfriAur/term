<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Action\MoveCursorPrevLine;
use PHPUnit\Framework\TestCase;

final class MoveCursorPrevLineTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new MoveCursorPrevLine(1));
    }

    public function testExposesNbLines(): void
    {
        self::assertSame(5, (new MoveCursorPrevLine(5))->nbLines);
        self::assertSame(0, (new MoveCursorPrevLine(0))->nbLines);
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('MoveCursorPrevLine(3)', (string) new MoveCursorPrevLine(3));
    }
}
