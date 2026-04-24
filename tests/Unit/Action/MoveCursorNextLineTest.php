<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Action\MoveCursorNextLine;
use PHPUnit\Framework\TestCase;

final class MoveCursorNextLineTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new MoveCursorNextLine(1));
    }

    public function testExposesNbLines(): void
    {
        self::assertSame(5, (new MoveCursorNextLine(5))->nbLines);
        self::assertSame(0, (new MoveCursorNextLine(0))->nbLines);
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('MoveCursorNextLine(3)', (string) new MoveCursorNextLine(3));
    }
}
